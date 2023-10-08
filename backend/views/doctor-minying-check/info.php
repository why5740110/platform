<?php
use yii\helpers\Url;
use common\libs\CommonFunc;
use yii\helpers\Html;
$this->title = '审核详情';
$domain = \Yii::$app->params['min_doctor_img_oss_url_prefix'];
?>

<style>
    .t_img{
        width: 100px;
    }

    .t_top_base{
        margin-top: 10px;
    }
    .t_name{
        margin-top:20px;
        display: flex;
    }
    .t_name .name{
        font-size: 15px;
        font-weight: bold;
    }
    .t_name .time{
        font-size: 12px;
        color: #666;
        margin-top:5px;
    }
    .t_hospital{
        font-size: 15px;
        height: 16px;
        border-left: 5px solid #2f74ff;
        display: flex;
        line-height: 16px;
        text-indent: 10px;
        font-weight: bolder;
        color: #333;
        margin-bottom: 15px;
    }
    .t_recommend{
        font-size: 14px;
        line-height: 1.5;
        color: #333;
    }
    .t_top{
        margin-top:30px;
    }
    .t_base{
        display: flex;
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
    }
    .t_base span{
        display: inline-flex;
        padding:10px 0;
    }
    .t_base  img{
        margin-left: 15px;
        margin-bottom: 10px;
        display: inline-block;
        border-radius: 6px;
        width: 80px;
    }
    .t_base :nth-child(1){
        display: flex;
        text-align: right;
    }
    .t_base_img{
        width: 120px;
    }
    .t_base :nth-child(2){
        margin-left: 10px;
    }
    .t_left{
        margin-left: 20px;
    }
    .role{
        color: #606266;
        font-size: 14px;
        line-height: 1.5;
    }
    .t_bottom{
        border-bottom: 6px solid #f6f6f6;
        width: 100%;
        margin-top: 40px;
    }

    .doctor{
        display: flex;
        font-size: 14px;
        color: #333;
        margin-bottom: 30px;
    }
    .doctor img{
        width: 100px;
        border-radius: 6px;
    }
    .doctor .doctorMessage{margin-left: 20px;}
    .doctor .doctorMessage .base_message span{
        margin-right: 10px;
    }
    .doctor .doctorMessage .base_message .name{
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }
    .doctor .doctorMessage .base_time{
        padding-top: 15px;
    }
    .doctor .doctorMessage .base_address{
        padding-top: 15px;
        font-weight: bold;
    }

    .enlargeImg_wrapper {
        display: none;
        position: fixed;
        z-index: 999;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
        background-color: rgba(52, 52, 52, 0.8);
        background-size: 37%;
    }
    .t_base img:hover {
        cursor: zoom-in;
    }
    .enlargeImg_wrapper:hover {
        cursor: zoom-out;
    }
