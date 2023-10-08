<?php
use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>404页面</title>
    <meta name="author" content="nisiya mal" />
    <meta name="Copyright" content="nisiya.top" />
    <meta name="keywords" content="王氏商城" />
    <meta name="description" content="王氏商城" />
    <!--引入External Css样式-->
    <link rel="stylesheet" href="<?php echo url::to('@commonStatic/news/statics/css/bootstrap.min.css')?>"/>
    <link rel="stylesheet" href="<?php echo url::to('@commonStatic/news/statics/css/jquery.jqzoom.css')?>"/>
    <link rel="stylesheet" href="<?php echo url::to('@commonStatic/news/statics/css/common.css')?>"/>
    <link rel="stylesheet" href="<?php echo url::to('@commonStatic/news/statics/css/style.css')?>"/>
</head>
<body lang="zh-CN">
<div class="error_box">
    <div class="error_main">
        <div class="fl error_img"><img src="<?php echo url::to('@commonStatic/news/statics/images/404pic.jpg')?>"></div>
        <div class="fl wenzi">
            <p class="blue">从前有座山，山里有个老神仙</p>
            <p class="white">他说，放松...呼吸...放松...呼吸...<br>即使再遥远我也能看得见</p>
            <p class="mt40">
                <a href="/" class="back_home">回首页</a>
                <a href="javascript:history.go(-1);" class="">返回上一页</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
