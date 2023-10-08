<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-pc',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'pc\controllers',
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'bootstrap' => ['log'],
    'modules' => [],
    'defaultRoute' => 'index', //设置默认路由
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-pc',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the pc
            'name' => 'advanced-pc',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                 // '/video/list_<f_class_id:\d+>_<s_class_id:\d+>_<order:\d+>_<page:\d+>.html' => 'video/list',
                '/hospital.html' => 'index/index', //首页
                //'/hospital/<hospital_id:\d+>.html' => 'index/index', //医院首页
                '/hospital/search/index' => 'search/index', //搜索结果
                //站内搜索
                '/hospital/search/<type:hospital>/<keyword:.*>' => 'search/so',
                '/hospital/search/<type:doctor>/<keyword:.*>' => 'search/so',
                '/hospital/search/<type:disease>/<keyword:.*>' => 'search/so',
                '/hospital/search/<keyword:.*>' => 'search/show',

                '/hospital/doctor_<doctor_id:\w+>.html' => 'doctor/home', //医生概览
                '/hospital/doctor_<doctor_id:\w+>/intro.html' => 'doctor/intro', //医生详情
                '/hospital/doctor_<doctor_id:\w+>/consult.html' => 'doctor/consult', //医生详情
                '/hospital/doctor_<doctor_id:\w+>/comment.html' => 'doctor/comment', //医生详情

                '/hospital/hospitallist/<region:\w+>_<sanjia:\d+>_<page:\d+>.html' => 'hospitallist/index', //地区找医院列表
                '/hospital/hospitallist/0_0_<page:\d+>.html' => 'hospitallist/index', //地区找医院列表
                '/hospital/hospitallist.html' => 'hospitallist/index', //医院列表首页


                '/hospital/hospitallist/departments/<region:\w+>_<sanjia:\d+>_<keshi_id:\d+>_<page:\d+>.html' => 'hospitallist/department', //科室找医院列表
                '/hospital/hospitallist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<dspinyin:\w+>_<page:\d+>.html' => 'hospitallist/diseases', //疾病找医院列表
                '/hospital/hospitallist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<page:\d+>.html' => 'hospitallist/diseases', //疾病找医院列表

                '/hospital/doctorlist/<region:\w+>_<sanjia:\d+>_<page:\d+>.html' => 'doctorlist/index', //地区找医院列表
                '/hospital/doctorlist/0_0_<page:\d+>.html' => 'doctorlist/index', //地区找医院列表
                '/hospital/doctorlist.html' => 'doctorlist/index', //医生列表首页

                '/hospital/doctorlist/departments/<region:\w+>_<sanjia:\d+>_<keshi_id:\d+>_<page:\d+>.html' => 'doctorlist/department', //医生科室首页
                '/hospital/doctorlist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<dspinyin:\w+>_<page:\d+>.html' => 'doctorlist/diseases', //医生疾病首页
                '/hospital/doctorlist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<page:\d+>.html' => 'doctorlist/diseases', //医生疾病首页

                '/hospital/hospital_<hospital_id:\w+>.html' => 'hospital/index', //医院概况
                '/hospital/hospital_<hospital_id:\w+>/departments.html' => 'hospital/departments', //医院科室

                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_<second_department_id:\d+>_<page:\d+>.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_0_0_<page:\d+>.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_<second_department_id:\d+>_0.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_0_0.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department.html' => 'hospital/doclist', //医院下医生列表

                '/hospital/hospital_<hospital_id:\w+>/index.html' => 'hospital/detail', //医院介绍


                '/hospital/hospital_<hospital_id:\w+>/diseases_<frist_department_id:\d+>_<second_department_id:\d+>_<page:\d+>.html' => 'hospital/diseases', //医院下医生疾病带分页
                '/hospital/hospital_<hospital_id:\w+>/diseases_0_0_<page:\d+>.html' => 'hospital/diseases', //医院下医生疾病带分页
                '/hospital/hospital_<hospital_id:\w+>/diseases_<frist_department_id:\d+>_<second_department_id:\d+>_0.html' => 'hospital/diseases', //医院下医生疾病
                '/hospital/hospital_<hospital_id:\w+>/diseases_<frist_department_id:\d+>_0_0.html' => 'hospital/diseases', //医院下医生疾病
                '/hospital/hospital_<hospital_id:\w+>/diseases.html' => 'hospital/diseases', //医院下医生疾病


            ],
        ],
    ],
    'params' => $params,
];
