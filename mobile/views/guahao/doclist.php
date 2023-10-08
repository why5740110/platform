<?php
use common\helpers\Url;
use common\libs\HashUrl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use \mobile\widget\DoclistViewWidget;
use common\libs\CommonFunc;

$this->registerCssFile(Url::getStaticUrl("css/number_source.css"));
$this->registerJsFile(Url::getStaticUrl("js/number_source.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/hospital_register/index.css"),['depends'=>'mobile\assets\AppAsset']);


$weekArr = ['周日','周一','周二','周三','周四','周五','周六'];
$platformArr = CommonFunc::getTpPlatformNameList();
$visit_type = CommonFunc::$visit_type;
$visit_nooncode_type = CommonFunc::$visit_nooncode_type;
$show_day = CommonFunc::SHOW_DAY;

?>
<div class="main_wrapper hospital_register">
    <div class="hr_header">
        <h4><?=Html::encode(ArrayHelper::getValue($data,'department'));?></h4>
        <p><?=Html::encode(ArrayHelper::getValue($data,'hosp_name'));?></p>
    </div>
    <!-- 头部号源选择模块 -->
    <div class="source_nav_main">
        <div class="source_allbox">
            <div class="swiper-container">
                <ul class="swiper-wrapper">
                    <?php $flag = false;?>
                    <?php foreach ($paibanData as $k=>$v): ?>
                        <li class="swiper-slide youhao<?php if (!$flag && ArrayHelper::getValue($v, 'status') == 1):?> active <?php $flag = true;?><?php endif;?>" data-date="<?=$k;?>" data-status="<?=ArrayHelper::getValue($v, 'status',-1);?>">
                            <?php $dayKey=date('w',strtotime($k));?>
                            <h6><?=ArrayHelper::getValue($weekArr,$dayKey)?></h6>
                            <p class="fs20"><?=date('m-d',strtotime($k))?></p>
                            <?php if (ArrayHelper::getValue($v, 'status') == 1): ?>
                                <p class=you_style>有号</p>
                            <?php elseif(!empty($v) && (ArrayHelper::getValue($v, 'status') != 1)): ?>
                                <p class="">约满</p>
                            <?php else: ?>
                                <p class="">无号</p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="allweekBox">
                <span>全部<br>日期</a></span>
            </div>
        </div>
        <div class="noData_showBox"><?= ArrayHelper::getValue($data, 'open_time', ''); ?></div>
    </div>
    <div class="allsourcePopul"></div>
    <div class="allsourceConcent">
        <?php if (!empty($data['month_list'])): ?>
            <?php $mount_num = 0;?>
            <?php $tmp_flag = false;?>
            <?php foreach ($data['month_list'] as $month_key => $month_item): ?>
                <h5><?=CommonFunc::dateToChinaDate($month_key);?></h5>
                <ul>
                    <li><span>日</span></li>
                    <li><span>一</span></li>
                    <li><span>二</span></li>
                    <li><span>三</span></li>
                    <li><span>四</span></li>
                    <li><span>五</span></li>
                    <li><span>六</span></li>
                </ul>
                <ul class="detial_list">
                    <?php
                    $month_item_value = array_keys($month_item);
                    $frist_day = $month_item_value[0];
                    $frist_date = $month_key.'-'.$frist_day;
                    // $frist_date = $month_key.$frist_day.'日';
                    // $frist_date = CommonFunc::chinaDateToDate($frist_date);
                    $t_week = CommonFunc::getSunday($frist_date);
                    $diff_day = CommonFunc::getTimeDiff($t_week,$frist_date);
                    unset($month_item_value,$t_week,$frist_date);
                    ?>
                    <?php if ($diff_day > 1): ?>
                        <?php for ($i=1; $i < $diff_day; $i++) { ?>
                            <li class=""><span>&nbsp;</span></li>
                        <?php }?>
                    <?php endif; ?>

                    <?php if (!empty($month_item)): ?>
                        <?php foreach ($month_item as $day_key => $day_item): ?>
                            <?php $month_day = $month_key.'-'.$day_key;?>
                            <li class="have_li <?php if (!$tmp_flag && ArrayHelper::getValue($day_item, 'status') == 1):?> active <?php $tmp_flag = true;?><?php endif;?>" data-date="<?=$month_day;?>"  data-id="<?=$mount_num;?>" data-status="<?=ArrayHelper::getValue($day_item, 'status',-1);?>"><span><?=$day_key;?></span>
                                <?php if ($day_item && ArrayHelper::getValue($day_item, 'status') == 1): ?>
                                    <span>有号</span>
                                <?php elseif($day_item && ArrayHelper::getValue($day_item, 'status') != 1):?>
                                    <span class="wuhaostyle">约满</span>
                                <?php else:?>
                                    <span class="wuhaostyle">无号</span>
                                <?php endif; ?>
                            </li>
                            <?php $mount_num++;?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- 医生列表 -->
    <div class="list_content" id="list_content">
        <?php if (!empty($visit_nooncode_list)): ?>
            <?php foreach ($visit_nooncode_list as $noon_key => $noon_item): ?>
                <?php if (!empty($noon_item)): ?>
                    <?php foreach ($noon_item as $sched_key => $sched_item):?>
                        <?php $doctor_id = (ArrayHelper::getValue($sched_item, 'primary_id') > 0) ? ArrayHelper::getValue($sched_item, 'primary_id') : ArrayHelper::getValue($sched_item, 'doctor_id');?>
                    <div class="doc_item">
                        <a class="doc_item_wrap" href="<?= Url::to(['/doctor/home', 'doctor_id' => $doctor_id])?>"">
                            <div class="doc_photo">
                                <img src="<?= ArrayHelper::getValue($sched_item, 'doctor_avatar') ?>" onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';" alt="<?= Html::encode(ArrayHelper::getValue($sched_item, 'realname')) ?>" />
                            </div>
                            <div class="doc_content">
                                <div class="doc_info">
                                    <div style="flex: 1">
                                        <span class="doc_name"><?= Html::encode(ArrayHelper::getValue($sched_item, 'realname'));?></span>
                                        <span class="doc_title"><?= Html::encode(ArrayHelper::getValue($sched_item, 'doctor_title'));?></span>
                                    </div>
                                    <span class="btn_little">去挂号</span>
                                </div>
                                <div class="doc_text text_wrap">
                                    <?=Html::encode(ArrayHelper::getValue($data,'department',''));?> | <?=Html::encode(ArrayHelper::getValue($data,'hosp_name',''));?>
                                </div>
                                <?php if (isset($sched_item['doctor_visit_type']) && !empty($sched_item['doctor_visit_type'])): ?>
                                    <div class="doc_tags">
                                        <span class="tags t_style01 t_short"><?= ArrayHelper::getValue($sched_item, 'doctor_visit_type','');?></span>
                                    </div>
                                <?php endif;?>
                                <p class="doc_descript text_over2">
                                    擅长：<?= Html::encode(ArrayHelper::getValue($sched_item, 'doctor_good_at',''));?>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($doctor_list['doctor_list'])): ?>
        <h5 class="hr_title_text">其他相同科室医生</h5>
        <div class="list_content">
            <?php foreach ($doctor_list['doctor_list'] as $key => $val):?>
                <div class="doc_item">
                    <a class="doc_item_wrap" href="<?= Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($val, 'doctor_id')])?>">
                        <div class="doc_photo">
                            <img src="<?= ArrayHelper::getValue($val, 'doctor_avatar','');?>" onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';" />
                        </div>
                        <div class="doc_content">
                            <div class="doc_info">
                                <div style="flex: 1">
                                    <span class="doc_name"><?= Html::encode(ArrayHelper::getValue($val, 'doctor_realname',''));?></span>
                                    <span class="doc_title"><?= Html::encode(ArrayHelper::getValue($val, 'doctor_title',''));?></span>
                                </div>
                                <span class="btn_little">去挂号</span>
                            </div>
                            <div class="doc_text text_wrap">
                                <?= Html::encode(ArrayHelper::getValue($val, 'doctor_second_department_name',''));?> | <?= Html::encode(ArrayHelper::getValue($val, 'doctor_hospital',''));?>
                            </div>
                            <?php if (isset($val['doctor_visit_type']) && !empty($val['doctor_visit_type'])): ?>
                                <div class="doc_tags">
                                    <span class="tags t_style01 t_short"><?= ArrayHelper::getValue($val, 'doctor_visit_type','');?></span>
                                </div>
                            <?php endif;?>
                            <p class="doc_descript text_over2">
                                擅长：<?= Html::encode(ArrayHelper::getValue($val, 'doctor_good_at',''));?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else:?>
            <!--暂无信息-->
            <div class="sr_nothing">
                <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
                <p>暂无符号条件的医生</p>
            </div>
        <?php endif; ?>
</div>

<input type="hidden" value="<?=$hospital_id;?>" id="hospital_id">
<input type="hidden" value="<?=$department_id;?>" id="department_id">
<input type="hidden" value="<?= \Yii::$app->request->csrfToken;?>" id="_csrf-mobile">
<input type="hidden" value="<?= Url::to(['guahao/ajax-get-doctor','hospital_id' => $hospital_id,'tp_department_id'=>$department_id]);?>" id="ajax_url">

<div id="doc_no_data_box" style="display: none;">
	<div class="doc_no_data_box"> <img src="<?=Url::getStaticUrl('imgs/no_data_img.png')?>" width="135" height="135" alt=""> <p class="">暂无符号条件的医生，请重新选择</p> </div>
</div>

<!--lodaing-->
<div class="loadings home_loadCon" style="display: none;">
    <div class="samePopul load_Popul">
        <div class="position_img"><img src="<?=Url::getStaticUrl("imgs/load2.gif")?>" width="100%"></div>
    </div>
</div>