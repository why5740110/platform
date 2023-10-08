<?php
/**
 * @file index.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/8/11
 */


use yii\helpers\ArrayHelper;
use common\helpers\Url;
use common\libs\CommonFunc;
use mobile\widget\Menu;
use yii\helpers\Html;

$this->registerCssFile(Url::getStaticUrlTwo("pages/hospital_home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/hospital_home/index.js'),['depends'=>'mobile\assets\AppAsset']);

?>
<div class="hospital_home">
    <!-- 头部 -->
    <div class="hh_box hh_top">
        <div class="hh_top_img">
            <img src="<?=ArrayHelper::getValue($data,'photo','https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg');?>" onerror="javascript:this.src='https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';" alt="<?=Html::encode(ArrayHelper::getValue($data,'name'))?>" />
        </div>
        <h4><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h4>
        <div class="hos_tags">
            <?php if(ArrayHelper::getValue($data,'kind')){ ?>
                <span class="tags t_style01 t_short"><?=ArrayHelper::getValue($data,'kind')?></span>
            <?php } ?>
            <?php if(ArrayHelper::getValue($data,'level')){ ?>
                <span class="tags t_style01 t_short"><?=ArrayHelper::getValue($data,'level')?></span>
            <?php } ?>
            <?php if(ArrayHelper::getValue($data,'type')){ ?>
                <span class="tags t_style01 t_short"><?=ArrayHelper::getValue($data,'type')?></span>
            <?php } ?>
            <?php if(ArrayHelper::getValue($data,'hospital_tags')){
                $hospital_tags = explode('、',ArrayHelper::getValue($data,'hospital_tags'));
                foreach ($hospital_tags as $hospital_tag) {
                ?>
                    <span class="tags t_style01 t_short"><?=$hospital_tag; ?></span>
            <?php } } ?>
        </div>
        <p class="introduce">医院概况：<?=CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($data,'description')))?><a href="javascript:;"><i></i>查看简介</a></p>
    </div>
    <!-- banner -->
    <?php if (!empty($lunbo)): ?>
        <div class="hh_box main_box swiper mySwiper banner_swiper">
            <div class="swiper-wrapper">
                <?php foreach ($lunbo as $value) : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo $value['link']; ?>">
                            <img src="<?php echo $value['imagelink']; ?>" width="100%"/>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination banner_pagination"></div>
        </div>
    <?php endif; ?>
    <!-- 医院位置 -->
    <div class="hh_box hh_position">
        <dl>
            <dt>
                <?php if (isset($distance) && $distance != 0){ ?>
                    <p>距您<?=$distance > 99 ? ' >99 ' : $distance;?>km</p>
                <?php } ?>
                <span><?=Html::encode(ArrayHelper::getValue($data,'address'))?></span>
            </dt>
        </dl>
        <a href="<?php echo rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['guahao/keshilist', 'hospital_id' => $hospital_id]); ?>">
            <div class="hh_position_but">
                去挂号
            </div>
        </a>
    </div>
    <!-- 医生列表 -->
    <?php if (isset($doctor_list['doctor_list']) && !empty($doctor_list['doctor_list'])): ?>
        <div class="hh_box hh_doctor">
            <div class="main_box_new">
                <div class="list_head_box">
                    <span class="list_title">本院医生</span>
                </div>
            </div>
            <div class="hh_doctor_list">
                <?php foreach ($doctor_list['doctor_list'] as $hk => $hv): ?>
                    <a class="doc_item_wrap" href="<?= Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($hv, 'doctor_id')]) ?>">
                    <div class="doc_photo">
                        <img src="<?= ArrayHelper::getValue($hv, 'doctor_avatar','') ?>" onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';" />
                    </div>
                    <div class="doc_content">
                        <div class="doc_info">
                            <div>
                                <span class="doc_name"><?= Html::encode(ArrayHelper::getValue($hv, 'doctor_realname','')) ?></span>
                                <span class="doc_title"><?= Html::encode(ArrayHelper::getValue($hv, 'doctor_title','')) ?></span>
                            </div>
                            <span class="btn_little">去挂号</span>
                        </div>
                        <div class="doc_text text_wrap">
                            <?= ArrayHelper::getValue($hv, 'doctor_second_department_name','') ?> | <?= ArrayHelper::getValue($hv, 'doctor_hospital','') ?>
                        </div>
                        <?php if (isset($hv['doctor_visit_type']) && !empty($hv['doctor_visit_type'])): ?>
                            <div class="doc_tags">
                                <span class="tags t_style01 t_short"><?=$hv['doctor_visit_type'] ?></span>
                            </div>
                        <?php endif;?>
                        <p class="doc_descript text_over2">
                            擅长：<?= Html::encode(ArrayHelper::getValue($hv, 'doctor_good_at','')) ?>
                        </p>
                    </div>
                </a>
                <?php endforeach;?>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="dh_pop_up hospital_introduce" style="display: none;">
    <div class="di_con">
        <h3>医院概况<span></span></h3>
        <div class="di_con_01">
            <h4><i></i>医院简介</h4>
            <p class="di_con_text">
                <?=CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($data,'description')))?>
            </p>
        </div>
    </div>
</div>

<?php
    //浏览医院详情埋点
    $shence_data = [
        'current_page' => 'msapp_register_hosptial_detail',
        'current_page_name' => '挂号医院详情页',
        'hospital_id' => $data['id'],
        'hospital_name' => Html::encode($data['name']),
        'page_source' => 'ankeshi',
        'page_source_name' => '按科室页',
    ];
    $shence_data = json_encode($shence_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if (in_array(\Yii::$app->controller->getUserAgent(),['patient'])) {
        //\mobile\widget\ShenceStatisticsWidget::widget(['type' => 'PageView', 'data' => $shence_data]);
    }
?>
