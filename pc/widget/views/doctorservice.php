<?php
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\ArrayHelper;
?>
<div class="doctoeMids">
    <h3 class="title"><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?> 服务推荐</h3>
    <div class="serviceList">

        <div class="serviceListh ">
            <div class="consultlist">
                <a href="javascript:void(0)" class="consulticon consulticon2">
                    <span></span>
                    <span>图文咨询</span>
                </a>
                <div class="consultTextMain">
                    <div class="consultText">
                        <p><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?>医生暂未开通图文咨询服务,建议您咨询其它同科室医生</p>
                    </div>
                    <ul class="consultExplain consultExplain1">
                        <li>
                            <span></span>
                            <span>医生真实</span>
                        </li>
                        <li>
                            <span></span>
                            <span>未接诊随时退</span>
                        </li>
                        <li>
                            <span></span>
                            <span>不满意可申诉退款</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="servicePrice">
                <span>咨询</span>
            </div>
        </div>
        <a  class="serviceListh">
            <div class="consultlist ">

                <div class="consulticon consulticon1">
                    <span></span>
                    <span>电话咨询</span>
                </div>

                <div class="consultTextMain">
                    <div class="consultText">
                        <p><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?>医生暂未开通电话咨询服务,建议您咨询其它同科室医生</p>
                    </div>
                    <ul class="consultExplain consultExplain2">
                        <li>
                            <span></span>
                            <span>医生真实</span>
                        </li>
                        <li>
                            <span></span>
                            <span>未接诊随时退</span>
                        </li>
                        <li>
                            <span></span>
                            <span>不满意可申诉退款</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="servicePrice">
                <span>咨询</span>
            </div>
        </a>
        <div class="serviceListh ">
            <div class="consultlist">
                <div class="consulticon consulticon3">
                    <span></span>
                    <span>预约挂号</span>
                </div>
                <div class="consultTextMain">
                    <div class="consultText">
                        <p>医生号源排班实时掌握，预约挂号快人一步</p>
                    </div>
                    <ul class="consultExplain consultExplain3">
                        <li>
                            <span></span>
                            <span>全网号源</span>
                        </li>
                        <li>
                            <span></span>
                            <span>24小时不间断</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="servicePrice">
                <span>挂号</span>
            </div>
        </div>

    </div>
</div>