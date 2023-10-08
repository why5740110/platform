<?php
use \common\helpers\Url;
use yii\helpers\Html;

$this->registerCssFile( Url::getStaticUrl("css/doc_detail.min.css") );

?>
<?php if (!empty($doctor_info)) : ?>
<div class="w1200">
    <div class="anchorNav">
        <ul>
            <a>当前位置：</a>
            <a href="/">首页</a>
            <?php if(isset($doctor_info['hospital'])&&!empty($doctor_info['hospital'])) : ?>
                <a href="<?php echo Url::to(['/hospital/introduce_'.HashUrl::getIdEncode($doctor_info['hospital_id']).'.html']);?>"><?php echo Html::encode($doctor_info['hospital']); ?></a>
            <?php endif;?>
            <?php if(!empty($doctor_info['doctor_second_department_id'])) : ?>
                <a href="<?= Url::to(['/hospital/doctorlist/departments/0_0_'.$doctor_info['doctor_second_department_id'].'_1.html']) ?>"><?php echo $doctor_info['doctor_second_department_name']?Html::encode($doctor_info['doctor_second_department_name']):''; ?></a>
            <?php endif;?>
            <span><?php echo Html::encode($doctor_info['doctor_realname']); ?></span>
            <span>网友咨询</span>
        </ul>
    </div>
    <div class="doctoeMains clr">
        <?php echo \pc\widget\DoctorInfoMenu::widget(['url'=>'consult','id'=>$doctor_info['doctor_id']])?>
    </div>
    <!-- 服务推荐 -->
    <?php echo \pc\widget\DoctorService::widget(['doctor_info'=>$doctor_info])?>
    <div class="doctoeMids">
        <h3 class="title"><?php echo Html::encode($doctor_info['doctor_realname']); ?> 回复的患友咨询</h3>
        <div class="visitingTime">
            <div class="newVisiTime">我要咨询</div>
        </div>
        <div class="otherConsultMain">
            <div class="kong">暂无咨询</div>
        </div>
    </div>
</div>
<?php endif;?>
<?php

$this->registerJsFile( Url::getStaticUrl("js/doc_detail.min.js") );

?>
</body>
</html>
