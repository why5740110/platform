<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use \common\helpers\Url;
use \mobile\widget\HospitalViewWidget;
use \mobile\widget\DoclistViewWidget;

$this->registerCssFile(Url::getStaticUrl("css/search_result.css"));
$this->registerJsFile(Url::getStaticUrl("js/search_result.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerCssFile(Url::getStaticUrlTwo("pages/search/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/search/index.js'),['depends'=>'mobile\assets\AppAsset']);

$this->title = '挂号';
if (\Yii::$app->controller->getUserAgent() == 'patient') {
    $this->context->seoTitle = $this->title;
}

?>

<div class="main_wrapper search_page search_result">
    <div class="fuzhu_box">
        <!-- 可输入搜索内容 -->
        <div class="search_box search_page_input">
            <i class="search_box_icon"></i>
            <input class="search_text" type="text" id="search_input" placeholder="请输入搜索内容" value="<?= Html::encode($keyword); ?>" />
            <b class="search_box_cha" style="display: none"></b>
            <span class="search_button search_qd_btn" from="all">搜索</span>
        </div>
    </div>
    <?php if ($keyword) { ?>
        <div class="sr_nav">
            <span class="xz" data-type="all" onclick="window.location.href='<?= Url::to(['search/show', 'keyword' => Html::encode($keyword)]); ?>'">全部<i></i></span>
            <span data-type="hospital" onclick="window.location.href='<?= Url::to(['search/so', 'type' => 'hospital', 'keyword' => Html::encode($keyword)]); ?>'">医院<i></i></span>
            <span data-type="doctor" onclick="window.location.href='<?= Url::to(['search/so', 'type' => 'doctor', 'keyword' => Html::encode($keyword)]); ?>'">医生<i></i></span>
        </div>
    <?php } ?>

    <?php if (!empty($hospital_list) || !empty($doctor_list)) { ?>
    <div class="sr_con">
        <div class="src_all">

            <?php if (!empty($hospital_list)): ?>
                <h4>相关医院</h4>
                <div class="src_hosp">
                    <?php foreach ($hospital_list as $key => $value): ?>
                        <?php $value['hospital_name'] = ArrayHelper::getValue($value, 'highlight.hospital_name_keyword.0', $value['hospital_name']) ?>
                        <?php
                            if (isset($value['hospital_department_name']) && !empty($value['hospital_department_name'])) {
                                $value['hospital_department_name'] = str_replace(Html::encode($keyword), '<span class="guanjianci">' . Html::encode($keyword) . '</span>', Html::encode($value['hospital_department_name']));
                            }
                        ?>
                        <?= HospitalViewWidget::widget(['row' => $value, 'type' => 1, 'shence_type' => 2]); ?>
                    <?php endforeach; ?>
                    <div class="more_but"><a href="<?= Url::to(['search/so', 'type' => 'hospital', 'keyword' => Html::encode($keyword)]); ?>">查看更多<span></span></a></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($doctor_list)): ?>
                <h4>相关医生</h4>
                <div class="src_doc">
                    <?php foreach ($doctor_list as $key => $value): ?>
                        <?php $value['shece_doctor_hospital'] = $value['doctor_hospital']; ?>
                        <?php $value['doctor_realname'] = ArrayHelper::getValue($value, 'highlight.doctor_realname_keyword.0', $value['doctor_realname']); ?>
                        <?php $value['doctor_hospital'] = $value['doctor_hospital'] == $keyword ? '<span class="guanjianci">' . $value['doctor_hospital'] . '</span>' : $value['doctor_hospital']; ?>
                        <?php $value['doctor_second_department_name'] = $value['doctor_second_department_name'] == $keyword ? '<span class="guanjianci">' . Html::encode($value['doctor_second_department_name']) . '</span>' : Html::encode($value['doctor_second_department_name']); ?>
                        <?= DoclistViewWidget::widget(['row' => $value, 'type' => 1,'shence_type' => 2]); ?>
                    <?php endforeach; ?>
                    <!-- 查看更多 start-->
                    <div class="more_but"><a href="<?= Url::to(['search/so', 'type' => 'doctor', 'keyword' => Html::encode($keyword)]); ?>">查看更多<span></span></a></div>
                    <!-- 查看更多 end-->
                </div>
            <?php endif; ?>

        </div>
    </div>
    <?php } ?>

    <?php if ($has_data == 0): ?>
        <div class="sr_nothing">
            <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
            <p>未找到相关内容</p>
            <span>换个词搜搜看</span>
        </div>
    <?php endif; ?>
</div>
