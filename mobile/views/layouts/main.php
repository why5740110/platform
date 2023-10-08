<?php

/* @var $this \yii\web\View */
/* @var $content string */

use mobile\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\Url as Url2;

AppAsset::register($this);
$request = Yii::$app->request;

// $this->registerCssFile(Url2::getStaticUrl("css/common.css"));
// $this->registerJsFile(Url2::getStaticUrl("js/commin.js"));
$this->registerJsFile(Url::to('@mobileCommonStatic/js/jquery-1.11.1.min.js'));

if (\Yii::$app->controller->getUserAgent() == 'mini') {
    $this->registerJsFile('https://res.wx.qq.com/open/js/jweixin-1.3.2.js');
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<script charset="UTF-8" src="https://www.nisiyacdn.com/sdk/sensorsdata.min.js"></script>
<script charset="UTF-8" src="https://www.nisiyacdn.com/sdk/heatmap.min.js"></script>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo Html::encode($this->context->filterSeoContent($this->context->seoTitle)) ?></title>
    <meta name="keywords" content="<?php echo Html::encode($this->context->filterSeoContent($this->context->seoKeywords)) ?>">
    <meta name="description" content="<?php echo Html::encode($this->context->filterSeoContent($this->context->seoDescription)) ?>">
    <meta name="applicable-device" content="mobile">
    <link rel="icon" href="/favicon.ico" />
    <link rel="dns-prefetch" href="//m.nisiya.net">
    <!--<link rel="dns-prefetch" href="//mnisiya.top">-->
    <link rel="dns-prefetch" href="//hm.baidu.com">
    <link rel="apple-touch-icon" href="<?=url::to('@mobileCommonStatic/images/apple_icon/57.png')?>"/>
    <link rel="apple-touch-icon" sizes="72x72" href="<?=url::to('@mobileCommonStatic/images/apple_icon/57.png')?>"/>
    <link rel="apple-touch-icon" sizes="114x114" href="<?=url::to('@mobileCommonStatic/images/apple_icon/114.png')?>"/>
    <?php
    $this->registerCssFile(Url2::getStaticUrlTwo("assets/css/swiper-bundle.min.css"));
    $this->registerCssFile(Url2::getStaticUrlTwo("pages/component/index.css"));
    $this->registerJsFile(Url2::getStaticUrlTwo('assets/js/swiper-bundle.min.js'));
    ?>
    <script>!function (e, t) { function n() { t.body ? t.body.style.fontSize = 12 * o + "px" : t.addEventListener("DOMContentLoaded", n) } function i(t) { var t = t ? t : 750, n = e.innerWidth / t * 100; return e.innerWidth > t ? (n = 50, d.style.maxWidth = t + "px", d.style.margin = "0 auto", void (d.style.fontSize = "100px")) : void (d.style.fontSize = n + "px") } var d = t.documentElement, o = e.devicePixelRatio || 1; if (n(), i(750), e.addEventListener("resize", function () { i(750) }), e.addEventListener("pageshow", function (e) { e.persisted && i(750) }), o >= 2) { var a = t.createElement("body"), r = t.createElement("div"); r.style.border = ".5px solid transparent", a.appendChild(r), d.appendChild(a), 1 === r.offsetHeight && d.classList.add("hairlines"), d.removeChild(a) } }(window, document);</script>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php $ua = \Yii::$app->controller->getUserAgent(); ?>
<?php if(in_array($ua, ['patient', 'mini', 'haoyiapp'])): ?>
<?php endif; ?>


<?= $content ?>

<?php $this->endBody() ?>

</body>

</html>
<?php $this->endPage() ?>
