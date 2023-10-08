<?php

/**
 * 王氏统计代码 埋点
 * @file MiaoStatisticsWidget.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @date    2022-05-19
 */

namespace common\widgets;

use yii\base\Widget;
use yii\web\View;

class MiaoStatisticsWidget extends Widget
{
    public $register_event;
    public $domain;

    public function init(){
        $this->domain = \Yii::$app->params['point_url'];
    }

    /**
     * 王氏统计代码最新版
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2022-05-19
     * @return  [type]     [description]
     */
    public function run()
    {
        $code = 'var _maq = _maq || {};';
        $code .= '_maq =' . $this->register_event . ';';
        $code .= <<<content
        (function() {
            var ma = document.createElement('script');
            ma.type = 'text/javascript';
                ma.async = true;
                ma.src = "$this->domain"+Math.random()
            var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ma, s);
        })();
content;
        $this->view->registerJs($code, View::POS_END);
    }
}
