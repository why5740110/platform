<?php

use yii\helpers\Url;
use yii\helpers\Html;   //引入辅助表单类
use yii\helpers\ArrayHelper;
use yii\grid\GridView;  //引入数据小插件类
use yii\widgets\ActiveForm;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
// use yii\timepicker\TimePicker;
use dosamigos\datetimepicker\DateTimePicker;
use common\components\GoPager;//新分页
$this->title = '操作日志';
?>

<div class="row" style="overflow: scroll; ">
    <div class="backer_top_nav bgfff">
        <div class="form-group">
            <?php
            $form = ActiveForm::begin(['action' =>'/log/list', 'method'=>'get','options' =>['name'=>'form'],'id'=>'layer-form-table']);
            ?>

            <ul style="padding-top: 10px;">

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>操作人：</td>
                            <td>
                                <input name='admin_name' type="text" value="<?php echo !empty($requestParams['admin_name'])?Html::encode($requestParams['admin_name']):'';?>" placeholder="操作人id或者名称" class="form-control input-sm" />
                            </td>
                        </tr>
                    </table>
                </li>  

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>操作内容:</td>
                            <td>
                                <input name='info' type="text" value="<?php echo !empty($requestParams['info'])?Html::encode($requestParams['info']):'';?>" placeholder="操作内容" class="form-control input-sm" />
                            </td>
                        </tr>
                    </table>
                </li>     

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>操作描述:</td>
                            <td>
                                <input name='description' type="text" value="<?php echo !empty($requestParams['description'])?Html::encode($requestParams['description']):'';?>" placeholder="操作描述" class="form-control input-sm" />
                            </td>
                        </tr>
                    </table>
                </li>       



                <li  style="float: left;padding-left: 25px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>
                                <?php echo Html::submitButton('搜索',array('class'=>'btn btn-block btn-primary')); ?>
                            </td>                         
                        </tr>
                    </table>
                </li>


            </ul>

         <?php ActiveForm::end(); ?>
        </div>
        <hr/>

        <?php echo GridView::widget([
            'tableOptions'=>['class' => 'table table-striped table-bordered table-expandable'],
            'layout'=> '{summary}{items}<div class="text-right tooltip-demo">{pager}</div>',
            'summary' => '<div class="text-right" style="font-size: 15px;margin-bottom: 10px;">第{begin} -{end}条, 共{totalCount}条</div>',
            'pager'=>[
                'class' => GoPager::className(),
                'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id.'/list'],$params['requestParams'],['1'=>1])),
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
            'emptyText' => '没有筛选到任何内容哦',
            'dataProvider' => $params['dataProvider'], //数据源($data为后台查询的数据)
            //设计显示的字段(说明:此数组为空,默认显示所有数据库查询出来的字段)
            'columns' => [
                // ['class' => 'yii\grid\CheckboxColumn', 'name' => 'id','cssClass'=>'push_source'],
                [
                    'label' => 'ID',
                    'attribute' => 'id',
                    'format'=>'html',
                    'value'=>function ($model,$key,$index,$column){
                        return $model->id;
                    }
                ],
                     
                [
                    'attribute' => '操作描述',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->description ? Html::encode($model->description) : '';
                    }
                ],   
                [
                    'attribute' => '操作人id',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->admin_id ?? '';
                    }
                ],   
                [
                    'attribute' => '操作人姓名',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->admin_name ?? '';
                    }
                ],   
                [
                    'attribute' => '操作内容',
                    'format'=>'html',
                    'contentOptions' => array(
                        'style' => 'width:30%;text-align:center;word-break:break-all;white-space:pre-wrap;',
                        'nowrap' => 'wrap'
                    ),
                    'value' => function ($model, $key, $index, $column) {
                        return $model->info ? Html::encode($model->info) : '';
                    },
                ],  
                [
                    'attribute' => '操作时间',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return !empty($model->create_time) ? date("Y-m-d H:i:s",$model->create_time) : '--';
                    }
                ], 

            ],
        ]);?>
    </div>
</div>
