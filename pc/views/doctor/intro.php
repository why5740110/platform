<?php
use \common\helpers\Url;
use common\libs\HashUrl;
use \yii\helpers\Html;

$this->registerCssFile( Url::getStaticUrl("css/doc_detail.min.css") );

?>
<?php if (!empty($doctor_info)) : ?>
<div class="w1200">
    <div class="anchorNav">
        <ul>
            <a>当前位置：</a>
            <a href="/">首页</a>
            <?php if(isset($doctor_info['doctor_hospital_data']['name'])&&!empty($doctor_info['doctor_hospital_data']['name'])) : ?>
                <a href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($doctor_info['hospital_id'])]) ?>"><?php echo Html::encode($doctor_info['doctor_hospital_data']['name']); ?></a>
            <?php endif;?>
            <?php if(!empty($doctor_info['doctor_second_department_id'])) : ?>
                <a href="<?= Url::to(['/hospital/doctorlist/departments/0_0_'.$doctor_info['doctor_second_department_id'].'_1.html']) ?>"><?php echo $doctor_info['doctor_second_department_name'] ? Html::encode($doctor_info['doctor_second_department_name']) : ''; ?></a>
            <?php endif;?>
            <span><?php echo Html::encode($doctor_info['doctor_realname']); ?></span>
            <span>详细介绍</span>
        </ul>
    </div>
    <div class="doctoeMains clr">
        <?php echo \pc\widget\DoctorInfoMenu::widget(['url'=>'introduce','id'=>$doctor_info['doctor_id']])?>
        <div class="doctorDetail">
            <div class="doctorDetail_l">
                <img src="<?php echo $doctor_info['doctor_avatar']?>" alt="<?php echo Html::encode($doctor_info['doctor_realname']); ?>">
            </div>
            <div class="doctorDetail_r">
                <div class="doctorName">
                    <h1><?php echo Html::encode($doctor_info['doctor_realname']); ?></h1>
                    <ul>
                        <?php if(!empty($doctor_info['doctor_title'])) : ?>
                            <li><?php echo Html::encode($doctor_info['doctor_title']); ?></li>
                        <?php endif;?>

                        <?php if(!empty($doctor_info['doctor_professional_title'])) : ?>
                            <li><?php echo Html::encode($doctor_info['doctor_professional_title']); ?></li>
                        <?php endif;?>
                    </ul>
                </div>
                <div class="doctortail">

                    <div class="hospitalocation ">
                        <p>
                            <a class="doctortailTitle">出诊地点：</a>
                            <a class="c14" target="_blank" href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($doctor_info['hospital_id'])]) ?>"><?php echo $doctor_info['doctor_hospital_data']['name'] ? Html::encode($doctor_info['doctor_hospital_data']['name']) : ''; ?></a>&nbsp;
                            <a class="c14" target="_blank" href="<?= Url::to(['/hospital/doctorlist/departments/0_0_'.$doctor_info['doctor_second_department_id'].'_1.html']) ?>"><?php echo Html::encode($doctor_info['doctor_second_department_name']); ?></a>
                        </p>
                        <?php /* <p class="js_hospitalocation" data-tar="1">其他8个执业点 <span>「展开」</span></p> */?>
                    </div>
                </div>
                <div class="doctortail">
                    <p class="doctorSkill "><a class="doctortailTitle">擅长领域：</a>
                        <?php echo Html::encode($doctor_info['doctor_good_at']); ?>
                    </p>
                </div>
                <?php /* <div class="otherHospital js_otherHospital">
                    <ul>
                        <a href=""><li>北京熙仁医院 眼科</li></a>
                        <a href="/"><li>北京熙仁医院 葡萄膜炎</li></a>
                        <a href="/"><li>北京熙仁医院 黄斑病变</li></a>
                        <a href="/"><li>北京熙仁医院 玻璃体疾病</li></a>
                        <a href="/"><li>北京熙仁医院 眼底出血</li></a>
                        <a href="/"><li>北京熙仁医院 视网膜脱离</li></a>
                        <a href="/"><li>北京熙仁医院 眼底病科</li></a>
                        <a href="/"><li>北京熙仁医院 眼外伤科</li></a>
                    </ul>
                    <div class="closePopul" >
                        <span>「收起」</span>
                    </div>
                </div> */?>
            </div>
            <div class="clr"></div>
        </div>
    </div>
    <div class="doctoeMids">
        <h3 class="title">执业经历</h3>
        <div class="practiceNote">
            <?php echo Html::encode($doctor_info['doctor_profile']); ?>
        </div>
    </div>
    <!-- 服务推荐 -->
    <?php echo \pc\widget\DoctorService::widget(['doctor_info'=>$doctor_info])?>
</div>

<?php endif;?>
<?php

$this->registerJsFile( Url::getStaticUrl("js/doc_detail.min.js") );

?>
