<?php
return [
    'usersdk' =>[
        'appid' => '10000200002',
        'appkey' => '1k0h&8W61WVGfp9^Tuv5DyIXm9IZz48qFEFE#',
        //路由映射地址
        'urlmapping' => [
            'ucenter' => 'http://test.api.ucenter.nisiya.top/v2',
            'doctor' =>  'http://test.api-doctor.nisiya.top/v2',
        ],
        'os' => 'nisiya', //自行申请
        'version' => '11101', //写死
    ],
    'adminUserCookieKey' => 'HospitalBaseAdminUser',
    'BaseAdminDomain' => 'http://test.base.nisiya.top',
    'AdminDomain' => 'http://test.admin.hospital.nisiya.top',

    'hospital_upload_dir' => "/data/upload/doctors/hospital",//医院上传图片目录
    'hospital_imgcdn_url_prefix' => 'http://test.img.nisiyacdn.com/doctors/hospital',//医院图片CDN URL
    'min_doctor_img_oss_url_prefix' => 'http://test.file.nisiyacdn.com/nisiya/guahao/min_doctor', // 民营医院医生oss上图片URL域名
    'min_hospital_img_oss_url_prefix' => 'http://test.file.nisiyacdn.com/nisiya/guahao/min_hospital', //民营医院oss上图片URL域名
    //相关sdkAPI地址
    'api_url' => [
        'ucenter' => 'http://test.api-doctor.nisiya.top/',
        'ucenterapi' => 'http://test.api.ucenter.nisiya.top/',
        'askapi' => 'http://test.askapi.nisiya.net',
        'bapi' => 'http://test.bapi.nisiya.net',
//        'sapi' => 'http://test.s.nisiya.net',
        'sapi' => 'http://test-dp-hospital-service.nisiya.top',
        'pihsapi'=>'http://test.pihs.nisiya.top/',
        'news' => 'http://test.newsapi.nisiya.top',
        'self' => 'http://test.hospitalapi.nisiya.net',
        'complain' => 'http://test.complainapi.nisiya.net',
        'ServiceApiUrl' => 'http://test.service.nisiya.top',
        //'baidugh' => 'https://expert.baidu.com/test/guahao',
        'baidugh' => 'http://test.s.nisiya.net/test/guahao',
        'base' => 'http://test.api.base.nisiya.top',
        // 新base后台配置
        'base_new' => [
            'system_api' => 'http://test-admin-base-api.nisiya.top/api',//权限中心domain
            'login' => 'http://test-admin-base.nisiya.top/#/login',
            'logout' => 'http://test-admin-base.nisiya.top/',
            'usercenter' => 'http://test-admin-base.nisiya.top/#/usercenter',
            'login_old' => 'http://test.base.nisiya.top/site/login',
            'cookie_login_key' => 'test_base_uc_login_key', //cookie 名称
            'role_keyword' => 'hospital',//权限中心分配的key
        ],
        'docapi'=>'http://test.dpsapi.nisiya.top/',
    ],
    'hnguahao' => [
        'spid' => 'H202009N2411158MiaoS167',
        'url' => 'http://115.29.175.63:8078/GuahaoService/services/GuaHaoService',
    ],
    //首页缓存时间
    'homeCacheTime' => 3600,
    //需要redis缓存的页面地址
    'cacheDomain' => [
        'pchome' => 'http://test.www.nisiya.net/hospital.html',
    ],
    //域名配置
    'domains' => [
        //'mobile' => 'http://test.m.nisiya.net/',
        'mobile' => 'http://test.mnisiya.top/',
        'pc' => 'http://test.www.nisiya.net/',
        'cdn' => 'http://test.branddoctor.nisiyacdn.com/',
        'news' => 'http://test.www.nisiya.top/news/',
        'ihs' => 'http://test.ihs.nisiya.top/',
        'ucenter' => 'http://test.ucenter.nisiya.top/',
        'mnews' => 'http://test.m.nisiya.top/news/',
        'm_com' => 'http://test.m.nisiya.top/',
    ],

    //微信平台接口配置
    'wechat' => [
        'wechatKey' => 'c63b344b',
        //地址
        'wechatApiUrl' => 'http://api.wc.nisiya.top',
    ],
    'loginurl' => 'http://test.ucenter.nisiya.top/index/login?goBack=http://test.www.nisiya.net/doc/login.html',
    'photoUrl' => 'http://test.u.nisiyacdn.com/doctor_avatar/',   //头像域名
    'avatarUrl' => 'http://test.file.nisiyacdn.com/nisiya/guahao/doctor_avatar/',   // 医生 头像域名
    //'hospitalUrl'=>'http://test.m.nisiya.net/hospital.html',
    'hospitalUrl'=>'http://test.mnisiya.top/hospital.html',
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
        'secret' => 'kbdsSN3iZqzss',
    ],
    //商城支付中心
    'paysdk' =>[
        'appid' => '60',
        'appkey' => '0HU+S8wmLUDX=oVniNoWXNWH50qSb7yC',
        'urlmapping' => [
            'open' =>'http://test.payopen.nisiya.top',
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
        //'secret' => 'P7032O6S47O5726IS8J40LROU1O2CS3O32XT903VH8K',  //测试
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
        'domain' => 'http://test-api-base.nisiya.top',
    ],

    // ---------------对外输出配置------- start--------
    'ali_healthy' => [
        'key' => '33499052',
        'encryptKey' => 'd4f6e8729d3a74dd42f73e8272c53d0f',
        'aliUrl' => 'http://140.205.164.4/top/router/rest',
        'crypt_key' => 'N235GRKQLKQVJFUBRU1I3EXAQYGGNRHT',
    ],

    // ---------------对外输出配置------- end--------

    //埋点配置
    'point_url' => 'https://tsdk.nisiya.top/miao.js?',
    //水印js
    'watermark' => 'https://test-www.nisiyacdn.com/watermark/watermark.js',
    // ---------------科大讯飞对外输出配置------- start--------
    'keda_guahao' => [
        'apiUrl' => 'http://healthcaretest.ihou.com:8087',
        "grantType" => "client_credentials",
        "clientId" => "yuanxin",
        "clientSecret" => "E6u1waPxBn5xYuleEjggJg=="
    ],
    // ---------------科大讯飞对外输出配置------- end--------
];
