<?php
use yii\helpers\Url;
use common\libs\CommonFunc;
use yii\helpers\Html;
$this->title = '审核详情';
$domain = \Yii::$app->params['min_hospital_img_oss_url_prefix'];
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
        margin-right: 15px;
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
    <div>
        <div class="name" style="font-size:18px;color: rgb(85, 85, 85);font-family: '微软雅黑 Bold, 微软雅黑, sans-serif'; font-weight: 700;">
            <?php echo Html::encode($dataProvider['min_hospital_name']); ?>
        </div>
        <div class="time">创建时间: <?php echo $dataProvider['create_time']; ?></div>
    </div>
    <hr />
</div>
<div class="t_hospital t_top_base">医院简介</div>
<div class="t_recommend t_left">
    <?php echo Html::encode($dataProvider['min_hospital_introduce']); ?>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">基本信息</div>
<div class="t_left">
    <div class="t_base">
        <span class="t_base_img">医院ID：
        </span> <span><?php echo $dataProvider['min_hospital_id']; ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">医院名称：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_name']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">医院标签：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_tags']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">医院类型：</span>
        <span><?php echo $dataProvider['min_hospital_type']; ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">医院等级：</span>
        <span><?php echo $dataProvider['min_hospital_level']; ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">医院性质：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_nature']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">所在地区：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_province_name'] . '-' . $dataProvider['min_hospital_city_name'] . '-' . $dataProvider['min_hospital_county_name']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">详细地址：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_address']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">乘车路线：</span>
        <span><?php echo Html::encode($dataProvider['min_bus_line']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">联系电话：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_phone']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">更新时间：</span>
        <span><?php echo $dataProvider['create_time']; ?></span>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">备案信息</div>
<div class="t_left">
    <div class="t_base ">
        <span class="t_base_img">单位名称：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_name']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">营业执照：</span>
        <?php foreach ($dataProvider['min_business_license'] as $business) { ?>
            <img class="enlargeImg" src="<?php echo $domain . $business; ?>" >
            <div class="imgBox"></div>
        <?php } ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医疗许可证件：</span>
        <?php foreach ($dataProvider['min_medical_license'] as $medical) { ?>
            <img class="enlargeImg" src="<?php echo $domain . $medical; ?>" >
            <div class="imgBox"></div>
        <?php } ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">卫健委备案：</span>
        <?php foreach ($dataProvider['min_health_record'] as $health) { ?>
            <img class="enlargeImg" src="<?php echo $domain . $health; ?>" >
            <div class="imgBox"></div>
        <?php } ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医疗广告证：</span>
        <?php foreach ($dataProvider['min_medical_certificate'] as $certificate) { ?>
            <img class="enlargeImg" src="<?php echo $domain . $certificate; ?>" >
            <div class="imgBox"></div>
        <?php } ?>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">其他信息</div>
<div class="t_left">
    <div class="t_base">
        <span class="t_base_img">医院联系人：</span>
        <span><?php echo Html::encode($dataProvider['min_hospital_contact']); ?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">联系人电话：
        </span> <span><?php echo Html::encode($dataProvider['min_hospital_contact_phone']); ?></span>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">挂号规则</div>
<div class="t_left">
    <span class="role"><span><?php echo Html::encode($dataProvider['min_guahao_rule']); ?></span></span>
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
        <input type="radio" name="reason_flag" value="2" data="医院基本信息不正确"> 医院基本信息不正确<br />
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
    var id = "<?php echo $dataProvider['min_hospital_id']; ?>";
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
                url: '/hospital-minying-check/audit',
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
                            window.location.href='/hospital-minying-check/info?id=' + id;
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