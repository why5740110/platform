<?php

use yii\helpers\Url;
use yii\helpers\Html;   //引入辅助表单类
use yii\helpers\ArrayHelper;
use yii\grid\GridView;  //引入数据小插件类
use yii\widgets\ActiveForm;
use common\models\BaseDoctorHospitals;
use common\libs\HashUrl;
use common\libs\CommonFunc;
// use yii\timepicker\TimePicker;
use dosamigos\datetimepicker\DateTimePicker;
use common\components\GoPager;//新分页
$this->title = '医院现有科室';
?>


<!--<div class="layui-row">
    <div class="form-group--4">
    <?php /*$form = ActiveForm::begin(['action' => Url::to(['record/grab']), 'method' => 'post', 'options' => ['name' => 'form', 'id'=>'grab_form', 'class' => 'layui-form']]);*/?>
        <div class="layui-form-item">
          <label class="layui-form-label" style="width: 110px;">医院名称:</label>
          <div class="layui-input-block">
            <?php /*if(!empty($hospital['kind'] =='公立')): */?>
              <?php /*$link = \Yii::$app->params['domains']['pc'].'hospital/hospital_'.HashUrl::getIdEncode(ArrayHelper::getValue($hospital,'id')).'.html';*/?>
                <?php /*$html = '<a style="color:blue;" target="_blank" href="'.$link.'">
                    '.$hospital['name'].'
                </a>';*/?>
                <?/*=$html*/?>
            <?php /*else:*/?>
                <?/*=$hospital['name'];*/?>
            <?php /*endif;*/?>
          </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="frist_department_id" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">请选择一级科室</option>
                    <?php /*if(!empty($fkeshi_list)): */?>
                        <?php /*foreach($fkeshi_list as $value):*/?>
                        <option value="<?/*=$value['department_id']*/?>"><?/*=$value['department_name']*/?></option>
                        <?php /*endforeach;*/?>
                    <?php /*endif;*/?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="second_department_id" lay-verify="required" lay-search="" lay-filter="search_skid"  class="search_skid">
                  <option value="">请选择二级科室</option>
                </select>
            </div>
        </div>

        <input name="frist_department_name" type="hidden" value="" id="fkeshi_name" />
        <input name="second_department_name" type="hidden" value="" id="skeshi_name" />

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_frist_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">请选择王氏一级科室</option>
                    <?php /*if(!empty($miao_fkeshi_list)): */?>
                        <?php /*foreach($miao_fkeshi_list as $value):*/?>
                        <option value="<?/*=$value['id']*/?>"><?/*=$value['name']*/?></option>
                        <?php /*endforeach;*/?>
                    <?php /*endif;*/?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_second_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_skid"  class="miao_search_skid">
                  <option value="">请选择王氏二级科室</option>
                </select>
            </div>
        </div>



        <div class="layui-form-item" style="display: none;">
          <div class="layui-input-block">
            <input type="text" name="hospital_id"  value="<?/*=$hospital['id'];*/?>" >
          </div>
        </div>

        <div class="layui-form-item">
          <div class="layui-input-block">
            <button class="layui-btn" lay-submit type="button" id="submit_form" lay-filter="formDemo">添加</button>
          </div>
        </div>

    <?php /*ActiveForm::end();*/?>
    </div>

