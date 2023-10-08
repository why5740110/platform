<?php

$config = [
    'id' => 'hospital-backend',
    'name'=>'医院管理后台',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5SyUJLmpP_uDWB0x5DXQf4qucqeVRo3A',
            'csrfParam' => '_csrf-backend'
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'cookieParams' => [
                'domain' => '.nisiya.top','lifetime' => 0
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'tips/error500',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

    ],

];

if (!YII_ENV_PROD) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1','192.168.1.50'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1','192.168.1.50'],
    ];
}

return $config;
