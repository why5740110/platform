<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '账号列表';
?>
<div class="account-model-index">
    <p>
        <?= Html::a('创建账号', ['create'], ['class' => 'layui-btn layui-btn-sm']) ?>
    </p>
    <hr>
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
            'account_number',
            [
                'attribute' => 'type',
                'value' => function ($model) {
                    return \common\models\minying\MinAccountModel::typeMaps()[$model->type] ?? '-';
                }
            ],
            [
                'attribute' => 'enterprise_name',
                'value' => function ($model) {
                    return $model->type == \common\models\minying\MinAccountModel::TYPE_HOSPITAL ? $model->hospitalModel->min_hospital_name : $model->agencyModel->agency_name;
                }
            ],
            'contact_name',
            [
                'attribute' => 'contact_mobile',
                'value' => function ($model) {
                    return substr_replace($model->contact_mobile, '****', 3, 4);
                }
            ],
            [
                'label' => '当前状态',
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->status == \common\models\minying\MinAccountModel::STATUS_NORMAL ? '启用' : '禁用';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{update-status}',
                'header' => '操作',
                'buttons' => [
                    'update' => function ($url) {
                        return "<a class='text-blue' href='{$url}' title='编辑'>编辑</a> ";
                    },
                    'update-status' => function ($url, $model) {
                        $status_text = $model->status == \common\models\minying\MinAccountModel::STATUS_NORMAL ? '禁用' : '启用';
                        return "<a class='text-blue' href='{$url}' title='修改状态' data-confirm='是否这么操作？'>{$status_text}</a>";
                    }
                ],
            ],
        ],
    ]); ?>
</div>
