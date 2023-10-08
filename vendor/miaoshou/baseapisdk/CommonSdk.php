<?php
/**
 * 公共的sdk调用方法
 *
 * @file    CommonSdk.php
 * @author  lixinhan <lixinhan@yuanxin-inc.com>
 * @date    2017-12-27
 * @version 2.0
 */

namespace nisiya\baseapisdk;

class CommonSdk
{
    private $app_id;
    private $app_key;
    // 接口域名
    private $domain;
    // 基本请求参数
    private $baseParams;

    public function __construct()
    {
        $this->app_id = Config::getConfig('app_id');
        $this->app_key = Config::getConfig('app_key');
        $this->domain = Config::getConfig('domain');

        $this->baseParams = [
            'appid' => $this->app_id,
            'time' => time(),
            'os' => Config::getConfig('os', 'h5'),
            'version' => Config::getConfig('version', '1.0'),
            'noncestr' => $this->getRandChar(6),
        ];
    }

    /**
     * 根据类名返回uri
     * User: niewei
     * Datetime: 2020/8/25 15:18
     *
     * @param string $className 类名
     *
     * @return string
     */
    private function getUriByClassName($className){
        $tmp = explode("::", $className);
        $controller = str_replace("Sdk", "", $tmp[0]);
        if(preg_match('/([A-Z])/', $controller) != false){
            $controller = preg_replace('/([A-Z])/', '-${0}', $controller);

            $controller = strtolower(trim($controller, '-'));
        }else{
            $controller = strtolower($controller);
        }
        $action = $tmp[1];
        if(preg_match('/([A-Z])/', $action) != false){
            $action = preg_replace('/([A-Z])/', '-${0}', $action);
            $action = strtolower($action);
        }else{
            $action = strtolower($action);
        }
        return $controller.'/'.$action;
    }

    /**
     * 发送请求
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2017-12-27
     * @param $params
     * @param $method
     * @param string $type
     * @return bool|mixed
     */
    protected function send($is_post, $params, $method)
    {
        $method = explode('\\', $method);
        $uri = $this->getUriByClassName(end($method));
        $requestUrl = rtrim($this->domain, '/') . '/' . $uri;

        $this->baseParams['time'] = time();
        $requestData = array_merge($this->baseParams, $params);
        $sign = $this->makeSign($requestData);
        $requestData['sign'] = $sign;

        return $this->curl($requestUrl, $requestData, $is_post);
    }

    /**
     * curl get和post
     * Author: guowenzheng
     * Email: guowenzheng@yuanxinjituan.com
     * Date: 2022/3/30.
     * @param $url
     * @param $params
     * @param $is_post
     * @return bool|string|array
     */
    public function curl($url, $params = [], $is_post = false)
    {
        //初始化
        $ch = curl_init();
        /*CURL_HTTP_VERSION_NONE (默认值，让 cURL 自己判断使用哪个版本)，CURL_HTTP_VERSION_1_0 (强制使用 HTTP/1.0)或CURL_HTTP_VERSION_1_1 (强制使用 HTTP/1.1)。
        */
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt( $ch, CURLOPT_USERAGENT , 'nisiya\baseapisdk' );
        //尝试连接等待的时间，以毫秒为单位。设置为0，则无限等待。 如果 libcurl 编译时使用系统标准的名称解析器（ standard system name resolver），那部分的连接仍旧使用以秒计的超时解决方案，最小超时时间还是一秒钟。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        // 允许 cURL 函数执行的最长秒数。
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //FALSE 禁止 cURL 验证对等证书（peer's certificate）。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //设置为 1 是检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。 设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置编码格式，为空表示支持所有格式的编码
        curl_setopt($ch, CURLOPT_ENCODING, '');
        if ($is_post) {
            // TRUE 时会发送 POST 请求，类型为：application/x-www-form-urlencoded，是 HTML 表单提交时最常见的一种。
            curl_setopt($ch, CURLOPT_POST, true);
            //全部数据使用HTTP协议中的 "POST" 操作来发送
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            //需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return ['code' => 500, 'msg' => '服务异常！', 'data' => null];
        }
        curl_close($ch);
        return json_decode($response, true);
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
        // 签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        // 签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->app_key;
        // header("string:".$string);
        // 签名步骤三：MD5加密
        $string = md5($string);
        // 签名步骤四：所有字符转为大写
        return strtoupper($string);
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
     * 获取指定长度的随机字符串
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $length
     * @return null|string
     */
    protected function getRandChar($length)
    {
        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[mt_rand(0, $max)];
        }
        return $str;
    }

    /**
     * 私钥生成签名
     *
     * @param string $string     加密字符串
     * @param string $privateKey 私钥
     *
     * @return string
     * @author niewei
     * @date   2021/9/4 10:11
     */
    public function createSign($string, $priKey = '')
    {
        $priKey = $this->formatKey($priKey, 'private');
        $res = openssl_get_privatekey($priKey);
        openssl_sign($string, $sign, $res, OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        return $sign = base64_encode($sign);
    }

    /**
     * 格式化公钥、私钥
     *
     * @param string $key  秘钥字符串
     * @param string $type 秘钥类型
     *
     * @return string
     * @author niewei
     * @date   2021/9/4 9:37
     */
    public function formatKey($key, $type)
    {
        if (strpos($key, "\n") === false) {
            $formatKey = wordwrap($key, 64, "\n", true);
            switch ($type) {
                case 'public':
                    $formatKey = "-----BEGIN PUBLIC KEY-----\n$formatKey\n-----END PUBLIC KEY-----\n";
                    break;
                case 'private':
                    $formatKey = "-----BEGIN RSA PRIVATE KEY-----\n$formatKey\n-----END RSA PRIVATE KEY-----\n";
            }
        } else {
            $formatKey = $key;
        }
        return $formatKey;
    }
}
