<?php
/**
 * 公共处理pc端，m端相互指向meta标签，只适用于频道首页，详情页面
 */

//注册meta标签
$uri = \Yii::$app->request->getUrl();
$host = \Yii::$app->request->getHostInfo();
if(preg_match('/(http[s]?):\/\/(\w+)\.m\.\w+\.\w+/', $host, $match) > 0){
    $url = $match[1].'://'.$match[2].'.www.nisiya.net'.$uri;
}else{
    $url = 'https://www.nisiya.net'.$uri;
}

$this->registerLinkTag([
    'rel' => 'canonical',
    'href' => $url,
]);