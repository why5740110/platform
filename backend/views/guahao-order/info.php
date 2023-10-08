<?php
use yii\helpers\Url;
use common\libs\CommonFunc;
use yii\helpers\Html;

$platform_list = CommonFunc::getTpPlatformNameList();
?>
<style>
    .eys_img{
        width: 16px;
    }
</style>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div style="width: 100%">
    <span style="float: left">
        订单详情
    </span>
    <span style="float: right;color:red;">
        订单状态： <?php echo Html::encode($dataProvider['state_desc']); ?>
    </span>
</div>
<br/>
<div style="width: 100%;display: inline-block;">
    <span style="float: left">
        流水号：<?php echo Html::encode($dataProvider['order_sn']); ?>
    </span>
    <span style="float: right;color:red;">
        状态备注： <?php echo $dataProvider['state_desc']; ?>
    </span>
</div>
<br/>
<div style="width: 100%;display: inline-block;">
    <span style="float: left">
        第三方流水号：<?php echo Html::encode($dataProvider['tp_order_id']); ?>
    </span>
    <?php if ($dataProvider['state'] == 6 && !empty($dataProvider['invalid_type']) && !empty($dataProvider['invalid_reason'])) { ?>
    <span style="float: right;color:red;">
        失效原因： <?php echo Html::encode($dataProvider['invalid_type']); ?>
    </span>
    <?php } ?>
</div>
<div style="width: 100%;display: inline-block;">
    <span style="float: left">
        来源方订单号：<?php echo Html::encode($dataProvider['coo_order_id']); ?>
    </span>
    <?php if ($dataProvider['state'] == 6 && !empty($dataProvider['invalid_type']) && !empty($dataProvider['invalid_reason'])) { ?>
        <span style="float: right;color:red;">
        具体内容： <?php echo Html::encode($dataProvider['invalid_reason']); ?>
    </span>
    <?php } ?>
</div>
<br/>

<table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
    <thead>
    <caption>就诊人信息</caption>
    </thead>
    <tbody align="center" valign="center">
    <tr >
        <td style="background: #ccc">就诊人姓名</td>
        <td ><span class="info_patient_name"><?php echo Html::encode($dataProvider['patient_name']); ?> </span><span id="patientNameShow" showType="1" style="float: right; color: blue;cursor:pointer;"><img
                        src="/img/eys_close.png"  class="eys_img eys_img_patient_name" alt=""></span></td>
        <td style="background: #ccc">手机号</td>
        <td><span class="info_mobile"><?php echo Html::encode($dataProvider['mobile']); ?></span> </span><span id="mobileShow" showType="1" style="float: right; color: blue;cursor:pointer;"><img
                        src="/img/eys_close.png"  class="eys_img eys_img_mobile" alt=""></span></td>
    </tr>
    <tr>
        <td style="background: #ccc">性别</td>
        <td><?php echo $dataProvider['gender']==1?'男':'女'; ?></td>
        <td style="background: #ccc">身份证号</td>
        <td><span class="info_card"><?php echo Html::encode($dataProvider['card']); ?></span></span><span id="cardShow" showType="1" style="float: right; color: blue;cursor:pointer;"><img
                        src="/img/eys_close.png"  class="eys_img eys_img_card" alt=""></span></td>
    </tr>
    </tbody>
