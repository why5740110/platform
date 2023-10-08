<?php
/**
 * Created by PhpStorm.
 * @file detail.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-27
 */
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;

$this->title = '医院详情';

?>
<div class="doctor">
    <div class="doctorMessage">
        <div class="base_message">
            <span class="name"><?= (isset($base_model->hospital_name) && !empty($base_model->hospital_name)) ? Html::encode($base_model->hospital_name) : ''; ?></span>
        </div>
        <div class="base_time">创建时间：<?= date('Y-m-d H:i:s', $base_model->create_time) ?></div>
    </div>
</div>
<div class="t_hospital t_top_base">医院简介</div>
<div class="t_recommend t_left">
    <?= Html::encode(ArrayHelper::getValue($info_data, 'min_hospital_introduce')); ?>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">基本信息</div>
<div class="t_left">
    <div class="t_base">
        <span>医院ID：</span> <span><?= $base_model->hospital_id ?></span>
    </div>
    <div class="t_base">
        <span>医院名称：</span> <span><?= (isset($base_model->hospital_name) && !empty($base_model->hospital_name)) ? Html::encode($base_model->hospital_name) : ''; ?></span>
    </div>
    <div class="t_base">
        <span>医院标签：</span> <span><?= isset($info_data['min_hospital_tags']) ? Html::encode($info_data['min_hospital_tags']) : ''; ?></span>
    </div>
    <div class="t_base">
        <span>医院类型：</span> <span><?= $info_data['min_hospital_type']; ?></span>
    </div>
    <div class="t_base">
        <span>医院等级：</span> <span><?= $base_model->tp_hospital_level ?></span>
    </div>
    <div class="t_base">
        <span>医院性质：</span> <span><?= !empty($info_data['min_hospital_nature']) ? $info_data['min_hospital_nature'] : '公立' ?></span>
    </div>
    <div class="t_base">
        <span>所在地区：</span> <span><?= $info_data['min_hospital_province_name'] .'-'. $info_data['min_hospital_city_name'] .'-'. $info_data['min_hospital_county_name'] ?></span>
    </div>
    <div class="t_base">
        <span>详细地址：</span> <span><?= $info_data['min_hospital_province_name'] .'-'. $info_data['min_hospital_city_name'] .'-'. $info_data['min_hospital_county_name']. Html::encode(ArrayHelper::getValue($info_data, 'min_hospital_address')); ?></span>
    </div>
    <div class="t_base">
        <span>乘车路线：</span> <span><?= Html::encode(ArrayHelper::getValue($info_data, 'min_bus_line')); ?></span>
    </div>
    <div class="t_base">
        <span>联系电话：</span> <span><?= Html::encode(ArrayHelper::getValue($info_data, 'min_hospital_phone')); ?></span>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">备案信息</div>
<div class="t_left">
    <div class="t_base">
        <span class="t_base_img">单位名称：</span>
        <span class="t_base_date"><?=Html::encode(ArrayHelper::getValue($info_data, 'min_company_name'));?></span>
    </div>
    <div class="t_base">
        <span class="t_base_img">营业执照：</span>
        <?php foreach ($info_data['min_business_license'] as $id_img): ?>
            <span><img class="enlargeImg" src="<?= $id_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医疗许可证件：</span>
        <?php foreach ($info_data['min_medical_license'] as $practicing_img): ?>
            <span><img class="enlargeImg" src="<?= $practicing_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">卫健委备案：</span>
        <?php foreach ($info_data['min_health_record'] as $doctor_img): ?>
            <span><img class="enlargeImg" src="<?= $doctor_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">医疗广告证：</span>
        <?php foreach ($info_data['min_medical_certificate'] as $professional_img): ?>
            <span><img class="enlargeImg" src="<?= $professional_img; ?>"/></span>
            <div class="imgBox"></div>
        <?php endforeach; ?>
    </div>
    <div class="t_base">
        <span class="t_base_img">诊疗项目：</span>
        <span class="t_base_date"><?= $info_data['min_treatment_project']?></span>
    </div>
</div>
<div class="t_bottom"></div>
<div class="t_hospital t_top">其他信息</div>
<div class="t_left">
    <div class="t_base">
        <span>医院代理商：</span> <span><?= Html::encode(ArrayHelper::getValue($info_data, 'agency_name')); ?></span>
    </div>
    <div class="t_base">
        <span>医院联系人：</span> <span><?= Html::encode(ArrayHelper::getValue($info_data, 'min_hospital_contact')); ?></span>
    </div>
    <div class="t_base">
        <span>联系人电话：</span> <span><?= Html::encode(ArrayHelper::getValue($info_data, 'min_hospital_contact_phone')); ?></span>
    </div>
    <div class="t_base">
        <span>合作时间：</span> <span><?= $info_data['begin_time'].'-'.$info_data['end_time']  ?></span>
    </div>
</div>
<div class="t_hospital t_top_base">挂号规则</div>
<div class="t_recommend t_left">
    <?= (isset($base_model->tp_guahao_description) && !empty($base_model->tp_guahao_description)) ? Html::encode($base_model->tp_guahao_description) : ''; ?>
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