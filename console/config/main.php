<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'timeZone'=>'Asia/Shanghai',
    'bootstrap' => ['log','queue','logqueue','logqueue2','slowqueue','guahaopush','guahaopush2','ghcoopatient','delschedule','delschedule2',
        'addvisitscheduleplan','addclosescheduleplan','delscheduleplan','delschedulecloseplan'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'params' => $params,
];
