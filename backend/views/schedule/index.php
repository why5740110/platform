<?php

use yii\helpers\Url;
use yii\helpers\Html;   //引入辅助表单类
use yii\helpers\ArrayHelper;
use yii\grid\GridView;  //引入数据小插件类
use yii\widgets\ActiveForm;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use dosamigos\datetimepicker\DateTimePicker;
use common\components\GoPager;//新分页
use common\libs\CommonFunc;
$this->title = '预约排班列表';
?>

<div class="row" style="overflow: scroll; ">
    <div class="backer_top_nav bgfff">
        <div class="form-group">
            <?php
            $form = ActiveForm::begin(['action' => Url::to(['schedule/index']), 'method'=>'get','options' =>['name'=>'form'],'id'=>'layer-form-table']);
            ?>

            <ul style="padding-top: 10px;">

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>医生名称：</td>
                            <td>
                                <input name='doctor_id' type="text" value="<?php echo !empty($requestParams['doctor_id'])?$requestParams['doctor_id']:'';?>" placeholder="" class="form-control input-sm" />
                            </td>
                        </tr>
                    </table>
                </li>     

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>执业地：</td>
                            <td>
                                <input name='scheduleplace_name' type="text" value="<?php echo !empty($requestParams['scheduleplace_name'])?$requestParams['scheduleplace_name']:'';?>" placeholder="" class="form-control input-sm" />
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
                'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id.'/index'],$params['requestParams'],['1'=>1])),
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
                    'attribute' => '医生id',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->doctor_id ?? '';
                    }
                ],     
                 [
                    'attribute' => '医生名称',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->realname ?? '';
                    }
                ],                
                [
                    'attribute' => '职称',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->job_title ?? '';
                    }
                ],   
                [
                    'attribute' => '执业地点',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->scheduleplace_name ?? '';
                    }
                ],   
                [
                    'attribute' => '所在医院',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->hospital_name ?? '';
                    }
                ], 
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    // 'options' => ['width' => '100px;'],
                    'contentOptions' => array(
                        'style' => 'width:80px;',
                        'nowrap' => 'wrap'
                    ),
                    'template' => ' {index} ',
                    'buttons' => [
                          'index' => function ($url, $model, $key) {
                            // return '<p>' . Html::a( '排班设置' , 'javascript:void(0);', ['title' => '排班设置', 'class' => 'layui-btn layui-btn-xs layui-btn-normal show_week', 'data-id' => $model->id]) . '</p>' ;
                            return '<p>' . Html::a( '排班设置' , Url::to(['schedule/setting','id'=>$model->id]), ['title' => '排班设置', 'class' => 'layui-btn layui-btn-xs layui-btn-normal show_week2', 'data-id' => $model->id]) . '</p>' ;

                        },
                    ],
                ],
            ],
        ]);?>
    </div>
</div>

<?php
$schedule = Url::to(['schedule/setting']);
?>
<script type="text/javascript">
$(".show_week").click(function (e){
    var id = $(this).attr('data-id');
    // $('#layui-layer-shade2').show();
    var schedule = "<?=$schedule;?>";
    // window.location.href=schedule+"?id="+id;
    layer.open({
        shade: false,
        type:2,
        title:'排班设置',
        area:['40%', '80%'],
        content: schedule+"?id="+id,
        // btn: ['确定'],
        // yes: function(index, layero){
        //     layer.close(index);
        //     $('#layui-layer-shade2').hide();
        // },
        // end:function(){
        //     $('#layui-layer-shade2').hide();
        // }
    });
});
</script>
