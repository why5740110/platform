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
use common\libs\CommonFunc;
$this->title = '医院列表';
?>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="row" style="overflow: scroll; ">
    <div class="backer_top_nav bgfff">
        <div class="form-group">
            <?php
            $form = ActiveForm::begin(['action' =>'/hospital/index', 'method'=>'get','options' =>['name'=>'form'],'id'=>'layer-form-table']);
            ?>

            <ul style="padding-top: 10px;">

                <li  style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>医院名称：</td>
                            <td>
                                <input name='name' type="text" value="<?php echo !empty($requestParams['name'])?$requestParams['name']:'';?>" placeholder="医院名称或者id" class="form-control input-sm" />
                            </td>
                        </tr>
                    </table>
                </li>          

                <li  style="float: left;padding-left: 25px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>医院等级：</td>
                            <td>
                                <?php $typelist = CommonFunc::getHospitalLevel();
                                $typelist = [0=>'全部']+$typelist;
                                ?>
                               <?php echo Html::dropDownList('level_num',$requestParams['level_num'] ?? '',$typelist,array('id'=>'level_num',"class"=>"form-control input-sm"));?>
                            </td>
                        </tr>
                    </table>
                </li>        

                <li  style="float: left;padding-left: 25px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>医院类型：</td>
                            <td>
                                <?php $typelist = array_merge(['0'=>'全部'],BaseDoctorHospitals::$Typelist);?>
                               <?php echo Html::dropDownList('type',$requestParams['type'] ?? '',$typelist,array('id'=>'type',"class"=>"form-control input-sm"));?>
                            </td>
                        </tr>
                    </table>
                </li>   

                <li  style="float: left;padding-left: 25px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>医院类别：</td>
                            <td>
                                <?php $typelist = array_merge(['0'=>'全部'],BaseDoctorHospitals::$Kindlist);?>
                               <?php echo Html::dropDownList('kind',$requestParams['kind'] ?? '',$typelist,array('id'=>'kind',"class"=>"form-control input-sm"));?>
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



                <li style="float: left;padding-left: 135px;">
                    <input style="display: inline-block;width:80px;line-height: 30px;height: 30px;"  type="text" value="" class="form-control input-sm" id="hos_val" placeholder="输入医院id">
                    <span style="display: inline-block;line-height: 30px;height: 30px;" class="hospital-filter layui-btn layui-btn-xs layui-btn-normal">更新</span>
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
                    'label' => '基础数据医院ID',
                    'attribute' => 'id',
                    'format'=>'html',
                    'value'=>function ($model,$key,$index,$column){
                        return $model->id;
                    }
                ],
            
               [
                    'attribute' => '医院名称',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model->kind =='公立') {
                            $link = \Yii::$app->params['domains']['mobile'].'hospital/hospital_'.HashUrl::getIdEncode(ArrayHelper::getValue($model,'id')).'.html';
                            $html = '<a style="color:blue;" target="_blank" href="'.$link.'">
                                '.$model->name.'
                            </a>';
                            return $html;
                        }else{
                            return $model->name ?? '';
                        }
                       
                    }
                ],                 
                [
                    'attribute' => '地区',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->province_name.'-'.$model->city_name.'-'.$model->district_name;
                    }
                ],   
                [
                    'attribute' => '医院等级',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->level ?? '';
                    }
                ],   
                [
                    'attribute' => '医院类型',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->type ?? '';
                    }
                ],   
                [
                    'attribute' => '医院类别',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->kind ?? '';
                    }
                ],  

                // 自定义动作列
                /*[
                    'class' => 'yii\grid\ActionColumn', //动作列参数 info-update、delete都代表方法 //这里的控制器是Article,两个参数分别访问的是 article/edit、article/delete方法
                    'template' => '{add}{edit}',
                    'header' => '操作',
                    'buttons' => [ // 指定每个方法页面显示(可以使图片/任意字)
                        'edit' => function ($url, $model, $key) { 
                            return Html::a('编辑', $url, ['title' => '编辑','class'=>'layui-btn layui-btn-xs']);
                        },
                    ],
                ],*/
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    // 'options' => ['width' => '100px;'],
                    'template' => ' {index} ',
                    'buttons' => [
                        'index' => function ($url, $model){
                            return Html::a(
                                '查看关联医院',
                                'javascript:;',
                                [
                                    'onclick'=>'test(this,'.$model->id.');',
                                    'title' => Yii::t(
                                        'app',
                                        '查看关联医院'
                                    ),
                                    'class' => 'layui-btn layui-btn-xs relation',
                                ]
                            );
                        },
                    ],
                ],
            ],
        ]);?>
    </div>
</div>

<script type="text/javascript">
$(".hospital-filter").click(function (){
    var hos_id = $('#hos_val').val();
    if (!hos_id) {
        return  layer.msg('请输入医院ID！', {icon: 1});
    }
    layer.confirm('确定要更新医院信息吗', function(index){
        //异步提交
        $.ajax({
            url: "/keshi/ajax-up-hospital", //提交地址
            data: {hospital_id:hos_id,"_csrf-backend":$('#_csrf-backend').val()}, //将表单数据序列化
            type: "get",
            timeout: 20000, //超时时间20秒
            dataType: "json",
            async: true,
            beforeSend: function() { // 禁用按钮防止重复提交
                loading = showLoad();
            },
            complete: function() {
                layer.close(loading);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('操作失败，请刷新重试', {icon: 2,time: 3000});
            },
            success: function(res) {
                if (res.status == 1) {
                    layer.msg('操作成功！', {icon: 1,time: 2000});
                }else{
                    layer.msg(res.msg, {icon: 2,time: 3000});
                }
                setTimeout(function () {
                    window.location.href = window.location.href;
                }, 2000);
            }
        });
        
        layer.close(index);
    });
});
</script>

<?php $this->beginBlock('myjs') ?>
    function test(_this,hospital_id) {
             
            layer.open({
                type:2,
                title:'关联医院',
                area:['500px', '400px'],
                content: "/hospital-relation/relation-list?hospital_id="+hospital_id,
                yes: function(index, layero){

                },
               
            });
       
     }
<?php $this->endBlock() ?>
<?php $this->registerJs($this->blocks['myjs'], \yii\web\View::POS_END); ?>
