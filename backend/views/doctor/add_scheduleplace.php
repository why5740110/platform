<?php

use common\libs\CommonFunc; //引入辅助表单类
use common\models\BaseDoctorHospitals; //引入数据小插件类
// use yii\timepicker\TimePicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm; //新分页
$this->title = '添加多点执业';
?>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-form">
    <?php
$form = ActiveForm::begin(['action' => Url::to(['doctor/save-scheduleplace']), 'method' => 'post', 'options' => ['name' => 'form'], 'id' => 'grab_form']);
?>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width:87px;">医院医生:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text"  id="docname" placeholder="请输入加号的医生id" autocomplete="off" class="layui-input doc_name" value="">
                <select name="doctor_id" lay-verify="required" lay-search lay-filter="doc" class="doc" id="doc">
                </select>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label" style="width:87px;">多点执业医院:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input hos_name" value="">
                <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label" style="width:87px;"></label>
            <button type="reset" style="width: 70px;" class="layui-btn layui-btn-normal">重置</button>
            <button type="button" style="width: 70px;" class="layui-btn " id="submit_form">确认</button>
        </div>

        <input type="hidden" name="realname" value="" id="doc_name">
        <input type="hidden" name="hos_name" value="" id="hos_name">



    <?php ActiveForm::end();?>


</div>


<script type="text/javascript">

layui.use('form', function(){
    form = layui.form;
});

$(".doc_name").on("input",function(e){
    var name = e.delegateTarget.value;
    if(name == ''){
        return false;
    }
    var hosUrl = "/keshi/ajax-doctor";
    $.get(hosUrl, {'name':name,'is_incr':1,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
        if(res.status == 1){
            var html = '<option value="">请选择医生</option>';
            $.each(res.data, function (i, v){
                html += '<option value="'+v.doctor_id+'">'+v.realname+'</option>';
            });
            $(".doc").html(html);
            //重新渲染select
            form.render('select');
        }else{
            layer.msg('获取信息失败，请稍后重试！', {icon: 2});
        }
    });
});

$(".hos_name").on("input",function(e){
    var name = e.delegateTarget.value;
    if(name == ''){
        return false;
    }
    var hosUrl = "/keshi/ajax-hos";
    $.get(hosUrl, {'name':name,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
        if(res.status == 1){
            var html = '<option value="">请选择医院</option>';
            $.each(res.data, function (i, v){
                html += '<option value="'+v.id+'">'+v.name+'</option>';
            });
            $(".hos").html(html);
            //重新渲染select
            form.render('select');
        }else{
            layer.msg('获取信息失败，请稍后重试！', {icon: 2});
        }
    });
});


$("#submit_form").click(function (event) {
    if ($(this).attr('disabled') == 'disabled') {
        return false;
    }

    var doc_id = $('.doc option:selected').val(); // 文本值
    var hos_id = $('.hos option:selected').val(); // 文本值

    var doc_name = $('#doc option:selected').text(); // 文本值
    var hos_name = $('#hos option:selected').text(); // 文本值

    $('#doc_name').val(doc_name);
    $('#hos_name').val(hos_name);

    if (!doc_id || !hos_id) {
        layer.msg('医生或者医院不能为空！', {icon: 2});
        return false;
    }

    if (confirm('确定要提交吗?') ? true : false) {
        var formData = $('#grab_form').serialize();
        var url =  $('#grab_form').attr('action');
         $.ajax({
            url: url,
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
                        window.location.href='/doctor/doc-scheduleplace';
                        // window.location.reload();
                    }, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function (){
                        window.location.href='/doctor/doc-scheduleplace';
                        // window.location.reload();
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

</script>