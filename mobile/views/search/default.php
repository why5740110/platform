<?php

use \common\helpers\Url;
use yii\helpers\Html;

//$this->registerCssFile(Url::getStaticUrl("css/search.css"));
$this->registerJsFile(Url::getStaticUrl("js/search.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerCssFile(Url::getStaticUrlTwo("pages/search/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/search/index.js'),['depends'=>'mobile\assets\AppAsset']);

$this->title = '挂号';
if (\Yii::$app->controller->getUserAgent() == 'patient') {
    $this->context->seoTitle = $this->title;
}

?>

<div class="main_wrapper search_page">
    <div class="fuzhu_box">
        <!-- 可输入搜索内容 -->
        <div class="search_box search_page_input">
            <i class="search_box_icon"></i>
            <input class="search_text" id="search_input" type="text" placeholder="请输入搜索内容" value="<?= Html::encode($keyword); ?>" />
            <b class="search_box_cha" style="display: none;"></b>
            <span class="search_button search_qd_btn" from="all">搜索</span>
        </div>
    </div>
    <?php if (!empty($hospital_list)): ?>
        <div class="sp_translate">
            <h4>热门推荐</h4>
            <div class="spt_list">
                <?php foreach ($hospital_list as $key => $value): ?>
                <a href="<?= Url::to(['search/so', 'type' => 'hospital', 'keyword' => Html::encode($value['hospital_name'])]); ?>"><i><?=$key+1?></i><p><?=Html::encode($value['hospital_name']); ?></p>
                    <?php if ($key < 3): ?>
                        <span></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
