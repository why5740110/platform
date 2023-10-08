<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'layui/css/layui.css',
    ];
    public $js = [
        'layui/layui.js',
        'js/jquery.js',
        'js/jquery.form.js',
        'js/common.js',
        'wangEditor/wangEditor.min.js',
        'js/xlsx.full.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    // js加载到页面头部
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
}

