<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'agencyapiAdmin',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'site/index',
    'controllerNamespace' => 'agencyapi\controllers',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'components' => [
        'errorHandler' => [
            'errorAction' => 'error/error',
        ],
        'user' => [
            'identityClass' => \common\models\minying\account\AccountIdentity::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'request' => [
            'enableCsrfValidation' => false,//关闭令牌验证
        ],
        'response' => [
            'class' => 'yii\web\Response',
            // 'as beforeSend' => \agencyapi\behaviors\BeforeSend::class,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],

    ],
    'params' => $params,
];
