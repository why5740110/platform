<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '医生证件到期提醒';

use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

?>
<?php $form = ActiveForm::begin([
    'method' => 'get',
    'action' => 'doctor',
    'options' => ['class' => 'form-horizontal1'],
    'fieldConfig' => [
        'template' => "<div class='col-lg-1 col-md-1 col-sm-2 control-label'>{label}</div><div class='col-lg-2 col-md-3 col-sm-2 form-group-sm'>{input}<br></div>",
    ]]); ?>
<div class="box-body">
    <?= $form->field($searchModel, 'doctor_cert_id', ['options' => ['class' => 'form-group-sm']])
        ->dropDownList(\common\models\minying\ResourceDeadlineModel::certTypeMaps(), ['prompt' => '全部'])->label('证件类型') ?>
    <?= $form->field($searchModel, 'doctor_id', ['options' => ['class' => 'form-group-sm']])->widget(\kartik\select2\Select2::class, [
        'options' => [
            'placeholder' => '请选择医生ID、医生名称',
        ],
        'initValueText' => Html::encode(\common\models\minying\MinDoctorModel::find()->where(['min_doctor_id' => $searchModel->doctor_id])->select(['concat_ws("-", min_hospital_name, min_doctor_name) as text'])->scalar()),
        'pluginOptions' => [
            'allowClear' => true,
            'language' => [
                'errorLoading' => new \yii\web\JsExpression("function () { return '查找中...'; }"),
                'noResults' => new \yii\web\JsExpression("function () { return '未找到记录.'; }"),
            ],
            'ajax' => [
                'url' => \yii\helpers\Url::to('ajax-get-doctors'),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { ;return {q:params.term}; }'),
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (d) { return d; }'),
            'templateResult' => new \yii\web\JsExpression('function (d) { return d.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function (d) { return d.text; }'),
        ],
    ])->label('医院/医生') ?>
    <div class="form-group-sm">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary btn-sm']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<hr>
<div class="deadline-model-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '<div class="text-right" style="font-size: 15px;margin-bottom: 10px;">第{begin} -{end}条, 共{totalCount}条</div>',
         'pager'=>[
            'class' => \common\components\GoPager::class,
            'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([$this->context->id.'/'. $this->context->action->id],Yii::$app->request->getQueryParams(),['1'=>1])),
            'firstPageLabel' => '首页',
            'prevPageLabel' => '《',
            'nextPageLabel' => '》',
            'lastPageLabel' => '尾页',
            'goPageLabel' => true,
            'totalPageLable' => '共x页',
            'totalCountLable' => '共x条',
            'goButtonLable' => 'GO',
            'maxButtonCount' => 5
        ],
        'columns' => [
            [
                'attribute' => '医院名称',
                'value' => function ($model) {
                    return Html::encode($model->min_hospital_name);
                }
            ],
            [
                'attribute' => '医生姓名',
                'value' => function ($model) {
                    return Html::encode($model->min_doctor_name);
                }
            ],
            [
                'attribute' => '证件类型',
                'value' => function ($model) {
                    return ArrayHelper::getValue(\common\models\minying\ResourceDeadlineModel::certTypeMaps(), $model->resource_minor_id, '');
                }
            ],
            [
                'attribute' => 'end_time',
                'label' => '证件有效期至',
                'value' => function ($model) {
                    return date('Y-m-d', $model->end_time);
                }
            ],
            [
                'attribute' => 'end_time',
                'label' => '距离失效天数',
                'format' => 'raw',
                'value' => function ($model) {
                    $left_days  = ceil(($model->end_time - time()) / 86400);
                    return $left_days > 0 ? '还剩<span class="text-red">' . $left_days . '</span>天' : '<span class="text-red">已失效</span>';
                }
            ],
        ],
    ]); ?>
</div>
