<?php
/**
 * @file CommonFunc.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/24
 */

namespace common\libs;

use common\helpers\Url;
use common\models\BaseDoctorHospitals;
use common\models\DiseaseDepartmentModel;
use common\models\Department;
use common\models\GuahaoCooListModel;
use common\models\GuahaoOrderInfoModel;
use common\models\DoctorModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoPlatformListModel;
use common\models\GuahaoPlatformModel;
use common\models\minying\MinAccountModel;
use common\sdks\BapiAdSdkModel;
use common\sdks\baseapi\BaseapiSdk;
use common\sdks\PaySdk;
use common\sdks\ServiceSdk;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\ucenter\PihsSDK;
use nisiya\baseapisdk\baseapi\DepartmentSdk;
use nisiya\baseapisdk\baseapi\SundrySdk;
use nisiya\ucentersdk\Config;
use nisiya\ucentersdk\ucenter\LoginSdk;
use queues\DeleteScheduleJob;
use queues\DeleteDoctorScheduleJob;
use queues\GuahaoPushJob;
use queues\VisitPlaceJob;
use queues\DeparmentCacheJob;
use queues\AfterSaveDoctorJob;
use queues\UpVisitPlaceJob;
use queues\DeleteHospitalDoctorJob;
use queues\GuahaoCooInterrogationJob;
use queues\upDoctorScheduleJob;
use nisiya\paysdk\CryptoTools;
use nisiya\paysdk\open\OrderSdk;
use Yii;
use common\libs\HashUrl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Cookie;
use common\models\BuildToEsModel;
use common\models\GuahaoCooInterrogationModel;
use yii\web\UploadedFile;
use common\sdks\CenterSDK;

class CommonFunc
{

    const PAGE_SIZE = 15;
    const SHOW_DAY = 30;
    const PAY_EXP_TIME = 900; //支付超时时间
    const SHARE_LOGO = 'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png';##分享logo
    
    // 密码发送短信错误信息
    public static $passwordSendSmsErrorMsg;

    // 订单发送短信错误信息
    public static $orderSendSmsErrorMsg;

    /**
     * 医院等级
     * @var [type]
     */
    public static $level_list = [
        2 => '三级甲等',
        3 => '三级乙等',
        4 => '三级丙等',
        5 => '二级甲等',
        6 => '二级乙等',
        7 => '二级丙等',
        8 => '一级甲等',
        9 => '一级乙等',
        10 => '一级丙等'
    ];

    /**
     * 医院类型
     * @var [type]
     */
    public static $hos_type_list = [
        1=>'公立',
        2=>'社会办医'
    ];

    /**
     * 医生职称
     * @var [type]
     */
    public static $title_list = [
        1 => '主任医师',
        6 => '副主任医师',
        3 => '主治医师',
        4 => '住院医师',
    ];
    
    /**
     * 挂号时间午别
     * @var [type]
     */
    public static $visit_nooncode_type = [
        1=>'上午',2=>'下午',3=>'晚上'
    ];    


    /**
     * 挂号门诊类型
     * @var [type]
     */
    public static $visit_type = [
        '0'=>'普通门诊',
        '1'=>'普通门诊',
        '2'=>'专家门诊',
        '3'=>'专科门诊',
        '4'=>'特需门诊',
        '5'=>'夜间门诊',
        '6'=>'会诊门诊',
        '7'=>'老院门诊',
        '8'=>'其他门诊'
    ];

    /**
     * 是否复诊类型
     * @var [type]
     */
    public static $famark_type_list = [
        1 => '初诊',
        2 => '复诊'
    ];

    /**
     * 性别
     * @var [type]
     */
    public static $gender_list = [
        1 => '男',
        2 => '女'
    ];

    /**
     * 测试医生过滤
     * @var [type]
     */
    public static $ceshi_doctor = [
        'ceshi','demo','测试'
    ];

    /**
     * @var string[]
     */
    public static $allow_refer = [
        'kepudl',//M端详情页
        'wxapp_kepudl',//小程序详情页
        'pc_kepudl',//PC端详情页
        'kedaxunfei',//科大讯飞
    ];

    /**
     * 获取codis缓存数据
     * @param $key
     * @return array|bool|mixed
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function getCodisCache($key)
    {
        //集群redis
        $redis = \Yii::$app->redis_codis;
        if ($redis->exists($key)) {
            $value = $redis->get($key);
            //解压字符串
            return self::getGzuncompress($value, $key);
        } else {
            return [];
        }
    }

    /**
     * 设置codis缓存数据
     * @param $key
     * @param $value
     * @param int $expire
     * @return bool
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function setCodisCache($key, $value, $expire = 0)
    {
        //集群redis
        $redis_codis = \Yii::$app->redis_codis;
        //压缩字符串
        $value = self::setGzcompress($value, $key);
        $redis_codis->set($key, $value);
        if ($expire > 0) {
            $redis_codis->expire($key, $expire);
        }
        return true;
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     * @author xiujianying
     * @date 2021/2/22
     */
    public static function delCodisCache($key){
        //集群redis
        $redis_codis = \Yii::$app->redis_codis;
        if ($redis_codis->exists($key)) {
            $redis_codis->del($key);
        }
        return true;
    }

    /**
     * 压缩内容
     * @param $str
     * @param string $flagId
     * @param int $level
     * @return bool|false|string
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function setGzcompress($str, $flagId = '', $level = 9)
    {
        try {
            $content = gzcompress(json_encode($str), $level);
            if (empty($content)) {
                \Yii::error('flagId：' . $flagId . ' 压缩失败！level：' . $level . ' str：' . $str, __CLASS__ . '::' . __METHOD__ . ' error');
                return false;
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            return false;
        }
        return $content;
    }

    /**
     * 解压内容
     * @param $str
     * @param string $flagId
     * @return bool|mixed
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function getGzuncompress($str, $flagId = '')
    {
        try {
            $content = json_decode(gzuncompress($str), true);
            if (empty($content)) {
                \Yii::error('flagId：' . $flagId . ' 解压失败！ str：' . $str, __CLASS__ . '::' . __METHOD__ . ' error');
                return false;
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            return false;
        }
        return $content;
    }

    /**
     * 获取后台登录用户信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-11
     * @version v1.0
     * @return  [type]     [description]
     */
    public static function getAdminInfo()
    {
        $cookie = \Yii::$app->request->cookies;
        $admin_id = $cookie->getValue('uid', 0);
        $admin_name = $cookie->getValue('name', '');
        return [
            'admin_id'=>$admin_id,
            'admin_name'=>$admin_name
        ];
    }

    /**
     * 获取疾病列表根据科室id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-25
     * @version 1.0
     * @param   string $frist_department_id [description]
     * @param   string $second_department_id [description]
     * @param   string $initial [description]
     * @param   boolean $update_cache [description]
     * @return  [type]                           [description]
     */
    public static function getDiseasesBykeshiID($frist_department_id = '', $second_department_id = '', $initial = '', $update_cache = false)
    {
        $key = sprintf(Yii::$app->params['cache_key']['diseases_list_by_fkeshi_skeshi_initial'], $frist_department_id, $second_department_id, $initial);
        $data = CommonFunc::getCodisCache($key);
        if (!$data || $update_cache) {
            $data = DiseaseDepartmentModel::diseases_list_by_keshi_initial($frist_department_id, $second_department_id, $initial);
            if ($data) {
                CommonFunc::setCodisCache($key, $data);
            }
        }
        return $data ?? [];
    }


