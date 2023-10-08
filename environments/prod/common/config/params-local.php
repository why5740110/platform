<?php
return [
    'usersdk' =>[
        'appid' => '10000200002',
        'appkey' => '1k0h&8W61WVGfp9^Tuv5DyIXm9IZz48qFEFE#',
        //路由映射地址
        'urlmapping' => [
            'ucenter' => 'http://api.ucenter.nisiya.top/v2',
            'doctor' =>  'http://api-doctor.nisiya.top/v2',
        ],
        'os' => 'nisiya', //自行申请
        'version' => '11101', //写死
    ],
    'adminUserCookieKey' => 'HospitalBaseAdminUser',
    'BaseAdminDomain' => 'http://base.nisiya.top',
    'AdminDomain' => 'http://admin.hospital.nisiya.top',

    'hospital_upload_dir' => "/data/upload/doctors/hospital",//医院上传图片目录
    'hospital_imgcdn_url_prefix' => 'https://img.nisiyacdn.com/doctors/hospital',//医院图片CDN URL
    'min_doctor_img_oss_url_prefix' => 'https://file.nisiyacdn.com/nisiya/guahao/min_doctor', // 民营医院医生oss上图片URL域名
    'min_hospital_img_oss_url_prefix' => 'https://file.nisiyacdn.com/nisiya/guahao/min_hospital', //民营医院oss上图片URL域名
    //相关sdkAPI地址
    'api_url' => [
        'ucenter' => 'http://api-doctor.nisiya.top/',
        'ucenterapi' => 'http://api.ucenter.nisiya.top/',
        'askapi' => 'https://askapi.nisiya.net',
        'bapi' => 'https://bapi.nisiya.net',
//        'sapi' => 'http://s.nisiya.net',
        'sapi' => 'http://dp-hospital-service.nisiya.top', // k8s 上 s.nisiya.net 联调域名
        'pihsapi'=>'https://pihs.nisiya.top/',
        'news' => 'https://newsapi.nisiya.top',
        'self' => 'https://hospitalapi.nisiya.net',
        'complain' => 'https://complainapi.nisiya.net',
        'ServiceApiUrl' => 'http://service.nisiya.top',
        //'baidugh' => 'https://expert.baidu.com',
        'baidugh' => 'https://expert.baidu.com/guahao',
        'base' => 'http://api.base.nisiya.top',
        // 新base后台配置
        'base_new' => [
            'system_api' => 'http://admin-base-api.nisiya.top/api',//权限中心domain
            'login' => 'http://admin-base.nisiya.top/#/login',
            'logout' => 'http://admin-base.nisiya.top/',
            'usercenter' => 'http://admin-base.nisiya.top/#/usercenter',
            'login_old' => 'http://base.nisiya.top/site/login',
            'cookie_login_key' => 'prod_base_uc_login_key', //cookie 名称
            'role_keyword' => 'hospital',//权限中心分配的key
        ],
        'docapi'=>'https://dpsapi.nisiya.top/',
    ],
    'hnguahao' => [
        'spid' => 'H202009N2411158MiaoS167',
        'url' => 'http://115.29.175.63:8078/GuahaoService/services/GuaHaoService',
    ],
    //首页缓存时间
    'homeCacheTime' => 3600,
    //需要redis缓存的页面地址
    'cacheDomain' => [
        'pchome' => 'http://www.nisiya.net/hospital.html',
    ],
    //域名配置
    'domains' => [
        'mobile' => 'https://m.nisiya.net/',
        //'mobile' => 'http://mnisiya.top/',
        'pc' => 'https://www.nisiya.net/',
        'cdn' => 'https://branddoctor.nisiyacdn.com/',
        'news' => 'http://www.nisiya.top/news/',
        'ihs' => 'https://ihs.nisiya.top/',
        'ucenter' => 'https://ucenter.nisiya.top/',
        'mnews' => 'https://m.nisiya.top/news/',
        'm_com' => 'https://m.nisiya.top/',
    ],

     //微信平台接口配置
    'wechat' => [
        'wechatKey' => 'c63b344b',
        //地址
        'wechatApiUrl' => 'http://api.wc.nisiya.top',
    ],
    'loginurl' => 'https://ucenter.nisiya.top/index/login?goBack=https://www.nisiya.net/doc/login.html',
    'photoUrl' => 'https://u.nisiyacdn.com/doctor_avatar/',   //头像域名
    'avatarUrl' => 'https://file.nisiyacdn.com/nisiya/guahao/doctor_avatar/',   // 医生 头像域名
    'hospitalUrl'=>'https://m.nisiya.net/hospital.html',
    //'hospitalUrl'=>'http://mnisiya.top/hospital.html',
    'cache_key'=>[
        'detail' => 'hospital:detail:%s',  //详情缓存前缀
        'diseases_list_by_fkeshi_skeshi_initial' => 'hospital:diseases_list_by_fkeshi_skeshi_initial:%s:%s:%s',  //疾病缓存：一级科室id:二级科室id:首字母
        'keshi_info' => 'hospital:keshi_info:%s',  //科室详情
        'hospital_doctor_info' => "hospital:doctor_info:%s", //医院医生缓存
        'hospital_all_skeshi_list' => "hospital:all_skeshi_slit:%s", //所有二级科室缓存
        'doctor_register_num' => "hospital:doctor_register_num:%s", //医生挂号服务数量
        'miaoid_hospital_doctor_id' => "hospital:miaoid_guahao_doctor_id:%s", //王氏id对应的挂号医生id
        'tp_hospital_info' => 'hospital:tp_info:%s:%s',  //第三方医生详情缓存前缀 $tp_platform,$tp_hospital_code
        'coo_list' => 'hospital:coo_list',  //合作平台列表
        'platform_list' => 'hospital:platform_list',  //第三方平台列表
        'department_config_list' => 'hospital:department_config_list',  //科室权重配置
        'departmentsdk_all' => 'departmentsdk_all',//所有科室缓存
    ],

    //好大夫挂号相关
    'gh_haodaifu' => [
        'partnerKey' => 'e05a86cce799806c',
        'secret' => '030X7YmUOg',
    ],
    //商城支付中心
    'paysdk' =>[
        'appid' => '100019',
        'appkey' => 'z60ZIQdq60Yyvts6QqVrErHpsN6g=2hQ',
        'urlmapping' => [
            'open' =>'https://payopen.nisiya.top',
        ],
        'os' => 'gh',
        'version' => '1.1'
    ],
    //健康160相关
    'gh_jiankang160' => [
        'cid' => '100013399',
        'token' => 'bca82e41ee7b0833588399b1fcd177c7',
    ],
    //陕西挂号相关
    'gh_shaanxi' => [
        'username' => 'SXSJKT_MSYS',
        'key' => '6A070E1F7D8E8A3B',
        'url' => 'http://tps.witdoctor.cn',
        'imgdomain' => 'http://tpwz.sx.witdoctor.cn:8089'
    ],
    //健康之路
    'gh_jiankangzhilu' => [
        'key' => '9001103',
        'secret' => 'P04W0G623156L4FAZR29T18R634Q9929317D5E859F0',  //线上
    ],
    //山西挂号相关
    'gh_shanxi' => [
        'secret' => '9ts14ptbbjzmg1tegl9igb9l0ydhspxw',
        'key' => '200000001',
        'url' => 'https://preapi.sxyygh.com/',
    ],

    //基础中心sdk
    'baseapisdk' =>[
        'app_id' => '1000000003',
        'app_key' => '#pfwFzmVWPA&@av9F%5S2hVj!s*3UvbY',
        'domain' => 'https://api-base.nisiya.top',
    ],

    // ---------------对外输出配置------- start--------
    'ali_healthy' => [
        'key' => '33499052',
        'encryptKey' => 'd4f6e8729d3a74dd42f73e8272c53d0f',
        'aliUrl' => 'https://eco.taobao.com/router/rest',
        'crypt_key' => 'N235GRKQLKQVJFUBRU1I3EXAQYGGNRHT',
    ],

    // ---------------对外输出配置------- end--------

    //埋点配置
    'point_url' => 'https://sdk.nisiya.top/miao.js?',
    //水印js
    'watermark' => 'https://www.nisiyacdn.com/watermark/watermark.js',
    // ---------------科大讯飞对外输出配置------- start--------
    'keda_guahao' => [
        'apiUrl' => 'https://iptv-public.ihou.com',
        "grantType" => "client_credentials",
        "clientId" => "yuanxin",
        "clientSecret" => "E6u1waPxBn5xYuleEjggJg=="
    ],
    // ---------------科大讯飞对外输出配置------- end--------
];