</table>
<br/>
<table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
    <thead>
    <caption>就诊信息</caption>

    </thead>
    <tbody align="center" valign="center">
    <tr >
        <td style="background: #ccc">就诊日期</td>
        <td><?php echo Html::encode($dataProvider['visit_time']) . ' ' . $dataProvider['nooncode']; ?></td>
        <td style="background: #ccc">创建时间</td>
        <td><?php echo Html::encode($dataProvider['create_time']); ?></td>
    </tr>
    <tr>
        <td style="background: #ccc">就诊时间精确小时/分钟</td>
        <td><?php echo $dataProvider['visit_time_long']; ?></td>
        <td style="background: #ccc">取号时间</td>
        <td colspan=""><?php echo isset($dataProvider['taketime_desc']) ? Html::encode($dataProvider['taketime_desc']) : ""; ?></td>
    </tr>
    <tr>
        <td style="background: #ccc">预约医院</td>
        <td><?php echo Html::encode($dataProvider['hospital_name']); ?></td>
        <td style="background: #ccc">取号登记号（密码）</td>
        <td><?php echo isset($dataProvider['visit_number']) ? Html::encode($dataProvider['visit_number']) : ""; ?></td>
    </tr>
    <tr >
        <td style="background: #ccc">预约科室</td>
        <td><?php echo Html::encode($dataProvider['department_name']); ?></td>
        <td style="background: #ccc">预约记录状态</td>
        <td colspan=""><?php echo isset($dataProvider['state']) ? $dataProvider['state'] : ""; ?></td>

    </tr>
    <tr >
        <td style="background: #ccc">医生姓名</td>
        <td><?php echo Html::encode($dataProvider['doctor_name']); ?></td>
        <td style="background: #ccc">门诊类型</td>
        <td><?php echo $dataProvider['visit_type']; ?></td>

    </tr>
  <!--  <tr >
        <td style="background: #ccc">支付状态</td>
        <td><?php /*echo '--'; */?></td>
        <td style="background: #ccc">取号时间</td>
        <td><?php /*echo $dataProvider['taketime_desc']; */?></td>
    </tr>-->
    <tr >
       <!-- <td style="background: #ccc">门诊类型</td>
        <td><?php /*echo '--'; */?></td>-->
        <td style="background: #ccc">医事服务费</td>
        <td><?php echo $dataProvider['visit_cost']; ?></td>
        <td style="background: #ccc">初/复诊</td>
        <td><?php echo $dataProvider['famark_type']==1?'初诊':'复诊'; ?></td>
    </tr>
    <tr>
        <td style="background: #ccc">来源</td>
        <!--<td><?php /*echo $dataProvider['tp_platform']; */?></td>-->
        <td><?=$platform_list[$dataProvider['tp_platform']] ?? '';?></td>
        <td style="background: #ccc">症状/疾病名</td>
        <td><?php echo isset($dataProvider['symptom']) ? Html::encode($dataProvider['symptom']) : ""; ?></td>
    </tr>
    <tr>
        <td style="background: #ccc">就诊地址</td>
        <td><?php echo isset($dataProvider['visit_address']) ? Html::encode($dataProvider['visit_address']) : ""; ?></td>
        <td style="background: #ccc">取号地址</td>
        <td colspan=""><?php echo isset($dataProvider['takeway']) ? Html::encode($dataProvider['takeway']) : ""; ?></td>
    </tr>
    <tr>
        <td style="background: #ccc">短信备注</td>
        <td colspan="3">
            <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
                <tbody align="center" valign="center">
                <tr>
                    <td style="background: #ccc">手机号</td>
                    <td style="background: #ccc">短信内容</td>
                    <td style="background: #ccc">时间</td>
                </tr>
                <?php
                if (!empty($message)) {
                    foreach ($message as $v) {
                        ?>
                        <tr>
                            <td><?php echo Html::encode($v['mobile']); ?></td>
                            <td><?php echo Html::encode($v['message']); ?></td>
                            <td><?php echo $v['time'] ? date('Y-m-d H:i:s') : ''; ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="background: #ccc">取消原因</td>
        <td colspan="3" class="text-left">
            <?= isset($dataProvider['remark']) ? Html::encode($dataProvider['remark']) : '';?>
        </td>
    </tr>
    </tbody>
</table>

<table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
    <thead>
    <caption>挂号规则</caption>
    </thead>
    <tbody align="center" valign="center">
    <tr >
        <td style="background: #ccc">医院允许取消天数</td>
        <td>医院允许取消时间</td>
        <td style="background: #ccc">医院放号天数</td>
        <td>医院放号时间</td>
    </tr>
    <tr>
        <td style="background: #ccc"><?php echo $dataProvider['tp_allowed_cancel_day']; ?></td>
        <td><?php echo $dataProvider['tp_allowed_cancel_time']; ?></td>
        <td style="background: #ccc"><?php echo $dataProvider['tp_open_day']; ?></td>
        <td><?php echo $dataProvider['tp_open_time']; ?></td>
    </tr>
    </tbody>
</table>
<?php

$orderSecretShow = Url::to(['/guahao-order/secret-show?']);

?>

<script type="text/javascript">
    $("#patientNameShow").click(function (event) {
        var type = $("#patientNameShow").attr("showType");
        if(type == 1){
           var show_hide = 1
        }else{
            var show_hide = 2;
        }
        $.ajax({
            url:"<?=$orderSecretShow;?>",//提交地址
            data:{'order_sn':"<?php echo $dataProvider['order_sn']; ?>","secret_type":1,'show_hide':show_hide,"_csrf-backend":$('#_csrf-backend').val()},
            type:"GET",
            dataType:"json",
            async: true,
            beforeSend: function () {
                loading = showLoad();
            },
            complete:function() {
                layer.close(loading);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('操作失败，请刷新重试', {icon: 2});
            },
            success: function(res){
                if(res.data.info){
                    $(".info_patient_name").text(res.data.info);
                    if(type == 1){
                        // 眼睛图片睁开，属性值设置2
                        $(".eys_img_patient_name").attr("src",'/img/eys_open.png');
                        $("#patientNameShow").attr("showType",2);
                    }else{
                        // 眼睛图片闭上，属性值设置1
                        $(".eys_img_patient_name").attr("src",'/img/eys_close.png');
                        $("#patientNameShow").attr("showType",1);
                    }
                }else{
                    layer.msg(res.msg, {icon: 2});
                }
            },
        });
    });
    $("#mobileShow").click(function (event) {
        var type = $("#mobileShow").attr("showType");
        if(type == 1){
            var show_hide = 1
        }else{
            var show_hide = 2;
        }
        $.ajax({
            url:"<?=$orderSecretShow;?>",//提交地址
            data:{'order_sn':"<?php echo $dataProvider['order_sn']; ?>","secret_type":2,'show_hide':show_hide,"_csrf-backend":$('#_csrf-backend').val()},
            type:"GET",
            dataType:"json",
            async: true,
            beforeSend: function () {
                loading = showLoad();
            },
            complete:function() {
                layer.close(loading);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('操作失败，请刷新重试', {icon: 2});
            },
            success: function(res){
                if(res.data.info){
                    $(".info_mobile").text(res.data.info);
                    if(type == 1){
                        // 眼睛图片睁开，属性值设置2
                        $(".eys_img_mobile").attr("src",'/img/eys_open.png');
                        $("#mobileShow").attr("showType",2);
                    }else{
                        // 眼睛图片闭上，属性值设置1
                        $(".eys_img_mobile").attr("src",'/img/eys_close.png');
                        $("#mobileShow").attr("showType",1);
                    }
                }else{
                    layer.msg(res.msg, {icon: 2});
                }
            },
        });
    });
    $("#cardShow").click(function (event) {
        var type = $("#cardShow").attr("showType");
        if(type == 1){
            var show_hide = 1
        }else{
            var show_hide = 2;
        }
        $.ajax({
            url:"<?=$orderSecretShow;?>",//提交地址
            data:{'order_sn':"<?php echo $dataProvider['order_sn']; ?>","secret_type":3,'show_hide':show_hide,"_csrf-backend":$('#_csrf-backend').val()},
            type:"GET",
            dataType:"json",
            async: true,
            beforeSend: function () {
                loading = showLoad();
            },
            complete:function() {
                layer.close(loading);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('操作失败，请刷新重试', {icon: 2});
            },
            success: function(res){
                if(res.data.info){
                    $(".info_card").text(res.data.info);
                    if(type == 1){
                        // 眼睛图片睁开，属性值设置2
                        $(".eys_img_card").attr("src",'/img/eys_open.png');
                        $("#cardShow").attr("showType",2);
                    }else{
                        // 眼睛图片闭上，属性值设置1
                        $(".eys_img_card").attr("src",'/img/eys_close.png');
                        $("#cardShow").attr("showType",1);
                    }
                }else{
                    layer.msg(res.msg, {icon: 2});
                }
            },
        });
    });

</script>