<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\minying\MinAgencyModel */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-primary min-agency-model-form" style="border-top: 1px solid #d2d6de!important;">
    <br>
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'enableAjaxValidation' => false,
        'fieldConfig' => [
            'template' => "<div class='col-lg-1 col-md-1 col-sm-2 control-label'>{label}</div><div class='col-lg-8 col-md-8 col-sm-8 form-group-sm'>{input}{error}</div>{hint}",
        ]
    ]); ?>
    <div class="box-body">

        <?= $form->field($model, 'agency_name')->textInput(['maxlength' => true, 'placeholder' => '请填写公司名称']) ?>

        <?= $form->field($model, 'contact_name')->textInput(['maxlength' => true, 'placeholder' => '请填写联系人']) ?>

        <?= $form->field($model, 'contact_mobile')->textInput(['maxlength' => 11, 'placeholder' => '请填写联系方式']) ?>

        <div class="box-footer">
            <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-primary', 'id' => 'submit-btn']) ?>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<style>
    .form-group.required > .control-label > label:before {
        content: "*";
        color: #ed5565;
        margin-right: 5px;
    }
</style>