<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'hospitalapi',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'site/index',
    'controllerNamespace' => 'api\controllers',
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'components' => [
        'request' => [
//            'csrfParam' => '_csrf-api',
            'enableCsrfValidation' => false,//关闭令牌验证
        ],
        'errorHandler' => [
            'errorAction' => 'error/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/hospital/hospital_<hospital_id:\w+>.html' => 'hospital/index', //医院首页
                '/hospital/guahao_<hospital_id:\w+>.html' => 'guahao/keshilist', //挂号科室选择
                '/hospital/doctor_<doctor_id:\w+>.html' => 'doctor/home', //医生概览
                '/baidu/doctor/info.htm' => 'baidu/doctor-info',  //百度医生详情
                '/baidu/doctor/doctorIdList.htm' => 'baidu/doctor-id-list', //百度医生列表
                '/baidu/order/appeal.htm' => 'baidu/appeal', //百度发起申诉接口
                '/baidu/order/cancel.htm' => 'baidu/order-cancel',  //百度取消订单
                '/baidu/dutySource/list.htm' => 'baidu/duty-source-list',  //百度号源列表
                '/baidu/dutySource/lock.htm' => 'baidu/duty-source-lock',  //百度创建订单接口
                '/baidu/order/status.htm' => 'baidu/order-status',  //百度同步订单状态
                '/spi/reg/cancelRegOrderForHisNotice' => 'shaanxi/index', //陕西回调
                '/spi/reg/stopedForHisNotice' => 'shaanxi/index', //陕西回调
                '/register/syncAppointInfo' => 'shanxi/index',   //山西回调
                '/jiankangzhilu.htm' => 'jiankangzhilu/index', //健康之路回调

                '/ali/hospital/hospitalList.htm' => 'ali/hospital-list', //阿里医院列表
                '/ali/department/departmentList.htm' => 'ali/department-list', //阿里医院列表
                '/ali/doctor/doctorList.htm' => 'ali/doctor-list', //阿里医生列表
                '/ali/schedule/scheduleList.htm' => 'ali/schedule-list', //阿里排班列表

                '/guahaoapi/schedule-change.html' => 'guahaoapi/schedule-change', //号源回调
                '/guahaoapi/order-change.html' => 'guahaoapi/order-change', //订单回调

                '/start' => 'detect/detect', //监控url

                '/kedaxunfei/guahao/orderlist' => 'guahao/keda-order-list', //(科大讯飞)挂号订单列表查询接口
            ],
        ],

    ],
    'params' => $params,
];
