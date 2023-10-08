<?php
namespace common\sdks;
use yii\helpers\ArrayHelper;
/**
 * @file CenterSDK.php
 * @xiujianying
 */
class CenterSDK
{
    protected static $_instance = null;

    protected $url    = null;
    protected $params = null;
    protected $result = null;

    protected $secret  = 'yuanxin';

    protected $appid   = 'mall';
    protected $os      = '11';
    protected $version = '1.0.0';
    protected $domain  = '';
    private $maps = [
        'updateuser' => 'v2/dappdoctor/updateuser',       //医生列表
        'registerthirdmember' => 'v3/member/register-third-member', //注册第三方用户信息
        'v2_dappdoctor_info' => 'v2/dappdoctor/info', //获取医生信息
        'v2_member_getuser' => 'v2/member/getuser', //通过id、用户名、手机号或王氏号获取用户信息 https://yapi.beijingyuanxin.com/project/452/interface/api/52883
        'v2_member_getusers' => '/v2/member/getusers', //批量获取用户数据，传递多个用户UID https://yapi.beijingyuanxin.com/project/452/interface/api/52892
    ];

    private function __construct()
    {
        $this->domain = \Yii::$app->params['api_url']['ucenterapi'];
    }

    public function updateuser($params)
    {
        $result = $this->send($this->maps['updateuser'] ,$params);
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    public function registerthirdmember($params)
    {
        $result = $this->sendPost($this->maps['registerthirdmember'] ,$params);
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    //获取用户信息
    public function memberGetUser($params)
    {
        $result = $this->send($this->maps['v2_member_getuser'], $params);
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    //批量获取用户数据，传递多个用户UID
    public function memberGetUsers($params)
    {
        $result = $this->send($this->maps['v2_member_getusers'], $params);
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }
    }

    /**
     * 获取医生信息
     * @param $params
     * @return array|mixed
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-25
     */
    public function dappdoctorInfo($params)
    {
        $result = $this->send($this->maps['v2_dappdoctor_info'], $params);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 单例
     * @return static object
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }
    public function setVersion($version){
        $this->version=$version;
    }
    /**
     * 发送对外接口
     * @author zhibin <xiezhibin@yuanxin-inc.com>
     * @date   2018-01-16
     * @param  [type]     $apiUri [description]
     * @param  [type]     $params [description]
     * @return [type]             [description]
     */
    public function send($apiUri, $params = [])
    {
        $url = $this->token($apiUri, $params);
        $list = $this->curl($url);
        return $list;
    }

    public function sendPost($apiUri, $params = [])
    {
        $url = $this->token($apiUri, []);
        $list = $this->curl($url, $params);
        return $list;
    }

    /**
     * 产生token验证，修饰请求url
     * @param  static::const $apiUri api constat uri
     * @return string        $url    请求地址
     */
    protected function token($apiUri,$params)
    {
        $params=array_merge($this->getTokenArr(),$params);

        return sprintf('%s%s?%s', $this->domain, $apiUri, http_build_query($params));
    }

    /**
     * dapp 所需要的 token
     * @return array token array
     */
    protected function getTokenArr()
    {
        $os      = $this->appid;
        $udid    = '112233asd2dfsa234-234';
        $version = $this->version;
        $time    = time();
        $sign    = md5($os.$udid.$version.$time.$this->secret);
        return [
            'os'      => $os,
            'udid'    => $udid,
            'version' => $version,
            'time'    => $time,
            'sign'    => $sign,
        ];
    }
    /**
     * curl $params为空则get方式调用,$params不空为POST调用
     * @param string $url  $params post数据
     *
     * curl errorno
     * http://curl.haxx.se/libcurl/c/libcurl-errors.html
     */
    protected  function curl($url = '', $params = []) {
        $timeout = YII_ENV == 'prod'? '5' :'30';
        $startime=microtime(true);
        $this->url    = $url;
        $this->params = $params;
        $this->result = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->result = curl_exec($ch);
        $error='';
        if(curl_errno($ch)) {
            $this->curlError(curl_error($ch), curl_errno($ch));
            $error=curl_error($ch);
        }
        curl_close($ch);
        $this->logger();

        $data=json_decode($this->result, true);
        $logData=[
            'error'=>$error,
            'usetime'=>microtime(true)-$startime,
            'url'=>$url,
            'origindata'=>$this->result,
            'returndata'=>$data
        ];
        //\Yii::warning($logData,'Sappsdk');
        return $data;
    }

    /**
     * curl 错误处理
     * @param   $e
     */
    public function curlError($errCode, $errMsg)
    {
        $errors = sprintf(
            "cURL Error:\nCode: %s\nMessage: %s\n",
            $errCode,
            $errMsg
        );
        $this->log($errors);
    }

    /**
     * 记录请求日志
     */
    protected function logger()
    {
        $logs = sprintf(
            "cURL Result:\nUrl: %s\nParams: %s\nResult: %s\n\n",
            $this->url,
            json_encode(
                $this->params,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
            ),
            $this->result
        );
        return $this->log($logs);
    }

    /**
     * 记录查询日志，按需重载
     * @param  string $message
     * @param  int    $code
     */
    protected function log($message)
    {
        return false;
    }

}