<?php

use \common\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;
use \mobile\widget\HospitalViewWidget;
use \mobile\widget\DoclistViewWidget;
use common\libs\CommonFunc;

$this->registerCssFile(Url::getStaticUrl("css/search_result.css"));
$this->registerJsFile(Url::getStaticUrl("js/search_result.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerCssFile(Url::getStaticUrlTwo("pages/search/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/search/index.js'),['depends'=>'mobile\assets\AppAsset']);

$this->title = '挂号';
if (\Yii::$app->controller->getUserAgent() == 'patient') {
    $this->context->seoTitle = $this->title;
}
$page_size    = CommonFunc::PAGE_SIZE;

?>

<div class="main_wrapper search_page search_result">
    <div class="fuzhu_box">
        <!-- 可输入搜索内容 -->
        <div class="search_box search_page_input">
            <i class="search_box_icon"></i>
            <input class="search_text" type="text" id="search_input" placeholder="请输入搜索内容" value="<?= Html::encode($keyword); ?>" />
            <b class="search_box_cha" style="display: none"></b>
            <span class="search_button search_qd_btn" onclick="_maq.click({'click_id':'挂号-M搜索结果页-搜索按钮' , 'click_url':'<?php echo rtrim(\Yii::$app->params['domains']['mobile'], '/') . \Yii::$app->request->url; ?>'})"
                  from="<?=$type ?>">搜索
            </span>
        </div>
    </div>
    <?php if ($keyword) { ?>
        <div class="sr_nav">
            <span class="<?php if ($type == 'all'): ?>xz<?php endif; ?>" data-type="all" onclick="window.location.href='<?= Url::to(['search/show', 'keyword' => Html::encode($keyword)]); ?>'">全部<i></i></span>
            <span class="<?php if ($type == 'hospital'): ?>xz<?php endif; ?>" data-type="hospital" onclick="window.location.href='<?= Url::to(['search/so', 'type' => 'hospital', 'keyword' => Html::encode($keyword)]); ?>'">医院<i></i></span>
            <span class="<?php if ($type == 'doctor'): ?>xz<?php endif; ?>" data-type="doctor" onclick="window.location.href='<?= Url::to(['search/so', 'type' => 'doctor', 'keyword' => Html::encode($keyword)]); ?>'">医生<i></i></span>
        </div>
    <?php } ?>

    <?php if ($type == 'hospital' || $type == 'doctor') { ?>
        <div class="sr_con">
        <?php if ($type == 'hospital'): ?>
            <?php if (!empty($list)): ?>
                <div class="src_hospital" id="hs_list_con">
                    <?php foreach ($list as $key => $value): ?>
                        <?php $value['hospital_name'] = ArrayHelper::getValue($value, 'highlight.hospital_name_keyword.0', $value['hospital_name']) ?>
                        <?= HospitalViewWidget::widget(['row' => $value, 'type' => 1, 'shence_type'=>2]); ?>
                    <?php endforeach; ?>
                </div>
                <!-- 查看更多 start-->
                <div class="more_but more_data">查看更多<span></span></div>
                <div class="more_but loading_data" style="display: none;">正在加载中~</div>
                <div class="more_but nothing_data" style="display: none;">已经到底了~</div>
                <!-- 查看更多 end-->
            <?php else: ?>
                <div class="sr_nothing">
                    <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
                    <p>未找到相关内容</p>
                </div>
            <?php endif; ?>
        <?php elseif ($type == 'doctor'): ?>
            <?php if (!empty($list)): ?>
                <div class="src_doctor" id="hs_list_con">
                    <?php foreach ($list as $key => $value): ?>
                        <?php $value['shece_doctor_hospital'] = $value['doctor_hospital']; ?>
                        <?php $value['doctor_realname'] = ArrayHelper::getValue($value, 'highlight.doctor_realname_keyword.0', $value['doctor_realname']); ?>
                        <?php $value['doctor_hospital'] = $value['doctor_hospital'] == $keyword ? '<span class="guanjianci">' . Html::encode($value['doctor_hospital']) . '</span>' : Html::encode($value['doctor_hospital']); ?>
                        <?php $value['doctor_second_department_name'] = $value['doctor_second_department_name'] == $keyword ? '<span class="guanjianci">' . Html::encode($value['doctor_second_department_name']) . '</span>' : Html::encode($value['doctor_second_department_name']); ?>
                        <?= DoclistViewWidget::widget(['row' => $value, 'type' => 1,'shence_type' => 2]); ?>
                    <?php endforeach; ?>
                </div>
                <!-- 查看更多 start-->
                <div class="more_but more_data">查看更多<span></span></div>
                <div class="more_but loading_data" style="display: none;">正在加载中~</div>
                <div class="more_but nothing_data" style="display: none;">已经到底了~</div>
                <!-- 查看更多 end-->
            <?php else: ?>
                <div class="sr_nothing">
                    <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
                    <p>未找到相关内容</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    <?php } ?>

    <input type="hidden" id="page_size" value="<?=$page_size?>">
    <div style="display: none;" id="more_page"
         data-uri="<?= Url::to(['search/so', 'type' => $type, 'region' => $region, 'sanjia' => $sanjia, 'keyword' => Html::encode($keyword)]); ?>"></div>
    <div class="jb_no_data hide" id="nothing">已全部加载完成</div>
</div>