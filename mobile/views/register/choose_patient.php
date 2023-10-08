<?php

use \common\helpers\Url;
use \yii\helpers\ArrayHelper;
use yii\helpers\Html;

//$this->registerCssFile(Url::getStaticUrl("css/confirm_registration.css"));
//$this->registerJsFile(Url::getStaticUrl("js/confirm_registration.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/confirm_order/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/confirm_order/index.js'),['depends'=>'mobile\assets\AppAsset']);
$session = \Yii::$app->session;


$weekname = ['周日','周一', '周二', '周三', '周四', '周五', '周六'];

?>

<div class="confirm_order">
    <!-- 就诊信息 -->
    <div class="co_information">
        <h4 class="ci_title">就诊信息</h4>
        <p>
            就诊日期
            <span class="ci_right01">
                <?=ArrayHelper::getValue($guahao_paiban,'visit_time')?>

                <?php if($guahao_paiban['visit_nooncode'] == 1){?> 上午 <?php }else{?> 下午 <?php }?>
                <?php
                $sections_on = ArrayHelper::getValue($guahao_paiban,'sections');
                if(is_array($sections_on)){
                    $sections_on = current($sections_on);
                }
                ?>
                <?=ArrayHelper::getValue($sections_on,'starttime')?>
                <?php if(ArrayHelper::getValue($sections_on,'endtime')){ ?><?='-'.ArrayHelper::getValue($sections_on,'endtime')?><?php } ?>
            </span>
        </p>
        <p>出诊医生<span class="ci_right01"><?=Html::encode(ArrayHelper::getValue($guahao_paiban,'realname'))?></span></p>
        <p>就诊科室<span class="ci_right01"><?=Html::encode(ArrayHelper::getValue($guahao_paiban,'department_name'))?></span></p>
        <p>就诊医院<span class="ci_right01"><?=Html::encode(ArrayHelper::getValue($guahao_paiban,'scheduleplace_name'))?></span></p>
        <p>
            挂号费用<span class="ci_right02">
                <?php
                //if(!empty($session['jzr_choose_tp_platform']) and $session['jzr_choose_tp_platform'] == 3)
                if(!empty($tp_platform) and $tp_platform == 3)
                {
                    $pay_way = '';
                    if($guahao_paiban['pay_mode'] == 1){
                        $pay_way = "线上支付";
                    }elseif($guahao_paiban['pay_mode'] == 2){
                        $pay_way = "到院付款";
                    }
                    if($guahao_paiban['famark_type'] == 1)
                    {
                        $visit_cost = ($guahao_paiban['visit_cost']/100)??"";
                        $bt_cost = (($guahao_paiban['visit_cost_original'] - $guahao_paiban['visit_cost'])/100)??"";
                        $referral_visit_cost = ($guahao_paiban['visit_cost_original']/100)??"";
                        if($guahao_paiban['visit_cost_original'] > $guahao_paiban['visit_cost'])
                        {
                            echo "初诊".$visit_cost.'元(<b>补贴'.$bt_cost.'元</b>) <strong>原价<span>'.$referral_visit_cost.'元（'.$pay_way.'）</strong>';
                        }else{
                            echo "初诊".$visit_cost."元";
                        }

                    }else{
                        $visit_cost_original = ($guahao_paiban['referral_visit_cost']/100)??"";
                        $bt_cost = (($guahao_paiban['referral_visit_cost_original'] - $guahao_paiban['referral_visit_cost'])/100)??"";
                        $referral_visit_cost_original = ($guahao_paiban['referral_visit_cost_original']/100)??"";
                        if($guahao_paiban['referral_visit_cost_original'] > $guahao_paiban['referral_visit_cost'])
                        {
                            echo "复诊".$visit_cost_original.'元(<b>补贴'.$bt_cost.'元</b>) <strong>原价'.$referral_visit_cost_original.'元（'.$pay_way.'）</strong>';
                        }else{
                            echo "复诊".$visit_cost_original.'元';
                        }

                    }

                }else{
                    $visit_cost = ($guahao_paiban['visit_cost']/100);
                    if(isset($guahao_paiban['pay_mode']) && $guahao_paiban['pay_mode'] == 1){
                        echo '<i>￥'.$visit_cost.'元</i>（线上支付）';
                    }else{
                        echo '<i>￥'.$visit_cost.'元</i>（到院付款）';
                    }
                }
                ?>
            </span>
        </p>
        <span class="co_information_tips">*具体费用以取号当天医院收取为准，敬请谅解</span>
    </div>
    <!-- 选择就诊人 -->
    <div class="ci_the_patient">
        <?php if(empty($patient_info)){?>
            <h4 class="ci_title"><p>选择就诊人</p>
                <span class="ctp_con_left"><i></i>
                   <a style="color: #00BE8C" href="<?=Url::to(['register/register-info-add','jzr_choose_doctor_id'=>$jzr_choose_doctor_id,'jzr_choose_scheduling_id'=>$scheduling_id,'jzr_choose_section_id'=>$tp_section_id,'jzr_choose_tp_platform'=>$tp_platform])?>">添加就诊人</a>
                </span></h4>
        <?php }else{?>
            <h4 class="ci_title">选择就诊人</h4>
            <div class="ctp_con">
                <div class="ctp_con_left">
                    <h5><?=Html::encode($patient_info['realname'])?><span>已实名</span></h5>
                    <p>居民身份证&nbsp;&nbsp;<?=substr_replace($patient_info['id_card'],'********',6,8)?></p>
                </div>
                <span class="ctp_con_right">更换</span>
            </div>
        <?php }?>
    </div>
    <!-- 疾病/症状 -->
    <div class="ci_symptom">
        <h4 class="ci_title">疾病/症状</h4>
        <div class="cis_textarea">
            <textarea placeholder="简述您的症状（2-15字）" maxlength="15"></textarea>
            <span><i>0</i>/15</span>
        </div>
    </div>
    <!-- 按钮 -->
    <div class="ci_but">
        <span>立即预约</span>
    </div>
