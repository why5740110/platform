<?php

use yii\helpers\Url;
use yii\widgets\LinkPager;
use backend\widget\PageWidget;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$request = \Yii::$app->request;
$this->title = '第三方来源';
?>
<style type="text/css">
    #miaoid{
        height: 38px;line-height: 1.3;line-height: 38px;border-width: 1px;border-style: solid;background-color: #fff;border-radius: 2px;padding: 6px;
    }
    .from-width {
        width: 200px;
    }
    .sp_text_input{
        height: 38px;width:300px;line-height: 1.3;line-height: 38px;border-width: 1px; border-style: solid; background-color: #fff; border-radius: 2px;padding: 6px; }
</style>

<meta name="referrer" content="never">


<?php
$form = ActiveForm::begin(['action' => '/' . Yii::$app->controller->id . '/save', 'method' => 'post', 'options' => ['name' => 'form','class'=>'layui-form','enctype' => 'multipart/form-data'],'id'=>'form_id']);
?>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<input type="hidden" name="id" value="<?php echo $info['id'] ?? '' ?>">
<div class="layui-form-item">
    <label class="layui-form-label from-width">平台类型Type <span style="color: red">*</span></label>
    <div class="layui-input-inline">
        <input id="tp_platform" class="sp_text_input" type="text" name="tp_platform" value="<?php echo $info['tp_platform'] ?? '' ?>" required="required"  lay-verify="required" placeholder="请输入平台类型Type(开发人员编写1-99)" autocomplete="off">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">平台名称<span style="color: red">*</span></label>
    <div class="layui-input-inline">
        <input id="platform_name" class="sp_text_input" type="text" name="platform_name" value="<?php echo $info['platform_name'] ?? '' ?>" required  lay-verify="required" placeholder="请输入平台名称(开发人员编写)" autocomplete="off">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">平台类型<span style="color: red">*</span></label>
    <div class="layui-input-inline">
        <input id="tp_type" class="sp_text_input" type="text" name="tp_type" value="<?php echo $info['tp_type'] ?? '' ?>" required  lay-verify="required" placeholder="请输入平台类型(开发人员编写)" autocomplete="off">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">平台SDK名称<span style="color: red">*</span></label>
    <div class="layui-input-inline">
        <input id="sdk" class="sp_text_input" type="text" name="sdk" value="<?php echo $info['sdk'] ?? '' ?>" required  lay-verify="required" placeholder="请输入输入平台sdk名称(开发人员编写)" autocomplete="off">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">拉取排班脚本维度<span style="color: red">*</span></label>
    <div class="layui-input-inline">

        <input type="radio" name="get_paiban_type" value="department" title="科室"
            <?php
            if(isset($info['get_paiban_type']) && $info['get_paiban_type'] == "department") { ?> checked
        <?php }elseif (empty($info['get_paiban_type'])) { ?>
                checked
            <?php } ?>
        >
        <input type="radio" name="get_paiban_type" value="doctor" title="医生"  <?php if(isset($info['get_paiban_type']) && $info['get_paiban_type'] == "doctor") { ?> checked
        <?php } ?> >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">号源类型<span style="color: red">*</span></label>
    <div class="layui-input-inline">
        <input type="radio" name="schedule_type" value="1" title="挂号"
            <?php if(isset($info['schedule_type']) &&$info['schedule_type'] == "1") { ?> checked
            <?php }elseif (empty($info['schedule_type'])) { ?>
                checked
            <?php } ?>
        >
        <input type="radio" name="schedule_type" value="2" title="加号"
            <?php if(isset($info['schedule_type']) && $info['schedule_type'] == "2") { ?> checked
            <?php } ?>
        >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">状态<span style="color: red">*</span></label>
    <div class="layui-input-block">
        <input type="radio" name="status" value="0" title="禁用"
            <?php if(isset($info['status']) && $info['status'] == "0") { ?> checked
            <?php }elseif (empty($info['status'])) { ?>
                checked
            <?php } ?>
        >
        <input type="radio" name="status" value="1" title="正常"
            <?php if(isset($info['status']) && $info['status'] == "1") { ?> checked
            <?php } ?>
        >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label from-width">开放时间</label>
    <div class="layui-input-inline">
        <input type="text" class="layui-input" id="open_time" name="open_time" placeholder="默认当前日期" value="<?php echo $info['open_time'] ?? '' ?>">
    </div>
</div>
<ul style="padding-top: 10px;">
    <li style="float: left;padding-left: 5px;">
        <table style="text-align: center;height: 40px;">
            <tr>
                <td><button class="layui-btn layui-btn-normal  layui-btn-sm save" type="button" style="margin-left:20px;">保存</button></td>
            </tr>
        </table>
    </li>
</ul>
</br>
</br>
</br>
<?php ActiveForm::end(); ?>
<?php

$platformUpdateUrl = Url::to(['guahao-platform/save']);
?>

<script type="text/javascript">
    //日期组件
    layui.use(['laydate', 'form', 'table'], function() {
        var laydate = layui.laydate;
        var form = layui.form;
        //执行一个laydate实例
        laydate.render({
            elem: '#open_time' //指定元素
        });

        //重置表单选择
        $("#reset").click(function () {
            var fid = getValue().fid;
            var page = getValue().page;
            var str = '?';
            if (fid) {
                str += 'fid=' + fid + '&';
            }
            if (page) {
                str += 'page=' + page
            }
            window.location.href = window.location.href.split('?')[0] + str;
        });
        //监听所有的下拉选框
        form.on('select', function (data) {
            var selecter = "option[value='" + data.value + "']";
            $(this).parents('.layui-form-select').siblings('select').find(selecter).attr("selected", "selected").siblings().removeAttr('selected');
        });

        //监听保存按钮
        $(".save").click(function () {
            //异步提交
            $.ajax({
                url: "<?=$platformUpdateUrl;?>", //提交地址
                data: $("#form_id").serializeArray(), //将表单数据序列化
                type: "POST",
                timeout: 20000, //超时时间20秒
                dataType: "json",
                async: true,
                complete: function () {
                    layer.close(loading);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('保存失败，请刷新重试', {icon: 2, time: 3000});
                },
                success: function (res) {
                    if (res.status == 1) {
                        layer.msg('操作成功！', {icon: 1});
                        setTimeout(function() {
                            window.location.href = '/guahao-platform/list';
                        }, 3000);
                        return false;
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                }
            });
        });
    });
</script>