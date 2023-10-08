<?php
use yii\helpers\Html;
use common\helpers\Url;

?>
<div class="doctorNav doctorNav1">
    <ul class="doctorNavTitle">
        <a href="<?= Url::to(['/doctor/home','doctor_id'=>$id]) ?>" class="<?php echo ($url=='overview') ? 'doctorNavActives' : ''; ?>">概览</a>
        <a href="<?= Url::to(['/doctor/intro','doctor_id'=>$id]) ?>" class="<?php echo ($url=='introduce') ? 'doctorNavActives' : ''; ?>">详细介绍</a>
        <a href="<?= Url::to(['/doctor/consult','doctor_id'=>$id]) ?>" class="<?php echo ($url=='consult') ? 'doctorNavActives' : ''; ?>">网友咨询</a>
        <a href="<?= Url::to(['/doctor/comment','doctor_id'=>$id]) ?>" class="<?php echo ($url=='jydp') ? 'doctorNavActives' : ''; ?>">就医分享与点评</a>
        <!--<a class="js_hasEmail">我是医生本人，立即完善资料&gt;
            <div class=" js_doctorEmail" style="display: none;">
                <p>请把您的资料及问题，发送邮件到以下邮箱：</p>
            </div>
        </a>-->
    </ul>
</div>
