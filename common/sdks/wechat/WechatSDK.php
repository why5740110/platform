<?php
/**
 * 微信分享JS相关调用SDK
 */
namespace common\sdks\wechat;

use Yii;

class WechatSDK{

    protected static $_instance = null;
    protected $os = 'ios7.04';
    protected $udid = '112233asd2dfsa234-234';
    protected $version = '2.3.0';
    protected $secret = 'yuanxin';
    public $key = '';
    protected $domain  = '';
    protected $url = null;
    protected $params = null;
    protected $result = null;

    //请求URI
    public static $uri = [
        //获取jsconfig 验证参数
        'getJsConfig' => '/service/getjsconfig',
    ];

    protected function __construct(){
        $this->domain = Yii::$app->params['wechat']['wechatApiUrl'];
        $this->key = Yii::$app->params['wechat']['wechatKey'];
    }

    /**
     * 单例
     * @return static object
     */
    public static function getInstance(){
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    /**
     * 获取js配置信息
     * @param array $params
     * @example ['url'=>'http://www.baidu.com']
     */
    public function getJsConfig($params = [])
    {
        return $this->curl(
            $this->token('getJsConfig'),
            $params
        );
    }

    /**
     * 产生token验证，修饰请求url
     * @param  static ::const $apiUri api constat uri
     * @return string        $url    请求地址
     */
    protected function token($uriKey)
    {
        $os = $this->os;
        $udid = $this->udid;
        $version = $this->version;
        $time = time();
        $secret = $this->secret;
        $sign = md5($os . $udid . $version . $time . $secret);
        $key = $this->key;
        $params = compact('os', 'udid', 'version', 'time', 'sign', 'key');
        return sprintf('%s%s?%s', $this->domain, self::$uri[$uriKey], http_build_query($params));
    }

    /**
     * 发起请求
     * $param array $params 为空则get方式调用,$params不空为POST调用
     * @param string $url  $params post数据
     */
    protected  function curl($url = '', $params = []) {
        $this->url    = $url;
        $this->params = $params;
        $this->result = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        if(!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面--解决301,302问题
        $this->result = curl_exec($ch);
        curl_close($ch);

        return json_decode($this->result, true);
    }

}