</div>-->
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-form">
    <?php
    $form = ActiveForm::begin(['action' =>'/hospital/edit', 'method'=>'get','options' =>['name'=>'form'],'id'=>'layer-form-table']);
    ?>

    <ul style="padding-top: 10px;">
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 110px;">医院名称:</label>
            <div class="layui-input-block">
                <?php if(!empty($hospital['kind'] =='公立')): ?>
                    <?php $link = \Yii::$app->params['domains']['pc'].'hospital/hospital_'.HashUrl::getIdEncode(ArrayHelper::getValue($hospital,'id')).'.html';?>
                    <?php $html = '<a style="color:blue;" target="_blank" href="'.$link.'">
                    '.$hospital['name'].'
                </a>';?>
                    <?=$html?>
                <?php else:?>
                    <?=$hospital['name'];?>
                <?php endif;?>
            </div>
        </div>
        <input name='id' type="hidden" value="<?php echo ArrayHelper::getValue($hospital,'id');?>" placeholder="" class="form-control input-sm" />
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="frist_department_id" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">请选择一级科室</option>
                    <?php if(!empty($fkeshi_list)): ?>
                        <?php foreach($fkeshi_list as $value):?>
                            <option value="<?=$value['department_id']?>" <?php if($value['department_id'] == ($requestParams['frist_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['department_name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="second_department_id" lay-verify="required" lay-search="" lay-filter="search_skid"  class="search_skid">
                    <option value="">请选择二级科室</option>
                    <?php if(!empty($skeshi_list)): ?>
                        <?php foreach($skeshi_list as $value):?>
                            <option value="<?=$value['department_id']?>" <?php if($value['department_id'] == ($requestParams['second_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['department_name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_frist_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">请选择王氏一级科室</option>
                    <?php if(!empty($miao_fkeshi_list)): ?>
                        <?php foreach($miao_fkeshi_list as $value):?>
                            <option value="<?=$value['id']?>" <?php if($value['id'] == ($requestParams['miao_frist_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_second_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_skid"  class="miao_search_skid">
                    <option value="">请选择王氏二级科室</option>
                    <?php if(!empty($miao_skeshi_list)): ?>
                        <?php foreach($miao_skeshi_list as $value):?>
                            <option value="<?=$value['id']?>" <?php if($value['id'] == ($requestParams['miao_second_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <!--<li  style="padding-left: 25px;">
            <table style="text-align: center;height: 40px;">
                <tr>
                    <td>
                        <?php /*echo Html::submitButton('搜索',array('class'=>'btn btn-block btn-primary')); */?>
                    </td>
                </tr>
            </table>
        </li>-->


    </ul>
    <div class="layui-form-item layui-inline">
        <button type="submit" style="width: 70px;" class="layui-btn layui-btn-sm">搜索</button>
        <button class="layui-btn layui-btn-sm" style="padding: 0px;">
            <a style="color:white;width: 70px;display: block" href="<?php echo Url::to(['hospital/add','id'=>ArrayHelper::getValue($hospital,'id')])?>" >新增</a>
        </button>
    </div>
    <?php ActiveForm::end(); ?>


</div>
<hr/>
<div>
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
                // ['class' => 'yii\grid\CheckboxColumn', 'name' => 'id',],
                [
                    'label' => 'ID',
                    'attribute' => 'id',
                    'format'=>'html',
                    'value'=>function ($model,$key,$index,$column){
                        return $model->id;
                    }
                ],

                [
                    'attribute' => '医院名称',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) use($hospital){
                        if ($hospital['kind'] =='公立') {
                            $link = \Yii::$app->params['domains']['mobile'].'hospital/hospital_'.HashUrl::getIdEncode(ArrayHelper::getValue($model,'hospital_id')).'.html';
                            $html = '<a style="color:blue;" target="_blank" href="'.$link.'">
                                '.$hospital['name'].'
                            </a>';
                            return $html;
                        }else{
                            return $hospital['name'];
                        }
                        
                    } 
                ],  

                [
                    'label' => '平台来源',
                    'attribute' => 'tp_platform',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return BaseDoctorHospitals::$Platformlist[$model->tp_platform];
                    }
                ],  
                [
                    'label' => '一级科室',
                    'attribute' => 'frist_department_name',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->frist_department_name;
                    }
                ],   

                 [
                    'label' => '二级科室',
                    'attribute' => 'second_department_name',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->second_department_name;
                    }
                ],   

                [
                    'label' => '对应王氏一级科室',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model->miao_frist_department_id) {
                            return CommonFunc::getKeshiName($model->miao_frist_department_id);
                        }else{
                            return '--';
                        }
                    }
                ],   

                 [
                    'label' => '对应王氏二级科室',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model->miao_second_department_id) {
                            return CommonFunc::getKeshiName($model->miao_second_department_id);
                        }else{
                            return '--';
                        }
                    }
                ],   
                 [
                    'label' => '医生人数',
                    'attribute' => 'doctors_num',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                        return $model->doctors_num;
                    }
                ],   

                [
                    'label' => '添加时间',
                    'attribute' => 'create_time',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column) {
                         return $model->create_time>0?date('Y-m-d H:i:s',$model->create_time):'--';
                    }
                ],    
                [
                    'class' => 'yii\grid\ActionColumn', //动作列参数 info-update、delete都代表方法 //这里的控制器是Article,两个参数分别访问的是 article/edit、article/delete方法
                    'template' => '{edit}{delete}',
                    'header' => '操作',
                    'buttons' => [ // 指定每个方法页面显示(可以使图片/任意字)
                        'edit' => function ($url, $model, $key) { 
                            return Html::a('编辑','javascript:void(0);', ['title' => '编辑','data-id' => $model->id,'class'=>'layui-btn layui-btn-xs recheck-open']);
                        },
                        'delete' => function ($url, $model, $key) { 
                            return Html::a('删除', 'javascript:void(0);', ['title' => '删除','data-id' => $model->id,'data-doctors_num'=>$model->doctors_num,'class'=>'layui-btn layui-btn-xs layui-btn-danger keshi_del-btn']);
                        },
                    ],
                ],           

            ],
        ]);?>

    
