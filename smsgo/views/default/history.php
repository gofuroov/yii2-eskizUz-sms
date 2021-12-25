<?php
/**
 * @author Olimjon G'ofurov <gofuroov@gmail.com>
 * Date: 10/10/21
 * Time: 14:02
 *
 * @var $this \yii\web\View
 * @var $searchModel \backend\modules\smsgo\models\search\SmsHistorySearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 */


use backend\modules\smsgo\models\SmsHistory;
use yii\bootstrap4\Html;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;

$this->title = "Smslar tarixi";

$this->params['breadcrumbs'][] = ['label' => "Sms", 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="row">
        <div class="col">
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-info-circle text-warning mr-2"></i>
                        Sms statuslari haqida ma'lumot
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <ul>
                        <li>
                            Waiting - СМС в ожидании отправления оператору;
                        </li>
                        <li>
                            TRANSMTD - СМС передан сотовому оператору, но со стороны оператора обратно не получено
                            статус смс
                            сообщений;
                        </li>
                        <li>
                            DELIVRD - доставлено;
                        </li>
                        <li>
                            UNDELIV - недоставлено, обычно причиной может быть то что абонент блокируется со стороны
                            оператора(недостаточно средст или долг);
                        </li>
                        <li>
                            EXPIRED - срок жизни смс истек(когда абонент в течение сутки не выходил на связь. У билайн
                            если в
                            теение часа);
                        </li>
                        <li>
                            REJECTD - один из основных причин это то что номер находится в черном списке;
                        </li>
                        <li>
                            DELETED - ошибка при отправки запроса(например когда адрес отправителя указан неверно);
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
                'columns' => [
                    [
                        'class' => SerialColumn::class,
                        'contentOptions' => static function (SmsHistory $model) {
                            $class = "text-center ";
                            if ($model->status === SmsHistory::STATUS_DELIVERED) {
                                $class .= "bg-success";
                            } elseif ($model->status === SmsHistory::STATUS_WAITING) {
                                $class .= "bg-warning";
                            } elseif ($model->status === null || $model->status === SmsHistory::STATUS_OTHER) {
                                $class .= "bg-danger";
                            } else {
                                $class .= "bg-primary";
                            }
                            return ['class' => $class];
                        }
                    ],
                    'phone',
                    [
                        'attribute' => 'user_id',
                        'value' => static function (SmsHistory $model) {
                            return isset($model->user->fullname) ? "{$model->user->fullname} ({$model->user->userType->name})" : "-";
                        }
                    ],
                    [
                        'attribute' => 'status',
                        'value' => static function (SmsHistory $model) {
                            return $model->renderStatus();
                        }
                    ],
                    'updated_at:datetime',
                    [
                        'attribute' => 'created_by',
                        'value' => static function (SmsHistory $model) {
                            return $model->createdBy->fullname;
                        }
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => "{view} {checkStatus} {resend}",
                        'buttons' => [
                            'view' => static function ($url, $model, $key) {
                                return Html::a("<i class='fa fa-eye text-success'></i>", ['default/sms-view', 'sms_history_id' => $key], [
                                    'class' => 'link',
                                    "data-toggle" => "tooltip",
                                    "title" => "Sms haqidagi ma'lumotni ko'rish"
                                ]);
                            },
                            'checkStatus' => static function ($url, $model, $key) {
                                return Html::a("<i class='fa fa-rotate text-info'></i>", ['default/check-status', 'id' => $key], [
                                    "data-toggle" => "tooltip",
                                    "title" => "Sms statusini tekshirish"
                                ]);
                            },
                            'resend' => static function ($url, $model, $key) {
                                return Html::a("<i class='fa fa-square-up-right text-danger'></i>", ['default/resend-sms', 'id' => $key], [
                                    "data-toggle" => "tooltip",
                                    "title" => "Smsni qayta jo'natish"
                                ]);
                            },
                        ],
                        'visibleButtons' => [
                            'checkStatus' => static function (SmsHistory $smsHistory) {
                                return $smsHistory->status !== SmsHistory::STATUS_DELIVERED;
                            },
                            'resend' => static function (SmsHistory $smsHistory) {
                                return $smsHistory->status === null;
                            },
                        ]
                    ]
                ]
            ])
            ?>
        </div>
    </div>

<?php

$js = <<<JS
$('.link').click(function (e){
    e.preventDefault();
    let url = $(this).attr('href')
    $.ajax({
            type: "GET",
            url: url,
            data: '',
            success: function (data, status) {
                $("#modal-body").html(data)
                $("#modal").modal("show")
            },
            error: function (data, status) {
                alert("Xatolik!");
            },
            dataType: 'html'
        });
})
JS;

$this->registerJs($js);