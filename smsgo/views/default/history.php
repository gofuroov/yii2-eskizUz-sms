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
                            } else {
                                $class .= "bg-danger";
                            }
                            return ['class' => $class];
                        }
                    ],
                    'phone',
                    [
                        'attribute' => 'user_id',
                        'value' => static function (SmsHistory $model) {
                            return $model->user->fullname ?? "-";
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
                        'template' => "{view}",
                        'buttons' => [
                            'view' => static function ($url, $model, $key) {
                                return Html::a("<i class='fa fa-eye text-success'></i>", ['default/sms-view', 'sms_history_id' => $key], ['class' => 'link']);
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