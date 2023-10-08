<?php
/**
 * @file index.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/27
 */
use \common\helpers\Url;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use pc\widget\HospitalNavCrumbs;
use common\libs\CommonFunc;
use pc\widget\OrtherHospital;

$this->registerCssFile( Url::getStaticUrl("css/hos_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_detail.min.js") );

?>


<!-- 标签导航 -->
<div class="hospitalContent">

    <?=HospitalNavCrumbs::widget(['hospital_id' => $hospital_id,'hosp_data' => $data])?>

    <!-- 医院介绍 -->
    <div class="serviceRecommen">
        <div class="serviceRecommen_l">
            <div class="departmentIntroduction borderBg" style="float:initial;">
                <div class="departmentDetailName">
                    <h5><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h5>
                </div>

                <?php
                if(ArrayHelper::getValue($data,'nick_name')){
                    ?>
                    <p>别称：<?=Html::encode(ArrayHelper::getValue($data,'nick_name'))?></p>
                <?php } ?>
                <p>类型/级别：<span><?=ArrayHelper::getValue($data,'level')?></span><span><?=ArrayHelper::getValue($data,'type')?></span></p>
                <p>医保定点：<span>未知</span></p>
                <div class="introductionText">
                    <h6>详细介绍</h6>
                    <p><?=  trim(CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($data,'description'))))?></p>
                </div>
            </div>
            <div class="contactAddress borderBg">
                <ul>
                    <li>
                        <span></span>
                        <p>医院地址：<?=Html::encode(ArrayHelper::getValue($data,'address'))?></p>
<!--                        <a href="/hospital_67/traffic.html">[地图导航]</a>-->
                    </li>
                    <li>
                        <span></span>
                        <p>乘车路线：<?=Html::encode(ArrayHelper::getValue($data,'routes'))?></p>
                    </li>
                    <li>
                        <span></span>
                        <p>联系电话：<?=Html::encode(ArrayHelper::getValue($data,'phone'))?></p>
                    </li>
                    <!--<li>
                        <span></span>
                        <p>官方网站：<a rel="nofollow" href="http://www.pumch.cn/">中国医学科学院北京协和医院</a></p>
                    </li>-->
                </ul>
            </div>
        </div>



    </div>



    <!-- 服务推荐 -->
    <div class="hospitalServiceMain">


        <!--挂号-->
        <a href="javascript:;" class="hospitalServiceLink borderBg" >
            <div class="hospitalServiceIcon hospitalServiceIcon3"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>预约挂号</h5>
                    <span class="hospitalServiceType">10秒挂号 快速就医</span>
                </div>
                <p>4科室，6名医生</p>
                <div class="hospitalServiceText">
                    <span>去挂号</span>
                    <span></span>
                </div>
            </div>
        </a>

        <!--电话咨询-->
        <a href="javascript:;" class="hospitalServiceLink borderBg" onclick="">
            <div class="hospitalServiceIcon"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>名医电话</h5>
                    <span class="hospitalServiceType">医生本人回电</span>
                </div>
                <p>13科室，20名医生</p>
                <div class="hospitalServiceText">
                    <span>预约通话</span>
                    <span></span>
                </div>
            </div>
        </a>

        <!--图文咨询-->
        <a href="javascript:;" class="hospitalServiceLink borderBg" onclick="">

            <div class="hospitalServiceIcon hospitalServiceIcon2"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>图文咨询</h5>
                    <span class="hospitalServiceType">在线咨询问疾病</span>
                </div>
                <p>24个科室，38名医生</p>
                <div class="hospitalServiceText">
                    <span>立即咨询</span>
                    <span></span>
                </div>
            </div>
        </a>


    </div>

    <!-- 同区域同类别医院 -->
    <!-- 同区域同类别医院 -->
    <?=OrtherHospital::widget(['city_id' => ArrayHelper::getValue($data,'city_id'),'type' => ArrayHelper::getValue($data,'type'),'hospital_id' => $hospital_id])?>




</div>

</div>
