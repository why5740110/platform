<?php

use \common\helpers\Url;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use mobile\widget\Menu;
use \mobile\widget\HospitalViewWidget;

$this->registerCssFile(Url::getStaticUrl("css/my.css"));
$this->registerJsFile(Url::getStaticUrl("js/my.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/myorder/index.css"),['depends'=>'mobile\assets\AppAsset']);

?>
<div class="main_wrapper">

    <?php if(!$list && $page == 1): ?>
        <div class="sr_nothing">
            <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
            <p>暂无挂号订单</p>
        </div>
    <?php endif;?>

    <div class="main_box_new">
        <div class="list_content db_box">
            <?php if(!empty($list)): ?>
                <?php foreach($list as $k=>$value):?>
                    <div class="doc_item item">
                        <div class="doc_item_wrap">
                            <div class="doc_photo">
                                <img src="<?=$value['doctor_avatar'];?>" onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';">
                            </div>
                            <div class="doc_content">
                                <div class="doc_info">
                                    <div>
                                        <span class="doc_name"><?=Html::encode($value['doctor_name']);?></span>
                                        <span class="doc_title">主任医师</span>
                                    </div>
                                    <?php if ($value['state_show'] == '待就诊'){ ?>
                                        <span class="order_status"><?=$value['state_show'];?></span>
                                    <?php }elseif($value['state_show'] == '已完成'){ ?>
                                        <span class="order_status or_cancel"><?=$value['state_show'];?></span>
                                    <?php } else{ ?>
                                        <span class="order_status or_cancel"><?=$value['state_show'];?></span>
                                    <?php } ?>
                                </div>
                                <div class="doc_text text_wrap"><span><?=Html::encode($value['department_name']);?> | <?=Html::encode($value['hospital_name']);?></div>
                                <ul class="order_details">
                                    <li>患者姓名：<?=Html::encode($value['patient_name']);?></li>
                                    <li>就诊时间：<?=$value['visit_time_ymd'];?> <?=$value['visit_nooncode_text'];?></li>
                                    <li>预约费用：<span class="order_num">￥<?= ($value['visit_cost']/100)??"";?></span></li>
                                </ul>
                                <div class="btn_box btn_order">
                                    <a href="<?= Url::to(['/register/register-detail', 'id' => ArrayHelper::getValue($value,'id')]); ?>" class="btn_style btn_full_gray">查看详情</a>
                                    <?php if ($value['state_show'] != '待就诊'){ ?>
                                        <a href="<?php $doctor_id = (ArrayHelper::getValue($value, 'primary_id') > 0) ? ArrayHelper::getValue($value, 'primary_id') : ArrayHelper::getValue($value, 'doctor_id'); $href_url = '/hospital/doctor_'.HashUrl::getIdEncode($doctor_id).'.html'; echo Url::to([$href_url]);?>" class="btn_style btn_full_blank">再次预约</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            <?php else:?>
                <input type="hidden" value="1" id="no_data">
            <?php endif;?>
        </div>

        <?php if(count($list) < 10 && $page == 1): ?>

        <?php else:?>
            <div class="more_but loadingMore" data-uri="<?= Url::to(['order/list','page'=>($page+1)]);?>">查看更多<span></span></div>
        <?php endif;?>

        <div class="list_more_wrap" style="display: none;" id="nothing">已经到底了~</div>
    </div>
</div>

<div style="display: none;" id="more_page" data-uri="<?= Url::to(['order/list','page'=>($page+1)]);?>"></div>
