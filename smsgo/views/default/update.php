<?php

/**
 * @var $this \yii\web\View
 * @var $model \backend\modules\smsgo\models\SmsSetting
 */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = "Tahrirlash: {$model->name}";

$this->params['breadcrumbs'][] = ['label' => $model->info, 'url' => ['settings']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(); ?>
    <div class="card">
        <div class="card-body">

            <?= $form->field($model, 'value')->textInput()->label(strtoupper($model->name)) ?>

            <div class="row">
                <div class="col d-flex justify-content-end">
                    <?= Html::submitButton('Saqlash', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>