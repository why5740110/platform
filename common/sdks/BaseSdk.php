<?php
namespace common\sdks;

use yii\helpers\FileHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 基类SDK
 * @file BaseSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

class BaseSdk
{

    protected static $_instance = null;
    /**
     * @var string 请求的appid
     */
    protected $appid = '';
    /**
     * @var string 请求加密的key
     */
    protected $appkey = '';
    /**
     * @var string 版本号
     */
    protected $version = '1.0';
    /**
     * @var string 域名
     */
    protected $domain = '';
    /**
     * @var string 请求的URL
     */
    protected $url = null;
    /**
     * @var array 请求接口的参数数组
     */
    protected $params = [];
    /**
     * @var array 请求接口返回的结果
     */
    protected $result = [];
    /**
     * @var array 基础参数数组
     */
    protected $baseParams = [];

    protected function __construct()
    {
        $this->appid = '1000000001';
        $this->appkey = 'rW@vM2UlXKGe2V%!7@%x5mjclBGT0HGc';
        $this->baseParams = [
            'appid' => $this->appid,
            'os' => 'hospital',
            'time' => time(),
            'version' => $this->version,
            'noncestr' => $this->getRandChar(6),
        ];
    }

    /**
     * 单例
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @return null|static
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }


    /**
     * curl $params为空则get方式调用,$params不空为POST调用
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $method
     * @param array $params 查询参数
     * @param int $timeout 过期时间
     * @return mixed
     */
    public function curl($uri, $params = [], $timeout = 10)
    {
        $url = $this->getApiUri($uri);
        $arr = explode('?', $url);
        if (!isset($arr[1])) {
            return false;
        }
        $http_build_query = $arr[1];
        parse_str($http_build_query, $baseParams);
        $sign = $this->makeSign(array_merge($baseParams, $params));
        $this->url = $url.'&sign='.$sign;
        $this->params = $params;
//         echo $this->url. PHP_EOL;
        try {
            $client = new Client([
                'base_uri' => $this->domain,
                'timeout'  => $timeout,
            ]);
            if (!empty($params)) {
                $response = $client->post($this->url,['form_params'=>$params]);
            }else{
                $response = $client->get($this->url);
            }
        } catch (\Throwable $e) {
            $this->curlError($e->getCode(), $e->getMessage());
            return false;
        }
        $this->result = $response->getBody()->getContents();
        return json_decode($this->result, true);
    }

    /**
     * 修饰请求url
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $uri
     * @return string
     */
    protected function getApiUri($uri)
    {
        if (strpos($uri, '?')) {
            $delimiter = '%s%s&%s';
        }else {
            $delimiter = '%s%s?%s';
        }
        return sprintf(
            $delimiter,
            $this->domain,
            $uri,
            http_build_query($this->baseParams)
        );
    }

    /**
     * 获取指定长度的随机字符串
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $length
     * @return null|string
     */
    protected function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * 格式化参数格式化成url参数
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $data
     * @return string
     */
    protected function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($v !== "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $data
     * @return string
     */
    protected function makeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->appkey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * curl 错误处理
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $err_code 错误码
     * @param $err_msg 错误信息
     */
    protected function curlError($err_code, $err_msg)
    {
        $errors = sprintf(
            "cURL Error:\nCode: %s\nMessage: %s\n",
            $err_code,
            $err_msg
        );
        $this->log($errors);
    }

    /**
     * 记录请求日志
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @return bool|int
     */
    protected function logger()
    {
        $logs = sprintf(
            "cURL Result:\nUrl: %s\nParams: %s\nResult: %s\n\n",
            $this->url,
            json_encode(
                $this->params,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            $this->result
        );
        return $this->log($logs);
    }

    /**
     * 记录查询日志，按需重载
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $log_message 日志信息
     * @return bool|int
     */
    protected function log($log_message)
    {
        $class_name = str_replace("\\", '-', get_called_class());
        $save_path = \Yii::$app->getRuntimePath() . DIRECTORY_SEPARATOR . 'logs/' . $class_name . DIRECTORY_SEPARATOR;
        $save_filename = $save_path . date("Y-m-d") . '.txt';

        if (!is_dir($save_filename)) {
            FileHelper::createDirectory($save_path, 0755, true);
        }
        $log_format = "[%s] - %s\n";
        $log_message = sprintf($log_format, date('Y-m-d H:i:s'), $log_message);

        return file_put_contents($save_filename, $log_message, FILE_APPEND);
    }

}