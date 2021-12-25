<?php

/**
 * @author Olimjon G'ofurov <gofuroov@gmail.com>
 * Date: 08/10/21
 * Time: 22:19
 */

namespace backend\modules\smsgo\models;

use common\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;

/**
 *
 * @property-read array $userInfo
 * @property-read string $newToken
 * @property-read null|mixed $info
 * @property-write int $status
 */
class Sms extends \yii\base\Component
{
    public $main_number = '4545';

    public function init()
    {
        $this->main_number = Yii::$app->params['smsNumber'] ?? $this->main_number;
        parent::init();
    }

    /**
     * Base Url for cURL
     * @var string
     */
    public $baseUrl = 'https://notify.eskiz.uz/api/';

    /**
     * Update token and save it
     * @return bool
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function updateToken(): bool
    {
        $response = $this->request("auth/refresh", [], 'PATCH', true, false);

        if ($response['message'] === "token_refreshed") {
            if (SmsSetting::updateToken($response['data']['token'])) {
                return true;
            }
            return false;
        }
        if (SmsSetting::updateToken($this->getNewToken())) {
            return true;
        }

        Yii::error($response);
        Yii::$app->session->setFlash('warning', $response['message']);
        return false;
    }

    /**
     * Call to API and fetch data
     * @param string $url
     * @param array $data
     * @param string $type
     * @param bool $bearerAuth
     * @param bool $checkTokenExpired
     * @return array
     * @throws ConflictHttpException
     * @throws HttpException
     */
    private function request(string $url, array $data = [], string $type = 'POST', bool $bearerAuth = true, bool $checkTokenExpired = true): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $bearerAuth ? ["Authorization: Bearer " . SmsSetting::getToken()] : [],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $result = Json::decode($response);

        if ($checkTokenExpired && $result['message'] === 'Token has expired' && $this->updateToken()) {
            Yii::$app->session->setFlash('info', 'Token yangilandi');
            return $this->request($url, $data, $type, $bearerAuth, false);
        }
        return $result;
    }

    /**
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function getNewToken(): string
    {
        $response = $this->request("auth/login", ['email' => SmsSetting::getEmail(), 'password' => SmsSetting::getPassword()], 'POST', false);

        if ($response['message'] === "token_generated") {
            return $response['data']['token'];
        }
        if ($response['message'] === "invalid_credentials") {
            throw new UnauthorizedHttpException("Sms xizmati uchun kiritilgan email yoki parol xato. Iltimos tekshirib qayta tering.");
        }
        throw new HttpException($response['message']);
    }

    /**
     * @return mixed|null
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function getInfo()
    {
        $response = $this->request("auth/user", [], 'GET');
        if ($response['message'] === "authenticated_user") {
            return $response['data'];
        }
        Yii::$app->session->setFlash('error', $response['message']);
        return null;
    }

    /**
     * @param string $phone
     * @param string $message
     * @param int|null $user_id
     * @param string $from
     * @return bool
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function send(string $phone, string $message, int $user_id = null): bool
    {
        $from = $this->main_number;
        
        $phone = $this->phoneNormalize($phone);

        $user = User::findOne(['phone' => $this->denormalize($phone)]);

        $sms = new SmsHistory([
            'user_id' => $user_id ?? $user->id ?? null,
            'phone' => $phone,
            'message' => $message,
            'from' => $from,
            'callback_url' => Url::to(['default/call-back'], true),
        ]);

        $errors = [];

        if ($sms->save()) {
            $response = $this->request("message/sms/send", [
                'mobile_phone' => $phone,
                'message' => $message,
                'from' => $from,
                'callback_url' => Url::to(['default/call-back'], true)
            ]);

            if (!isset($response['status_code'])) {
                $sms->message_id = (int)$response['id'];
                $sms->status = $response['status'] === "waiting" ? SmsHistory::STATUS_WAITING : SmsHistory::STATUS_OTHER;
                $sms->save();

                return true;
            }
            $errors[] = $response;
        }
        $errors[] = $sms->errors;
        Yii::error([$errors]);
        return false;
    }

    /**
     * @param array $messages
     * @return bool
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function sendBatch(array $messages): bool
    {
        $response = $this->request("message/sms/send-batch", [
            'messages' => $messages,
            'from' => $this->main_number,
            'dispatch_id' => 123
        ]);
        if (isset($response['status']) && $response['status'] === 'success') {
            return true;
        }
        Yii::error($response);
        return false;
    }

    /**
     * @param string $phone
     * @return string
     */
    private function phoneNormalize(string $phone): string
    {
        $phone = preg_replace("/[^\d]/", "", $phone);
        if (strlen($phone) === 12) {
            return $phone;
        }
        throw new InvalidArgumentException("Telefon raqami notog'ri {$phone}");
    }

    /**
     * Denormalize phone number
     * input: 998991234567
     * output: +998 99 123 45 67
     * @param string $phone
     * @return string
     */
    private function denormalize(string $phone): string
    {
        if (strlen($phone) === 12) {
            $p = $phone;
            return "+{$p[0]}{$p[1]}{$p[2]} {$p[3]}{$p[4]} {$p[5]}{$p[6]}{$p[7]} {$p[8]}{$p[9]} {$p[10]}{$p[11]}";
        }
        throw new InvalidArgumentException("Telefon raqami notog'ri {$phone}");
    }

    /**
     * @param int $sms_history_id
     * @return bool
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function setStatus(int $sms_history_id): bool
    {
        $sms = SmsHistory::findOne($sms_history_id);
        if (!is_null($sms) && !is_null($sms->message_id)) {
            $response = $this->request("message/sms/status/{$sms->message_id}", [], 'GET');
            if ($response['status'] === "success") {
                $sms->status = $response['message']['status'];
                $sms->status_date = $response['message']['status_date'];
                if ($sms->save()) {
                    return true;
                }
                Yii::error($sms->errors);
            }
        }
        return false;
    }

    /**
     * @return array
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function getUserInfo(): array
    {
        return $this->request("auth/user", [], 'GET');
    }

    /**
     * @param int $message_id
     * @return array
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function getStatus(int $message_id): array
    {
        return $this->request("message/sms/status/{$message_id}", [], 'GET');
    }
}