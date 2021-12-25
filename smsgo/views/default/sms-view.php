<?php
/**
 * @author Olimjon G'ofurov <gofuroov@gmail.com>
 * Date: 11/10/21
 * Time: 06:34
 *
 * @var $this \yii\web\View
 * @var $model \backend\modules\smsgo\models\SmsHistory
 */


$this->title = "Sms: " . $model->message_id;

$this->params['breadcrumbs'][] = ['label' => "Sms", 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => "Smslar tarixi", 'url' => ['history']];
$this->params['breadcrumbs'][] = $this->title;


echo \yii\widgets\DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        [
            'attribute' => 'user_id',
            'value' => $model->user->fullname ?? '-'
        ],
        'phone',
        'message:ntext',
        'from',
        'callback_url',
        [
            'attribute' => 'status',
            'value' => $model->renderStatus()
        ],
        'message_id',
        'status_date:datetime',
        'created_at:datetime',
        'updated_at:datetime',
        [
            'attribute' => 'created_by',
            'value' => $model->createdBy->fullname ?? "-"
        ],
        [
            'attribute' => 'updated_by',
            'value' => $model->updatedBy->fullname ?? "-"
        ],
    ]
]);