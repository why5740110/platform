<?php
/**
 * 公共的sdk调用方法
 * @file CommonSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk;
class CommonSdk
{
    /**
     * @var string 请求域名
     */
    public $domain;
    /**
     * @var string 域名配置
     */
    public $domain_index;
    /**
     * @var string 加密key
     */
    private $appkey;
    /***
     * @var array 基础请求参数
     */
    private $baseParams;
     /***
     * @var array 请求参数
     */
    private $params;
    /***
     * @var string 请求URL
     */
    private $url;
    /**
     * @var int 请求开始时间
     */
    private $start_time;
    /**
     * @var int 请求结束时间
     */
    private $end_time;
    /**
     * @var string 接口返回数据
     */
    private $result;

    public function __construct()
    {
        $config = Config::getConfig();
        if (!isset($config['appid']) || !isset($config['appkey']) || !isset($config['version']) || !isset($config['os']) || !isset($config['env'])) {
            return $this->jsonOutput(400 , '缺少基础参数');
        }
        $this->domain = Config::getHostName($this->domain_index);
        if (empty($this->domain)) {
            return $this->jsonOutput(400 , '缺少基础参数');
        }
        $this->appkey = $config['appkey'];
        $this->baseParams = [
            'appid' => $config['appid'],
            'time' => time(),
            'os' => $config['os'],
            'version' => $config['version'],
            'noncestr' => $this->getRandChar(6),
            'host' => !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli'),
        ];
    }

    /**
     * 发送请求接口
     * @param array $params
     * @param string $method
     * @param string $type
     * @param int $timeout
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|mixed
     */
    protected function sendHttpRequest($params = [], $method, $type = 'get', $timeout = 2)
    {
        $url = sprintf('%s/%s?%s', $this->domain, $this->getApiUri($method), http_build_query($this->baseParams));
        // echo $url;
        $this->start_time = microtime(true);
        $ch = curl_init();
        // echo $url;die;
        $type = strtolower($type);
        switch ($type) {
            case 'get':
                $url = $url . '&' . http_build_query($params);
                curl_setopt($ch, CURLOPT_POST, false);
                break;
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                $this->params = $params;
                break;
        }
        $sign = $this->makeSign(array_merge($this->baseParams, $params));
        $url = $url . '&sign=' . $sign;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $this->url = $url;
        $this->result = $result;
        $this->end_time = microtime(true);
        if (curl_errno($ch)) {
            return $this->jsonOutput(400 , $this->curlError(curl_error($ch), curl_errno($ch)));
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * 返回成功的json数据
     * @param int $code
     * @param string $msg
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array
     */
    protected function jsonOutput($code = 400, $msg = 'error')
    {
        return [
            'request_id' => md5(uniqid() . microtime() . mt_rand(111111, 999999)),
            'code' => $code,
            'data' => [],
            'msg' => $msg,
        ];
    }

    /**
     * 获取接口的地址
     * @param string $method
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return null|string
     */
    protected function getApiUri($method)
    {
        $method = explode('\\', $method);
        $arr = explode('Sdk::', end($method));
        $c = lcfirst($arr[0]);
        $a = lcfirst($arr[1]);
        //大写字母分割数组
        $reg = '/(?<=[a-z0-9])(?=[A-Z])/x';
        $c = preg_split($reg, $c);
        $a = preg_split($reg, $a);
        return strtolower(implode('-', $c) . '/' . implode('-', $a));
    }

    /**
     * 获取指定长度的随机字符串
     * @param $length
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
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
     * 生成签名
     * @param $data
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
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
     * 格式化参数格式化成url参数
     * @param $data
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
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
     * curl 错误处理
     * @param $errorcode 错误码
     * @param $errormsg 错误信息
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return string
     */
    protected function curlError($errorcode, $errormsg)
    {
        return sprintf(
            "cURL Error:\tCode: %s\tMessage: %s\t",
            $errorcode,
            $errormsg
        );
    }

    /**
     * 记录请求日志
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return string
     */
    public function logger()
    {
        $logs = sprintf(
            "cURL Result:\nUrl: %s\nBaseParams: %s\nParams: %s\nTime: %s\nResult: %s\n\n",
            $this->url,
            json_encode(
                $this->baseParams,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            json_encode(
                $this->params,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            json_encode([
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'result_time' => round($this->end_time - $this->start_time, 3),
            ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            $this->result
        );
        return $logs;
    }

}