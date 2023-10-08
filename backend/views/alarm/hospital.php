<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '医院合作到期提醒';

use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>
<?php $form = ActiveForm::begin([
    'method' => 'get',
    'action' => 'hospital',
    'options' => ['class' => 'form-horizontal1'],
    'fieldConfig' => [
        'template' => "<div class='col-lg-1 col-md-1 col-sm-2 control-label'>{label}</div><div class='col-lg-2 col-md-3 col-sm-2 form-group-sm'>{input}<br></div>",
    ]]); ?>
<div class="box-body">
    <?= $form->field($searchModel, 'agency_id', ['options' => ['class' => 'form-group-sm']])->widget(\kartik\select2\Select2::class, [
        'data' => \common\models\minying\MinAgencyModel::agencies(),
        'options' => ['placeholder' => '请选择代理商...'],
        'pluginOptions' => [
            'allowClear' => true,
        ]
    ])->label('代理商名称') ?>
    <?= $form->field($searchModel, 'hospital_id', ['options' => ['class' => 'form-group-sm']])->widget(\kartik\select2\Select2::class, [
        'data' => \common\models\minying\MinHospitalModel::hospitals(),
        'options' => ['placeholder' => '请选择医院...'],
        'pluginOptions' => [
            'allowClear' => true,
        ]
    ])->label('医院名称') ?>
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
                'attribute' => '代理商名称',
                'value' => function ($model) {
                    return Html::encode($model->agency_name);
                }
            ],
            [
                'attribute' => '合作开始时间',
                'value' => function ($model) {
                    return date('Y-m-d', $model->begin_time);
                }
            ],
            [
                'attribute' => 'end_time',
                'label' => '合作结束时间',
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
