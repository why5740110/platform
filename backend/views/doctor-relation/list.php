<?php
use yii\helpers\Url;
use yii\helpers\Html; 
use yii\widgets\LinkPager;
use common\libs\Url as CommonUrl;
use common\libs\CommonFunc;
use \yii\helpers\ArrayHelper;
use common\components\GoPager;//新分页
use backend\models\DoctorInfoModel;
use common\models\TmpDoctorThirdPartyModel;
use yii\grid\GridView;  //引入数据小插件类
use common\libs\HashUrl;
use common\libs\DoctorUrl;
$request = \Yii::$app->request;
$this->title = '关联医生';
?>

<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:100px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
    .layui-textarea{min-height:60px;}
    .layui-layer-shade{display:none;}
    .check_faild_reason_css{
        overflow:hidden; word-wrap:break-word;
    }
</style>
<div class="layui-layer-shade" id="layui-layer-shade2" times="2" style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医生姓名</label>
            <div class="layui-input-block" style="width:100px;margin-left:110px;">
                <input type="text" name="doctor" <?php if($request->get('doctor')){echo 'value="'.$request->get('doctor').'"';}?> placeholder="姓名" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:120px;">第三方医生id</label>
            <div class="layui-input-block" style="width:120px;margin-left:120px;">
                <input type="text" name="tp_doctor_id" <?php if($request->get('tp_doctor_id')){echo 'value="'.$request->get('tp_doctor_id').'"';}?> placeholder="第三方医生id" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:120px;">医院线医生id</label>
            <div class="layui-input-block" style="width:120px;margin-left:120px;">
                <input type="text" name="doctor_id" <?php if($request->get('doctor_id')){echo 'value="'.$request->get('doctor_id').'"';}?> placeholder="医院线医生id" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">科室</label>
            <div class="layui-input-block" style="width:100px;margin-left:102px;">
                <input type="text" name="keshi" <?php if($request->get('keshi')){echo 'value="'.$request->get('keshi').'"';}?>  autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">对接平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = ['0'=>'全部'] + CommonFunc::getTpPlatformNameList(1);?>
                <?php echo Html::dropDownList('tp_platform',$request->get('tp_platform') ?? '',$tp_platform_list,array('id'=>'tp_platform_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">关联状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $is_relation_list = array_merge([''=>'全部'],TmpDoctorThirdPartyModel::$is_relation_list);?>
                <?php echo Html::dropDownList('is_relation',$request->get('is_relation') ?? '',$is_relation_list,array('id'=>'is_relation_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医院名称</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="hospital_name"  placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo $request->get('hospital_name', '');?>">

            </div>
        </div>

      <!--   <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">操作人</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="admin_name"  placeholder="操作人id或者名称" autocomplete="off" class="layui-input" value="<?php echo $request->get('admin_name', '');?>">

            </div>
        </div> -->

        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </div>
    </form>

</div>

<hr>

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
                    'attribute' => '对接平台',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        $tp_platform_list = CommonFunc::getTpPlatformNameList(1);
                        return $tp_platform_list[$model->tp_platform] ?? '';
                    }
                ], 
                           
                [
                    'attribute' => '第三方医生id',
                    'format'=>'html',
                    'contentOptions' => array(
                        'style' => 'max-width:20%;text-align:center;word-break:break-all;white-space:pre-wrap;',
                        'nowrap' => 'wrap'
                    ),
                    'value' => function ($model, $key, $index, $column) {
                        return $model->tp_doctor_id;
                    }
                ],                
                [
                    'attribute' => '医生姓名',
                    'format'=>'html',
                    'contentOptions' => array(
                        'style' => 'max-width:20%;text-align:center;word-break:break-all;white-space:pre-wrap;',
                        'nowrap' => 'wrap'
                    ),
                    'value' => function ($model, $key, $index, $column) {
                        return  str_replace(' ', '&nbsp;', $model->realname);
                    }
                ],       
                [
                    'attribute' => '第三方科室id',
                    'format'=>'html',
                    'contentOptions' => array(
                        'style' => 'max-width:20%;text-align:center;word-break:break-all;white-space:pre-wrap;',
                        'nowrap' => 'wrap'
                    ),
                    'value' => function ($model, $key, $index, $column) {
                        return $model->tp_department_id;
                    }
                ],                               
                [
                    'attribute' => '科室',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        // return $model->frist_department_name.'-'.$model->second_department_name;
                        if ($model->frist_department_name && !$model->second_department_name) {
                            return $model->frist_department_name;
                        }elseif (!$model->frist_department_name && $model->second_department_name) {
                            return $model->second_department_name;
                        }else{
                            return $model->frist_department_name.'-'.$model->second_department_name;
                        }
                        
                    }
                ],   
                [
                    'attribute' => '医生职称',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->job_title;
                    }
                ],  
                [
                    'attribute' => '医院名称',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->hospital_name ?? '';
                    }
                ],                  
                [
                    'attribute' => '医院线医生ID',
                    'header' => '医院线医生ID',
                    'format' => 'html',
                    // 'options' => ['width' => '100px;'],
                    'value' => function ($model, $key, $index, $column) {
                        if ($model->doctor_id) {
                            return '<a style="width: 70px;" href='.Url::to(['doctor/add','doctor_id'=>$model->doctor_id]).'>'.$model->doctor_id.'</a>';
                        }
                        return $model->doctor_id ?? '';
                    }
                ],   
                [
                    'attribute' => '关联状态',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return TmpDoctorThirdPartyModel::$is_relation_list[$model->is_relation] ?? '';
                    }
                ],   
                [
                    'attribute' => '创建时间',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return date('Y-m-d H:i:s',$model->create_time) ?? '';
                    }
                ],  
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    // 'options' => ['width' => '100px;'],
                    'template' => ' {index} ',
                    'buttons' => [
                        'index' => function ($url, $model){
                            if ($model->is_relation == 0) {
                                $addurl = Url::to(['doctor/add','tp_doctor_id'=>$model->tp_doctor_id,'tp_platform'=>$model->tp_platform,'tmp_id'=>$model->id]);

                                return Html::a('关联医生', 'javascript:void(0);', ['title' => '关联医生','tp_platform'=>$model->tp_platform,'tmp_id'=>$model->id,'tp_doctor_id'=>$model->tp_doctor_id,'class'=>'layui-btn layui-btn-xs relation']).Html::a('新添医生',$addurl, ['title' => '新添医生','class'=>'layui-btn layui-btn-xs layui-btn-warm']);
                            }else{
                                return Html::a('已关联', 'javascript:void(0);', ['title' => '已关联','class'=>'layui-btn layui-btn-xs layui-btn-normal']);
                            }
                        },
                    ],
                ],
            ],
        ]);?>

