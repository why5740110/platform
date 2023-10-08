# baseapisdk

基础数据接口composer SDK

### 简介
1. 第一步注入config配置信息
2. 第二步调用具体SDK内方法，如：$districtSdk = new DistrictSdk(); $result = $districtSdk->province();

### 调用类 Test.php
```php
<?php
namespace nisiya\baseapisdk;

use nisiya\baseapisdk\baseapi\DistrictSdk;
use nisiya\baseapisdk\Config;
class Test
{
    /**
     * 配置app参数
     * @param $config
     */
    public function __construct($config)
    {
        Config::setConfig($config);
    }

    /**
     * 以调用地区数据为例：获取省列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function province()
    {   
        return (new DistrictSdk())->province();
    }

    /**
     * 根据省份ID获取城市列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function city()
    {
        $province_id = 5;
        return (new DistrictSdk())->city($province_id);
    }

    /**
     * 根据城市ID获取地区列表
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @return bool|mixed|string
     */
    public function district()
    {
        $city_id = 132;
        return (new DistrictSdk())->district($city_id);
    }
}
?>
```
### 使用方法
```php
// baseapi 配置信息，由基础服务后端开发人员提供
$config = [
    'app_id' => '1000000001',
    'app_key' => 'rW@vM2UlXKGe2V%!7@%x5mjclBGT0HGc',
    // 请求域名（dev, test, prod）
    'domain' => 'http://0.0.0.0:9501',
];
// 配置信息注入进Config类
$test = new Test($config);
// 获取省份列表
$province = $test->province();
// 根据省份ID获取城市列表
$city = $test->city();
// 根据城市ID获取地区列表
$district = $test->district();

print_r($province);
```

### 返回信息
```json
{
  "code": 200,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "name": "北京",
      "pinyin": "beijing",
      "suffix": "市",
      "area_code": "010"
    },
    {
      "id": 2,
      "name": "天津",
      "pinyin": "tianjin",
      "suffix": "市",
      "area_code": "022"
    },
    {
      "id": 3,
      "name": "上海",
      "pinyin": "shanghai",
      "suffix": "市",
      "area_code": "021"
    },
    {
      "id": 4,
      "name": "重庆",
      "pinyin": "zhongqing",
      "suffix": "市",
      "area_code": "023"
    },
    "...",
    "..."
  ]
}
```

### 备注说明
1. 返回参数无加密，无需解密
2. 上述Test.php可以理解为某个业务中的调用者，实际真实调用为 (new XxxSdk())->action();





