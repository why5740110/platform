<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '代理商列表';

?>
<div class="min-agency-model-index">
    <p>
        <?= Html::a('添加代理商', ['create'], ['class' => 'layui-btn layui-btn-sm']) ?>
    </p>
    <hr>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '<div class="text-right" style="font-size: 15px;margin-bottom: 10px;">第{begin} -{end}条, 共{totalCount}条</div>',
        'columns' => [
            'agency_id',
            'agency_name',
            'contact_name',
            [
                'attribute' => 'contact_mobile',
                'value' => function ($model) {
                    return substr_replace($model->contact_mobile, '****', 3, 4);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'header' => '操作',
                'buttons' => [
                    'update' => function ($url) {
                        return "<a class='text-blue' href='{$url}' title='编辑'>编辑</a> ";
                    }
                ],
            ],
        ],
    ]); ?>
</div>
