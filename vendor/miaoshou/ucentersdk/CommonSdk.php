<?php
/**
 * 公共的sdk调用方法
 * @file CommonSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @date 2017-12-27
 * @version 2.0
 */

namespace nisiya\ucentersdk;
class CommonSdk
{
    private $urlMapping = [];
    private $memberLoginToken='';
    protected $errorMessage;
    protected $errorArray;
    private $appid;
    private $appkey;
    private $baseParams;
    protected $log = [
        'baseurl' => '',
        'requestUrl' => '',
        'method' => '',
        'baseparams' => '',
        'requestparams' => '',

    ];

    public function __construct()
    {
        $this->appid = Config::getConfig('appid');
        $this->appkey = Config::getConfig('appkey');
        $this->urlMapping = Config::getConfig('urlmapping');
        $this->memberLoginToken=Config::getMemberLoginToken();

        $this->baseParams = [
            'appid' => $this->appid,
            'os' => Config::getConfig('os'),
            'time' => time(),
            'version' => Config::getConfig('version'),
            'noncestr' => $this->getRandChar(6),
            'http_host'=>isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '' ,
        ];
    }

    /**
     * 发送请求
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @param $params
     * @param $method
     * @param string $type
     * @return bool|mixed
     */
    protected function send($params, $method, $type = 'get')
    {
        $type = strtolower($type);
        $method = explode('\\', $method);
        $project = strtolower($method[2]);
        $url = strtolower(str_replace('Sdk::', '/', end($method)));
        $requestUrl = rtrim($this->urlMapping[$project], '/') . '/' . $url;
        $curl = curl_init();
        $requestData = [];
        //设置展示方式
        $this->setlog("method", $type);
        $this->setlog("baseurl", $requestUrl);
        $this->setlog('baseparams', $this->baseParams);
        $this->setlog('requestparams', $params);

        switch ($type) {
            case 'post':
                //如果是post请求
                curl_setopt($curl, CURLOPT_POST, 1);
                if (is_array($params) && count($params)) {
                    //把参数加密后放到post参数的key:data中
//                    $requestData['data'] = CryptoTools::AES256ECBEncrypt(json_encode());
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
                }
                $sign = $this->makeSign(array_merge($this->baseParams, $params));
                $requestUrl = $requestUrl . '?' . http_build_query(array_merge($params,$this->baseParams)).'&sign='.$sign;
                break;
            case 'get':
                //如果是get请求
                if (is_array($params) && count($params)) {
//                    //把参数加密后放到get参数的key:data中
//                    $this->baseParams['data'] = CryptoTools::AES256ECBEncrypt(json_encode($params));
                    $sign = $this->makeSign(array_merge($this->baseParams, $params));

                }
                $requestUrl = $requestUrl . '?' . http_build_query(array_merge($params,$this->baseParams)).'&sign='.$sign;
                curl_setopt($curl, CURLOPT_POST, 0);
                break;
        }
        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        $this->setlog("requestUrl", $requestUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        if(strstr(PHP_OS, 'WIN')!==false){
            //如果是windows下不验证ssl
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
//        }
        $startTime = microtime(true);
        $data = curl_exec($curl);
        $endTime = microtime(true);
        $this->setlog("startTime", $startTime);
        $this->setlog("endTime", $endTime);
        $this->setlog("networkTime", $endTime - $startTime);
        if (curl_errno($curl)) {
            $curlErrorMessage=curl_error($curl);
            $this->setError($curlErrorMessage);
            $this->setErrorArray([
                'code'=>400,
                'msg'=>$curlErrorMessage
            ]);
            return false;
        }
        $returnData = json_decode($data, true);
        $this->setlog("responseData", $returnData);

        if (isset($returnData['code']) && $returnData['code'] == 200) {
//            $data = json_decode(CryptoTools::AES256ECBDecrypt($returnData['data']), true);
            $this->setlog("responseDataArray", $data);
            return $data;
        } else {
            if(is_array($returnData)&&isset($returnData['code'])){
                $this->setErrorArray([
                    'code'=>$returnData['code'],
                    'msg'=>$returnData['msg']
                ]);
                $this->setError($returnData['msg']);
            }else{
                $errorMessage="未知错误";
                $this->setErrorArray(
                    [
                        'code'=>400,
                        'msg'=>$errorMessage
                    ]
                );
                $this->setError($errorMessage);
            }
            return $data;
        }
    }
    protected function setErrorArray($data){
        return $this->errorArray=$data;
    }
    public function getErrorArray(){
        return $this->errorArray;
    }
    /**
     * 设置错误信息
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @param $error
     */
    protected function setError($error)
    {
        $this->errorMessage = $error;
    }

    /**
     * 获取错误信息
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @return mixed
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     *设置日志
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @param $key
     * @param $value
     */
    protected function setlog($key, $value)
    {
        $this->log[$key] = $value;
    }

    /**
     *获取日志
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     *获取格式化后的日志
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @return string
     */
    public function getFormatLog()
    {
        $return = '';
        $return .= $this->formatArray($this->log);
        return $return;
    }

    /**
     *格式化数组方法
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-27
     * @param $value
     * @param int $paddingLength
     * @return string
     */
    private function formatArray($value, $paddingLength = 0)
    {
        if (is_scalar($value)) {
            return $value . "\n";
        }
        $return = '';
        $length = 0;
        foreach ($value as $k => $v) {
            if (($tempLength = strlen($k)) > $length) {
                $length = $tempLength;
            }
        }
        foreach ($value as $k => $v) {
            $return .= str_repeat(' ', $paddingLength) . str_pad($k, $length, ' ') . " : " . (is_array($v) ? "\n" : "") . $this->formatArray($v, $paddingLength + $length + 3);
        }
        return $return;

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
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

}