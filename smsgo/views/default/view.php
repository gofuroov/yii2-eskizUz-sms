<?php

/**
 * @var $this \yii\web\View
 * @var $model \backend\modules\smsgo\models\SmsSetting
 */

use yii\bootstrap4\Html;

$this->title = $model->info;

$this->params['breadcrumbs'][] = ['label' => $model->info, 'url' => ['settings']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['update'] = Html::a("<i class='fa fa-pencil-alt'></i>", ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
?>

<div class="card">
    <div class="card-body">
        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'label' => $model->info,
                    'value' => $model->value,
                ],
                'name',
                'info',
                'value',
                'created_at:datetime',
                'updated_at:datetime',
                [
                    'attribute' => 'created_by',
                    'value' => $model->createdBy->fullname ?? '-',
                ],
                [
                    'attribute' => 'updated_by',
                    'value' => $model->updatedBy->fullname ?? '-',
                ],
            ]
        ]) ?>
    </div>
</div>