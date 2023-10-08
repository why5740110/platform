<?php
use yii\helpers\Url;
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0,viewport-fit=cover"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="applicable-device" content="mobile">
    <title></title>
    <meta name="Keywords" content="" />
    <meta name="Description" content="" />
    <link rel="canonical" href="https://www.nisiya.net/"/>
    <link rel="dns-prefetch" href="//m.nisiya.net">
    <!--<link rel="dns-prefetch" href="//mnisiya.top">-->
    <link rel="apple-touch-icon" href="<?php echo url::to('@mobileCommonStatic/images/apple_icon/57.png?v=v1')?>"/>
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo url::to('@mobileCommonStatic/images/apple_icon/114.png?v=v1')?>"/>
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo url::to('@mobileCommonStatic/images/apple_icon/114.png?v=v1')?>"/>
    <!--    <link rel="stylesheet" href="--><!--">-->
    <link rel="stylesheet" href="<?php echo url::to('@mobileCommonStatic/news/statics/css/public.css?v=v1')?>">
    <link rel="stylesheet" href="<?php echo url::to('@mobileCommonStatic/news/statics/css/index.css?v=v1')?>">
    <link href="<?php echo url::to('@mobileCommonStatic/css/popup.css?v=v1')?>" rel="stylesheet">
    <link href="<?php echo url::to('@mobileCommonStatic/css/scroll_select.css?v=v1')?>" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo url::to('@mobileCommonStatic/news/statics/css/new_mCss.css?v=v1')?>">
</head>
<body>
<div id="Cbody">
    <div class="tc" style="margin-top:75px;">
        <img src="<?php echo url::to('@mobileCommonStatic/news/statics/images/404.png?v=v1')?>" alt="" width="172" />
    </div>
    <div class="tc f404-b">
        <p class="mt20 mb15 fs16">哎呦~ 你访问的挂号页面不存在。</p>
    </div>
    <div class="tc f404-botton mt30 mb30">
        <?php if (\Yii::$app->controller->getUserAgent() == 'patient'): ?>
        <a href="pd://switchTab?page=home" class="mr15 textf dib">回首页</a>
        <?php else:?>
        <a href="<?=\Yii::$app->params['hospitalUrl'];?>" class="mr15 textf dib">回首页</a>
        <?php endif;?>
    </div>
</div>
</body>
</html>