</div>
<!-- toast -->
<div class="toast">
    <span>疾病名称/症状不可为空</span>
</div>
<!-- 就诊人列表弹层 -->
<div class="main_wrapper people_list" style="display:none;">
    <div class="pl_con">
        <?php if(!empty($patient_list)){?>
        <?php foreach ($patient_list as $pk=>$pv){?>
            <div class="ctp_con">
                <?php if($pv['is_auth_card'] != 1 || ($pv['is_auth_card'] == 1 && (!$pv['realname'] || !$pv['id_card'] || !$pv['tel'] || !$pv['sex'] || !$pv['birth_time']))){?>
                    <a class="ctp_con_a" href="<?= Url::to(['register/register-info-add','id'=>$pv['id'],'jzr_choose_doctor_id'=>$jzr_choose_doctor_id,'jzr_choose_scheduling_id'=>$scheduling_id,'jzr_choose_section_id'=>$tp_section_id,'jzr_choose_tp_platform'=>$tp_platform])?>">
                    <span class="ctp_con_choice01 ctp_con_choice_wsm"><i></i></span>
                <?php } else { ?>
                    <span data_patient_id="<?=$pv['id']?>" data_auth="<?php echo $pv['is_auth_card'];?>" class="ctp_con_choice01 ctp_con_choice <?php if( ArrayHelper::getValue($patient_info,'id_card') == $pv['id_card']){ ?> xz <?php } ?>"></span>
                <?php } ?>
                    <div class="ctp_con_left">
                        <h5><?=Html::encode($pv['realname'])?>
                            <?php if($pv['is_auth_card'] == 1){?>
                                <span>已实名</span>
                            <?php } ?>
                        </h5>
                        <p>居民身份证&nbsp;&nbsp;<?=substr_replace($pv['id_card'],'********',6,8)?></p>
                    </div>

                    <?php if($pv['is_auth_card'] != 1 || ($pv['is_auth_card'] == 1 && (!$pv['realname'] || !$pv['id_card'] || !$pv['tel'] || !$pv['sex'] || !$pv['birth_time']))){?>
                        </a>
                        <a href="<?= Url::to(['register/register-info-add','id'=>$pv['id'],'jzr_choose_doctor_id'=>$jzr_choose_doctor_id,'jzr_choose_scheduling_id'=>$scheduling_id,'jzr_choose_section_id'=>$tp_section_id,'jzr_choose_tp_platform'=>$tp_platform])?>" class="ctp_con_right"></a>
                    <?php }?>
            </div>
        <?php }?>
        <?php }?>
    </div>
    <!-- 按钮 -->
    <div class="ci_but">
        <a href="<?=Url::to(['register/register-info-add','jzr_choose_doctor_id'=>$jzr_choose_doctor_id,'jzr_choose_scheduling_id'=>$scheduling_id,'jzr_choose_section_id'=>$tp_section_id,'jzr_choose_tp_platform'=>$tp_platform])?>" class="ci_but_add">新增就诊人</a>
        <span class="ci_but_ok confirm_registration_people_con_but">确认</span>
    </div>
