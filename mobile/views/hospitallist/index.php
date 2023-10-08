<?php

use common\libs\CommonFunc;
use \common\helpers\Url;
use \mobile\widget\HospitalViewWidget;
use \yii\helpers\ArrayHelper;

$this->title = '按医院';
//$this->registerCssFile(Url::getStaticUrl("css/hospital_list.css"));
//$this->registerJsFile(Url::getStaticUrl("js/hospital_list.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerCssFile(Url::getStaticUrlTwo("pages/hospital_search/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/hospital_search/index.js'),['depends'=>'mobile\assets\AppAsset']);

$level_list = [0=>'不限']+CommonFunc::$level_list;
$hos_type_list = [0=>'全部']+CommonFunc::$hos_type_list;
$page_size    = CommonFunc::PAGE_SIZE;
$current_data = 0;

?>

<div class="main_wrapper hospital_search">
    <!-- 搜索 -->
    <div class="region_search">
        <div class="location_city">
            <span class="city_name"><?= $city['name'] ?? $province['name'] ?? '全国' ?></span>
            <i class="city_select_icon"></i>
        </div>
        <!-- 搜索框 start -->
<!--        <div class="search_box">-->
            <a class="search_box" href="<?=Url::to(['search/so']);?>">
                <i class="search_box_icon"></i>
                <input class="search_text" type="text" placeholder="搜索医院、科室、医生" />
<!--                <span class="search_button">搜索</span>-->
            </a>
