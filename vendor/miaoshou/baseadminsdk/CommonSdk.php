<?php
/**
 * 公共的sdk调用方法
 */

namespace nisiya\baseadminsdk;

class CommonSdk
{
    // 接口域名
    private $domain;

    public function __construct($domain)
    {
        $this->domain = $domain;
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
        $controller = str_replace("sdk", "", strtolower($tmp[0]));
        $action = $tmp[1];
//        if(preg_match('/([A-Z])/', $action) != false){
//            $action = preg_replace('/([A-Z])/', '-${0}', $action);
//            $action = strtolower($action);
//        }else{
//            $action = strtolower($action);
//        }
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

        return $this->curl($requestUrl, $params, $is_post);
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
}
