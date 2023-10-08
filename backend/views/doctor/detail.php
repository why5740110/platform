<?php
/**
 * Created by wangwencai.
 * @file: detail.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-25
 */
$this->title = '医生详情';

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<div class="doctor">
    <?php if($base_model->avatar):?>
        <div class="col-sm-12 isup" style="width:auto;">
            <img src="<?=$base_model->avatar ?>" />
        </div>
    <?php elseif($base_model->source_avatar):?>
    <div class="col-sm-12 isup" style="width:auto;">
        <img src="<?=$base_model->source_avatar ?>" />
    </div>
    <?php endif; ?>
    <div class="doctorMessage">
        <div class="base_message"><span
                    class="name"><?= Html::encode($base_model->realname) ?></span><span><?= Html::encode($base_model->job_title) ?></span><span><?= Html::encode($base_model->second_department_name) ?></span>
        </div>
        <div class="base_address"><?= Html::encode($base_model->hospital_name) ?></div>
        <div class="base_time">开通时间：<?= date('Y-m-d H:i:s', $base_model->create_time) ?></div>
    </div>
</div>
<div class="t_hospital t_top_base">擅长</div>
<div class="t_recommend t_left">
    <?= Html::encode($base_model->doctorInfo->good_at) ?>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top_base">医生简介</div>
<div class="t_recommend t_left">
    <?= Html::encode($base_model->doctorInfo->profile) ?>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">基本信息</div>
<div class="t_left">
    <div class="t_base">
        <span>医生ID：</span> <span><?= $base_model->doctor_id ?></span>
    </div>
    <div class="t_base">
        <span>医生姓名：</span> <span><?= Html::encode($base_model->realname) ?></span>
    </div>
    <div class="t_base">
        <span>医生标签：</span> <span><?= isset($info_data['doctor_tags']) ? Html::encode($info_data['doctor_tags']) : ''; ?></span>
    </div>
    <div class="t_base">
        <span>出诊科室：</span> <span><?= Html::encode($base_model->frist_department_name) ?>
            - <?= Html::encode($base_model->second_department_name) ?></span>
    </div>
    <div class="t_base">
        <span>医院名称：</span> <span><?= Html::encode($base_model->hospital_name) ?></span>
    </div>
    <div class="t_base">
        <span>出诊类型：</span> <span><?= $info_data['visit_type'] ?></span>
    </div>
    <div class="t_base">
        <span>第一执业医院：</span> <span><?= isset($info_data['multi_hospital_name']) ? Html::encode($info_data['multi_hospital_name']) : ''; ?></span>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">证件信息</div>
<div class="t_left">
    <div class="t_base">
        <span class="t_base_img">身份证：</span>
        <span class="t_base_date"><?= $info_data['id_card_expire']?></span>
        <?php foreach ($info_data['id_card_file'] as $id_img): ?>
            <span><img class="enlargeImg" src="<?= $id_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医师执业证：</span>
        <span class="t_base_date"><?= $info_data['practicing_cert_expire']?></span>
        <?php foreach ($info_data['practicing_cert_file'] as $practicing_img): ?>
            <span><img class="enlargeImg" src="<?= $practicing_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医师资格证：</span>
        <span class="t_base_date"><?= $info_data['doctor_cert_expire']?></span>
        <?php foreach ($info_data['doctor_cert_file'] as $doctor_img): ?>
            <span><img class="enlargeImg" src="<?= $doctor_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">专业技术资格证：</span>
        <span class="t_base_date"><?= $info_data['professional_cert_expire']?></span>
        <?php foreach ($info_data['professional_cert_file'] as $professional_img): ?>
            <span><img class="enlargeImg" src="<?= $professional_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">多点执业证明：</span>
        <span class="t_base_date"><?= $info_data['multi_practicing_cert_expire']?></span>
        <?php foreach ($info_data['multi_practicing_cert_file'] as $multi_img): ?>
            <span><img class="enlargeImg" src="<?= $multi_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">其他信息</div>
<div class="t_left">
    <div class="t_base">
        <span>医生联系方式：</span> <span><?= Html::encode($info_data['mobile']) ?></span>
    </div>
</div>

<style>
    .t_img {
        width: 120px;
    }

    .t_top_base {
        margin-top: 10px;
    }

    .t_base_date {
        align-items: center;
    }

    .t_name {
        margin-top: 20px;
        display: flex;
    }

    .t_name .name {
        font-size: 15px;
        font-weight: bold;
    }

    .t_name .time {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .t_hospital {
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

    .t_recommend {
        font-size: 14px;
        line-height: 1.5;
        color: #333;
    }

    .t_top {
        margin-top: 30px;
    }

    .t_base {
        display: flex;
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
        align-items: center;
    }

    .t_base span {
        display: inline-flex;
        padding: 10px 0;
    }

    .t_base img {
        margin-bottom: 10px;
        margin-left: 10px;
        display: inline-block;
        border-radius: 6px;
        width: 80px;
    }

    .t_base :nth-child(1) {
        display: flex;
        text-align: right;
    }

    .t_base_img {
        width: 120px;
    }

    .t_base :nth-child(2) {
        margin-left: 10px;
    }

    .t_left {
        margin-left: 20px;
    }

    .role {
        color: #606266;
        font-size: 14px;
        line-height: 1.5;
    }

    .t_bottom {
        border-bottom: 6px solid #f6f6f6;
        width: 100%;
        margin-top: 40px;
    }

    .doctor {
        display: flex;
        font-size: 14px;
        color: #333;
        margin-bottom: 30px;
    }

    .doctor img {
        width: 100px;
        border-radius: 6px;
    }

    .doctor .doctorMessage {
        margin-left: 20px;
    }

    .doctor .doctorMessage .base_message span {
        margin-right: 10px;
    }

    .doctor .doctorMessage .base_message .name {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .doctor .doctorMessage .base_time {
        padding-top: 15px;
    }

    .doctor .doctorMessage .base_address {
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