<?php

    use yii\helpers\Html;

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noarchive">
        <!-- <meta name="referrer" content="never"> -->
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <style>
            /*layui关闭选项样式修改*/
            .layui-table-tips-c:before {position: relative; right: 1px; top: -3px; }
        </style>
        <script src="<?= \Yii::$app->params['watermark'] ?>"></script>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>

    <?php
        //拼接水印字符串
        $watermarkStr = (Yii::$app->controller->userInfo['staff_code'] ?? '') . ' ' . (Yii::$app->controller->userInfo['realname'] ?? '') . ' ' . (Yii::$app->controller->userInfo['username'] ?? '') . ' ' . date('Y-m-d H:i:s');
    ?>
    <script>
        yxPlugin.createWaterMark("<?= $watermarkStr ?>");
    </script>

