<?php
/**
 * @file hospital_nav_crumbs.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/28
 */

use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


?>

<div class="doctorNav borderBg">
    <ul class="doctorNavTitle">
        <a href="<?=Url::to(['hospital/index','hospital_id'=>$hospital_id])?>"  <?php if(\Yii::$app->controller->action->id=='index'){?> class="doctorNavActive clickrecommend" <?php } ?> >概览</a>
        <a href="<?=Url::to(['hospital/detail','hospital_id'=>$hospital_id])?>" <?php if(\Yii::$app->controller->action->id=='detail'){?> class="doctorNavActive clickrecommend" <?php } ?> >详细介绍</a>
<!--        <a href="guahao_hospital.html" >预约挂号</a>-->
        <a href="<?=Url::to(['hospital/departments','hospital_id'=>$hospital_id])?>" <?php if(\Yii::$app->controller->action->id=='departments'){?> class="doctorNavActive clickrecommend" <?php } ?> >医院科室</a>
        <a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id])?>" <?php if(\Yii::$app->controller->action->id=='doclist'){?> class="doctorNavActive clickrecommend" <?php } ?> >医院医生</a>
        <a style="display:none;" href="<?=Url::to(['hospital/diseases','hospital_id'=>$hospital_id])?>" <?php if(\Yii::$app->controller->action->id=='diseases'){?> class="doctorNavActive clickrecommend" <?php } ?> >擅长疾病</a>
        <!--<a href="/hospital_67/guide.html">预约指南</a>-->
<!--        <a href="hospital_comment.html" >医院口碑</a>-->
    </ul>
</div>
<!-- 标签导航 -->
<div class="anchorNav">
    <ul>
        <a>当前位置：</a>
        <a href="<?=Url::to(['index/index'])?>">首页</a>
        <a href="<?=Url::to(['hospitallist/index'])?>">医院排行榜</a>
        <a class="nohospitalindexpage"><?=Html::encode(ArrayHelper::getValue($hosp_data,'name'))?></a>
    </ul>
</div>
