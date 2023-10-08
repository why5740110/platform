<?php

use \common\helpers\Url;
use yii\helpers\Html;

$this->registerCssFile(Url::getStaticUrl("css/jzr_details.css"));
$this->registerJsFile(Url::getStaticUrl("js/jzr_details.js"));
?>
<div class=jzr_t_bg_box>
    <div class=jzr_qh_all>
        <div class=jzr_yycg_info><i class=icon_jzr_yycg></i><span><?php if($type == 'success'){ echo "预约成功";}elseif ($type == 'faild'){ echo "已取消";}else{ echo "确认预约";}?></span></div>
        <div class=jzr_qh_con2><h3><?php echo $patient_info['realname'];?></h3>
            <p>居民身份证 <?php echo substr_replace($patient_info['id_card'],'********',6,8);?></p></div>
    </div>
    <div class=me_zx_box>
        <div class="zx_con_box bgfff"><p class=P_tit>挂号信息</p>
            <p class="dflex fs15 mt15"><span class=text5>就诊日期</span> <span class="flex1 ml5"><?php if(!empty($guahao_paiban['visit_time'])){ echo $guahao_paiban['visit_time'];}?> <?php if($guahao_paiban['visit_nooncode'] == 1){?>上午<?php }else{?>下午<?php }?></span></p>
            <p class="dflex fs15 mt15"><span class=text5> 初/复诊</span> <span class="flex1 ml5"><?php if(!empty($visit) and $visit == 1){echo '初诊';}else{echo '复诊';}?></span></p>
            <p class="dflex mt15"><span class=text5>就诊医院</span> <span class="flex1 ml5"><?php echo $doctor_info['doctor_hospital']?Html::encode($doctor_info['doctor_hospital']):'';?></span></p>
            <p class="dflex mt15"><span class=text5>就诊科室</span> <span class="flex1 ml5"><?php echo $doctor_info['doctor_second_department_name']?Html::encode($doctor_info['doctor_second_department_name']):'';?></span></p>
            <p class="dflex mt15"><span class=text5>医生姓名</span> <span class="flex1 ml5"><?php echo $doctor_info['doctor_realname']?Html::encode($doctor_info['doctor_realname']):'';?></span></p>
            <p class="dflex mt15"><span class=text5>医生职称</span> <span class="flex1 ml5"><?php echo $doctor_info['doctor_title']?Html::encode($doctor_info['doctor_title']):'';?></span></p>
            <p class="dflex mt15"><span class=text5>医事服务费</span> <span class="flex1 ml5"><?php echo ($guahao_paiban['visit_cost']/100)??'';?>元<i class="text6">（到院付款）</i></span></p></div>
    </div>
</div>
<div class=jzr_zysx_box>
    <div class=jzr_zysx_con><h3>注意事项</h3>
        <p>1.请确认就诊人信息准确无误，若填写错误将无法取号就诊，损失由本人承担。</p>
        <p>2.建议您就诊当天提前30分钟内取号，上午取号与下午取号截至时间以医院实际取号截至时间为准。</p>
        <p>3.就诊前<?php echo !empty($allowed_cancel_day)?$allowed_cancel_day:'1';?>天<?php echo !empty($allowed_cancel_time)?$allowed_cancel_time:'12:00';?>之前可在线退号，具体以医院的在线退号时间为准，或至少提前就诊时间1小时到医院窗口办理退号（工作时间），逾期不办理退款。</p>
        <p>4.请于就诊当日携带预约挂号所用的有效身份证和社保卡到院取号。</p>
        <p>5.请注意医患患者在住院期间不能使用社保卡在门诊取号。</p>
    </div>
</div>
<div style=height:60px></div>
<span id="jzr_order_id" data_order_id="<?php if(!empty($tp_order_id)){ echo $tp_order_id;}?>"></span>
<span id="patient_id" data_patient_id="<?php echo $patient_info['id'];?>"></span>
<span id="visit" data_visit="<?php echo $visit;?>"></span>
<?php if(\Yii::$app->request->get('source_from') == 'mycenter' and \Yii::$app->request->get('type') != 'faild'){?>
<div class=qx_jrz_btn_box><a href=# class=qx_jzr_qrgh_btn>取消预约</a></div>
<?php }?>
<?php if(\Yii::$app->request->get('type') == 'success' and \Yii::$app->request->get('source_from') == 'hospital'){?>
<div class=yycg_btn_box>

    <?php
        if(\Yii::$app->controller->getUserAgent() == 'mini'){
            $href = "pages/appointment_register/reserveList/reserveList";
    ?>
        <a class="qx_btn go_mini" href="javascript:;">预约记录</a>
    <?php }else{ ?>
        <a class=qx_btn href=<?= Url::to([\Yii::$app->params['domains']['ihs'].'my/guahaolist'])?>>预约记录</a>
    <?php } ?>
    <a class=ck_btn href=<?= Url::to(['/hospital.html'])?>>回首页 </a>
</div>
<?php }?>
<div class=zezao style=display:none></div>
<div class="wenzhang_pop_box pt30" style=display:none><p class="mb20 pr15 pl15">确认取消本次预约吗？</p>
    <div class=ovH>
        <div class=no_qd_btn>取消</div>
        <div class=qd_btn>确定</div>
    </div>
</div>
<?php if($type == 'confirm'){?>
<div class=qr_pop_box>
    <div class=qr_pop_box_con><h3>请确认挂号须知</h3>
        <p class=fs14>1.请确认就诊人信息准确无误，若填写错误将无法取号就诊，损失由本人承担。</p>
        <p class=fs14>2.建议您就诊当天提前30分钟内取号，上午取号与下午取号截至时间以医院实际取号截至时间为准。</p>
        <p class=fs14>3.就诊前一天<?php echo !empty($allowed_cancel_time)?$allowed_cancel_time:'12:00';?>之前可在线退号，具体以医院的在线退号时间为准，或至少提前就诊时间1小时到医院窗口办理退号（工作时间），逾期不办理退款。</p>
        <p class=fs14>4.请于就诊当日携带预约挂号所用的有效身份证和社保卡到院取号。</p>
        <p class=fs14>5.请注意医患患者在住院期间不能使用社保卡在门诊取号。</p>
        <a class=que_zf_btn>确认挂号</a>
    </div>
</div>
<?php }?>
<script src="<?=Url::getStaticUrl("js/jquery-1.11.1.min.js");?>"></script>
<script src="<?=Url::getStaticUrl("js/tools.js");?>"></script>
