<?php

use \common\helpers\Url;

//$this->registerCssFile(Url::getStaticUrl("css/jzr_details.css"));
//$this->registerJsFile(Url::getStaticUrl("js/jzr_details.js"));

//new
$this->registerCssFile(Url::getStaticUrl("css/order_details.css"));
$this->registerJsFile( Url::getStaticUrl("js/order_details.js") );

?>

<?php echo \mobile\widget\RegisterDetailWidget::widget(
        [
                'visit_time'=>$visit_time,
            'famark_type'=>$famark_type,
            'hospital_name'=>$hospital_name,
            'department_name'=>$department_name,
            'card_type'=>$card_type,
            'doctor_name'=>$doctor_name,
            'doctor_title'=>$doctor_title,
            'visit_cost'=>$visit_cost,
            'tp_order_id'=>$tp_order_id,
            'create_time'=>$create_time,
            'tp_platform'=>$tp_platform,
            'visit_nooncode'=>$visit_nooncode,
            'state'=>$state,
            'patient_name'=>$patient_name,
            'card'=>$card,
            'pay_status'=>$pay_status,
            'cancel_status'=>$cancel_status,
            'ua'=>$ua,
            'doctor_id'=>$doctor_id,
            'pay_url'=>$pay_url,
            'source_from'=>$source_from,
            'symptom'=>$symptom,
            'visit_type'=>$visit_type,
            'pay_mode'=>$pay_mode,
            'times' => $times,
            'visit_starttime'=>$visit_starttime,
            'visit_endtime'=>$visit_endtime,
            'is_disable'=>$is_disable,
            'hospital_address'=>$hospital_address,
            'update_time'=>$update_time,
            'cancel_time'=>$cancel_time,
            'tp_guahao_description'=>(isset($tp_guahao_description) && !empty($tp_guahao_description)) ? $tp_guahao_description : '',
        ]);?>
<div style=height:60px></div>

<span id="jzr_choose_platform" data_platform="<?php if(!empty($tp_platform)){ echo $tp_platform;}?>"></span>
<span id="jzr_order_id" data_order_id="<?php if(!empty($tp_order_id)){ echo $tp_order_id;}?>"></span>
<span id="jzr_register_id" data_jzr_register_id="<?php echo $order_sn;?>"></span>
<span id="gh_domain" data_gh_domain="<?=rtrim(\Yii::$app->params['domains']['mobile'],'/').'/hospital/'?>"></span>

<script src="<?=Url::getStaticUrl("js/jquery-1.11.1.min.js");?>"></script>