    public static function filterContent($content)
    {
        //过滤html标签
        $content = strip_tags($content);
        $content = str_replace('>', '', $content);
        $content = str_replace('<', '', $content);
        $content = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $content);
        //过滤html特殊字符
        $content = htmlspecialchars($content);
        //过滤空白字符
        preg_filter("/\s/", '', $content);
        //过滤双引号
        $content = str_replace('"', "'", $content);
        return $content;
    }

    /**
     * 字符串过滤csv
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-19
     * @version v1.0
     * @param   string     $content [description]
     * @return  [type]              [description]
     */
    public static function strFilterCsv($content = '')
    {
        $content = str_replace([
            "\r\n",
            "\n",
            "\\n",
            "\r",
            "\t",
            "\v",
            "\f",
            ",",
            "，",
            "&nbsp;",
            "&#8203;",
            "\u200b",
            "\xe2\x80\x8b",
            "\xe2\x80\x8c",
            "\xe2\x80\x8d",
            " ",
            "　",
        ], "", $content);
        return $content;
    }



    public static function getFKeshiInfo($update_cache = false)
    {
        $key = Yii::$app->params['cache_key']['keshi_info'];
        $data = CommonFunc::getCodisCache($key);
        if (!$data || $update_cache) {
            $data = Department::find()->where(['parent_id' => 0, 'is_common' => 1, 'status' => 1])->select('department_id,department_name,parent_id,status,is_common')->asArray()->all();
            if ($data) {
                CommonFunc::setCodisCache($key, $data);
            }

        }
        return $data;
    }

    ##修改科室信息为王氏科室
    public static function getKeshiInfo($keshi_id = '', $update_cache = false)
    {
        $update_cache = 1;
        $key = sprintf(Yii::$app->params['cache_key']['keshi_info'], $keshi_id);
        $data = CommonFunc::getCodisCache($key);
        if (!$data || $update_cache) {
            $keshiInfos_arr = self::getDepartment();
            $result = [];
            foreach ($keshiInfos_arr as $k => $v) {
                if ($v['id'] == $keshi_id) {
                    $result['department_id'] = $v['id'];
                    $result['department_name'] = $v['name'];
                    $result['parent_id'] = $v['parentid'];
                    $result['status'] = 1;
                    $result['is_common'] = 1;
                    $result['second_arr'] = [];
                    if ($result['parent_id'] == 0) {
                        foreach ($keshiInfos_arr as $item) {
                            if ($item['parentid'] == $keshi_id) {
                                $second = [];
                                $second['department_id'] = $item['id'];
                                $second['department_name'] = $item['name'];
                                $second['parent_id'] = $keshi_id;
                                $second['status'] = 1;
                                $second['is_common'] = 1;
                                $result['second_arr'][$item['id']] = $second;
                            }
                        }
                    }
                    break;
                }
            }
            if ($result) {
                $data = $result;
                CommonFunc::setCodisCache($key, $data);
            }
        }
        return $data;
    }

    public static function getTitle($id = 0)
    {
        $arr = [1 => '主任医师', 2 => '主任中医师', 3 => '主治医师', 4 => '住院医师', 5 => '副主任中医师', 6 => '副主任医师', 7 => '主任技师', 8 => '副主任技师', 9 => '主管技师', 10 => '主任护师', 11 => '主管护师', 12 => '副主任护师', 13 => '主任检验师', 14 => '主管检验师', 15 => '副主任检验师', 16 => '主任药师', 17 => '主管药师', 18 => '副主任药师', 19 => '医师', 20 => '技士', 21 => '技师', 22 => '护师', 23 => '检验师', 24 => '药师', 25 => '讲师', 99 => '未知'];
        if ($id) {
            return $arr[$id];
        }
        return $arr;
    }

    /**
     * @param bool $intger
     * @return mixed|string
     * @author xiujianying
     * @date 2020/8/10
     */
    public static function getRealIpAddressForNginx($intger = false)
    {
        $ip = '';
        $remote_addr = '';
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remote_addr = $_SERVER['REMOTE_ADDR'];
        }
        !$ip && $ip = $remote_addr;
        //解决代理问题
        $ipArr = explode(',', $ip);
        if (count($ipArr) > 1) {
            $ip = ArrayHelper::getValue($ipArr, '0');
        }

        if ($intger) {
            return sprintf("%u", ip2long($ip));
        }
        return $ip;
    }

    /**
     * 根据ip获取地理位置（高德api 暂不支持ipv6）
     * 文档地址：http://lbsyun.baidu.com/index.php?title=webapi/ip-api
     * @param string $ip ip地址
     * @return string
     * @author yueyuchao <yueyuchao@yuanxinjituan.com>
     * @date 2019/12/24
     */
    public static function getIpLocationGd(string $ip)
    {
        $uri = "/v3/ip";
        $url = \Yii::$app->params['gaode_map']['url'] . $uri;
        $key = \Yii::$app->params['gaode_map']['key'];

        $params = [];
        $params['ip'] = $ip;
        $params['key'] = $key;

        $url = $url . '?' . http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result, true);
        $tmpRes = [
            'status' => 1,
            'lng' => 116.397447,
            'lat' => 39.909167,
            'province' => '北京市',
            'city' => '北京市'
        ];
        $centerLngLats = [];
        if (!empty($result['status']) && !empty($result['adcode']) && !empty($result['rectangle'])) {
            $tmpProvince = $result['province'];
            $tmpCity = $result['city'];
            $lngLats = explode(';', $result['rectangle']);
            if (!empty($lngLats) && count($lngLats) == 2) {
                $one = explode(',', $lngLats[0]);
                $two = explode(',', $lngLats[1]);
                $centerLngLats = self::getCenterFromDegrees([$one,$two]);
            }
            if (!empty($centerLngLats)) {
                $result = [
                    'status' => 1,
                    'lng' => $centerLngLats[0],
                    'lat' => $centerLngLats[1],
                    'province' => $tmpProvince,
                    'city' => $tmpCity
                ];
            } else {
                $result = $tmpRes;
            }
        } else {
            $result = $tmpRes;
        }
        return $result;
    }

    /**
     * 获得客户端IP
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/03/08
     * @return string
     */
    public static function getIp()
    {
        $onlineip = '';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }

    /**
     * 百度sn计算函数
     * @param $ak
     * @param $sk
     * @param $url
     * @param $querystring_arrays
     * @param string $method
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2019/12/24
     */
    public static function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST') {
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url . '?' . $querystring . $sk));
    }

    /**
     * 获取经纬度，顺序：1缓存，2ip，3地址
     * @param $address
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2022/5/19
     */
    public static function getLongitude($address = '北京')
    {
        $cookies = \Yii::$app->request->cookies;
        $lat = $cookies->getValue('lat');
        $lon = $cookies->getValue('lon');

        //ip获取
        if (empty($lat) || empty($lon)) {
            $ip = CommonFunc::getRealIpAddressForNginx();
            $result = CommonFunc::getIpLocationGd($ip);
            if (is_array($result) && ArrayHelper::getValue($result, 'status') == 1) {
                $lat = ArrayHelper::getValue($result, 'lat', '');
                $lon = ArrayHelper::getValue($result, 'lng', '');
            }

            //地址获取
            if (empty($lat) || empty($lon)) {
                $result = CommonFunc::city2latlngGd($address);
                $lat = ArrayHelper::getValue($result, 'lat', '');
                $lon = ArrayHelper::getValue($result, 'lng', '');
            }

            //写缓存
            if (!empty($lat) && !empty($lon)) {
                CommonFunc::common_set_cookie('lat', $lat);
                CommonFunc::common_set_cookie('lon', $lon);
            }
        }

        return ['lat' => $lat, 'lon' => $lon];
    }

    /**
     * 跟ip返回城市
     * @return array
     * @author xiujianying
     * @date 2020/8/10
     */
    public static function getLocalDistrict()
    {
        $ip = CommonFunc::getRealIpAddressForNginx();
//        $ip = '1.119.56.6';
        $result = CommonFunc::getIpLocationGd($ip);
        if (is_array($result) && ArrayHelper::getValue($result, 'status') == 1) {
            $local_city = $result['city'] ?: $result['province'] ?: '未知';
            $lat = ArrayHelper::getValue($result, 'lat', '');
            $lon = ArrayHelper::getValue($result, 'lng', '');
        } else {
            $local_city = '未知';
            $lat = '';
            $lon = '';
        }
        //$local_city = '未知';
        $city_pid = 0;
        $city_cid = 0;
        $pinyin = '';

        if ($local_city == '未知') {
            $local_city = '全国';
        } else {
            $city = rtrim($local_city, '省');
            $city = rtrim($city, '市');
            //获取地区
            $district = SnisiyaSdk::getInstance()->getDistrict();
            $flag = false;
            if ($district) {
                foreach ($district as $p) {
                    //匹配市
                    foreach ($p['city_arr'] as $c) {
                        if ($c['name'] == $city) {
                            $flag = true;
                            $city_pid = $p['id'];
                            $city_cid = $c['id'];
                            $pinyin = $c['pinyin'];
                            break;
                        }
                    }
                    if (!$flag) {
                        //匹配省
                        if ($p['name'] == $city) {
                            $flag = true;
                            $city_pid = $p['id'];
                            $city_cid = '-';
                            $pinyin = $p['pinyin'];
                            break;
                        }
                    }

                }
            }

            //判断城市 配置的城市开启定位
            /*$cityConfig = self::get_city_config($city_pid,$city_cid);
            if($cityConfig){

            }else{
                $local_city = '全国';
                $city_pid = 0;
                $city_cid = 0;
                $pinyin = '';
            }*/
        }

        //获取完后 存储到cookie
        CommonFunc::city_cookie($city_pid, $city_cid, $local_city, $pinyin, true);

        return ['city_pid' => $city_pid, 'city_cid' => $city_cid, 'city' => $local_city, 'ip' => $ip, 'lat' => $lat, 'lon' => $lon];
    }

    /**
     * 根据经纬度 获取 省市区
     * @param $lat 纬度
     * @param $lng 经度
     * @return array
     * @throws \Exception
     * @author yueyuchao
     * @date 2022/02/24
     */
    public static function latlng2cityGd($lat, $lon)
    {
        $province = '';
        $city = '';
        $district = '';
        $code = '';

        $uri = '/v3/geocode/regeo';
        $url = \Yii::$app->params['gaode_map']['url'] . $uri;
        $key = \Yii::$app->params['gaode_map']['key'];
        $params = [];
        $params['location'] = $lon . "," . $lat;
        $params['key'] = $key;
        $params['output'] = 'json';

        $url = $url . '?' . http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        //本地开发报错 无法获取本地颁发者证书 添加以下设置lixiaolong 20210107
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);

        //echo 'Curl error: ' . curl_error($curl);
        curl_close($curl);
        $result = json_decode($result, true);
        if (!empty($result) && !empty($result['status']) && !empty($result['regeocode'])) {
            $result = ArrayHelper::getValue($result, 'regeocode');

            $province = ArrayHelper::getValue($result, 'addressComponent.province');
            $city = ArrayHelper::getValue($result, 'addressComponent.city');
            if (empty($city)) {
                $city = ArrayHelper::getValue($result, 'addressComponent.province');
            }
            $district = ArrayHelper::getValue($result, 'addressComponent.district');
            if ($province == $city) {
                //$city = $district;
            }
        }
        return ['province' => $province, 'city' => $city];
    }

    /**
     * 地址转换经纬度
     * @param $address
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/5/18
     */
    public static function city2latlngGd($address)
    {
        $lat = '';
        $lng = '';

        $params['address'] = $address;
        $uri = '/v3/geocode/geo';
        $url = \Yii::$app->params['gaode_map']['url'] . $uri;
        $key = \Yii::$app->params['gaode_map']['key'];
        $params['key'] = $key;
        $params['output'] = 'json';

        $url = $url . '?' . http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        //本地开发报错 无法获取本地颁发者证书 添加以下设置lixiaolong 20210107
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($result, true);
        $latLng = '';
        $lat = '';
        $lng = '';
        if ($result && !empty($result['status']) && !empty($result['geocodes'])) {
            if (ArrayHelper::getValue($result['geocodes'], '0')) {
                $result = ArrayHelper::getValue($result['geocodes'], '0');
                $latLng = ArrayHelper::getValue($result, 'location');
            }
        }
        if ($latLng) {
            $tmpLatLng = explode(',', $latLng);
            $lat = $tmpLatLng[1];
            $lng = $tmpLatLng[0];
            unset($tmpLatLng);
        }
        return ['lat' => $lat, 'lng' => $lng];
    }

    /**
     * 城市名称 转换id 拼音
     * @param $province
     * @param $city
     * @param false $isLonLat
     * @return array
     * @author xiujianying
     * @date 2021/5/17
     */
    public static function city2id($province, $city, $isLonLat = false)
    {
        $address = $province.$city;
        if (strpos($city, '区')) {
            $city = rtrim($city, '区');
        }
        if (strpos($city, '市')) {
            $city = rtrim($city, '市');
        }

        if (strpos($province, '省')) {
            $province = rtrim($province, '省');
        }

        $p = mb_substr($province,-1,1,'utf-8');
        if($p=='省'){
            $province = str_replace('省','',$province);
        }

        if (strpos($province, '市')) {
            $province = rtrim($province, '市');
        }
        //获取地区
        $district = SnisiyaSdk::getInstance()->getDistrict();
        $city_pid = 0;
        $city_cid = 0;
        $pinyin = '';
        $local_city = '全国';
        if ($district) {
            foreach ($district as $p) {
                //匹配省
                if ($p['name'] == $province) {
                    $city_pid = $p['id'];
                    $flag = true;
                    //匹配市
                    foreach ($p['city_arr'] as $c) {
                        if ($c['name'] == $city) {
                            $city_cid = $c['id'];
                            $pinyin = $c['pinyin'];
                            $local_city = $city;
                            $flag = false;
                            break;
                        }
                    }
                    if ($flag) {
                        $city_cid = '-';
                        $pinyin = $p['pinyin'];
                        $local_city = $province;
                    }

                    break;
                }
            }
        }
        $lat = '';
        $lng = '';
        if($isLonLat){
            $latLng = CommonFunc::city2latlngGd($address);
            $lat = ArrayHelper::getValue($latLng,'lat');
            $lng = ArrayHelper::getValue($latLng,'lng');
            CommonFunc::common_set_cookie('lat', $lat);
            CommonFunc::common_set_cookie('lon', $lng);
        }
        $data = [
            'city_pid' => $city_pid,
            'city_cid' => $city_cid,
            'pinyin' => $pinyin,
            'local_city' => $local_city,
            'lat' => $lat,
            'lng' => $lng,
        ];
        return $data;
    }

    /**
     * 存储地区
     * @param $p 省份id
     * @param $c 城市id
     * @param $city 城市名称
     * @param bool $local 是否是定位城市名称
     * @author xiujianying
     * @date 2020/8/10
     */
    public static function city_cookie($p='-', $c='-', $city='全国', $pinyin = '', $local = false)
    {
        $cookies = \Yii::$app->response->cookies;

        //$expire = time() + 30 * 24 * 3600;
        $expire = 0;
        $local = $local ? 'local_' : '';
        //设置cookie
        $cookies->add(new Cookie([
            'name' => $local . 'p',
            'value' => $p,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => $local . 'c',
            'value' => $c,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => $local . 'city',
            'value' => $city,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => $local . 'pinyin',
            'value' => $pinyin,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => 'first',
            'value' => 1,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
        return ['city_pid' => $p, 'city_cid' => $c, 'city' => $city, 'pinyin' => $pinyin];
    }

    /**
     * 读取选择的地区
     * @param bool $local 是否获取本地城市
     * @return array
     * @author xiujianying
     * @date 2020/8/13
     */
    public static function get_city_cookie($local = false)
    {
        $cookies = \Yii::$app->request->cookies;

        $local = $local ? 'local_' : '';

        $p = $cookies->getValue($local . 'p');
        $c = $cookies->getValue($local . 'c');
        $city = $cookies->getValue($local . 'city');
        $pinyin = $cookies->getValue($local . 'pinyin');

        return ['city_pid' => $p, 'city_cid' => $c, 'city' => $city, 'pinyin' => $pinyin];
    }

    /**
     * 根据拼音选择城市
     * @param $pinyin
     * @author xiujianying
     * @date 2021/5/7
     */
    public static function auto_dingwei($pinyin)
    {
        $district = CommonFunc::pinyin2id($pinyin);
        if ($district) {
            CommonFunc::city_cookie($district['p_id'], $district['c_id'], $district['name'], $pinyin);
        }
    }

    /**
     * 拼音转换成城市id
     * @param $pinyin
     * @return array
     * @author xiujianying
     * @date 2021/5/7
     */
    public static function pinyin2id($pinyin)
    {
        $district = SnisiyaSdk::getInstance()->getDistrict();
        foreach ($district as $v) {
            if ($v['pinyin'] == $pinyin) {
                return ['p_id' => $v['id'], 'c_id' => '-', 'name' => $v['name']];
            }
            foreach ($v['city_arr'] as $cArr) {
                if ($cArr['pinyin'] == $pinyin) {
                    return ['p_id' => $v['id'], 'c_id' => $cArr['id'], 'name' => $cArr['name']];
                }
            }
        }
        return [];
    }

    /**
     * 开启定位的配置 true：开启
     * @param $p_id 省id
     * @param $c_id 市id
     * @return bool
     * @author xiujianying
     * @date 2020/11/3
     */
    public static function get_city_config($p_id, $c_id)
    {
        //河南 17   江苏11 南京195
        $cityParr = ['17'];
        $cityCarr = ['195'];
        if (in_array($p_id, $cityParr) || in_array($c_id, $cityCarr)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $name
     * @param $value
     * @author xiujianying
     * @date 2020/12/26
     */
    public static function common_set_cookie($name, $value)
    {
        $cookies = \Yii::$app->response->cookies;

        $expire = time() + 30 * 24 * 3600;
        //设置cookie
        $cookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'domain' => '.nisiya.net'
        ]));
    }

    /**
     *Notes:获取医院等级
     *User:lixiaolong
     *Date:2021/1/9
     *Time:16:09
     * @return mixed
     */
    public static function getHospitalLevel()
    {
        return array_column(self::getHospitalLevelData(), 'value', 'key');
    }

    /**
     * 获取医院等级
     * @return array
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public static function getHospitalLevelData(): array
    {
        \nisiya\baseapisdk\Config::setConfig(\Yii::$app->params['baseapisdk']);
        return ArrayHelper::getValue((new SundrySdk())->list(1), 'data', []);
    }

    /**
     * 获取所有二级科室列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @param   string $fkeshi_id [description]
     * @param   integer $update_cache [description]
     * @return  [type]                   [description]
     */
    public static function get_all_skeshi_list($fkeshi_id = '', $update_cache = false)
    {
        $key = sprintf(Yii::$app->params['cache_key']['hospital_all_skeshi_list'], $fkeshi_id);
        $data = CommonFunc::getCodisCache($key);
        if (!$data || $update_cache) {
            $data = Department::getAllSkeshiList($fkeshi_id);
            CommonFunc::setCodisCache($key, $data);
        }
        return $data;
    }

    /**
     * 获取王氏科室一二级关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-16
     * @version 1.0
     * @return  [type]     [description]
     */
    public static function getMiaoKeshi()
    {
        $keshiInfos_arr = self::getDepartment();
        $data = [];
        foreach ($keshiInfos_arr as $v) {
            if ($v['parentid'] == 0) {
                $row['department_id'] = $v['id'];
                $row['department_name'] = $v['name'];
                $data[$v['id']] = $row;
            } else {
                $row['department_id'] = $v['id'];
                $row['department_name'] = $v['name'];
                $data[$v['parentid']]['second_arr'][] = $row;
            }
        }
        return $data;
    }

    /**
     * 获取一级科室信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public static function getFkeshiInfos()
    {
        $keshiInfos_arr = self::getDepartment();
        $result = [];
        foreach ($keshiInfos_arr as $k => $v) {
            if ($v['parentid'] == 0) {
                $result[] = $v;
            }
        }
        return $result;
    }

    /**
     * 获取二级科室信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @param   integer $pid [description]
     * @return  [type]          [description]
     */
    public static function getSkeshiInfos($pid = 0)
    {
        $keshiInfos_arr = self::getDepartment();
        $result = [];
        foreach ($keshiInfos_arr as $v) {
            if ($pid == 0 && $v['parentid'] != 0) { //获取所有二级科室
                $result[] = $v;
            } elseif ($pid != 0 && $v['parentid'] == $pid) { //获取指定科室二级科室信息
                $result[] = $v;
            }
        }
        return $result;
    }

    /**
     * 根据科室ID获取科室名称
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @param   [type]     $id [description]
     * @return  [type]         [description]
     */
    public static function getKeshiName($id)
    {
        $keshiInfos = self::getDepartment();
        $keshiInfos_arr = ArrayHelper::map($keshiInfos, 'id', 'name');
        if (isset($keshiInfos_arr[$id])) {
            return $keshiInfos_arr[$id] ?? '';
        } else {
            return '';
        }
    }

    /**
     * 医院等级别名
     * @param int $level_id
     * @return mixed|string
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/5
     */
    public static function getLevelAlias($level_id=0){
        if($level_id==0){
            return '';
        }
        return ArrayHelper::getValue(array_column(self::getHospitalLevelData(), 'alias_value', 'key'), $level_id);
    }


    public static function getPlatform($pt)
    {
        $arr = [
            0 => '家庭医生',
            1 => '河南医生',
            2 => '南京医生',
            3 => '好大夫'
        ];
        return $arr[$pt] ?? '';
    }


    /**
     * 验证sign
     * @param $partnerKey
     * @param $secret
     * @param $params
     * @return array|int[]
     * @throws \Exception
     * @author xiujianying
     * @date 2020/11/27
     */
    public static function validatorApi($partnerKey, $secret, $params)
    {
        $sign = ArrayHelper::getValue($params, 'sign');
        if (!$sign) {
            return ['code' => -1, 'msg' => 'sign不能为空'];
        }
        if (!$partnerKey) {
            return ['code' => -1, 'msg' => 'partnerKey不能为空'];
        }
        $time = ArrayHelper::getValue($params, 'time');
        if (!$time) {
            return ['code' => -1, 'msg' => 'time不能为空'];
        }
        $ago = time() - $time;
        if ($ago > 300) {
            //return ['code' => -1, 'msg' => '已过期'];
        }
        unset($params['sign']);
        unset($params['partnerKey']);
        unset($params['time']);
        //post内容二维时
        if (isset($params['content']) && is_array($params['content'])) {
            $params_2 = $params['content'];
            unset($params['content']);
            $params = array_merge($params, $params_2);
        }


        $signed = CommonFunc::generateSign($partnerKey, $time);
        if ($sign != $signed) {
            return ['code' => -1, 'msg' => '验证不通过'];
        }

        return ['code' => 0];
    }


    /**
     * @Param $partnerKey 合作方key
     * @Param $secret 私钥，请联系好大夫开发获取
     * @Param $timestamp 秒级时间戳，5分钟内有效
     * @params params 请求的业务参数（除去请求参数里的 partnerKey,time,sign 字段）
     */
    public static function generateSignature($partnerKey, $secret, $timestamp, $params)
    {
        $paramArray = array();
        $paramArray = array_values($params);
        $paramArray[] = strval($partnerKey);
        $paramArray[] = strval($secret);
        $paramArray[] = strval($timestamp);
        $paramArray = array_map(function ($item) {
            return strval($item);
        }, $paramArray);
        sort($paramArray, SORT_STRING);
        $paramString = implode('', $paramArray);
        return sha1($paramString);
    }

    /**
     * 好大夫新签名方法
     * @param $partnerKey
     * @param $time
     * @return false|string|null
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/23
     */
    public static function generateSign($partnerKey, $time)
    {/*{{{*/
        if (empty($partnerKey) || $time <= 0) {
            return null;
        }

        $str = $partnerKey . strval($time);
        $md5 = md5($str);
        return substr($md5, 8, 16);
    }/*}}}*/

    /**
     * 生成完订单 生成商城支付连接
     * @param $miao_order_id_sn
     * @return array
     * @author xiujianying
     * @date 2020/12/1
     */
    public static function guahaoGoPay($miao_order_id_sn)
    {
        try {
            $pay_url = '';
            $pay_no = '';
            //同步地址
            $pay_return_url = ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile') . 'hospital/register/show-ordermsg.html?id=' . $miao_order_id_sn;
            //异步回调地址
            $pay_notify_url = ArrayHelper::getValue(\Yii::$app->params, 'api_url.self') . '/pay/pay-back?encryption=false';   //退款回调

            $orderModel = new GuahaoOrderModel();
            $row = $orderModel->find()->where(['order_sn' => $miao_order_id_sn])->asArray()->one();
            if ($row) {
                $expTime = CommonFunc::PAY_EXP_TIME;
                //判断订单
                if ((time() - $row['create_time']) > $expTime) {
                    throw new \Exception('订单已超时');
                }
                $fee = $row['visit_cost'] / 100;
                if ($fee <= 0) {
                    throw new \Exception('金额为负数异常');
                }
                //"[{\"pay_goods_code\":1,\"pay_goods_fee\":0.01,\"pay_goods_count\":1,\"pay_goods_name\":\"test product1\"}]"
                $pay_goods = [
                    'pay_goods_code' => 1,
                    'pay_goods_fee' => $fee,
                    'pay_goods_count' => 1,
                    'pay_goods_name' => '预约挂号'
                ];
                $pay_goods_[] = $pay_goods;
                //请求支付接口
                $sdk = PaySdk::getInstance();
                $params = [
                    'pay_out_trade_no' => $miao_order_id_sn,
                    'pay_subject' => '预约挂号',
                    'pay_fee' => $fee,
                    'pay_goods' => json_encode($pay_goods_),
                    'pay_expire_time' => $row['create_time'] + $expTime,
                    'pay_return_url' => $pay_return_url,
                    'pay_notify_url' => $pay_notify_url
                ];
                $payReturn = $sdk::pay($params);
                if ($payReturn['code'] == 1) {
                    $pay_url = ArrayHelper::getValue($payReturn, 'data.pay_url');
                    $pay_no = ArrayHelper::getValue($payReturn, 'data.pay_no');  //支付系统订单号
                } else {
                    throw new \Exception($payReturn['msg']);
                }
            } else {
                throw new \Exception('订单不存在');
            }

            return ['code' => 1, 'data' => $pay_url];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }


    /**
     * 支付回调
     * @param $data
     * pay_no           string 是 支付平台单号
     * pay_out_trade_no string 是 外部订单号
     * pay_platform_no  string 是 三方支付平台单号
     * pay_true_fee     float   是 实际支付金额
     * pay_type_id      string 是 支付类型
     * pay_type_name    int    是 支付类型名
     * pay_config_id     int    否 支付配置信息
     * pay_config_name   string 是 支付配置名
     * pay_config_appid  string 是 支付配置 id
     * wechat_config_appid int  是 微信支付 appid 【只有微信支付时 有值】
     * wechat_config_name Int   是 微信支付的配置名 【只有微信支付 时有值】
     * pay_complete_time Int   是 支付完成时间戳
     * @return array|int[]
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/3
     */
    public static function payBack($data)
    {
        $notify = false;  //通知
        $miao_order_id_sn = ArrayHelper::getValue($data, 'pay_out_trade_no');
        $pay_fee = ArrayHelper::getValue($data, 'pay_true_fee');
        try {
            if ($miao_order_id_sn) {
                $orderModel = new GuahaoOrderModel();

                $row = $orderModel->find()->where(['order_sn' => $miao_order_id_sn])->one();
                if ($row) {
                    //判断价格
                    if ($pay_fee == $row->visit_cost / 100) {
                        //判断状态
                        if ($row->state == 5 && $row->pay_status == 1) {  //待支付
                            if ($row->tp_platform == 6) {//王氏医生加号为待审核 2021-03-29 zhangfan
                                $row->state = 8;
                            } else {
                                $row->state = 0;
                            }
                            $row->pay_status = 3;
                            $row->save();
                            $notify = true;
                            //订单附表信息
                            $id = $row->id;
                            $infoQuery = GuahaoOrderInfoModel::find()->where(['order_id' => $id])->one();
                            if ($infoQuery) {
                                $infoQuery->pay_time = ArrayHelper::getValue($data, 'pay_complete_time');
                                $infoQuery->pay_no = ArrayHelper::getValue($data, 'pay_no');
                                $infoQuery->pay_platform_no = ArrayHelper::getValue($data, 'pay_platform_no');

                                $infoQuery->save();
                            }


                        } else {
                            //throw new \Exception('支付状态异常-state:' . $row->state. '-pay_status:' . $row->pay_status);
                            //不是支付完成和不是待支付状态为异常 改为待退款
                            if ($row->pay_status != 3) {
                                $row->pay_status = 4;
                                $row->save();
                            }
                        }


                    } else {
                        throw new \Exception('价格异常-传参:' . $pay_fee . '-mysql:' . $row->visit_cost);
                    }
                } else {
                    throw new \Exception('订单不存在:' . $miao_order_id_sn);
                }
            } else {
                throw new \Exception('订单号为空');
            }

            if ($notify) {
                //通知好大夫下单成功
                $sSdk = SnisiyaSdk::getInstance();
                $sSdk->pay_order(['id' => $miao_order_id_sn]);
                //预约成功发送短信
                CommonFunc::guahaoSendSms('guahao_success',$miao_order_id_sn);
            }
            return ['code' => 1];
        } catch (\Exception $e) {
            //log 记录错误信息
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 退款回调
     * @param $miao_order_id_sn
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/1
     */
    public static function refundBack($miao_order_id_sn)
    {
        $notify = false;  //通知
        try {
            if ($miao_order_id_sn) {
                $orderModel = new GuahaoOrderModel();

                $row = $orderModel->find()->where(['order_sn' => $miao_order_id_sn])->one();
                if ($row) {
                    //退款中改为退款完成
                    if (($row->state == 1 || $row->state == 2) && $row->pay_status == 5) {
                        $row->pay_status = 6;
                        $row->save();
                        $notify = true;
                        //订单附表信息
                        $id = $row->id;
                        $infoQuery = GuahaoOrderInfoModel::find()->where(['order_id' => $id])->one();
                        if ($infoQuery) {
                            $infoQuery->refund_time = time();
                            $infoQuery->save();
                        }

                        //退款完成通知好大夫
                        $sSdk = SnisiyaSdk::getInstance();
                        $sSdk->order_refund(['id' => $miao_order_id_sn]);
                    }
                } else {
                    throw new \Exception('订单不存在-' . $miao_order_id_sn);
                }
            } else {
                throw new \Exception('订单id为空');
            }

            if($notify){
                //退款发送取消预约短信
                CommonFunc::guahaoSendSms('guahao_cancel',$miao_order_id_sn);
            }

            return ['code' => 1];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 获取某天的一周日期
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @param   string     $time   [日期]
     * @param   string     $format [日期格式]
     * @return  [type]             [description]
     */
    public static function get_week($time = '', $format = 'Y-m-d')
    {
        $time = $time != '' ? $time : time();
        //获取当前周几
        $week     = date('w', $time);
        $weekname = array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日');
        //星期日排到末位
        if (empty($week)) {
            $week = 7;
        }
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date_time = date($format, strtotime('+' . (($i + 1) - $week) . ' days', $time));
            $item      = [
                'date' => $date_time,
                'week' => $weekname[$i],
            ];
            $date[$date_time] = $item;
        }
        return $date;
    }

    /**
     * 获取指定日期的上周日日期
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-27
     * @version v1.0
     * @param   integer    $date_time [指定日期（2021-04-27）]
     * @return  [type]          [description]
     */
    public static function getSunday($date_time = '')
    {
        if (!$date_time) {
            $date_time = strtotime(date('Y-m-d'));
        }else{
            $date_time = strtotime($date_time);
        }
        for ($i=1; $i <= 7; $i++) {    
            $pre_sunday_time = strtotime('-'.$i.' day',$date_time);
            if (date('w', $pre_sunday_time) == 0 ) {
                return date('Y-m-d', $pre_sunday_time);
            }
        }
    }

    /**
     * 带中文的年月日(2021年4月12日)转标准日期格式2021-04-12
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-28
     * @version v1.0
     * @param   string     $china_date [description]
     */
    public static function chinaDateToDate($china_date ='',$split = '-')
    {
        $arr = date_parse_from_format('Y年m月d日',$china_date);
        $arr['month'] = $arr['month'] < 10 ? '0'.$arr['month'] : $arr['month'];
        $arr['day'] = $arr['day'] < 10 ? '0'.$arr['day'] : $arr['day'];
        $date = $arr['year'].$split.$arr['month'].$split.$arr['day'];
        return $date;
    }

    /**
     * 标准格式的日期转中文日期
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-28
     * @version v1.0
     * @param   string     $date [description]
     * @return  [type]           [description]
     */
    public static function dateToChinaDate($date = '',$split = '-')
    {
        $china_date = '';
        $date_text = [
            0=>'年',
            1=>'月',
            2=>'日',
        ];
        $arr = explode($split, $date);
        foreach ($arr as $k=>$value) {
            $china_date.= (int)$value.$date_text[$k];
        }
        return $china_date;
    }

    /**
     * 获取两个日期差的天数
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-27
     * @version v1.0
     * @param   [type]     $start_time [description]
     * @param   [type]     $end_time   [description]
     * @return  [type]                 [description]
     */
    public static function getTimeDiff($start_time, $end_time = null)
    {
        if (!is_numeric($start_time)) {
           $start_time = strtotime($start_time);
        }

       if (!$end_time) {
            $end_time = time(); 
        }
        if (!is_numeric($end_time)) {
           $end_time = strtotime($end_time);
        }
        $days = [];

        while ($start_time <= $end_time){    
            $days[] = date('Y-m-d',$start_time);    
            $start_time = strtotime('+1 day',$start_time);
        }
        return count($days);
        
    }

    /**
     * 计算两个时间段是否有交集（边界重叠不算）
     *
     * @param int $beginTime1 开始时间1时间戳
     * @param int $endTime1 结束时间1时间戳
     * @param int $beginTime2 开始时间2时间戳
     * @param int $endTime2 结束时间2时间戳
     * @return bool
     */
    public static function checkTimeCross($beginTime1 = 0, $endTime1 = 0, $beginTime2 = 0, $endTime2 = 0) {
        $status = $beginTime2 - $beginTime1;
        if ($status > 0) {
            $status2 = $beginTime2 - $endTime1;
            if ($status2 > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $status2 = $endTime2 - $beginTime1;
            if ($status2 >= 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 时间段内容的星期几对应的日期
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @param string $stime 2022-07-01
     * @param string $etime 2022-07-20
     * @param string $cycle 出诊周期内容 按周期格式： {"周一":["上午","下午"],"周二":["上午"],"周三":["上午","下午"],"周四":["上午","下午"],"周五":["上午","下午"],"周六":["上午","下午"],"周日":["上午","下午"]} 。 按日期设置：{"2022-07-11":["上午","下午"],"2022-07-12":["上午","下午"],"2022-07-13":["上午","下午"]} 。 按时间段：{"0":"2022-07-11|2022-07-19"}
     * @param int
     * @return array
     */
    public static function getDateWeekDetail($stime, $etime, $cycle='')
    {
        $weekArr = ["周日","周一","周二","周三","周四","周五","周六"];
        $dateArr = self::periodDate($stime, $etime);
        $result = [];
        foreach ($dateArr as $date) {
            $num_wk = date('w', strtotime($date));
            $result[$weekArr[$num_wk]][] = $date;
        }

        $scheduleDate = [];
        if (!empty($cycle)) {
            $cycleArr = json_decode($cycle, true);
            foreach ($cycleArr as $key => $value) {
                foreach ($value as $val) {
                    if (isset($result[$key]) && !empty($result[$key])) {
                        foreach ($result[$key] as $date) {
                            $scheduleDate[] = $date . " " . $val;
                        }
                    }
                }
            }
        }
        return $scheduleDate;
    }

    /**
     * 时间段内所有日期
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @param string $stime 2022-07-01
     * @param string $etime 2022-07-20
     * @param int
     * @return array
     */
    public static function periodDate($startDate, $endDate)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $arr = [];
        while ($startTime <= $endTime)
        {
            $arr[] = date('Y-m-d', $startTime);
            $startTime = strtotime('+1 day', $startTime);
        }
        return $arr;
    }

    /**
     * 验证是否为日期格式
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @param string $date 2022-07-01
     * @param string $fotmat Y-m-d
     * @param string
     * @return boolean
     */
    public static function checkDate($date, $fotmat="Y-m-d")
    {
        $unixTime1 = strtotime($date);
        if (!is_numeric($unixTime1)) return false;

        $checkDate = date($fotmat, $unixTime1);
        $unixTime2 = strtotime($checkDate);

        if ($unixTime1 == $unixTime2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 分组时间段
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-08
     * @version v1.0
     * @param   array      $sections [description]
     * @return  [type]               [description]
     */
    public static function group_section($sections = [])
    {
        $tmp_sections = [];
        if ($sections) {
            $column = array_column($sections,'tp_section_id');
            array_multisort($column,SORT_ASC,$sections);
            $sections = self::secondArrayUniqueByKey($sections,'tp_section_id');
            foreach ($sections as $key => $value) {
                $value['tp_scheduling_id'] = urlencode(ArrayHelper::getValue($value, 'tp_scheduling_id', ''));
                $value['tp_section_id'] = urlencode(ArrayHelper::getValue($value, 'tp_section_id', ''));
                if ($value['section_state']== 1) {
                    $tmp_sections['may'][] = $value;
                }else{
                    $tmp_sections['maynot'][] = $value;
                }
            }
        }
        return $tmp_sections;
    }

    /**
     *  二维数组指定key 去重
     * @param $arr
     * @param $key
     * @return mixed
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-10-13
     */
    public static function secondArrayUniqueByKey($arr, $key)
    {
        $tmp_arr = array();
        foreach($arr as $k => $v)
        {
            if ($v[$key]) {
                if(in_array($v[$key], $tmp_arr))
                {
                    unset($arr[$k]);
                } else {
                    $tmp_arr[$k] = $v[$key];
                }
            }
        }
        return $arr;
    }

    /**
     * 解析支付回调aes数据
     * @param $data
     * @return mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/3
     */
    public static function validatorBackData($data)
    {
        CryptoTools::setKey(ArrayHelper::getValue(\Yii::$app->params, 'paysdk.appkey'));
        $data = json_decode(CryptoTools::AES256ECBDecrypt($data), true);
        return $data;
    }

    /**
     * 设置王氏医生对应医院医生id缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-12-10
     * @version 1.0
     * @param   integer $miao_doctor_id [description]
     * @param   integer $doctor_id [description]
     */
    public static function setMiaoid2HospitalDoctorID($miao_doctor_id = 0, $doctor_id = 0)
    {
        $redis = \Yii::$app->redis_codis;
        $docKeyHeader = Yii::$app->params['cache_key']['miaoid_hospital_doctor_id'];
        $docKey = sprintf($docKeyHeader, $miao_doctor_id);
        if ($doctor_id) {
            $hash_id = HashUrl::getIdEncode($doctor_id);
        } else {
            $hash_id = 0;
        }
        $redis->set($docKey, $hash_id);
        return true;
    }

    /**
     * 删除王氏医生对应医院医生id缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-12-10
     * @version 1.0
     * @param   integer $miao_doctor_id [description]
     * @param   integer $doctor_id [description]
     * @return  [type]                     [description]
     */
//    public static function delMiaoid2HospitalDoctorID($miao_doctor_id = 0)
//    {
//        $redis = \Yii::$app->redis_codis;
//        $docKeyHeader = Yii::$app->params['cache_key']['miaoid_hospital_doctor_id'];
//        $docKey = sprintf($docKeyHeader, $miao_doctor_id);
//        $redis->del($docKey);
//        return true;
//    }


    /**
     *
     * 暂停
     * 退款 走商城退款
     * @param $miao_order_id_sn  订单号
     * @param $type 默认用户发起  1 为商家发起
     * @param $state_desc 好大夫订单描述
     * @return array|int[]
     * @author xiujianying
     * @date 2020/12/1
     */
    public static function guahaoRefund11111($miao_order_id_sn, $type = 0, $state_desc = '')
    {
        try {
            $notifyUrl = ArrayHelper::getValue(\Yii::$app->params, 'api_url.self') . '/pay/refund-back?encryption=false';   //退款回调
            $row = GuahaoOrderModel::find()->where(['order_sn' => $miao_order_id_sn])->one();
            if ($row) {
                if ($type) {  //商家退款  由支付完成 改为停诊 退款中
                    $flag = $row->state == 0 && $row->pay_status == 3;
                    $row->state_desc = $state_desc;
                    $row->state = 2;
                } else {   //用户取消  由取消 改为 退款中
                    $flag = ($row->state == 1 || $row->state == 2) && $row->pay_status == 4;
                }

                //判断订单状态
                if ($flag) {
                    //订单附表信息
                    $id = $row->id;
                    $info = GuahaoOrderInfoModel::find()->where(['order_id' => $id])->asArray()->one();
                    if (!$info) {
                        throw new \Exception('附表信息异常');
                    }

                    //提交退款 改为退款中 等回调完改退款完成（pay_status=6 state=1取消）
                    $row->pay_status = 5;  //退款中
                    $row->save();
                    //走商城退款
                    $sdk = PaySdk::getInstance();
                    $params = [
                        //'pay_no' => ArrayHelper::getValue($info,'pay_no'),
                        'pay_out_trade_no' => $miao_order_id_sn,
                        'refund_fee' => $row->visit_cost / 100,
                        'refund_notify_url' => $notifyUrl,
                        'refund_out_trade_no' => $miao_order_id_sn . '_refund',
                        'refund_desc' => '挂号退款',
                    ];
                    $refundRes = $sdk::refund($params);
                    if ($refundRes['code'] == 1) {

                    } else {
                        throw new \Exception($refundRes['msg']);
                    }


                } else {
                    throw new \Exception('状态异常');
                }
            } else {
                throw new \Exception('订单不存在');
            }
            return ['code' => 1];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }


    /**
     * 获取取消规则
     * @param $doctor_id
     * @param $tp_platform
     * @param $visit_time
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/3
     */
    public static function getCancelTime($doctor_id, $tp_platform, $visit_time)
    {
        $data = ['allowed_cancel' => 0, 'allowed_cancel_time' => '12:00', 'allowed_cancel_day' => '1'];

        if (!empty($doctor_id) && !empty($tp_platform)) {
            //医生信息
            $doctor_info = DoctorModel::getInfo($doctor_id);
            if (!empty($doctor_info)) {
                //医院信息
                $hospital_id = $doctor_info['hospital_id'];
                $hospital_info = BaseDoctorHospitals::HospitalDetail($hospital_id);
                if (!empty($hospital_info) && !empty($hospital_info['tb_third_party_relation'])) {
                    $hospital_tp_platform = array_column($hospital_info['tb_third_party_relation'], null, 'tp_platform');
                    if (array_key_exists($tp_platform, $hospital_tp_platform)) {
                        $data['allowed_cancel_day'] = $hospital_tp_platform[$tp_platform]['tp_allowed_cancel_day'] ?? $data['allowed_cancel_day'];
                        $data['allowed_cancel_time'] = $hospital_tp_platform[$tp_platform]['tp_allowed_cancel_time'] ?? $data['allowed_cancel_time'];
                    }
                }
            }
        }

        if (!empty($visit_time)) {
            $day = ' -' . $data['allowed_cancel_time'] . ' day';
            if (time() < strtotime($visit_time . ' ' . $data['allowed_cancel_time'] . $day)) {
                $data['allowed_cancel'] = 1;
            }
        }

        return $data;
    }

    /**
     * 格式化订单状态
     * @param $state
     * @param $pay_status
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function formatGuahaoState($state, $pay_status)
    {
        if ($state == 5) {
            return '待支付';
        } elseif ($state == 1 && $pay_status == 6) {
            return '已退款';
        } elseif ($state == 1) {
            return '已取消';
        } elseif ($state == 0) {
            return '待就诊';
        } elseif ($state == 3) {
            return '已完成';
        } elseif ($state == 4) {
            return '爽约';
        } elseif ($state == 2) {
            return '已停诊';
        } elseif ($state == 8) {
            return '待审核';
        }
        return '';
    }

    /**
     * 更新缓存
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/7
     */
    public static function UpdateInfo($doctor_id = 0, $hospital_id = 0)
    {
        DoctorModel::getInfo($doctor_id, 1);
        $model = new BuildToEsModel();
        $model->db2esByIdDoctor($doctor_id);
        if ($hospital_id) {
            $model->db2esByIdHospital($hospital_id);
            BaseDoctorHospitals::HospitalDetail($hospital_id, true);
        }
    }

    /**
     * 更新医院缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-25
     * @version 1.0
     * @param   integer    $hospital_id [description]
     */
    public static function UpHospitalCache($hospital_id = 0)
    {
        $buildToEsModel= new BuildToEsModel();
        $buildToEsModel->db2esByIdHospital($hospital_id);
        BaseDoctorHospitals::HospitalDetail($hospital_id,true);
    }

    /**
     * 异步更新医院科室缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @param   integer    $doctor_id   [description]
     * @param   integer    $hospital_id [description]
     */
    public static function UpDeparmentCacheJob($doctor_id = 0, $hospital_id = 0)
    {
        \Yii::$app->slowqueue->push(new DeparmentCacheJob(['doctor_id'=>$doctor_id,'hospital_id'=>$hospital_id]));
    }

    /**
     * 异步拉取医生出诊地队列
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-19
     * @version 1.0
     * @param   string     $doctor_id [description]
     * @param   string     $tp_doctor_id [description]
     * @param   integer    $tp_platform  [description]
     * @return  [type]                   [description]
     */
    public static function getDoctorVisitPlace($doctor_id = 0,$tp_doctor_id ='',$tp_platform = 6)
    {
        \Yii::$app->queue->push(new VisitPlaceJob(['doctor_id'=>$doctor_id,'tp_doctor_id'=>$tp_doctor_id]));
    }

    /**
     * 更新医生出诊机构
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @param   array      $postData [description]
     * @return  [type]               [description]
     */
    public static function upDoctorVisitPlace($postData = [])
    {
        \Yii::$app->queue->push(new UpVisitPlaceJob(['postData'=>$postData]));
    }

    /**
     * 更新医院以及医院下医生es队列
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @param   integer    $hospital_id   [description]
     * @param   integer    $delete_doctor [description]
     * @return  [type]                    [description]
     */
    public static function upHospitalDoctorEsData($hospital_id = 0,$delete_doctor = 1)
    {
        \Yii::$app->slowqueue->push(new DeleteHospitalDoctorJob(['hospital_id'=>$hospital_id,'delete_doctor'=>$delete_doctor]));
    }

    /**
     * 更新医生保存之后的信息变更
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-19
     * @version v1.0
     * @param   [type]     $insert            [description]
     * @param   [type]     $changedAttributes [description]
     * @return  [type]                        [description]
     */
    public static function upAfterSaveJobData($doctor_id = 0, $hospital_id = 0)
    {
        \Yii::$app->queue->push(new AfterSaveDoctorJob(['doctor_id'=>$doctor_id,'hospital_id'=>$hospital_id]));
    }

    /**
     * 更新医生排班信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-01
     * @version v1.0
     * @param   array      $params [doctor_id,tp_platform]
     * @param   integer    $delay  [description]
     * @return  [type]             [description]
     */
    public static function updateScheduleCacheByDoctor($params = [],$delay = 100)
    {
        if ($delay) {
            \Yii::$app->slowqueue->delay($delay)->push(new upDoctorScheduleJob(['params' => $params]));
        }else{
            \Yii::$app->slowqueue->push(new upDoctorScheduleJob(['params' => $params]));
        }
        
    }


    /**
     * 禁用医院后 删除排班（1）
     * @param $tp_doctor_id
     * @param $doctor_id
     * @param $tp_platform
     * @param $admin_name
     * @param $admin_id
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-14
     */
    public static function deleteScheduleJob($tp_platform, $tp_hospital_code,$admin_name,$admin_id)
    {
        \Yii::$app->delschedule->push(new DeleteScheduleJob([
            'tp_hospital_code'=>$tp_hospital_code,
            'tp_platform'=>$tp_platform,
            'admin_name'=>$admin_name,
            'admin_id'=>$admin_id,
        ]));
    }

    /**
     *  删除医生排班
     * @param $tp_doctor_id
     * @param $doctor_id
     * @param $tp_platform
     * @param $admin_name
     * @param $admin_id
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-15
     */
    public static function deleteDoctorSchedule($tp_doctor_id, $doctor_id, $tp_platform, $admin_name, $admin_id)
    {
        \Yii::$app->delschedule2->push(new DeleteDoctorScheduleJob([
            'tp_doctor_id'=>$tp_doctor_id,
            'doctor_id'=>$doctor_id,
            'tp_platform'=>$tp_platform,
            'admin_name'=>$admin_name,
            'admin_id'=>$admin_id,
        ]));
    }


    /**
     * 超简单带样式的excel导出
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-25
     * @version 1.0
     * @param   array      $data      [数据二维]
     * @param   array      $keys      [标题]
     * @param   string     $file_name [文件名]
     * @return  [type]                [description]
     */
    public static function down_xls($data = [], $keys = [], $file_name = '')
    {
        $xls = "<html><meta http-equiv=content-type content=\"text/html;charset=UTF-8\"><body><table border='1'>";
        $xls .= "<tr><td>" . implode("</td><td>", $keys) . "</td></tr>";
        $xls .= implode("", array_map(function ($x) {
            return '<tr><td>' . implode("</td><td>", $x) . '</td></tr>';
        }, $data));
        $xls .= "</table></body></html>";
        header("Content-Type:application/vnd.ms-excel");
        if (!$file_name) {
            $file_name = date("Y-m-d H:i:s");
        }
        header("Content-Disposition:attachment;filename=" . $file_name . ".xlsx");
        die($xls);
    }  

    /**
     * 保存csv文件并返回
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-19
     * @version v1.0
     * @param   string     $csv_data  [description]
     * @param   string     $file_name [保存文件需要传全路径]
     * @return  [type]                [description]
     */
    public static function export_csv($csv_data = '', $file_name = '', $is_down = 0) 
    {
        $file_name = empty($file_name) ? date('Y-m-d-H-i-s', time()) : $file_name;
        $file_name.= '.csv';
        if ($is_down) {
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=" . $file_name);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            echo $csv_data;
            exit;
        }else{
            file_put_contents($file_name, $csv_data);
            return $file_name;
        }
    }

    /**
     * 生成多级目录
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-07
     * @version 1.0
     * @param   string     $path [description]
     * @return  [type]           [description]
     */
    public static function createDir($path = '')
    {
        if (!$path) {
            return false;
        }
        return is_dir($path) || mkdir($path, 0755, true);
    }

    /**
     * 检查是否是测试医生
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-12
     * @version v1.0
     * @param   string     $realname [description]
     * @return  boolean              [description]
     */
    public static function isDemoDoctor($realname = '')
    {
        $realname = mb_strtolower($realname);
        $demo_arr = CommonFunc::$ceshi_doctor;
        foreach ($demo_arr as $value) {
            if (strpos($realname, $value) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否是空图或者默认图
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-17
     * @version v1.0
     * @param   string     $source_avatar [description]
     * @return  [type]                    [description]
     */
    public static function filterSourceAvatar($source_avatar = '')
    {
        if (strpos($source_avatar, 'nopho') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, 'doctor_default') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, '/false') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, '_image140') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, '/null') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, 'doctor_logo') !== false) {
            $source_avatar = '';
        }
        if (strpos($source_avatar, '_fullsize') !== false) {
            $source_avatar = '';
        }
        return $source_avatar;
    }

    /*
     * 获取挂号医院初始化附属信息
     * @param $tp_platform
     * @param $tp_hospital_code
     * @return string[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/8
     */
    public static function getHospitalGuahaoinfo($tp_platform, $tp_hospital_code)
    {
        $tp_platform_arr = ['1' => 'henan', '2' => 'nanjing', '3' => 'haodaifu'];

        //河南各医院取消时间
        $cancelTimeArr = [
            'henan' => [
                '0' => ['time' => '14:30'],//默认
                '2020925004' => ['time' => '14:00'],//郑州大学第一附属医院
                '2020925001' => ['time' => '16:00'],//河南省人民医院
                '2020925002' => ['time' => '16:00'],//河南中医药大学第一附属医院
            ],
            'nanjing' => [
                '0' => ['time' => '12:00'],//默认
                'YA1211276' => ['time' => '12:00', 'day' => '2'],//南京长江医院
                '426070495' => ['time' => '12:00', 'day' => '2'],//南京市溧水区中医院
                '426061150' => ['time' => '12:00', 'day' => '2'],//南京市六合区中医院
                '32040700' => ['time' => '12:00', 'day' => '2'],//南京市浦口区中心医院
                '425850238' => ['time' => '12:00', 'day' => '2'],//南京市红十字医院
                '426032421' => ['time' => '12:00', 'day' => '2'],//南京市江宁区中医医院
                'E93792052' => ['time' => '12:00', 'day' => '2'],//南京江北人民医院
                '425802367' => ['time' => '12:00', 'day' => '2'],//南京市祖堂山精神病院
                '787104988' => ['time' => '12:00', 'day' => '2'],//南京博大肾科医院
                '32017100' => ['time' => '12:00', 'day' => '2'],//南京明基医院
                '32017000' => ['time' => '12:00', 'day' => '2'],//南京同仁医院
                '425802359' => ['time' => '12:00', 'day' => '2'],//南京市青龙山精神病院
                '771261508' => ['time' => '12:00', 'day' => '2'],//南京医科大学友谊整形外科医院
                '32012000' => ['time' => '12:00', 'day' => '2'],//南京市中心医院(南京市市级机关医院)
            ],
            'haodaifu' => [
                '0' => ['time' => '18:00'],//默认
            ]
        ];

        $guahaoDescription = [
            'nanjing' => [
                '0' => "应南京12320要求，本院挂号需填写南京12320密码；\n未注册12320的用户可忽略不填写；\n忘记密码，可通过12320官网找回密码；",
            ],
            'haodaifu' => [
                '0' => "温馨提示：您目前预约的医院为民营医院\n取消预约：就诊前一天18：00取消，全额退款，18：00后及爽约不退款",
            ],
        ];

        $data = [
            'tp_allowed_cancel_day' => ArrayHelper::getValue($cancelTimeArr, $tp_platform_arr[$tp_platform] . '.' . $tp_hospital_code . '.day', ArrayHelper::getValue($cancelTimeArr, $tp_platform_arr[$tp_platform] . '.0.day', '1')),
            'tp_allowed_cancel_time' => ArrayHelper::getValue($cancelTimeArr, $tp_platform_arr[$tp_platform] . '.' . $tp_hospital_code . '.time', ArrayHelper::getValue($cancelTimeArr, $tp_platform_arr[$tp_platform] . '.0.time', '12:00')),
            'tp_guahao_description' => ArrayHelper::getValue($guahaoDescription, $tp_platform_arr[$tp_platform] . '.' . $tp_hospital_code, ArrayHelper::getValue($guahaoDescription, $tp_platform_arr[$tp_platform] . '.0', '')),
        ];

        return $data;

    }

    /**
     * 医院单个列表html
     * @param $row  医院单条数据
     * @param $type 样式类别
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/31
     */
    public static function returnHospView($row,$type=1){

        $km = ArrayHelper::getValue($row,'sort.3');
        if ($km != 'Infinity') {
            if($km<1){
                $km =intval($km*1000).'m';
            }elseif ($km>1 && $km<100) {
                $km =number_format($km, 1).'km';
            }else{
                $km = '>99km';
            }
        }else{
            $km = '';
        }
        

        $html = '';
        if ($type == 1) {
            $html .= '<div class="item">';
            if (ArrayHelper::getValue($row,'hospital_is_plus') == 1) {
                $url = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['guahao/keshilist', 'hospital_id' => $row['hospital_id']]);
            } else  {
                $url = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['/hospital/index', 'hospital_id' => $row['hospital_id']]);   
            }

            //王氏埋点
            $maidian =  "{'click_id':'挂号-M医院列表-去挂号按钮' , 'click_url':'".$url."'}";

            $html .= '<a onclick="_maq.click('.$maidian.')"  href="' . $url. '">';
            $html .= '    <div class="detailFlex">';
            $html .= '        <div class="detailImg"><img src="' . ArrayHelper::getValue($row, 'hospital_photo') . '"  alt="' . ArrayHelper::getValue($row, 'hospital_name') . '" onerror="javascript:this.src='."'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'".';"></div>';
            $html .= '        <div class="detailInfo">';
            $html .= '            <p class="title">' . ArrayHelper::getValue($row, 'hospital_name') . '</p>';
            $html .= '            <div class="label_style">';
            if(ArrayHelper::getValue($row, 'hospital_level_alias'))
            {
                $html .= '                <span>' . ArrayHelper::getValue($row, 'hospital_level_alias') . '</span>';
            }
            if(ArrayHelper::getValue($row, 'hospital_type'))
            {
                $html .= '                <span>'. ArrayHelper::getValue($row, 'hospital_type') .'</span>';
            }
            $html .= '                <em>'.$km.'</em>';
            $html .= '            </div>';
            $html .= '        </div>';

            if(ArrayHelper::getValue($row,'hospital_is_plus') == 1){
                $html .= '        <div class="detailLink"><span class="guahao_link">去挂号</span></div>';
            }else{
                $html .= '        <div class="detailLink"><span class="guahao_link2">未开通</span></div>';
            }

            $html .= '    </div>';
            if(ArrayHelper::getValue($row,'hospital_fudan_order')) {
                $html .= '    <div class="label_ph"><span class="paihang">复旦版全国医院排行No.'.ArrayHelper::getValue($row,'hospital_fudan_order').'</span><span class="score">'.ArrayHelper::getValue($row,'hospital_fudan_score').'分</span></div>';
            }
            $html .= '</a>';
            $html .= '</div>';
        }
        if ($type == 3) {

            //王氏埋点
            $maidian = "{'click_id':'挂号-M医生列表-有号按钮' , 'click_url':'". rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospital/index', 'hospital_id' => arrayHelper::getValue($row, 'hospital_id')])."'}";

            $html .= '<div class="new_host_list_con">';
            $html .= '<div class="dflex">';
            $html .= '<div class="left_img">';
            if (ArrayHelper::getValue($row, 'hospital_photo')) {
                $hospital_photo = ArrayHelper::getValue($row, 'hospital_photo');
            } else {
                $hospital_photo = 'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';
            }
            $html .= '<img src="' . $hospital_photo . '" alt="' . ArrayHelper::getValue($row, 'hospital_name') . '" onerror="javascript:this.src='."'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'".';">';
            $html .= '</div>';
            $html .= '<div class="flex1">';
            $html .= '<div class="right_t">';
            $html .= '<a onclick="_maq.click('.$maidian.')" href="' . Url::to(['/hospital/index', 'hospital_id' => $row['hospital_id']]) . '">';
            $html .= '<p class="text_over1 bt_p">' . ArrayHelper::getValue($row, 'hospital_name') . '</p>';
            $html .= '<div class="tags_box">';
            if (ArrayHelper::getValue($row, 'hospital_level_alias')) {
                $html .= '<span>' . ArrayHelper::getValue($row, 'hospital_level_alias') . '</span>';
            }
            if (ArrayHelper::getValue($row, 'hospital_type')) {
                $html .= '<span>' . ArrayHelper::getValue($row, 'hospital_type') . '</span>';
            }
            if (ArrayHelper::getValue($row, 'hospital_kind')) {
                $html .= '<span>' . ArrayHelper::getValue($row, 'hospital_kind') . '</span>';
            }
            $html .= '<i>'.$km.'</i>';
            $html .= '</div>';
            if(ArrayHelper::getValue($row,'hospital_fudan_order'))
            {
                $html .= '<div class="fdph_box">复旦版全国医院排行No.'.ArrayHelper::getValue($row,'hospital_fudan_order').' | '.ArrayHelper::getValue($row,'hospital_fudan_order').'分</div>';
            }

            $html .= '</a>';
            $html .= '</div>';

            $html .= '<div class="right_c ovH">';
            $html .= '<span class="fl">';
            if (!empty($row['doctor_second_department_name'])) {
                $html .= $row['doctor_second_department_name'];
            }
            $html .= '</span>';
            if(ArrayHelper::getValue($row,'department_is_plus') == 1)
            {
                $html .= '<a href="' . Url::to(['guahao/doclist', 'hospital_id' => $row['hospital_id'], 'tp_department_id' => ArrayHelper::getValue($row,'doctor_department_relation_id')]) . '" class="ljgh_span">立即挂号</a>';
            }else{
                $html .= '<a class="ljgh_span2">无号</a>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }


        return $html;
    }

    /**
     *Notes:医生列表单个html
     *User:lixiaolong
     *Date:2021/1/4
     *Time:10:31
     */
    public static function returnDoclHtml($row, $type = 1, $shence_type = 1)
    {
        if (ArrayHelper::getValue($row, 'realname')) {
            $row['doctor_realname'] = ArrayHelper::getValue($row, 'realname');
        }
        $html = '';
        if ($type == 1) {

            //王氏埋点
            $maidian = "{'click_id':'挂号-M医生列表-有号按钮' , 'click_url':'" . rtrim(\Yii::$app->params['domains']['mobile'], '/') . Url::to(['doctor/home', 'doctor_id' => arrayHelper::getValue($row, 'doctor_id')]) . "'}";
            $doctor_good_at = ArrayHelper::getValue($row, 'doctor_good_at') ? '擅长：' . ArrayHelper::getValue($row, 'doctor_good_at') : '';
            $doctor_real_plus = ArrayHelper::getValue($row, 'doctor_real_plus') ? '去挂号' : '';
            $doctor_shence_real_plus = $doctor_real_plus == '有号' ? '有号' : '无号';

            $html.= '<a onclick="_maq.click(' . $maidian . ')" href="' . Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($row, 'doctor_id')]) . '" class=list_item_wrap>';
            $html.=     '<div class=doc_photo> <img src="' . ArrayHelper::getValue($row, 'doctor_avatar') . '" onerror="javascript:this.src=' . "'https://u.nisiyacdn.com/avatar/default_2.jpg'" . ';" alt="' . Html::encode(ArrayHelper::getValue($row, 'doctor_realname')) . '"></div>';
            $html.=     '<div class=doc_content>';
            $html.=         '<div class=doc_info>';
            $html.=             '<div>';
            $html.=                 '<span class=doc_name>'. ArrayHelper::getValue($row, 'doctor_realname') .'</span>';
            $html.=                 '<span class=doc_title>'. ArrayHelper::getValue($row, 'doctor_title') .'</span>';
            $html.=             '</div>';
            $html .= '<span class=btn_little>去挂号</span>';
            $html.=         '</div>';
            $html.=         '<div class="doc_text text_wrap">'. ArrayHelper::getValue($row, 'doctor_second_department_name')  .' | '. ArrayHelper::getValue($row, 'doctor_hospital') .'</div>';            //如果是民营医院出诊类型必有的，医生标签可能为空，公立医院出诊类型和医生标签都为空
            if (isset($row['doctor_visit_type']) && !empty($row['doctor_visit_type'])){
                $html.=         '<div class=doc_tags>';
                $html.=             '<span class="tags t_style02">'.$row['doctor_visit_type'].'</span>';
                if (isset($row['doctor_tags']) && !empty($row['doctor_tags'])) {
                    $doctor_tags = explode('、',$row['doctor_tags']);
                    foreach ($doctor_tags as $doctor_tag) {
                        $html .= '<span class="tags t_style02">' . $doctor_tag . '</span>';
                    }
                }
                $html.=         '</div>';
            }
            $html.=         '<p class="doc_descript text_over2">'. $doctor_good_at .'</p>';
            $html.=     '</div>';
            $html.= '</a>';
        }
        return $html;
    }

    /**
     * 民营医院账号相关短信通知
     * @param $type
     * @param MinAccountModel $model
     * @param array $params
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-28
     * @return bool
     */
    public static function minPasswordSendSms($type, MinAccountModel $model, $params = [])
    {
        $template_map = [
            'create' => 'guahao_account_create',
            'reset' => 'guahao_account_reset',
        ];
        if (!in_array($type, array_keys($template_map))) {
            self::$passwordSendSmsErrorMsg = '短息模板不正确';
            return false;
        }

        $sms_sdk = new ServiceSdk();
        $msgCon = [
            '%enterprise_type%' => $model->type == MinAccountModel::TYPE_AGENCY ? '代理商版' : '医院版',
            '%account_number%' => $model->account_number,
            '%password%' => $params['password'] ?? '',
        ];

        $mobile = '';
        // 发送短信给医院联系人
        if ($model->type == MinAccountModel::TYPE_HOSPITAL) {
            $mobile = \common\models\minying\MinHospitalModel::find()
                ->where(['min_hospital_id' => $model->enterprise_id])
                ->select('min_hospital_contact_phone')
                ->scalar();
        }

        // 发送短信给代理商联系人
        if ($model->type == MinAccountModel::TYPE_AGENCY) {
            $mobile = \common\models\minying\MinAgencyModel::find()
                ->where(['agency_id' => $model->enterprise_id])
                ->select('contact_mobile')
                ->scalar();
        }

        if (!$mobile) {
            self::$passwordSendSmsErrorMsg = '接受短信手机号不能为空';
            return false;
        }

        if (!$sms_sdk->send($mobile, $template_map[$type], $msgCon)) {
            self::$passwordSendSmsErrorMsg = $sms_sdk->errorMsg;
            return false;
        }
        return true;
    }

    /**
     * 订单操作时发送短信
     * @param $type
     * @param $order_sn
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-01订单
     * @return bool
     */
    public static function minCancelOrderSendSms($type, $order_sn)
    {
        $template_map = [
            'cancel' => 'guahao_hospital_cancel'
        ];

        $model = GuahaoOrderModel::findOne(['order_sn' => $order_sn]);
        if (!$model) {
            self::$orderSendSmsErrorMsg = '未找到订单信息';
            return false;
        }
        if (!in_array($type, array_keys($template_map))) {
            self::$orderSendSmsErrorMsg = '短息模板不正确';
            return false;
        }

        $sms_sdk = new ServiceSdk();

        $msgCon = [
            '%patient_name%' => $model->patient_name,
            '%hospital_name%' => $model->hospital_name,
            '%keshi_name%' => $model->department_name,
            '%doctor_name%' => $model->doctor_name,
            '%visit_time%' => date('Y年m月d日', strtotime($model->visit_time)) . (GuahaoOrderModel::$visit_nooncode[$model->visit_nooncode] ?? ''),
        ];

        if (!$sms_sdk->send($model->mobile, $template_map[$type], $msgCon)) {
            self::$orderSendSmsErrorMsg = $sms_sdk->errorMsg;
            return false;
        }
        return true;
    }

    /**
     * 挂号发送短信
     * @param $type
     * @param $order_sn
     * @return false
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/3
     */
    public static function guahaoSendSms($type, $order_sn, $stop_type = '', $stop_desc = '')
    {
        $visit_nooncode = ['1'=>'上午','2'=>'下午','3'=>'晚上'];
        if (!in_array($type, ['guahao_success', 'guahao_cancel', 'guahao_stop', 'guahao_baidu_success'])) {
            return false;
        }

        $orderModel = GuahaoOrderModel::find()->where(['order_sn' => $order_sn])->one();
        //  下单后只有下单成功状态才发短信
        if (in_array($type, ['guahao_success', 'guahao_baidu_success']) && $orderModel->state != 0) {
            return false;
        }
        if (!$orderModel) {
            return false;
        }

        if (in_array($orderModel->tp_platform, [2, 4, 5, 7, 8, 9, 10, 12, 13]) || $orderModel->coo_platform == 1) {
            $smsSdk = new ServiceSdk();
            $patient_name = mb_substr($orderModel->patient_name, 1,3,'utf-8');
            $date = date('Y年m月d日', strtotime($orderModel->visit_time));
            $nooncode = ArrayHelper::getValue($visit_nooncode,$orderModel->visit_nooncode);
            $date = $date.$nooncode;
            $msgCon = [
                '%hospital_name%' => $orderModel->hospital_name,
                '%visit_time%' => $date,
                '%keshi_name%' => $orderModel->department_name,
                '%doctor_name%' => $orderModel->doctor_name,
            ];

            $infoQuery = [];
            if ($orderModel) {
                $infoQuery = GuahaoOrderInfoModel::find()->where(['order_id' => $orderModel->id])->one();
            }
            //guahao_success  guahao_cancel  guahao_baidu_success
            if ($type == 'guahao_success' || $type == 'guahao_cancel' || $type == 'guahao_baidu_success') {
                $msgCon['%department_name%'] = $orderModel->department_name;
                $msgCon['%patient_name%'] = '*' . $patient_name;

                if(ArrayHelper::getValue($infoQuery, 'visit_number')){
                    $code = '您的取号码'.ArrayHelper::getValue($infoQuery, 'visit_number').'，';
                }else{
                    $code = '';
                }
                $msgCon['%visit_number_desc%'] = $code;

                //百度短信模板新增第三方来源平台名称
                if ($type == 'guahao_baidu_success') {
                    $msgCon['%platform_name%'] = GuahaoPlatformListModel::getPlatformNameByTpPlatform($orderModel->tp_platform);
                }
            } elseif ($type == 'guahao_stop') {
                $msgCon['%content%'] = $stop_type;
                $msgCon['%desc%'] = $stop_desc;
            }
            // 拼接 就诊时间 （预约成功， 取消预约， 停诊）
            if (ArrayHelper::getValue($infoQuery, 'visit_starttime')) {
                $msgCon['%visit_time%'] .= ArrayHelper::getValue($infoQuery, 'visit_starttime');
            }
            if (ArrayHelper::getValue($infoQuery, 'visit_endtime')) {
                $msgCon['%visit_time%'] .= "-".ArrayHelper::getValue($infoQuery, 'visit_endtime');
            }

            $smsSdk->send($orderModel->mobile, $type, $msgCon);
            return false;
        }
    }

    /**
     * 二维数组排序
     * @param $array
     * @param $keys
     * @param int $sort
     * @return mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/4/21
     */
    public static function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $last_names = array_column($array, $keys);
        array_multisort($last_names, $sort, $array);
        return $array;
    }

    /**
     * 格式化放号时间
     * @param int $open_day
     * @param string $open_time
     * @param int $type 1：医院组件；2：科室号源页
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/5/10
     */
    public static function openTimeStr($open_day = 0, $open_time = '', $type = 1)
    {
        if (empty($open_day) || empty($open_time)) {
            $open_day = 7;
            $open_time = '08:30';
        }

        if ($type == 2) {
            $date = date('m月d日', strtotime(" +$open_day day"));
            return "{$open_time}放{$date}号源";
        } else {
            return "{$open_time}放第{$open_day}天号源";
        }
    }

    /**
     * 挂号业务队列
     * @param $id  业务id
     * @param $type 业务类型  1：医生  2：订单  3：号源
     * @param $action 操作  type=1 (add:新增医生  edit:医生修改  del:禁用/删除)
     * @param $tp_platform 来源
     * @param $from 脚本推送来源
     * @return false
     * @author xiujianying
     * @date 2021/6/19
     */
    public static function guahaoPushQueue($id, $type, $action='',$tp_platform=0)
    {
        if ($id && $type) {
            $queue['id'] = $id;
            $queue['type'] = $type;
            $queue['action'] = $action;
            $is_push = false;
            //优先判断来源是否有合作
            if ($tp_platform) {
                $cooArr = GuahaoPlatformModel::getCoo($tp_platform);
                if ($cooArr) {
                    $is_push = true;
                }
            } else {
                $is_push = false;
            }
            if ($is_push) {
                //订单单独队列推送
                if ($type == 2) {
                    $job_id = \Yii::$app->guahaopush2->delay(10)->push(new GuahaoPushJob($queue));
                } else {
                    $job_id = \Yii::$app->guahaopush->delay(10)->push(new GuahaoPushJob($queue));
                }
                return $job_id;
            }


        } else {
            return false;
        }

    }

    /**
     * aes解密
     * @param $secretData
     * @param $aesSecretKey
     * @param string $aesIv
     * @return false|string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/22
     */
    public static function aesDecode($secretData, $aesSecretKey, $aesIv = '')
    {
        return openssl_decrypt(base64_decode($secretData), 'aes-256-ecb', $aesSecretKey, OPENSSL_RAW_DATA, $aesIv);
    }

    /**
     * @param $data
     * @param $aesSecretKey
     * @param string $aesIv
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/22
     */
    public static function aesEncode($data, $aesSecretKey, $aesIv = '')
    {
        return base64_encode(openssl_encrypt($data, 'aes-256-ecb', $aesSecretKey, OPENSSL_RAW_DATA, $aesIv));
    }

    /**
     * 获取用户id和就诊人id
     * @param $guahaoData
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public static function getGuahaoUserId($guahaoData)
    {
        $GuahaoCooInterrogationData = GuahaoCooInterrogationModel::getInfo($guahaoData);
        //注册用户
        if (empty($GuahaoCooInterrogationData)) {
            //获取uid
            Config::setConfig(\Yii::$app->params['usersdk']);
            $loginSdk = new LoginSdk();
            $httpData = $loginSdk->mobilelogin(ArrayHelper::getValue($guahaoData, 'mobile'), '', 'hospital', 'user', '', '', '1');
            //记录日志
            $logData = [
                'curlAction' => '----------------请求第三方接口----------------',
                'curlUrl' => \Yii::$app->params['usersdk']['urlmapping']['ucenter'] . 'mobilelogin',
                'curlParams' => ArrayHelper::getValue($guahaoData, 'mobile'),
                'curlReturnData' => $httpData
            ];
            \Yii::$app->params['DataToHospitalRequest']['curlList'][] = $logData;
            $httpData = json_decode($httpData, true);
            if (isset($httpData['code']) && $httpData['code'] == 200) {
                $GuahaoCooInterrogationData['uid'] = ArrayHelper::getValue($httpData, 'data.userInfo.uid');
            }
            //获取patient_id
            /*if (!empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid'))) {
                $patientParams = [];
                $patientParams['user_id'] = $GuahaoCooInterrogationData['uid'];
                $patientParams['realname'] = ArrayHelper::getValue($guahaoData, 'patient_name');
                $patientParams['sex'] = ArrayHelper::getValue($guahaoData, 'gender');
                $patientParams['id_card'] = ArrayHelper::getValue($guahaoData, 'card');
                $patientParams['tel'] = ArrayHelper::getValue($guahaoData, 'mobile');
                $patientParams['birth_time'] = ArrayHelper::getValue($guahaoData, 'birth_time');
                $patientParams['province'] = ArrayHelper::getValue($guahaoData, 'province');
                $patientParams['city'] = ArrayHelper::getValue($guahaoData, 'city');
                $patientParams['is_real_auth'] = 0;
                $patientParams['is_filter_auth'] = 1;
                $patientData = PihsSDK::getInstance()->interrogationDetailAndAdd($patientParams);
                if (isset($patientData['code']) && $patientData['code'] == 'success') {
                    $GuahaoCooInterrogationData['patient_id'] = ArrayHelper::getValue($patientData, 'data.id');
                }
            }

            //异步保存信息
            if (!empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid')) && !empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'patient_id'))) {
                $saveCooInterrogationData = [];
                $saveCooInterrogationData['card'] = ArrayHelper::getValue($guahaoData, 'card');
                $saveCooInterrogationData['mobile'] = ArrayHelper::getValue($guahaoData, 'mobile');
                $saveCooInterrogationData['patient_id'] = ArrayHelper::getValue($GuahaoCooInterrogationData, 'patient_id');
                $saveCooInterrogationData['uid'] = ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid');
                $saveCooInterrogationData['coo_platform'] = ArrayHelper::getValue($guahaoData, 'coo_platform');
                $saveCooInterrogationData['coo_patient_id'] = ArrayHelper::getValue($guahaoData, 'coo_patient_id');
                \Yii::$app->ghcoopatient->push(new GuahaoCooInterrogationJob(['data' => $saveCooInterrogationData]));
            }*/

            $GuahaoCooInterrogationData = self::addInterrogationData($GuahaoCooInterrogationData, $guahaoData);
        }

        return $GuahaoCooInterrogationData;
    }

    public static function addInterrogationData($GuahaoCooInterrogationData, $guahaoData)
    {
        if (!empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid'))) {
            //获取patient_id
            $patientParams = [];
            $patientParams['user_id'] = $GuahaoCooInterrogationData['uid'];
            $patientParams['realname'] = ArrayHelper::getValue($guahaoData, 'patient_name');
            $patientParams['id_card'] = ArrayHelper::getValue($guahaoData, 'card');
            $patientParams['tel'] = ArrayHelper::getValue($guahaoData, 'mobile');
            $patientParams['is_real_auth'] = 0;
            $patientParams['is_filter_auth'] = 1;
            $patientData = PihsSDK::getInstance()->interrogationDetailAndAdd($patientParams);
            if (isset($patientData['code']) && $patientData['code'] == 'success') {
                $GuahaoCooInterrogationData['patient_id'] = ArrayHelper::getValue($patientData, 'data.id');
            }
        }

        //异步保存信息
        if (!empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid')) && !empty(ArrayHelper::getValue($GuahaoCooInterrogationData, 'patient_id'))) {

            $saveCooInterrogationData = [];
            $saveCooInterrogationData['card'] = ArrayHelper::getValue($guahaoData, 'card');
            $saveCooInterrogationData['mobile'] = ArrayHelper::getValue($guahaoData, 'mobile');
            $saveCooInterrogationData['patient_id'] = ArrayHelper::getValue($GuahaoCooInterrogationData, 'patient_id');
            $saveCooInterrogationData['uid'] = ArrayHelper::getValue($GuahaoCooInterrogationData, 'uid');
            $saveCooInterrogationData['coo_platform'] = ArrayHelper::getValue($guahaoData, 'coo_platform');
            $saveCooInterrogationData['coo_patient_id'] = ArrayHelper::getValue($guahaoData, 'coo_patient_id');
            $saveCooInterrogationData['coo_user_id'] = ArrayHelper::getValue($guahaoData, 'coo_user_id', '');
            \Yii::$app->ghcoopatient->push(new GuahaoCooInterrogationJob(['data' => $saveCooInterrogationData]));
        }

        return $GuahaoCooInterrogationData;
    }

    /**
     * 根据fiedata 上传oss
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-07
     */
    public static function uploadAvatarOssByFile()
    {

        $uploadImage = UploadedFile::getInstanceByName('Filedata');
        $content = '';
        $suffix = explode('/', $uploadImage->type)[1];
        $isFalse = self::checkDoctorAvatarSuffix($suffix);
        if(false == $isFalse){
            echo json_encode(array('error' => 1, 'message' => '请上传标准图片文件, 支持gif,jpg,png和jpeg！!!!!'));
        }else{
            $fp = fopen($uploadImage->tempName, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $content .= fgets($fp, 8888);
                }
            }
            $params['platform'] = 'nisiya';
            $params['type']     = 'guahao';
            $params['path']     = 'doctor_avatar';
            $params['fileDate'] = '';
            $params['file']     = base64_encode($content);

            $result = BapiAdSdkModel::getInstance()->uploadOss($params);

            if(false != $result){
                if($result['code'] == 200){
                    echo json_encode(array('error' => 0, 'url' => $result['data']['img_path'], 'img_path' => $result['data']['img_path'], 'img_url' => $result['data']['img_url']));
                } else {
                    echo json_encode(array('error' => 1,'message'=>$result['msg']));
                }

            }else{
                echo json_encode(array('error' => 1, 'message' => '请上传标准图片文件, 支持gif,jpg,png和jpeg！'));
            }
        }

    }

    /**
     *  根据远程图片链接上传oss
     * @param $url
     * @param string $file_date
     * @return array|false|string|string[]
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-07
     */
    public static function uploadImageOssByUrl($url , $file_date='')
    {
        $params['platform'] = 'nisiya';
        $params['type']     = 'guahao';
        $params['path']     = 'doctor_avatar';
        $params['file']     = $url;
        $params['fileDate'] = trim(strval($file_date),'/');
        $result = BapiAdSdkModel::getInstance()->uploadOss($params);
        if(false != $result){
            if($result['code'] == 200){
                return ['img_path'=>trim(strval($result['data']['img_path']),'/'),'img_url'=>$result['data']['img_url']];
            } else {
                $result['img_path'] = '';
                return $result;
            }
        } else {
            return  ['img_path'=>'','img_url'=>''];
        }
    }

    /**
     *  医生头像上传前检测是否允许
     * @param $suffix
     * @return bool
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-16
     */
    public static function checkDoctorAvatarSuffix($suffix)
    {
        $fileExt = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strval($suffix), $fileExt)) {
            return true;
        }
        return false;
    }

    /*
     * 格式化导入字段
     * @param $content
     * @return string|string[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/13
     */
    public static function formatImportContent($content)
    {
        $content = strip_tags($content);
        $content = trim($content);
        return $content;
    }

    /**
     * 根据第三方地址上传头像
     * @param $url
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/23
     */
    public static function uploadDoctorAvatarByUrl($url)
    {
        $url = CommonFunc::filterSourceAvatar($url);
        if (empty($url)) {
            return '';
        }
        if ((strpos($url, 'http')) !== false) {
            //上传头像
            $img = CommonFunc::uploadImageOssByUrl($url);
            return $img['img_path'] ?? '';
        }
        return '';
    }

    /**
     * 隐藏字符串 用*代替部分内容
     * @param $str    字符串
     * @param $start  第几个位置开始用*号代替
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2021/11/10
     */
    public static function hiddenString($str = '', $start = 1)
    {
        if (empty($str)) return '';
        if ($start < 0) $start = 0;
        return mb_substr($str, 0, $start, 'utf-8') . str_repeat("*", (mb_strlen($str, 'utf-8') - $start));
    }

    /**
     * 记录refer
     * @param string $srefer
     * @param string $skey
     * @return false
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/11/12
     */
    public static function setReferCookie($srefer = '', $skey = '')
    {
        if (empty($srefer) || empty($skey)) {
            return false;
        }

        if (in_array($srefer, self::$allow_refer)) {
            $skeyLen = strlen($skey);
            if ($skeyLen > 50) {
                $skey = substr($skey, 0, 50);
            }
            $value = json_encode(['srefer' => $srefer, 'skey' => $skey]);

            $cookies = \Yii::$app->response->cookies;
            $expire = time() + 24 * 3600;
            $cookies->add(new Cookie([
                'name' => 'srefer',
                'value' => $value,
                'expire' => $expire,
                'domain' => '.nisiya.net'
            ]));
            return true;
        }

        return false;
    }

    /**
     * 获取refer
     * @return array|mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/11/12
     */
    public static function getReferCookie()
    {
        $cookies = \Yii::$app->request->cookies;
        $value = $cookies->getValue('srefer');
        if ($value) {
            return json_decode($value, 'true');
        }
        return [];
    }

    /**
     * 获取平台类型
     * @param string $status
     * @return array|mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/11
     */
    public static function getTpPlatformNameList($status = '')
    {
        $data = [];
        $list = GuahaoPlatformListModel::getPlatformListCache();
        if (!empty($list)) {
            if ($status !== '') {
                foreach ($list as $key => $value) {
                    if ($value['status'] != $status) {
                        unset($list[$key]);
                    }
                }
            }
            $data = array_column($list, 'platform_name', 'tp_platform');
        }
        return $data;
    }

    /**
     * 获取日志类型列表
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/31
     */
    public static function getLogPlatformNameList()
    {
        $list = [];

        //第三方平台类型 河南：1；河南回调：101；
        $tp_platform = GuahaoPlatformListModel::getTpPlatformList();
        if (!empty($tp_platform)) {
            $list = $list + $tp_platform;
            foreach ($tp_platform as $key => $value) {
                $list[$key + 100] = $value . "回调";
            }
        }

        //公共类型
        $list = $list + [100 => '挂号业务公共回调', 200 => '支付日志'];

        //合作方平台类型 百度：201；百度回调：301；
        $coo_platform = GuahaoCooListModel::getCooPlatformList();
        if (!empty($coo_platform)) {
            foreach ($coo_platform as $key => $value) {
                $list[$key + 200] = $value;
                $list[$key + 300] = "推送" . $value;
            }
        }

        //其他类型 400+

        return $list;
    }

    /**
     * 注册第三方用户信息
     * @return array
     * @throws \Exception
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022/03/03
     */
    public static function registerThirdUser($param)
    {
        //注册第三方用户信息
        $userRes = [
            'unique_id' => $param['unique_id'],
            'channel' => $param['channel'],
            'mobile' => $param['mobile'],
        ];
        $userResult = CenterSDK::getInstance()->registerthirdmember($userRes);
        return $userResult;
    }

    /**
     * 解析url参数
     *
     * @param    string    query
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022/03/08
     * @return    array    params
     */
    public static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    /**
     * 获取当前访问的完整url
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/05/18
     */
    public static function getUrl() {

        // 判断当前页采用的协议是HTTP还是HTTPS
        // 443端口：即网页浏览端口，主要用于HTTPS服务，是提供加密和通过安全端口传输的另一种HTTP。
        $url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        // 判断端口
        /**
         * REQUEST_URI：URI用来指定要访问的页面
         * SERVER_PORT：Web服务器使用的端口，默认为80
         */
        if($_SERVER['SERVER_PORT'] != '80') {
            $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
        return $url;
    }

    /**
     * 获取省列表
     * @return mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public static function getProvince(){
        $baseapiSdk = new BaseapiSdk();
        $provinceInfo = $baseapiSdk->getProvince();
        if (!empty($provinceInfo) && is_array($provinceInfo) && $provinceInfo['code'] == 200){
            return $provinceInfo['data'];
        } else {
            return '';
        }
    }

    /**
     * 根据省id获取城市列表
     * @param $province_id
     * @return mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public static function getCity($province_id){
        $baseapiSdk = new BaseapiSdk();
        $cityInfo = $baseapiSdk->getCity($province_id);
        if (!empty($cityInfo) && is_array($cityInfo) && $cityInfo['code'] == 200){
            return $cityInfo['data'];
        } else {
            return '';
        }
    }

    /**
     * 根据城市id获取县（区）列表
     * @param $city_id
     * @return mixed|string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public static function getDistrict($city_id){
        $baseapiSdk = new BaseapiSdk();
        $districtInfo = $baseapiSdk->getCity($city_id);
        if (!empty($districtInfo) && is_array($districtInfo) && $districtInfo['code'] == 200){
            return $districtInfo['data'];
        } else {
            return '';
        }
    }

    /**
     * 通过身份证号获取年龄
     * @param $id_number
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-28
     * @return string
     */
    public static function getAgeByIdCard($id_number)
    {
        $year = substr($id_number, 6, 4);
        $month = substr($id_number, 10, 2);
        $day = substr($id_number, 12, 2);

        $target_time = time() - strtotime("{$year}-{$month}-{$day}");

        $y = floor($target_time / 31536000);
        $m = round($target_time / 2592000);
        $w = round($target_time / 604800);
        $d = round($target_time / 86400);

        $str = '';
        if ($y > 0) {
            $str = "{$y}岁";
        } elseif ($m > 0) {
            $str = "{$m}个月";
        } elseif ($w > 0) {
            $str = "{$w}周";
        } elseif ($d > 0) {
            $str = "{$d}天";
        }
        return $str;
    }

    /**
     * 拼接图片域名
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-08-04
     * @return Array
     */
    public static function getDomainPic($domain, $pic='')
    {
        if (empty($pic)) return [];
        $picArr = explode(',', $pic);
        foreach ($picArr as &$val) {
            $val = $domain . $val;
        }
        return $picArr;
    }

    /**
     * 非百度经纬度转百度经纬度
     * @author yueyuchao <yueyuchao@yuanxinjituan.com>
     * @param string $gaodeLongitude 高德经度
     * @param string $gaodeLatitude  高德纬度
     * @return array
     * @date 2023-02-24
     */
    public static function gaode2BaiduGnote(string $gaodeLongitude, string $gaodeLatitude):array
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0; 
        $x = $gaodeLongitude;
        $y = $gaodeLatitude;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $lngs = $z * cos($theta) + 0.0065;
        $lats = $z * sin($theta) + 0.006;
        return [ 'lng' => $lngs, 'lat' => $lats ];
    }

    /**
     * 非高德经纬度转高德经纬度
     * @author yueyuchao <yueyuchao@yuanxinjituan.com>
     * @param string $baiduLongitude
     * @param string $baiduLatitude
     * @return array
     */
    public static function baidu2GaodeGnote(string $baiduLongitude, string $baiduLatitude):array
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $baiduLongitude - 0.0065;
        $y = $baiduLatitude - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta);
        $lat = $z * sin($theta);
        return array('lng'=>$lng,'lat'=>$lat);
    }

    /**
     * 根据两个地点的经纬度计算直线距离
     * @author yueyuchao <yueyuchao@yuanxinjituan.com>
     * @param array $origin 起点经纬度
     * $origin = [
     * 'lng' => '起点经度',
     * 'lat' => '起点纬度'
     * ];
     * @param array $destination 终点经纬度
     * $destination = [
     * 'lng' => '终点经度',
     * 'lat' => '终点纬度'
     * ];
     * @return string 两点的距离，默认单位: 千米(公里) 
     */
    public static function getDistanceByLngLat(array $origin, array $destination, string $unit = 'km'):string
    {
        //起点经纬度
        $lng1 = $origin['lng'];
        $lat1 = $origin['lat'];
        //终点经纬度
        $lng2 = $destination['lng'];
        $lat2 = $destination['lat'];
        //地球半径，单位米
        $R = 6378.137;
        //将角度转为弧度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * $R * 1000;
        $s = $unit == 'km' ? round($s/1000,1) : $s; 
        return $s;
    }
   
    /**
     * 含有xss脚本返回true, 否则返回false
     * @param $content
     * @return bool
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-09-13
     */
    public static function checkXss($content)
    {
        if (empty($content) || !is_string($content)) {
            return false;
        }
        if (preg_match('/(<script[\s\S]*?)|(<iframe[\s\S]*?)|(alert[\s\S]*?\()|(<form[\s\S]*?)|(javascript[\s\S]*?)/i', $content)) {
            return true;
        }
        return false;
    }

    /**
     * 医生挂号服务数量
     * @return int
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public static function getDoctorRegisterNum(int $miaoDoctorId = 0): int
    {
        if ($miaoDoctorId <= 0) {
            return 0;
        }
        $docKeyHeader = Yii::$app->params['cache_key']['doctor_register_num'];
        $redis = Yii::$app->redis_codis;
        $redisKey = sprintf($docKeyHeader, $miaoDoctorId);
        $cacheData = $redis->get($redisKey);
        if (!is_null($cacheData)) {
            return (int)$cacheData;
        }
        $count = GuahaoOrderModel::find()->where(['miao_doctor_id' => $miaoDoctorId, 'state' => 3])->count();
        $randSeconds = $count > 0 ? mt_rand(3600 * 23, 3600 * 24) : mt_rand(3600 * 1, 3600 * 2);
        $redis->set($redisKey, $count ?: 0);
        $redis->expire($redisKey, $randSeconds);
        return (int)$count;
    }

    /**
     * 获取一二级科室
     * @return array
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public static function getDepartment(): array
    {
        $key = Yii::$app->params['cache_key']['departmentsdk_all'];
        $data = CommonFunc::getCodisCache($key);
        if ($data) {
            return $data;
        }
        \nisiya\baseapisdk\Config::setConfig(\Yii::$app->params['baseapisdk']);
        $res = ArrayHelper::getValue((new DepartmentSdk())->all(), 'data', []);
        $data = [];
        foreach ($res as $v) {
            $data[] = [
                'id' => $v['id'] ?? 0,
                'name' => $v['name'] ?? '',
                'namepy' => $v['namepy'] ?? '',
                'parentid' => $v['parentid'] ?? '',
            ];
            if (!empty($v['children'])) {
                foreach ($v['children'] as $child) {
                    $data[] = [
                        'id' => $child['id'] ?? 0,
                        'name' => $child['name'] ?? '',
                        'namepy' => $child['namepy'] ?? '',
                        'parentid' => $child['parentid'] ?? '',
                    ];
                }
            }
        }
        if ($data) {
            CommonFunc::setCodisCache($key, $data, 3600);
        }
        return $data;
    }

    /**
     * 根据给定的经纬度范围计算中心点经纬度
     * @author yueyuchao <yueyuchao@yuanxinjituan.com>
     * @param array $data
     * $data = [
     * ['lng'// 经度,
     * 'lat'// 纬度
     * ],
     * ['lng'//经度,'lat'//纬度]
     * ];
     * @return mixed
     */
    public static function getCenterFromDegrees(array $data)
    {
        if (!is_array($data)) return FALSE;
        $num_coords = count($data);

        $X = 0.0;
        $Y = 0.0;
        $Z = 0.0;

        foreach ($data as $coord)
        {
            $lon = $coord[0] * pi() / 180;
            $lat = $coord[1] * pi() / 180;

            $a = cos($lat) * cos($lon);
            $b = cos($lat) * sin($lon);
            $c = sin($lat);

            $X += $a;
            $Y += $b;
            $Z += $c;
        }

        $X /= $num_coords;
        $Y /= $num_coords;
        $Z /= $num_coords;

        $lon = atan2($Y, $X);
        $hyp = sqrt($X * $X + $Y * $Y);
        $lat = atan2($Z, $hyp);

        return array($lon * 180 / pi(), $lat * 180 / pi());
    }
}