<!--        </div>-->
    </div>
    <!-- 筛选 -->
    <div class="hs_screen">
        <div class="hs_screen_con">
            <p><i><?php if ($hosTypeStr == '0') {?> 医院类型 <?php } else {?> <?php echo $hosTypeStr; ?> <?php }?></i><span></span></p>
        </div>
        <div class="hs_screen_con">
            <p><i><?php if ($sanjiaStr == '0') {?> 医院等级 <?php } else {?> <?php echo $sanjiaStr; ?> <?php }?></i><span></span></p>
        </div>
    </div>
    <!-- 筛选弹框 -->
    <div class="screen_box">
        <div class="screening" filter_items="hsoType" >
            <div class="screen_box_bg"></div>
            <div class="screen_box_con">
            <ul>
                <?php foreach($hos_type_list as $hos_key=>$hos_val):?>
                    <li <?php if(isset($hos_type) && $hos_type == $hos_key): ?>class="xz"<?php endif;?> data_id="<?=$hos_key;?>" id="hos_type<?=$hos_key;?>"><?=$hos_val;?></li>
                <?php endforeach;?>
            </ul>
            <div class="screenSubmit">
                <b></b>
                <span>确定</span>
            </div>
            </div>
        </div>
        <div class="screening" filter_items="hosGrade">
            <div class="screen_box_bg"></div>
            <div class="screen_box_con">
            <ul>
                <?php foreach($level_list as $level_key=>$level_val):?>
                    <li <?php if(isset($sanjia) && $sanjia == $level_key): ?>class="xz"<?php endif;?> data_id="<?=$level_key;?>"><?=$level_val;?></li>
                <?php endforeach;?>
            </ul>
            <div class="screenSubmit">
                <b></b>
                <span>确定</span>
            </div>
            </div>
        </div>
    </div>
    <!-- 列表 -->
    <div class="hs_list">
        <?php if (!empty($hospital_list)): ?>
            <div class="hs_list_con" id="hs_list_con">
                    <?php foreach ($hospital_list as $hk => $hv): ?>
                        <?=HospitalViewWidget::widget(['row' => $hv, 'type' => 1, 'shence_type' => 1]);?>
                    <?php endforeach;?>
            </div>
            <!-- 查看更多 start-->
            <div class="more_but more_data">查看更多<span></span></div>
            <div class="more_but nothing_data" style="display: none;">已经到底了~</div>
            <!-- 查看更多 end-->
        <?php else:?>
            <div class="sr_nothing">
                <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
                <p>未找到相关内容</p>
            </div>
        <?php endif;?>
    </div>

    <div style="display: none;" id="more_page"
         data-uri="/hospital/hospitallist"
         region="<?= $region ?? '' ?>"
         sanjia="<?= $sanjia ?? 0 ?>"
         hos_type="<?= $hos_type ?? 0 ?>"
         keshi_id="<?= $keshi_id ?? 0 ?>"
         page="<?= $page ?? 1 ?>"
         disease="0"
         type="region"
         dspinyin="<?= $dspinyin ?? 0 ?>" ></div>
    <input type="hidden" id="page" value="<?=$page?>">
    <input type="hidden" id="page_size" value="<?=$page_size?>">

    <!--地区选择弹窗-->
    <div class="samePopul hos_citysPopul">
        <div class="citysBox">
            <div class="ms_regionalselection">
                <div class="ms_regionalselection_l">
                    <ul>
                        <li <?php if (isset($region) && $region): ?> class="" <?php else: ?>  class="selActive" <?php endif; ?> >全国</li>
                        <?php if (!empty($province_list)): ?>
                            <?php foreach ($province_list as $key_pro => $value): ?>
                                <li <?php if (isset($province['id']) && $value['id'] == $province['id']): ?>class="selActive" <?php else: ?> class="getcity" <?php endif; ?> province_pinyin="<?php echo $value['pinyin']; ?>"><?= $value['name']; ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="ms_regionalselection_r">
                    <ul <?php if (isset($city['pinyin']) || isset($province['pinyin'])): ?>style="display:none" <?php endif; ?>>
                        <li class="js_selCity1">
                            <a href="<?= Url::to(['hospitallist/index', 'region' => 0, 'sanjia' => $sanjia,'hostype'=>$hos_type, 'page' => 1]) ?>">全国</a>
                        </li>
                    </ul>
                    <?php if (!empty($province_list)): ?>
                        <?php foreach ($province_list as $key_pro => $value): ?>
                            <ul <?php if (empty($province['pinyin']) || (isset($province['pinyin']) && $province['pinyin'] != $value['pinyin'])): ?>style="display:none" <?php endif; ?>>
                                <li><a href="<?= Url::to(['hospitallist/index', 'region' => $value['pinyin'] ?? 0, 'sanjia' => $sanjia,'hostype'=>$hos_type, 'page' => 1]) ?>">全部</a></li>
                                <?php if (!empty($value['city_arr'])): ?>
                                    <?php foreach ($value['city_arr'] as $ck => $cv): ?>
                                        <li <?php if (isset($city['id']) && $cv['id'] == $city['id']): ?> class="js_selCity" <?php endif; ?>><a href="<?= Url::to(['hospitallist/index', 'region' => $cv['pinyin'], 'sanjia' => $sanjia,'hostype'=>$hos_type, 'page' => 1]) ?>"><?= $cv['name']; ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!--1.10隐私合规弹窗-->
    <div class="zqxy_pop_all" style="display: none">
        <div class="zqxy_pop_box">
            <div class="zqxy_con">
                <p>
                    我们需要获取您的位置信息，便于更好的为您提供附近的医疗服务，推荐本地医院、医生。
                </p>
            </div>
            <div class="btns_box">
                <div class="bty_btn">拒绝</div>
                <div class="ty_btn">同意</div>
            </div>
        </div>
    </div>
</div>

<?php
echo \mobile\widget\ShenceStatisticsWidget::widget(['type' => '','data'=>[]]);
?>
<input style="display: none" id="shenceplatform_type" value="<?=\Yii::$app->controller->getUserAgent()?>">
<script>
    /**
     * 医院埋点
     * 首页按医院点击-》进入医院列表-》点击医院埋点
     * @param data
     */
    function clickHospitalShence(data) {
        if ($("#shenceplatform_type").val() == 'patient') {
            //sensors.track('HospitalClick', data);
        }
    }
</script>