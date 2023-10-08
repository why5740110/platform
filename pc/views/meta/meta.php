<?php

/**
 * 公共处理pc端，m端相互指向meta标签，只适用于频道首页，详情页面
 */

//注册meta标签
$uri = \Yii::$app->request->getUrl();
$host = \Yii::$app->request->getHostInfo();

if (preg_match('/(http[s]?):\/\/(\w+)\.www\.\w+\.\w+/', $host, $match) > 0) {
    $url = $match[1] . '://' . $match[2] . '.m.nisiya.net' . $uri;
    //$url = $match[1] . '://' . $match[2] . '.mnisiya.top' . $uri;
} else {
    $url = 'https://m.nisiya.net' . $uri;
    //$url = 'http://mnisiya.top' . $uri;
}

$this->registerMetaTag(['name' => 'mobile-agent', 'content' => "format=html5; url={$url}"]);
$this->registerMetaTag(['name' => 'mobile-agent', 'content' => "format=xhtml; url={$url}"]);
$this->registerMetaTag(['name' => 'mobile-agent', 'content' => "format=wml; url={$url}"]);
$this->registerLinkTag([
    'rel' => 'alternate',
    'type' => 'application/vnd.wap.xhtml+xml',
    'media' => 'handheld',
    'href' => $url,
]);