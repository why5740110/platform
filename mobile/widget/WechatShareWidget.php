<?php
/**
 *
 * @file WechatShareWidget.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-03-12
 */

namespace mobile\widget;



use common\models\WechatSdkModel;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

class WechatShareWidget extends Widget
{
    public $link;
    public $title;
    public $imgUrl;
    public $description;
    public $wxtitle;
    public $wxtimeline;
    public $controller;
    public function run()
    {
        //如果不是微信浏览器
        if(\Yii::$app->controller->getUserAgent()!='wechat'){
            return '';
        }
        $this->title = $this->title ? Html::encode($this->title) : '';
        $this->wxtitle = $this->wxtitle ? Html::encode($this->wxtitle) : '';
        $this->description = $this->description ? Html::encode($this->description) : '';
        $wechatSdk=new WechatSdkModel();
        if (isset($this->wxtitle) && !empty($this->wxtitle) && \Yii::$app->controller->getUserAgent() == 'wechat') {
            $this->wxtimeline = $this->wxtitle;
        } else {
            $this->wxtimeline = $this->title;
        }

        if (isset(\Yii::$app->params['domains']['mobile']) && isset(\Yii::$app->request->url)) {
            $url = rtrim(\Yii::$app->params['domains']['mobile'], '/') . \Yii::$app->request->url;
        } else {
            $url = \Yii::$app->request->absoluteUrl;
        }

        $data= $wechatSdk->share($url);
        if (!$data) {
            return '';
        }
        $this->view->registerJsFile("https://res.wx.qq.com/open/js/jweixin-1.3.2.js",['position'=>\yii\web\View::POS_HEAD]);
        $time = ArrayHelper::getValue($data,'timestamp','1');
        $return=<<<content
$(function(){
//判断是微信，但不是小程序，异步加载微信分享js代码
if(navigator.userAgent.match(/(MicroMessenger)/i) && window.__wxjs_environment !== 'miniprogram'){
wx.config({
    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
    appId: '{$data['appid']}', // 必填，公众号的唯一标识
    timestamp:{$time} , // 必填，生成签名的时间戳
    nonceStr: '{$data['noncestr']}', // 必填，生成签名的随机串
    signature: '{$data['signature']}',// 必填，签名
    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage'] // 必填，需要使用的JS接口列表
});

wx.ready(function(){
    wx.onMenuShareTimeline({
    title: '{$this->wxtimeline}', // 分享标题
    link: '{$this->link}', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
    imgUrl: '{$this->imgUrl}', // 分享图标
    success: function () {
// 用户确认分享后执行的回调函数
},
cancel: function () {
// 用户取消分享后执行的回调函数
}
});

wx.onMenuShareAppMessage({
title: '{$this->title}', // 分享标题
desc: '{$this->description}', // 分享描述
link: '{$this->link}', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
imgUrl: '{$this->imgUrl}', // 分享图标
type: '', // 分享类型,music、video或link，不填默认为link
dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
success: function () {
// 用户确认分享后执行的回调函数
},
cancel: function () {
// 用户取消分享后执行的回调函数
}
});

})

}
})
content;
        $this->view->registerJs($return,View::POS_END);
    }

}