</style>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div>
    <div class="doctor">
        <img src="<?php echo $dataProvider['avatar']; ?>" >
        <div class="doctorMessage">
            <div class="base_message"><span class="name"><?php echo Html::encode($dataProvider['min_doctor_name']); ?></span><span><?php echo Html::encode($dataProvider['min_job_title']); ?></span><span><span><?php echo Html::encode($dataProvider['department']); ?></span></div>
            <div class="base_address"><?php echo Html::encode($dataProvider['min_hospital_name']); ?></div>
        </div>
    </div>

    <div class="t_hospital t_top_base">擅长</div>
    <div class="t_recommend t_left">
        <?php echo Html::encode($dataProvider['good_at']); ?>
    </div>

    <div class="t_hospital t_top_base">医生简介</div>
    <div class="t_recommend t_left">
        <?php echo Html::encode($dataProvider['intro']); ?>
    </div>

    <div class="t_bottom"></div>
    <div class="t_hospital t_top">基本信息</div>
    <div class="t_left">
        <div class="t_base">
            <span class="t_base_img">医生ID：</span>
            <span><?php echo $dataProvider['min_doctor_id']; ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">医生姓名：</span>
            <span><?php echo Html::encode($dataProvider['min_doctor_name']); ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">医生标签：</span>
            <span><?php echo Html::encode($dataProvider['min_doctor_tags']); ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">出诊科室：</span>
            <span><?php echo Html::encode($dataProvider['department']); ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">医院名称：</span>
            <span><?php echo Html::encode($dataProvider['min_hospital_name']); ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">出诊类型：</span>
            <span><?php echo $dataProvider['visitType']; ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">第一执业医院：</span>
            <span><?php echo Html::encode($dataProvider['miao_hospital_name']); ?></span>
        </div>
        <div class="t_base">
            <span class="t_base_img">医生手机号：</span>
            <span><?php echo Html::encode($dataProvider['mobile']); ?></span>
        </div>
    </div>

    <div class="t_bottom"></div>
    <div class="t_hospital t_top">证件信息</div>
    <div class="t_left">
        <div class="t_base">
            <span class="t_base_img">身份证：</span>
            <span><?php echo $dataProvider['id_card_begin']; ?>-<?php echo $dataProvider['id_card_end']; ?></span>
            <?php foreach ($dataProvider['id_card_file'] as $card) { ?>
                <img class="enlargeImg" src="<?php echo $domain . $card; ?>" >
                <div class="imgBox"></div>
            <?php } ?>
        </div>
        <div class="t_base">
            <span class="t_base_img">医师执业证：</span>
            <span><?php echo $dataProvider['practicing_cert_begin']; ?>-<?php echo $dataProvider['practicing_cert_end']; ?></span>
            <?php foreach ($dataProvider['practicing_cert_file'] as $cert) { ?>
                <img class="enlargeImg" src="<?php echo $domain . $cert; ?>" >
                <div class="imgBox"></div>
            <?php } ?>
        </div>
        <div class="t_base">
            <span class="t_base_img">医师资格证：</span>
            <span><?php echo $dataProvider['doctor_cert_begin']; ?>-<?php echo $dataProvider['doctor_cert_end']; ?></span>
            <?php foreach ($dataProvider['doctor_cert_file'] as $cert) { ?>
                <img class="enlargeImg" src="<?php echo $domain . $cert; ?>" >
                <div class="imgBox"></div>
            <?php } ?>
        </div>
        <div class="t_base">
            <span class="t_base_img">专业技术资格证：</span>
            <span><?php echo $dataProvider['professional_cert_begin']; ?>-<?php echo $dataProvider['professional_cert_end']; ?></span>
            <?php foreach ($dataProvider['professional_cert_file'] as $cert) { ?>
                <img class="enlargeImg" src="<?php echo $domain . $cert; ?>" >
                <div class="imgBox"></div>
            <?php } ?>
        </div>
        <?php if ($dataProvider['visit_type'] == 2) { ?>
            <div class="t_base">
                <span class="t_base_img">多点执业证明：</span>
                <span><?php echo $dataProvider['multi_practicing_cert_begin']; ?>-<?php echo $dataProvider['multi_practicing_cert_end']; ?></span>
                <?php foreach ($dataProvider['multi_practicing_cert_file'] as $cert) { ?>
                    <img class="enlargeImg" src="<?php echo $domain . $cert; ?>" >
                    <div class="imgBox"></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div class="t_bottom"></div>
    <div class="t_hospital t_top">审核操作</div>
    <div class="t_left">
        <div class="t_base">
            <span class="t_base_img">初审状态：</span>
            <span>通过</span>
        </div>
        <div class="t_base">
            <span class="t_base_img">二审状态：</span>
            <?php if ($dataProvider['check_status'] == 2): ?>
                <div>
                    <input type="radio" name="check_status" checked value="1" style="display: inline;"> 通过
                    <input type="radio" name="check_status" value="2"> 拒绝
                </div>
            <?php else: ?>
                <span><?php echo $dataProvider['check_status_desc']; ?></span>
            <?php endif; ?>
        </div>

        <?php if ($dataProvider['check_status'] == 5): ?>
            <div class="t_base">
                <span class="t_base_img">二审拒绝原因：</span>
                <span><?php echo Html::encode($dataProvider['fail_reason']); ?></span>
            </div>
        <?php endif; ?>

        <div class="fail_reason"  style="display: none;">
            <input type="radio" name="reason_flag" value="1" checked data="资质图片上未展示所填写的任何属性或者展示不完整"> 资质图片上未展示所填写的任何属性或者展示不完整<br />
            <input type="radio" name="reason_flag" value="2" data="医生基本信息不正确"> 医生基本信息不正确<br />
            <input type="radio" name="reason_flag" value="3"> 其他<br />
            <div class="other_div" style="display: none;">
                <textarea rows="5" cols="50" id="other_reason" style="resize:none;" placeholder="请填写具体原因"></textarea>
            </div>
        </div>
        <?php if ($dataProvider['check_status'] == 2): ?>
            <button class="layui-btn layui-btn-normal submit_form" type="button" data-sub_type="1" id="submit_form">提交</button>
        <?php endif; ?>

    </div>
</div>

<script type="text/javascript">
    //选择审核状态
    $(document).ready(function() {
        $("input[name='check_status']").click(function() {
            if($(this).val() == 2) {
                $(".fail_reason").show();
                var reason_flag = $("input[name='reason_flag']:checked").val();
                if (reason_flag == 3) {
                    $(".other_div").show();
                } else {
                    $(".other_div").hide();
                }
            } else {
                $(".fail_reason").hide();
                $(".other_div").hide();
            }
        });

        $("input[name='reason_flag']").click(function() {
            if($(this).val() == 3) {
                $(".other_div").show();
            } else {
                $(".other_div").hide();
            }
        });
    });

    //提交审核
    var id = "<?php echo $dataProvider['min_doctor_id']; ?>";
    $(".submit_form").click(function (event) {
        var check_status = $("input[name='check_status']:checked").val();
        var reason_flag = $("input[name='reason_flag']:checked").val();
        var reason = '';
        if (check_status == 2) {
            if (reason_flag == 3) {
                reason = $("#other_reason").val();
                if (reason == '') {
                    layer.msg('请填写具体原因', {icon: 2});
                    return false;
                }
            } else {
                reason= $("input[name='reason_flag']:checked").attr('data');
            }
        }

        if (confirm('确定要审核吗?') ? true : false) {
            $.ajax({
                url: '/doctor-minying-check/audit',
                data: {"id":id, "status":check_status, fail_reason:reason,"_csrf-backend":$('#_csrf-backend').val()},
                timeout: 20000,//超时时间20秒
                type: 'POST',
                async: true,
                beforeSend: function () {
                    loading = showLoad();
                },
                complete:function() {
                    layer.close(loading);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.close(loading);
                    layer.msg('审核失败，请刷新重试', {icon: 2});
                },
                success: function (res) {
                    console.log(res);
                    layer.close(loading);
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1});
                        window.location.reload();
                        setTimeout(function (){
                            window.location.reload();
                            window.location.href='/doctor-minying-check/info?id=' + id;
                        }, 1000);
                    } else {
                        layer.msg(res.msg, {icon: 2});
                        setTimeout(function (){
                            window.location.reload();
                        }, 3000);
                    }
                },
            });
        }
    });
</script>

<script type="text/javascript">
    $(function() {
        enlargeImg();
    })
    //关闭并移除图层
    function closeImg() {
        $('.enlargeImg_wrapper').fadeOut(200).remove();
    }
    //查看大图
    function enlargeImg() {
        $(".enlargeImg").click(function () {
            $('.imgBox').html("<div  class='enlargeImg_wrapper'></div>");
            var imgSrc = $(this).attr('src');
            $(".enlargeImg_wrapper").css("background-image", "url(" + imgSrc + ")");
            $('.enlargeImg_wrapper').fadeIn(200);
        })
        $('.imgBox').on('click', '.enlargeImg_wrapper', function () {
            $('.enlargeImg_wrapper').fadeOut(200).remove();
        })
    }
</script>