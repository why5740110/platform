<?php

use \common\helpers\Url;

$this->registerCssFile(Url::getStaticUrl("css/my_succes.css"));
$this->registerJsFile(Url::getStaticUrl("js/my_succes.js"));
?>


<div class="jzr_zfcg_t_box">
    <div class="paddingcon">
        <h3><?php if(isset($msg) && $msg ){?>支付失败<?php }else{ ?>支付成功<?php } ?></h3>
        <p class="jine_p">￥<span><?php echo ($visit_cost/100)??"";?></span></p>
        <p class="tips"><?php if(isset($msg) && $msg ){?>   <?php }else{ ?> 您的挂号订单已支付成功，请尽快到院就诊<?php } ?></p>
        <div class="zfcg_btn_box" >
            <a class="qx_btn" href="<?php if($ua == 'patient'){ echo "pd://switchTab?page=home"; }else{echo Url::to(['/hospital.html']);}?>">返回首页</a>
            <a class="ljzf_btn" href="<?php echo Url::to(['/hospital/register/register-detail.html','id'=>$order_sn,'source_from'=>'mycenter']);?>">查看订单 </a>
        </div>
        <i class="icontu"></i>
    </div>
</div>

<div class="jzr_zfcg_c_box">
    <div class="jzr_zfcg_con">


        <div class="jzr_zfcg_sm">
            <h3>到院就诊流程</h3>
            <div class="dflex sm_zq">
                <div class="left_sm"><img src="<?=Url::getStaticUrl("imgs/zf05.png")?>"></div>
                <div class="flex1 right_sm">
                    <h5>诊前</h5>
                    预约成功后，可直接联系医院咨询相关就诊事项，医院也会在就诊前一天晚18点前，联系您通知具体到院时间，避免您等候过久
                </div>
            </div>
            <div class="dflex sm_qh">
                <div class="left_sm"><img src="<?=Url::getStaticUrl("imgs/zf04.png")?>"></div>
                <div class="flex1 right_sm">
                    <h5>取号</h5>
                    在就诊当日，您可直接凭身份证件，到医院前台取号，无需再次缴款挂号费
                </div>
            </div>
            <div class="dflex sm_jz">
                <div class="left_sm"><img src="<?=Url::getStaticUrl("imgs/zf01.png")?>"></div>
                <div class="flex1 right_sm">
                    <h5>就诊</h5>
                    与公立医院就诊类似，直接与医生面诊，完成相关检查治疗等。
                </div>
            </div>
        </div>

    </div>

</div>