</div>
<input type="hidden" id="visit" value="1">
<input type="hidden" id="patient_id" value="<?php if(!empty($patient_info['id'])){ echo $patient_info['id'];}?>">
<input type="hidden" id="tp_section_id" value="<?php if(!empty($tp_section_id)){ echo $tp_section_id;}?>">
<input type="hidden" id="jzr_choose_tp_platform" value="<?php echo $tp_platform; ?>">

<input type="hidden" id="jzr_choose_doctor_id" value="<?php echo $jzr_choose_doctor_id;?>">
<input type="hidden" id="jzr_choose_scheduling_id" value="<?php echo $scheduling_id; ?>">
<input type="hidden" id="tp_doctor_id" value="<?php echo $guahao_paiban['tp_doctor_id']??''; ?>">
<input type="hidden" id="check_info" value="<?php if(empty($patient_info['realname']) || empty($patient_info['id_card']) || empty($patient_info['tel']) || empty($patient_info['sex']) || empty($patient_info['birth_time'])){echo 1;}else{ echo 2;}?>">
<div class="tools_assembly" style="display: none;">
    <div class="tools_assembly_bg"></div>
    <div class="tools_assembly_con">
        <p>就诊人信息不完善，请重新选择！</p>
    </div>
</div>
<!-- 挂号须知 -->
<?php if((!empty($tp_guahao_description) and empty($repeat_visit))){?>
    <div class="register_tips">
        <div class="rt_con">
            <h4>挂号须知</h4>
            <div class="p_con">
                <p>
                    <?php echo nl2br(Html::encode($tp_guahao_description));?>
                </p>
            </div>
            <div class="rt_but">确定</div>
        </div>
    </div>
<?php } ?>
<script>
    //$(function () {
    //    //先弹框提示
    //    var patient_count = "<?php //echo $patient_count;?>//";
    //    var md5_useId = "<?php //echo $md5_useId;?>//";
    //    var cookie_key = md5_useId + '_tools_title';
    //    var tools_title = getCookie(cookie_key);
    //    if (tools_title <= 0 && patient_count > 0) {
    //        $('.zqxy_pop_all').show();
    //    }
    //
    //    $('.ty_btn').click(function () {
    //        setCookie(cookie_key, 1, 30);
    //        $('.zqxy_pop_all').hide();
    //    });
    //
    //    $('.bty_btn').click(function () {
    //        $('.zqxy_pop_all').hide();
    //        window.history.go(-1);
    //    });
    //
    //    function setCookie(cname,cvalue,exdays){
    //        var d = new Date();
    //        d.setTime(d.getTime()+(exdays*24*60*60*1000));
    //        var expires = "expires="+d.toGMTString();
    //        document.cookie = cname+"="+cvalue+"; "+expires;
    //    }
    //
    //    function getCookie(name)
    //    {
    //        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    //        if(arr=document.cookie.match(reg))
    //            return unescape(arr[2]);
    //        else
    //            return null;
    //    }
    //})
</script>