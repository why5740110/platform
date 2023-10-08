<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=nisiya_top', // 备注修改
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4',
        ],
        /*'log_branddoctor_db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=172.16.20.40;port=3306;dbname=log_branddoctor_db',
            'username' => 'test_log_branddoctor_db_rw',
            'password' => 'gnHmpNNIbZMoVM0RjCdl',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'log_',
        ],*/
        'elasticsearch' => [
            'class' => 'Elasticsearch\ClientBuilder',
            'nodes' => [
                [
                    'http_address' => 'http://172.16.30.41:9200',
                ],
            ],
            'auth' => ['username' => 'elastic', 'password' => '0Fjga0XOUUwJPRff']
        ],
        //缓存redis配置
        'redis_codis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.16.30.124',
            'port' => 6379,
            'password' => 'okOwMZFanyFO4zwc',
            'database' => 0
        ],
        //队列redis配置
        'redis_queue' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.16.30.13',
            'port' => 6379,
            'password' => 'K07EaIp6gy4eYjAb',
            'database' => 0,
        ],
        //队列
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'hospital_queue', // Queue channel key
        ],
        //日志队列
        'logqueue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'hospital_log_queue', // Queue channel key
        ],
        //日志队列2 量大且不重要日志
        'logqueue2' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'hospital_log_queue2', // Queue channel key
        ],
        //慢队列
        'slowqueue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'hospital_slow_queue', // Queue channel key
        ],
        //挂号异步通知第三方
        'guahaopush' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'guahao_push_queue', // Queue channel key
        ],
        //挂号异步通知第三方 重要推送（订单变更）
        'guahaopush2' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'guahao_push_queue2', // Queue channel key
        ],
        //挂号异步存储来源患者队列
        'ghcoopatient' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'gh_coo_patient_queue', // Queue channel key
        ],
        //挂号异步删除医生排班
        'delschedule' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'gh_delete_schedule', // Queue channel key
        ],
        //挂号异步删除医生排班2
        'delschedule2' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'gh_delete_schedule2', // Queue channel key
        ],
        //民营医院出诊计划
        'addvisitscheduleplan' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'min_add_visit_schedule_plan', // Queue channel key
        ],
        //民营医院停诊计划
        'addclosescheduleplan' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'min_add_close_schedule_plan', // Queue channel key
        ],
        //民营医院删除出诊计划
        'delscheduleplan' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'min_delete_schedule_plan', // Queue channel key
        ],
        //民营医院删除停诊计划
        'delschedulecloseplan' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis', // Redis connection component or its config
            'channel' => 'min_delete_close_schedule_plan', // Queue channel key
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '/data/logs/php/nisiya.top/'.date('Y-m').'/'.date('Y-m-d').'.log',
                    'maxFileSize' => 15360, //15M
                    'maxLogFiles' => 5, //同个文件名最大数量
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['paylog'],
                    'levels' => ['info'],
                    'logVars' => ['*'],
                    'logFile' => '/data/logs/php/nisiya.top/' . date('Y') . '/paylog.log',
                    'maxFileSize' => 15360, //15M
                    'maxLogFiles' => 5, //同个文件名最大数量
                ]
            ],
        ],
    ],
];
