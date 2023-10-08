###王氏医生.商城sdk

`商城相关的sdk`


composer.json
```json
{
    "config":{
        "secure-http":false
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://test.composer.nisiya.top"
        }
    ],
    "require": {
        "nisiya/mallsdk":"*"
    }
}


```

composer命令
 ```
 composer install nisiya/mallsdk
```


用法
```php
<?php

/**
 *
 * @file index.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-02-24
 */
include "vendor/autoload.php";
$config = [
    //appid
    'appid' => 'xxxxxxxxxxxxxxxxxxx', 
    //
    'appkey' => 'UZMGK%xxxxxxxxxxxxxxxx*5VYmQXwoo4fugm', 
    //路由映射地址
    'urlmapping' => [
        'other' => 'http://apis.nisiya.local/other',
        'order' => 'http://apis.nisiya.local/order',
        'user' => 'http://apis.nisiya.local/user',
        'message' => 'http://apis.nisiya.local/message',
        'product' => 'http://apis.nisiya.local/product',
    ],
    'os' => 'MallServer',
    'version' => '1.0.0',
];
//初始化配置文件，建议放在初始化方法中，初始化一次即可
\nisiya\mallsdk\Config::setConfig($config);
$productSdk=new \nisiya\mallsdk\product\ProductSdk();
$data=$productSdk->productlist(['store_id'=>'17']);

```