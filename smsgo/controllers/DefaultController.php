<?php

namespace backend\modules\smsgo\controllers;

use backend\modules\smsgo\models\search\SmsHistorySearch;
use backend\modules\smsgo\models\SendSmsForm;
use backend\modules\smsgo\models\Sms;
use backend\modules\smsgo\models\SmsHistory;
use backend\modules\smsgo\models\SmsSetting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ConflictHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `smsgo` module
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['call-back'],
                        'roles' => ['@', '?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'call-back' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id === "call-back") {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $response = Yii::$app->cache->getOrSet('getInfo', function () {
            return (new Sms())->getInfo();
        }, 60);

        $model = new SendSmsForm();

        if ($model->load($this->request->post())) {
            if ((new Sms())->send($model->phone, $model->text)) {
                Yii::$app->session->setFlash('success', "Xabar «{$model->phone}» raqamiga jo'natildi.");
            } else {
                Yii::$app->session->setFlash('error', "Xabar jo'natishda xatolik yuz berdi.");
            }
            return $this->refresh();
        }

        return $this->render('index', [
            'response' => $response,
            'model' => $model,
        ]);
    }

    /**
     * Sms settings
     * @return string
     */
    public function actionSettings(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SmsSetting::find(),
        ]);

        return $this->render('settings', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return SmsSetting
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): SmsSetting
    {
        if (($model = SmsSetting::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $id
     * @return SmsHistory
     * @throws NotFoundHttpException
     */
    protected function findSms(int $id): SmsHistory
    {
        if (($model = SmsHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws ConflictHttpException
     * @throws \yii\web\HttpException
     */
    public function actionUpdateToken(): \yii\web\Response
    {
        $session = Yii::$app->session;

        if ((new Sms())->updateToken()) {
            $session->setFlash('success', "Token yangilandi.");
        } else {
            $session->setFlash('error', "Token yangilashda xatolik. Email va parolingizni tekshiring");
        }
        return $this->redirect(['settings']);
    }

    /**
     * @return string
     */
    public function actionHistory(): string
    {
        $searchModel = new SmsHistorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('history', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionCallBack(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = $this->request->post();

        $model = SmsHistory::findOne([
            'message_id' => (int)($post['message_id'] ?? 0)
        ]);
        if (is_null($model)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model->setStatus($post['status'] ?? '');
        $model->status_date = date('Y-m-d H:i:s', strtotime($post['status_date']));
        if ($model->save()) {
            return ['message' => 'Status successfully changed.'];
        }
        return $model->errors;
    }

    /**
     * @param int|null $sms_history_id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionSmsView(int $sms_history_id = null): string
    {
        if (!is_null($sms_history_id)) {
            $model = SmsHistory::findOne($sms_history_id);
            if (!is_null($model)) {
                if ($this->request->isAjax) {
                    return $this->renderAjax('sms-view', [
                        'model' => $model
                    ]);
                }
                return $this->render('sms-view', [
                    'model' => $model
                ]);
            }
        }
        throw new NotFoundHttpException("Sahifa topilmadi.");
    }

    /**
     * @param int $id
     * @return Response
     * @throws ConflictHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\HttpException
     */
    public function actionCheckStatus(int $id): Response
    {
        $sms = $this->findSms($id);

        if ($sms->message_id === null) {
            Yii::$app->session->setFlash('info', "Ushbu sms serverga yuborilmagan. Qayta jo'natish tavsiya qilinadi.");
            return $this->redirect($this->request->referrer);
        }
        $response = (new Sms())->getStatus($sms->message_id);

        if ($response['status'] === "success") {
            $sms->setStatus($response['message']['status'] ?? '');
            $sms->save();
        } else {
            Yii::$app->session->setFlash('danger', $response['message'] ?? "Tizimda xatolik");
        }
        return $this->redirect($this->request->referrer);
    }

    /**
     * @param int $id
     * @return Response
     * @throws ConflictHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\HttpException
     */
    public function actionResendSms(int $id): Response
    {
        $sms = $this->findSms($id);

        $sent = (new Sms())->send($sms->phone, $sms->message, $sms->user_id);

        if ($sent) {
            $sms->delete();
        }
        return $this->redirect($this->request->referrer);
    }
}
