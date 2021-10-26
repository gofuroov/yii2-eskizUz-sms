<?php

/**
 * @author Olimjon G'ofurov <gofuroov@gmail.com>
 * Date: 08/10/21
 * Time: 22:19
 */

namespace backend\modules\smsgo\models;

use http\Exception\InvalidArgumentException;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;

class Sms extends \yii\base\Component
{
    public const MAIN_NUMBER = '4546';
    public const CALL_BACK_URL = "https://test.uz";

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
        $response = $this->request("auth/refresh", [], 'PATCH');
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
     * @return array
     * @throws ConflictHttpException
     */
    private function request(string $url, array $data = [], string $type = 'POST'): array
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
            CURLOPT_HTTPHEADER => ["Authorization: Bearer " . SmsSetting::getToken()],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return Json::decode($response);
    }

    /**
     * @throws ConflictHttpException
     * @throws HttpException
     */
    public function getNewToken(): string
    {
        $response = $this->request("auth/login", ['email' => SmsSetting::getEmail(), 'password' => SmsSetting::getPassword()]);
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
     */
    public function send(string $phone, string $message, int $user_id = null, string $from = self::MAIN_NUMBER): bool
    {
        $phone = $this->phoneNormalize($phone);

        $sms = new SmsHistory([
            'user_id' => $user_id,
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
            ], 'POST');

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
     * @return bool
     * @throws ConflictHttpException
     */
    public function sendBatch(array $messages): bool
    {
        $response = $this->request("message/sms/send-batch", [
            'messages' => $messages,
            'from' => '4546',
            'dispatch_id' => 123
        ]);
        if (isset($response['status']) && $response['status'] === 'success') {
            return true;
        }
        Yii::error($response);
        var_dump($response);
        exit();
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
     * @param int $sms_history_id
     * @return bool
     * @throws ConflictHttpException
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
     */
    public function getUserInfo(): array
    {
        return $this->request("auth/user", [], 'GET');
    }
}