<?php
return [
    'appidcryptokey' => [
        '1000000001' => [ //APP医生端appid md5
            'appid' => 1000000001,
            'appkey' => 'rW@vM2UlXKGe2V%!7@%x5mjclBGT0HGc',
            'checkrules' => 'MD5',
        ],
        '1000000002' => [ //APP医生端appid aes
            'appid' => 1000000002,
            'appkey' => 'KGe2%x5mjclBrW@vM2UlXGT0HGcV%!7@',
            'checkrules' => 'AES-256-ECB',
        ],
        '2000000060' => [ //互联网医院调用
            'appid' => 2000000060,
            'appkey' => 'Tx5m0HcXGl%!7KGe@%vM2UGjclB2VrW@',
            'checkrules' => 'MD5',
        ],
        '1000000004' => [ //王氏医生小程序调用
            'appid' => 1000000004,
            'appkey' => 'jcXKGeGTx5m0H2V%!7@%lBGcrW@vM2Ul',
            'checkrules' => 'MD5',
        ],
        '1000000015' => [ //用户中心调用
            'appid' => 1000000015,
            'appkey' => '7@%mW@c!r5vM22V%lBGcGT0HlUXKGexj',
            'checkrules' => 'MD5',
        ],
        '2000000100' => [ //挂号业务测试
            'appid' => 2000000100,
            'appkey' => 'U@7V6GUXTmZaH0!ril%CHXCp4D6mlotN',
            'checkrules' => 'MD5',
        ],
        '2000000201' => [ //挂号代理商后台api
            'appid' => 2000000201,
            'appkey' => 'h0rx@@uyB08#Mjv5UN2O03HN!TyG4u*o',
            'checkrules' => 'AES-256-ECB',
        ],
        '2000000202' => [ //挂号民营医院后台api
            'appid' => 2000000202,
            'appkey' => 'v8tJ93y&voMlUYh?u_$cX0BRsRCPruA!',
            'checkrules' => 'AES-256-ECB',
        ],
        '2000000112' => [ //四川
            'appid' => 2000000112,
            'appkey' => 'qDKWGImT&ACJESuGzGBZHeERizG0@7Hf',
            'checkrules' => 'MD5',
        ],
        '3000000001' => [   //app医链
            'appid' => 3000000001,
            'appkey' => 'cjfV$!7XKGmGdo@tp2@c1Hr6%laGcllx',
            'checkrules' => 'AES-256-ECB',
        ],
        '3000000002' => [   //前端医链
            'appid' => 3000000002,
            'appkey' => 'Gexj2V%!72UlBGmW@lcXK@%GT0Hr5vMc',
            'checkrules' => 'AES-256-ECB',
        ],
        '3000000007' => [   //超级小程序
            'appid' => 3000000007,
            'appkey' => '2X0XluS3!g6153Ix%XZpO9vEK9sIBIsz',
            'checkrules' => 'MD5',
        ],
        '1000000188' => [   //科大讯飞
            'appid' => 1000000188,
            'appkey' => 'P28FqfkBBiaOvgVb',
            'checkrules' => 'MD5',
        ],
    ],

    //缓存key
    'cache_key'=>[
        'hospital_detail' => 'hospital:detail:%s',  //医院详情页缓存前缀
        'diseases_list_by_fkeshi_skeshi_initial' => 'hospital:diseases_list_by_fkeshi_skeshi_initial:%s:%s:%s',  //疾病缓存：一级科室id:二级科室id:首字母
        'keshi_info' => 'hospital:keshi_info:%s',  //科室详情
        'hospital_doctor_info' => "hospital:doctor_info:%s", //医院医生缓存
        'hospital_all_skeshi_list' => "hospital:all_skeshi_slit:%s", //所有二级科室缓存
        'doctor_register_num' => "hospital:doctor_register_num:%s", //医生挂号服务数量
        'miaoid_hospital_doctor_id' => "hospital:miaoid_guahao_doctor_id:%s", //王氏id对应的挂号医生id
    ],

    //静态资源版本号
    //'version' => '20220110',
    'version' => '20220926',

    'gaode_map' => [
        'url' => 'https://restapi.amap.com',
        'key' => 'f29058081a742a1f73d52e39e0cea5d1'
    ],

    'baiduguahao' => [
        'cipherid' => 'ghms',
        'key' => 'nGFeGUzso2zuXo7AkekbAyQeIcnNj1o6',
        'encryptKey' => 'WJkWodJt1jZxfo2ebjmDRfeSBWynfPcp',
    ]

];