</div>

<script type="text/javascript">

layui.use('form', function(){
    var form = layui.form;
    form.on('select(search_fkid)', function (data){
        var fkeshi_id = data.value;
        if(fkeshi_id == ''){
            $(".search_skid").html('<option value="">请选择二级科室</option>');
            form.render('select');
            return false;
        }
        var keshiUrl = "/keshi/skeshi-list";
        $.get(keshiUrl, {'fkeshi_id':fkeshi_id,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择二级科室</option>';
                $.each(res.data, function (i, v){
                    html += '<option value="'+v.department_id+'">'+v.department_name+'</option>'; 
                });
                $(".search_skid").html(html);
                //重新渲染select
                form.render('select');
            }else{
                layer.msg('获取科室信息失败，请稍后重试！', {icon: 2});
            }
        });
    });

    form.on('select(miao_search_fkid)', function (data){
        var fkeshi_id = data.value;
        if(fkeshi_id == ''){
            $(".miao_search_skid").html('<option value="">请选择王氏二级科室</option>');
            form.render('select');
            return false;
        }
        var keshiUrl = "/keshi/miao-second-department-list";
        $.get(keshiUrl, {'fkeshi_id':fkeshi_id,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择王氏二级科室</option>';
                $.each(res.data, function (i, v){
                    html += '<option value="'+v.id+'">'+v.name+'</option>'; 
                });
                $(".miao_search_skid").html(html);
                //重新渲染select
                form.render('select');
            }else{
                layer.msg('获取科室信息失败，请稍后重试！', {icon: 2});
            }
        });
    });
});

$('.keshi_del-btn').click(function (event) {
    var id = $(this).attr('data-id');
    var doctors_num = $(this).attr('data-doctors_num');
    if (doctors_num > 0) {
        return layer.msg('该科室下存在关联医生不能删除！', {icon: 2});
    }
    var _this = $(this);
    if ($(this).attr('disabled') == 'disabled') {
        return false;
    } 
    if (confirm('确定要删除吗?') ? true : false) {
        $.ajax({
            url: '/hospital/del-keshi',
            data: {id:id,"_csrf-backend":$('#_csrf-backend').val()},
            timeout: 20000,//超时时间20秒
            type: 'POST',
            async: true,
            beforeSend: function () {
                _this.attr({ disabled: "disabled" });
                loading = showLoad();
            },
            success: function (res) {
                layer.close(loading);
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1});
                    setTimeout(function (){
                        window.location.reload();
                    }, 500);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function (){
                        window.location.reload();
                    }, 3000);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.close(loading);
                layer.msg('保存失败，请刷新重试', {icon: 2});
            }
        });

    }

})

