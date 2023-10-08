<?php

namespace pc\assets;

use yii\web\AssetBundle;

/**
 * Main pc application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
//        'css/global.css'
    ];

    public $js = [
        //'js/jquery-1.11.1.min.js'
    ];

    public $depends = [
    ];


    public function init()
    {
        $this->baseUrl = \Yii::getAlias('@static');
        parent::init();
    }
}
