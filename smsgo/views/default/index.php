<?php

/**
 * @var $this \yii\web\View
 * @var $response array
 * @var $model \backend\modules\smsgo\models\SendSmsForm
 */

use backend\modules\smsgo\models\SmsHistory;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$this->title = "SMS xizmati";
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <i class="text-blue fa-regular fa-envelope mr-2"></i>
                        SMS xizmati
                    </h3>
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item"><a class="nav-link active" href="#tab_1" data-toggle="tab">Menyular</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#tab_2" data-toggle="tab">SMS xizmati holati</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="<?= Url::to('history') ?>">
                                <div class="info-box">
                                <span class="info-box-icon bg-gradient-blue">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Xabarlar</span>
                                        <span class="info-box-number"> <?= SmsHistory::find()->cache(60 * 30)->count() ?> ta </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="<?= Url::to('settings') ?>">
                                <div class="info-box">
                                <span class="info-box-icon bg-gradient-blue">
                                    <i class="fa fa-cogs"></i>
                                </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sozlamalar</span>
                                        <span class="info-box-number text-muted">-</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab_2">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <?php
                                if (is_array($response)) :
                                    foreach ($response as $key => $value) :
                                        ?>
                                        <tr>
                                            <td class="text-bold"> <?= $key ?> </td>
                                            <td> <?= $value ?> </td>
                                        </tr>
                                    <?php
                                    endforeach;
                                endif;
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="text-success mr-2 fa-regular fa-message"></i>
                        Sms jo'natish <small class="text-muted">(Pullik)</small>
                    </h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::class, [
                        'mask' => '\+\9\9\8 99 999 99 99',
                        'options' => [
                            'placeholder' => $model->getAttributeLabel('phone'),
                            'minlength' => 17,
                        ]
                    ])->label(false) ?>

                    <?= $form->field($model, 'text')
                        ->textarea(['placeholder' => $model->getAttributeLabel('text'), 'rows' => 4, 'id' => 'smsText'])
                        ->hint("Lotin alifbosida 170 belgi, krill alifbosida esa 70 belgi bir sms narxida hisoblanadi.")->label(false) ?>

                    <div class="row">
                        <div class="col d-flex justify-content-between">
                            <p class="text-primary">
                                <span id="letterCount">0</span> ta belgi
                            </p>
                            <?= Html::submitButton("Jo'natish", [
                                'class' => 'btn btn-outline-success',
                                'data-confirm' => "Tasdiqlaysizmi?"
                            ]) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

<?php

$js = <<<JS
    $("#smsText").on('change keyup paste', function() {
        $("span#letterCount").text(this.value.length)
    });
JS;

$this->registerJs($js);