<!--审核失败原因-->
<div id="reason" style="display:none;"></div>
<?php
$updateDocidUrl = Url::to(['doctor-relation/update-relation']);
$relationDocidUrl = Url::to(['doctor-relation/relation']);
?>
<script type="text/javascript">

var updateDocidUrl = "<?=$updateDocidUrl;?>";
var relationDocidUrl = "<?=$relationDocidUrl;?>";

 $(".relation").click(function (e){
        var tp_doctor_id = $(this).attr('tp_doctor_id');
        var tp_platform = $(this).attr('tp_platform');
        var tmp_id = $(this).attr('tmp_id');
        $('#layui-layer-shade2').show();
        layer.open({
            type:2,
            title:'关联医生',
            area:['60%','80%'],
            content: relationDocidUrl+"?tp_doctor_id="+tp_doctor_id+'&tmp_id='+tmp_id,
            btn: ['关联', '取消'],
           
            yes: function(index, layero){
             
                var doctorObj = layer.getChildFrame("#doctor_id", index);
                var doctor_id = doctorObj.val();
                var doctorid = layer.getChildFrame("#doctorid", index);
                if(!doctorid.text()){
                    return layer.msg('请先查询医生信息', {icon: 2});
                }

                var new_doc_hospital_id = $(layer.getChildFrame("#hos option:selected", index)).val(); // 文本值
                var new_doc_search_fkid = $(layer.getChildFrame(".search_fkid option:selected", index)).val(); // 文本值
                var new_doc_search_skid = $(layer.getChildFrame(".search_skid option:selected", index)).val(); // 文本值

                if (!new_doc_hospital_id || !new_doc_search_fkid || !new_doc_search_skid) {
                    return layer.msg('请选择医院信息', {icon: 2});
                }

                //异步提交
                $.ajax({
                    url: "<?=$updateDocidUrl;?>", //提交地址
                    data: {
                        tmp_id:tmp_id,
                        tp_doctor_id:tp_doctor_id,
                        doctor_id:doctor_id,
                        tp_platform:tp_platform,
                        new_doc_hospital_id:new_doc_hospital_id,
                        new_doc_search_fkid:new_doc_search_fkid,
                        new_doc_search_skid:new_doc_search_skid,
                        "_csrf-backend":$('#_csrf-backend').val()
                    }, 
                    type: "POST",
                    timeout: 20000, //超时时间20秒
                    dataType: "json",
                    async: true,
                    beforeSend: function() { // 禁用按钮防止重复提交
                        loading = showLoad();
                    },
                    complete: function() {
                        layer.close(loading);
                        layer.close(index);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('保存失败，请刷新重试', {icon: 2,time: 3000});
                    },
                    success: function(res) {
                        if (res.status == 1) {
                            layer.msg('操作成功！', {icon: 1});
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                            return false;
                        }else{
                            layer.msg(res.msg, {icon: 2});
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                    }
                });
             
            },
            btn2: function (index, layero){
                layer.close(index);
            },
            end:function(){
                $('#layui-layer-shade2').hide();
            }
        });
});



</script>