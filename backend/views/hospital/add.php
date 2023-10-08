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
$this->title = '添加医院科室';
?>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <div class="form-group--4">
    <?php $form = ActiveForm::begin(['action' => Url::to(['record/grab']), 'method' => 'post', 'options' => ['name' => 'form', 'id'=>'grab_form', 'class' => 'layui-form']]);?>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo $hospital['name']??'';?>">
                <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">

                </select>
            </div>
        </div>
        <br/>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="frist_department_id" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">请选择一级科室</option>
                    <?php if(!empty($fkeshi_list)): ?>
                        <?php foreach($fkeshi_list as $value):?>
                        <option value="<?=$value['department_id']?>"><?=$value['department_name']?></option>
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
                </select>
            </div>
        </div>

        <input name="frist_department_name" type="hidden" value="" id="fkeshi_name" />
        <input name="second_department_name" type="hidden" value="" id="skeshi_name" />
        <br/>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_frist_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">请选择王氏一级科室</option>
                    <?php if(!empty($miao_fkeshi_list)): ?>
                        <?php foreach($miao_fkeshi_list as $value):?>
                        <option value="<?=$value['id']?>"><?=$value['name']?></option>
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
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">科室地址:</label>
            <div class="layui-input-block" style="width:150px;margin-left:100px;">
                <input type="text" name="address" value="" placeholder="请输入科室地址" autocomplete="off" class="layui-input address">
            </div>
        </div>

        <div class="layui-form-item">
          <div class="layui-input-block">
            <button class="layui-btn" lay-submit type="button" id="submit_form" lay-filter="formDemo">添加</button>
          </div>
        </div>

    <?php ActiveForm::end();?>
    </div>

</div>

<script type="text/javascript">

layui.use('form', function(){
    var form = layui.form;
    $(".layui-input").on("input",function(e){
        var name = e.delegateTarget.value;
        if(name == ''){
            return false;
        }
        var hosUrl = "/doctor/ajax-hos";
        $.get(hosUrl, {'name':name,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
            if(data.code == 200){
                var html = '<option value="">请选择医院</option>';
                $.each(data.hos, function (i, v){
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



$("#submit_form").click(function (event) {
    if ($(this).attr('disabled') == 'disabled') {
        return false;
    } 
    var id = $('.hos option:selected').val(); // 文本值
    var fkid = $('.search_fkid option:selected').val(); // 文本值
    var skid = $('.search_skid option:selected').val(); // 文本值

    var miao_fkid = $('.miao_search_fkid option:selected').val(); // 文本值
    var miao_skid = $('.miao_search_skid option:selected').val(); // 文本值
    var search_fkid = $('.search_fkid option:selected').text(); // 文本值
    $('#fkeshi_name').val(search_fkid);

    var search_skid = $('.search_skid option:selected').text(); // 文本值
    $('#skeshi_name').val(search_skid);

    if(!id){
        layer.msg('医院不能为空！', {icon: 2});
        return false;
    }

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
                        window.location.href='/keshi-relation/keshi-list?hospital_id='+id;
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