<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\minying\MinAccountModel */
/* @var $form yii\widgets\ActiveForm */
?>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="box box-primary account-model-form" style="border-top: 1px solid #d2d6de!important;">
    <br>
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'enableAjaxValidation' => false,
        'fieldConfig' => [
            'template' => "<div class='col-lg-1 col-md-1 col-sm-2 control-label'>{label}</div><div class='col-lg-8 col-md-8 col-sm-8 form-group-sm'>{input}{error}</div>{hint}",
        ]
    ]); ?>
    <div class="box-body">
        <?php
        $options_type = ['id' => 'form-type', 'onchange' => "changeEnterprise(this)", 'prompt' => '请选择账号类型'];
        !$model->isNewRecord && $options_type['disabled'] = 'disabled';
        echo $form->field($model, 'type')->dropDownList(\common\models\minying\MinAccountModel::typeMaps(), $options_type)
        ?>

        <?php $is_hide = $model->type == \common\models\minying\MinAccountModel::TYPE_HOSPITAL ? true : false; ?>
        <div class="enterprise_agency_div <?= $is_hide ? 'hide' : '' ?>">
            <?php
            $options_agency['placeholder'] = '请选择所属单位...';
            !$model->isNewRecord && $options_agency['disabled'] = 'disabled';
            echo $form->field($model, 'enterprise_agency')->widget(\kartik\select2\Select2::class, [
                'data' => \common\models\minying\MinAgencyModel::agencies(),
                'options' => $options_agency,
            ])->label('所属单位') ?>
        </div>
        <div class="enterprise_hospital_div <?= $is_hide ? '' : 'hide' ?>">
            <?php
            $options_hospital['placeholder'] = '请选择医院...';
            !$model->isNewRecord && $options_hospital['disabled'] = 'disabled';
            echo $form->field($model, 'enterprise_hospital')->widget(\kartik\select2\Select2::class, [
                'data' => \common\models\minying\MinHospitalModel::hospitals(),
                'options' => $options_hospital,
            ])->label('所属单位') ?>
        </div>

        <?php
        $options_contact_name = ['maxlength' => true, 'placeholder' => '请填写联系人'];
        !$model->isNewRecord && $options_contact_name['disabled'] = 'disabled';
        echo $form->field($model, 'contact_name')->textInput($options_contact_name) ?>

        <?php
        $options_contact_mobile = ['maxlength' => 11, 'placeholder' => '请填写联系方式'];
        !$model->isNewRecord && $options_contact_mobile['disabled'] = 'disabled';
        echo $form->field($model, 'contact_mobile', ['enableAjaxValidation' => true])->textInput($options_contact_mobile) ?>

        <?php if ($model->isNewRecord): ?>
            <?= $form->field($model, 'password')->textInput(['maxlength' => true, 'readonly' => true, 'placeholder' => '确认提交后密码自动生成'])->label('设置密码') ?>
        <?php else: ?>
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'readonly' => true, 'disabled' => $model->isNewRecord ? '' : 'disabled'])->label('密码')->hint("<a href='javascript:;' class='text-blue' onclick='resetPassword()'>重置密码</a>") ?>
        <?php endif; ?>
        <div class="box-footer">
            <?php if ($model->isNewRecord): ?>
                <?= Html::submitButton('确认', ['class' => 'btn btn-primary', 'id' => 'submit-btn']); ?>
            <?php else: ?>
                <?= Html::a('返回', Url::to('/account/index'), ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
    function changeEnterprise(obj) {
        var type = $(obj).val(),
            agency = $('.enterprise_agency_div'),
            hospital = $('.enterprise_hospital_div');
        if (type == 1) {
            agency.removeClass('hide').addClass('block')
            hospital.addClass('hide')
        }
        if (type == 2) {
            hospital.removeClass('hide').addClass('block')
            agency.addClass('hide')
        }
    }

    function resetPassword() {
        layer.confirm('是否确认重置密码？', {icon: 3, title: '密码重置提示'}, function (index) {
            $.post(
                '<?= Url::to('reset-password')?>',
                {id: '<?= $model->account_id;?>', "_csrf-backend":$('#_csrf-backend').val()},
                function (res) {
                    if (res.status == 1) {
                        layer.alert('密码重置成功！', {icon: 1})
                    } else {
                        layer.alert('重置失败，请重试！', {icon: 2});
                    }
                }
            );
            layer.close(index);
        });
    }
</script>
<style>
    .form-group.required > .control-label > label:before {
        content: "*";
        color: #ed5565;
        margin-right: 5px;
    }
</style>
