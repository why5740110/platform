<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-mobile',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'mobile\controllers',
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'bootstrap' => ['log'],
    'modules' => [],
    'defaultRoute' => 'index',//默认路由
    'components' => [

        'request' => [
            'csrfParam' => '_csrf-mobile',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the mobile
            'name' => 'advanced-mobile',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/hospital/pihs.html'=>'pihs/pihs',
                '/hospital.html' => 'index/index', //首页
                '/hospital/indexs.html' => 'index/indexs', //首页备份

                '/hospital/search/index' => 'search/index', //搜索结果

                '/hospital/doctor_<doctor_id:\w+>.html' => 'doctor/home', //医生概览
                '/hospital/doctor/ajax-refresh' => 'doctor/ajax-refresh', //医生概览
                '/hospital/doctor_<doctor_id:\w+>/intro.html' => 'doctor/intro', //医生概览

                '/hospital/doctorlist/<region:\w+>_<sanjia:\d+>_<keshi_id:\d+>_<page:\d+>.html' => 'doctorlist/index', //地区找医生列表
                '/hospital/doctorlist/<region:\w+>.html' => 'doctorlist/index', //地区找医生列表

                '/hospital/index/ajaxDoctor' => 'index/ajax-doctor', //异步获取医生


                '/hospital/doctorlist/0_0_0_<page:\d+>.html' => 'doctorlist/index', //地区找医生列表
                '/hospital/doctorlist/ajaxget-more' => 'doctorlist/ajaxget-more',
                '/hospital/doctorlist/ajax-get-keshi' => 'doctorlist/ajax-get-keshi',
                '/hospital/doctorlist.html' => 'doctorlist/index', //医生列表首页

                '/hospital/doctorlist/departments/<region:\w+>_<sanjia:\d+>_<keshi_id:\d+>_<page:\d+>.html' => 'doctorlist/department', //医生科室首页
                '/hospital/doctorlist/departments/<region:\w+>_0_<keshi_id:\d+>_1.html' => 'doctorlist/department', //医生科室首页
                '/hospital/doctorlist/departments/<region:\w+>.html' => 'doctorlist/department', //医生科室首页
                '/hospital/doctorlist/departments/0_0_<keshi_id:\d+>_1.html' => 'doctorlist/department', //医生科室首页
                '/hospital/doctorlist/departments.html' => 'doctorlist/department', //医生科室首页



                '/hospital/doctorlist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<dspinyin:\w+>_<page:\d+>.html' => 'doctorlist/diseases', //医生疾病首页
                '/hospital/doctorlist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<page:\d+>.html' => 'doctorlist/diseases', //医生疾病首页
                '/hospital/doctorlist/diseases/<region:\w+>_0_0_1.html' => 'doctorlist/diseases', //医生疾病首页
                '/hospital/doctorlist/diseases.html' => 'doctorlist/diseases', //医生疾病首页

                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_<second_department_id:\d+>_<page:\d+>.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_0_0_<page:\d+>.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_<second_department_id:\d+>_0.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department_<frist_department_id:\d+>_0_0.html' => 'hospital/doclist', //医院下医生列表 科室
                '/hospital/hospital_<hospital_id:\w+>/department.html' => 'hospital/doclist', //医院下医生列表


                '/hospital/hospital_<hospital_id:\w+>.html' => 'hospital/index', //医院首页
                '/hospital/hospital_<hospital_id:\w+>/index.html' => 'hospital/detail', //医院介绍


                '/hospital/hospitallist/<region:\w+>_<sanjia:\d+>_<hostype:\d+>_<page:\d+>.html' => 'hospitallist/index', //地区找医院列表
                '/hospital/hospitallist/<region:\w+>_0_0_1.html' => 'hospitallist/index', //地区找医院列表
                '/hospital/hospitallist/0_0_0_<page:\d+>.html' => 'hospitallist/index', //地区找医院列表
                '/hospital/hospitallist/ajaxlist' => 'hospitallist/ajaxlist', //医院列表ajax加载更多
                '/hospital/hospitallist/ajaxregion' => 'hospitallist/ajaxregion', //医院列表ajax获取一级地区的二级地区
                '/hospital/hospitallist/ajaxkeshi' => 'hospitallist/ajaxkeshi', //医院列表ajax获取一级科室的二级科室
                '/hospital/hospitallist/ajaxgetdisease' => 'hospitallist/ajaxgetdisease', //医院列表ajax获取首字母对应的疾病
                '/hospital/hospitallist.html' => 'hospitallist/index', //医院列表首页

                '/hospital/hospitallist/departments/<region:\w+>_<sanjia:\d+>_<keshi_id:\d+>_<hostype:\d+>_<page:\d+>.html' => 'hospitallist/department', //科室找医院列表
                '/hospital/hospitallist/departments.html' => 'hospitallist/department', //科室找医院列表

                '/hospital/departments.html' => 'hospitallist/department-list', //科室列表

                '/hospital/hospitallist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<dspinyin:\w+>_<page:\d+>.html' => 'hospitallist/diseases', //疾病找医院列表
                '/hospital/hospitallist/diseases/<region:\w+>_<sanjia:\d+>_<diseases:\w+>_<page:\d+>.html' => 'hospitallist/diseases', //疾病找医院列表
                '/hospital/hospitallist/diseases.html' => 'hospitallist/diseases', //疾病找医院列表

                ##搜索相关

                '/hospital/search/<type:hospital>/<keyword:.*>' => 'search/so',
                '/hospital/search/<type:doctor>/<keyword:.*>' => 'search/so',
                '/hospital/search/<type:disease>/<keyword:.*>' => 'search/so',
                '/hospital/search.html' => 'search/so',
                '/hospital/search/<keyword:.*>' => 'search/show',
                '/hospital/search/<type:hospital>' => 'search/so',
                '/hospital/search/<type:doctor>' => 'search/so',
                '/hospital/search/<type:disease>' => 'search/so',
                '/hospital/city.html' => 'index/select-city', //选择地区
                '/hospital/latlon.html' => 'index/ajax-lat-lon', //选择地区

                ##预约挂号相关
                '/hospital/register/choose-patient.html' => 'register/choose-patient',
                '/hospital/register/register-info-add.html' => 'register/register-info-add',
                '/hospital/register/ajax-patient-info-up' => 'register/ajax-patient-info-up',
                '/hospital/register/register-confirm.html' => 'register/register-confirm',
                '/hospital/register/submit-register-confirm' => 'register/submit-register-confirm',//提交需确认挂号
                '/hospital/register/register-detail.html' => 'register/register-detail',//挂号详情
                '/hospital/register/regiter-cancel' => 'register/regiter-cancel',//取消挂号
                '/hospital/guahao_<hospital_id:\w+>.html' => 'guahao/keshilist', //挂号科室选择
                '/hospital/guahao_<hospital_id:\w+>-<tp_department_id:\w+>.html' => 'guahao/doclist', //挂号科室选择
                '/hospital/guahao/ajax-get-doctor'=>'guahao/ajax-get-doctor',//异步获取排班
                '/hospital/medicalclicknum'=>'doctor/medical-click',//医院/医生主页对应的就医信息推荐点击统计
                '/hospital/register/show-ordermsg.html'=>'register/show-ordermsg',//支付成功展示
                '/hospital/my/guahaolist'=>'order/list',//我的预约挂号列表

                ##阿里相关
                '/hospital/ali/doctor-info.html' => 'ali/doctor-info',  //阿里跳转医生主页
                '/hospital/ali/order-detail.html' => 'ali/order-detail',  //阿里跳转订单详情页
            ],
        ],
    ],
    'params' => $params,
];
