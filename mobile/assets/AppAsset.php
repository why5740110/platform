<?php

namespace mobile\assets;

use yii\web\AssetBundle;

/**
 * Main mobile application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [

    ];

    public function init()
    {
        $this->baseUrl = \Yii::getAlias('@static');
        parent::init();
    }
}