$("#submit_form").click(function (event) {
    if ($(this).attr('disabled') == 'disabled') {
        return false;
    } 
    var fkid = $('.search_fkid option:selected').val(); // 文本值
    var skid = $('.search_skid option:selected').val(); // 文本值    

    var miao_fkid = $('.miao_search_fkid option:selected').val(); // 文本值
    var miao_skid = $('.miao_search_skid option:selected').val(); // 文本值
    var search_fkid = $('.search_fkid option:selected').text(); // 文本值
    $('#fkeshi_name').val(search_fkid);

    var search_skid = $('.search_skid option:selected').text(); // 文本值
    $('#skeshi_name').val(search_skid);

    if (!fkid || !skid) {
        layer.msg('医院科室不能为空！', {icon: 2});
        return false;
    }

    if (!miao_fkid || !miao_skid) {
        layer.msg('王氏科室不能为空！', {icon: 2});
        return false;
    }

    if (confirm('确定要提交吗?') ? true : false) {
        var formData = $('#grab_form').serialize();               
         $.ajax({
            url: '/hospital/save',
            data: formData,
            timeout: 20000,//超时时间20秒
            type: 'POST',
            async: true,
            beforeSend: function () {
                // 禁用按钮防止重复提交
                $("#submit_form").attr({ disabled: "disabled" });
                loading = showLoad();
            },
            success: function (res) {
                layer.close(loading);
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1});
                    setTimeout(function (){
                        window.location.reload();
                    }, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function (){
                        window.location.reload();
                    }, 3000);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.close(loading);
                layer.msg('保存失败，请刷新重试', {icon: 2});
            }
        });
    }
});  


$(".recheck-open").click(function () {
    var id = $(this).attr('data-id');
    var lock = false;
    layer.open({
        type: 2,
        area: 'auto',
        scrollbar: false,
        closeBtn: false,
        shadeClose: true,
        skin: 'layui-layer-demo',
        title: '修改科室',
        area: ['70%', '70%'],
        content: "/keshi/relation?id=" + id,
        btn : ['保存','取消'],
        yes: function (index, layero) {
            //防止重复提交
            if (lock) {
                return false;
            }
            lock = true;

            // var fkid = $(layer.getChildFrame(".search_fkid option:selected", index)).val(); // 文本值
            // var skid =  $(layer.getChildFrame(".search_skid option:selected", index)).val(); // 文本值

            // var fkeshi_name = $(layer.getChildFrame(".search_fkid option:selected", index)).text(); // 文本值
            // var skeshi_name = $(layer.getChildFrame(".search_skid option:selected", index)).text(); // 文本值
            // if (!fkid || !skid) {
            //     layer.msg('医院科室不能为空！', {icon: 2});
            //     return false;
            // }

            var miao_fkid = $(layer.getChildFrame(".miao_search_fkid option:selected", index)).val(); // 文本值
            var miao_skid = $(layer.getChildFrame(".miao_search_skid option:selected", index)).val(); // 文本值

            if (!miao_fkid || !miao_skid) {
                layer.msg('王氏科室不能为空！', {icon: 2});
                return false;
            }

            $.ajax({
                url: '/keshi/save-relation',
                data: {
                    'id': id,
                    // 'frist_department_id': fkid,
                    // 'second_department_id': skid,
                    // 'frist_department_name': fkeshi_name,
                    // 'second_department_name': skeshi_name,
                    'miao_frist_department_id': miao_fkid,
                    'miao_second_department_id': miao_skid,
                    "_csrf-backend":$('#_csrf-backend').val()
                },
                timeout: 5000,
                type: 'POST',
                success: function (res) {
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1});
                        setTimeout(function (){
                            window.location.href = window.location.href;
                        }, 1000);
                    } else {
                        layer.msg(res.msg, {icon: 2});
                        setTimeout(function (){
                            window.location.href = window.location.href;
                        }, 2000);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('获取失败，请刷新重试', {icon: 2});
                }
            });

        },
        btn2: function (index, layero) {
            layer.close(index);
        },
        //关闭窗口时回调
        end: function () {
            //解除提交锁定
            lock = false;
        }
    });
    
}); 

</script>