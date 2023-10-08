<?php
/**
 * @file main.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-25
 */

use pc\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?php echo Html::encode($this->context->filterSeoContent($this->context->seoTitle)) ?></title>
    <meta name="keywords" content="<?php echo Html::encode($this->context->filterSeoContent($this->context->seoKeywords)) ?>">
    <meta name="description" content="<?php echo Html::encode($this->context->filterSeoContent($this->context->seoDescription)) ?>">
    <link rel="icon" href="/favicon.ico" />
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<!--头部-->
<div class="toper">
    <div class="w1200">
        <div class="fl nologin">
            <span>您好！</span>
            <a href="<?=ArrayHelper::getValue(\Yii::$app->params,'loginurl') ?>" target="_blank" class="login">请登录</a>
        </div>

        <div class="fl login_ hide">
            <span>欢迎光临王氏医生!</span>
            <!--登录状态开始-->
            <a href="" class="personal"><span class="name loginname"></span>医生</a>
            欢迎您！
            <a href="javascript:void(0)" class="btn_exit">退出</a>
            <!--登录状态结束-->
        </div>

        <div class="fr">
            <a href="/contactus.html" class="alink">品牌营销合作</a>
        </div>
    </div>
</div>

<div class="header">
  <div class="w1200 clearfix">
      <a target="_blank" href="/hospital.html" class="fl logo_wrap">
        <img src="<?=Url::getStaticUrl("images/cf0fa047c8fb475bc259df794c52a254.png");?>" width="186" height="56">
      </a>
    
      <div class="fl homeNav"> <a  href="/hospital.html" <?php if (in_array($this->context->nav,['index'])) : ?>class="active"<?php endif; ?> >医院首页</a>
        <a href="/hospital/doctorlist.html" <?php if (in_array($this->context->nav,['doctorlist','doctor'])) : ?>class="active"<?php endif; ?> >找医生</a>
        <a href="/hospital/hospitallist.html" <?php if (in_array($this->context->nav,['hospitallist','hospital'])) : ?>class="active"<?php endif; ?> >找医院</a>
      </div>
      <div class="pr fr searcmsys_wrap">
        <input type="text" placeholder="搜索内容" value="" id="searcmsys_input" class="searchbox">
        <a target="_blank" class="pa searcmsys_bt">
          <img src="<?=Url::getStaticUrl("images/6a0ba431dac4973eae7038bd374c00ff.png");?>" width="18" height="18">
        </a>
      </div>
  </div>
</div>


<?= $content ?>

<div class="footer">
    <div class="w1200">

            <p>本站内容仅供参考，不作为诊断及医疗依据</p>
            <p>
                <a href="https://www.nisiya.net/about.html" class="alink">关于我们</a>
                &nbsp;&nbsp;&nbsp;ICP备案号：<a href="https://beian.miit.gov.cn/" target="_blank" class="alink" rel="nofollow">京ICP备17074892号</a>
            </p>
            <p>互联网药品信息服务资格证书：(京)-非经营性-2018-0055</p>
            <p>Copyright © 2015-<?php echo date("Y") ?> 王氏集团股份有限公司版权所有</p>

    </div>
</div>

<?php $this->endBody() ?>

<?php if (isset($this->blocks['endbody'])) : ?>
    <?= $this->blocks['endbody'] ?>
<?php endif; ?>
</body>
</html>
<?php $this->endPage() ?>
