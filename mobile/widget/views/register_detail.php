<?php

use common\helpers\Url;
use common\libs\HashUrl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->registerJsFile( Url::getStaticUrl("js/order_details.js") );
$this->registerCssFile(Url::getStaticUrlTwo("pages/detail/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/detail/index.js'),['depends'=>'mobile\assets\AppAsset']);

$weekname = ['周日','周一', '周二', '周三', '周四', '周五', '周六'];

?>

<div class="main_wrapper detail_box">
    <div class="detail_top_box">
        <div class="detail_top_info">
                <?php if($state == 0){ ?>
                    <h3>预约成功，待就诊</h3>
                    <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 1){ ?>
                    <?php if($pay_status == 6){?>
                        <h3>已退款</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                    <?php }else{?>
                        <h3>已取消</h3>
                        <p>您的挂号于<?= (isset($cancel_time) && !empty($cancel_time)) ? date('Y-h-d H:i:s',$cancel_time) : ''; ?> 取消挂号<br/>如需再次挂号请再次预约</p>
                    <?php }?>
                <?php }elseif($state == 5 and $pay_status == 1){ ?>
                        <h3>订单已提交，待支付</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 3){?>
                        <h3>患者已就诊，订单完成</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 4){?>
                        <h3>患者未就诊，患者爽约</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 2){?>
                        <h3>预约成功，已停诊</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 6){?>
                        <h3>预约失败，无效订单</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }elseif($state == 8){?>
                        <h3>订单已提交，待审核</h3>
                        <p>就诊地址：<?php echo $hospital_address ?Html::encode($hospital_address): ''; ?></p>
                <?php }?>
        </div>
    </div>
    <div class="main_box detail_main_box">
        <h3>就诊信息</h3>
        <ul>
            <li><span>就诊时间</span>
                <?php
                if (!empty($visit_time)) {
                    echo $visit_time;
                }
                $is_sections = false;
                if(isset($visit_starttime) && $visit_starttime  ){
                    $is_sections = $visit_starttime;
                    if(isset($visit_endtime) && $visit_endtime  ){
                        $is_sections .= '-'.$visit_endtime;
                    }
                }
                if ($visit_nooncode == 1) {
                    ?>
                    上午 <?php if($is_sections){ echo $is_sections; } ?>
                <?php } else { ?>
                    下午 <?php if($is_sections){ echo $is_sections; } ?>
                <?php } ?>
            </li>
            <li><span>就诊医院</span><?php echo $hospital_name ?Html::encode($hospital_name): ''; ?></li>
            <li><span>就诊科室</span><?php echo $department_name ?Html::encode($department_name): ''; ?></li>
            <li><span>医生姓名</span><?php echo $doctor_name ?Html::encode($doctor_name): ''; ?></li>
            <li><span>医生职称</span><?php echo $doctor_title ?Html::encode($doctor_title): ''; ?></li>
            <li><span>出诊类型</span>
                <?php
                switch ($visit_type){
                    case "1":
                        echo "普通门诊";
                        break;
                    case "2":
                        echo "专家门诊";
                        break;
                    case "3":
                        echo "专科门诊";
                        break;
                    case "4":
                        echo "特需门诊";
                        break;
                    case "5":
                        echo "夜间门诊";
                        break;
                    case "6":
                        echo "会诊门诊";
                        break;
                    case "7":
                        echo "老院门诊";
                        break;
                    case "8":
                        echo "其他门诊";
                        break;
                    default:
                        echo "普通门诊";
                        break;

                }
                ?>
            </li>
            <li class="info_line"><span>挂号费用</span>￥<?php echo ($visit_cost / 100) ?? ''; ?>元<?php if($pay_mode == 2){?>（到院付款）<?php }elseif($pay_mode == 1){?>（线上支付）<?php }?></li>
            <li><span>挂号单号</span><?= $tp_order_id ?? ''; ?></li>
            <li class="info_line"><span>挂号时间</span><?php echo date('Y-m-d H:i:s', $create_time); ?></li>
            <li><span>就诊人</span><?=Html::encode($patient_name)?></li>
            <li><span>身份证号</span><?=substr_replace($card, '********', 6, 8)?></li>
        </ul>
    </div>

        <div class="main_box detail_notice">
            <?php if(isset($tp_guahao_description) && !empty($tp_guahao_description)){?>
                <h2 class="d_n_title">预约须知</h2>
                <div class="d_n_words">
                    <p><?php echo nl2br(Html::encode($tp_guahao_description));?></p>
                </div>
            <?php } ?>
        </div>

    <!-- 订单已取消跟订单已退款状态按钮 -->
    <?php if($state == 1 and $is_disable == 1){?>
        <div class="btn_box safe_area" id="yuyue_cancel">
            <span class="btn_style"><a style="color: white" href="<?php $href_url = '/hospital/doctor_'.HashUrl::getIdEncode($doctor_id).'.html'; echo Url::to([$href_url]);?>" >再次预约</a></span>
        </div>
    <?php }?>
    <!-- 订单已提交待支付状态按钮 -->
    <?php if($state == 5 and $pay_status == 1 ){?>
        <div class="btn_box safe_area" id="yuyue_cancel">
            <span class="btn_style btn_full_blank cancel">取消预约</span>
            <span class="btn_style btn_full_blank"><a href="<?php echo $pay_url;?>">立即支付</a></span>
        </div>
    <?php }?>

    <?php if( ($state == 0 || $state==3 || $state == 8) && ($pay_status == 0 || $pay_status == 3)){?>
        <!-- 预约成功待就诊 -->
        <div class="btn_box safe_area" id="yuyue_cancel">
            <?php if($cancel_status == 1 and $state == 0 && $tp_platform!=6 ){?>
                <span class="btn_style btn_full_blank cancel">取消预约</span>
            <?php } ?>
            <span class="btn_style btn_full_blank"><a href=<?php if($ua == 'patient'){ echo "pd://switchTab?page=home"; }else{echo Url::to(['/hospital.html']);}?>>回首页</a></span>
        </div>
    <?php }?>
    <!-- 取消预约 -->
    <div class="layer_main_wrap">
        <div class="layer_bg"></div>
        <div class="layer_main_content">
            <div class="layer_title">
                取消订单
                <i class="layer_close"></i>
            </div>
            <div class="layer_content_box">
                <h6 class="l_cont_title">用户须知</h6>
                <p>1、一个挂号一周内累计取消挂号三次，平台将自动暂停账号未来7天预约挂号服务</p>
                <p>2、若为医院医生无法就诊，可取消订单后进行申诉</p>
                <h6 class="l_cont_title l_title02">请选择取消原因</h6>
                <div class="l_cont_select">
                    <span class="active">时间冲突</span>
                    <span>信息填写有误</span>
                    <span>预约多个订单</span>
                    <span>对医生不了解</span>
                    <span>医生临时停诊</span>
                    <span>对医院不了解</span>
                    <span>选错科室</span>
                    <span>其他</span>
                </div>
                <div class="btn_box">
                    <a href="javascript:;" class="btn_style btn_full_blank" id="cancel_confirm">确定取消</a>
                    <span class="btn_style close_btn">暂不取消</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tools_assembly" style="display: none;">
    <div class="tools_assembly_bg"></div>
    <div class="tools_assembly_con">
        <p>取消预约！</p>
    </div>
</div>
<script src="<?=Url::getStaticUrl("js/jquery-1.11.1.min.js")?>"></script>

<script>
    // console.log("=====this is page A")
    // var count = sessionStorage.getItem("count")
    // if(count){
    //     sessionStorage.setItem("count",++count)
    // }else {
    //     sessionStorage.setItem("count", 1)
    // }
    // console.log(count)
    // function reloadHtml() {
    //     console.log("===test======"+count);
    //     if(count>1){
    //         sessionStorage.setItem("count", 0);
    //         window.location.reload();
    //     }
    // }
    // $(document).ready(function () {
    //     reloadHtml()
    // })
</script>
<?php if($state == 5 and $pay_status == 1){ ?>
    <script>
        //var maxtime = "<?//=$times?>//"; //一个小时，按秒计算，自己调整!
        //function CountDown() {
        //    if (maxtime >= 0) {
        //        minutes = Math.floor(maxtime / 60);
        //        seconds = Math.floor(maxtime % 60);
        //
        //        var minute_html = document.getElementById("minute");
        //        var seconde_html = document.getElementById("seconde");
        //
        //        if(minutes < 10){
        //            minutes = "0"+minutes;
        //        }
        //        if(seconds < 10){
        //            seconds = "0"+seconds;
        //        }
        //
        //        minute_html.innerHTML=minutes;
        //        seconde_html.innerHTML=seconds;
        //
        //        --maxtime;
        //    } else{
        //        clearInterval(timer);
        //        window.location.reload();
        //    }
        //}
        //timer = setInterval("CountDown()", 1000);
    </script>
<?php } ?>
