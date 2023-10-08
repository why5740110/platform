<?php

use \common\helpers\Url;
use \yii\helpers\Html;

$this->registerCssFile(Url::getStaticUrl("css/ks_choice.css"));
$this->registerJsFile(Url::getStaticUrl("js/ks_choice.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/department_list/index.css"),['depends'=>'mobile\assets\AppAsset']);

?>

<div class="departmentMain"  style="display: block;">
<!--    --><?php //if($ua != 'patient' && $ua != 'mini'){?>
<!--        <div class="nav">-->
<!--        <span class="close" onclick="window.history.go(-1);"></span>-->
<!--        <span class="fs16">科室选择</span>-->
<!--    </div>-->
<!--    --><?php //}?>
    <?php if (!empty($config_keshi_list)): ?>
    <ul class="dl_top">
        <?php foreach ($config_keshi_list as $key => $val):?>
            <?php
            if(!$region)
            {
                $region = ($province['pinyin'] ?? 0);
            }
            $url = '/hospital/hospitallist/departments/'.$region.'_0_'.$val['second_department_id'].'_0_1.html';
            ?>
            <li><a href="<?= $url?>"><?= Html::encode($val['second_department_name']) ?></a></li>
        <?php endforeach;?>
    </ul>
    <?php endif?>

    <div class="classificationList">
        <ul class="populMainSel ">
            <?php foreach ($fkeshi_list as $fk=>$fv):?>
            <li class="getkeshi <?php if($fk == 1){?>dep_active<?php }?>" fkeshi_id="<?=$fv['department_id'];?>"><s></s><?= Html::encode($fv['department_name']);?></li>
            <?php endforeach;?>
        </ul>
        <div class="populMainTexth">
            <?php foreach ($fkeshi_list as $fk=>$fv):?>
            <ul class="populMainText" <?php if($fk != 1){?>style="display: none;"<?php }?>>
                <?php foreach ($fv['second_arr'] as $sk=>$sv):?>
                <?php
                    if(!$region)
                    {
                        $region = ($province['pinyin'] ?? 0);
                    }
                    $url = '/hospital/hospitallist/departments/'.$region.'_0_'.$sv['department_id'].'_0_1.html';
                ?>
                <li <?php if($sk == 0){?>class="dep_active"<?php }?>><a href="<?= $url?>"><?= Html::encode($sv['department_name']);?></a></li>
                <?php endforeach;?>
            </ul>    
            <?php endforeach;?>                    
        </div>
    </div>
</div>