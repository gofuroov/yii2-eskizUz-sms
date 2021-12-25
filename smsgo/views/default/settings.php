<?php

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use yii\bootstrap4\Html;
use yii\grid\GridView;

$this->title = "Sms sozlamalari";
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card">
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => \yii\grid\SerialColumn::class],
                'info',
                [
                    'attribute' => 'value',
                    'format' => 'raw',
                    'contentOptions' => [
                        'style' => 'word-wrap:anywhere',
                    ],
                ],
                'updated_at:datetime',
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => "{update}",
                    'buttons' => [
                        'update' => static function ($url, $model, $key) {
                            if ($model->name === 'token') {
                                return Html::a('<i class="fa fa-sync"></i>',
                                    ['update-token'],
                                    [
                                        'title' => 'Tokenni yangilash',
                                    ]);
                            }
                            return Html::a('<i class="fa fa-edit"></i>',
                                ['update', 'id' => $key]);
                        }
                    ]
                ]
            ]
        ])
        ?>
    </div>
</div>