<?php
namespace common\sdks\ucenter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class DoctorServerSDK
 * @package common\sdks\ucenter
 */
class DoctorServerSDK
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
        $this->domain = \Yii::$app->params['api_url']['docapi'];
        $this->appid = '2100000000';
        $this->appkey = 'obHB&m9Lpe4pAryP@wGa#mV52^#0c^mV';
        $this->baseParams = [
            'appid' => $this->appid,
            'os' => 'hospital',
            'time' => time(),
            'version' => $this->version,
            'noncestr' => $this->getRandChar(6),
        ];
    }

    /**
     * 获取指定长度的随机字符串
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-12-20
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
     * 单例
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-12-20
     * @return null|static
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    //路由配置
    private $routeMap = [
        'get-patient-list' => 'account/interrogation/patient-list', //获取就诊人列表
        'get-patient-detail' => 'account/interrogation/get-patient', //获取就诊人详情
        'save-patient' => 'account/interrogation/submit-patient', //添加、编辑就诊人信息
    ];

    /**
     * 获取就诊人详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-12-20
     * @param array $params
     * @return array|false|mixed
     */
    public function getPatientDetail($param)
    {

        //请求参数
        $params['uc_login_key'] = $param['uc_logic_key'];
        $params['patient_id'] = $param['patient_id'];
        $params['is_card'] = 1;//是否检验实名认证 0 不需要 1 需要
        $params['is_filter_auth'] = 1;//(目前挂号业务需要用到)实名认证失败是否正常显示详情页 0 给出提示 1 正常显示

        //获取签名
        $data = $this->getEncryptArray($params);
        $this->baseParams['data'] = $data['data'];
        $url = $this->domain. $this->routeMap['get-patient-detail'] . '?' . http_build_query($this->baseParams);

        $result = $this->requestUrl($url);
        if (is_array($result) && ArrayHelper::getValue($result,'code') == 200) {
            $datas = ArrayHelper::getValue($result,'data');
            $res = json_decode($datas, true);
            if ($params['is_card'] == 1) {
                $res['id_card'] = $res['id_card_complete'];
                $res['tel'] = $res['tel_complete'];
            }
            return $res;
        }else{
            return [];
        }
    }

    /**
     * 获取就诊人列表
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-12-20
     * @param array $param
     * @return array|false|mixed
     */
    public function getPatientList($param = [])
    {
        //请求参数
        $params = [];
        $params['uc_logic_key'] = $param['uc_logic_key'];
        $params['page'] = $param['page'];
        $params['pagesize'] = $param['pagesize'];

        //获取签名
        $data = $this->getEncryptArray($params);
        $this->baseParams['data'] = $data['data'];
        $url = $this->domain . $this->routeMap['get-patient-list'] . '?' . http_build_query($this->baseParams);

        $result = $this->requestUrl($url);
        if (is_array($result) && ArrayHelper::getValue($result,'code') == 200) {
            $datas = ArrayHelper::getValue($result,'data');
            $res = json_decode($datas, true);
            $list = ArrayHelper::getValue($res,'list');
            return ['code' => 200, 'data' => $list];
        }else{
            return ['code' => $result['code'], 'msg' => $result['msg']];
        }
    }

    /**
     * 就诊人添加、编辑
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-12-20
     * @param array $formData
     * @return array|false|mixed
     */
    public function savePatient($formData = [])
    {
        //请求参数
        $params = [];
        $params['uc_login_key'] = $formData['uc_login_key'];
        $params['realname'] = $formData['realname'];
        $params['id_card'] = $formData['id_card'];
        $params['tel'] = $formData['tel'];
        //$params['province'] = $formData['province'];
        //$params['city'] = $formData['city'];
        //$params['address'] = $formData['address'];
        /*$params['guarder_name'] = $formData['guarder_name'];
        $params['guarder_card'] = $formData['guarder_card'];
        $params['guarder_tel'] = $formData['guarder_tel'];*/
        $params['is_real_auth'] = $formData['is_real_auth'];
        $params['platform'] = 7;//默认参数 挂号业务线
        //编辑时追加就诊人id
        if (isset($formData['id']) && $formData['id'] != 0) {
            $params['patient_id'] = $formData['id'];
        }
        //获取签名
        $data = $this->getEncryptArray($params);

        $this->baseParams['data'] = $data['data'];
        $url = $this->domain . $this->routeMap['save-patient'] . '?' . http_build_query($this->baseParams);
        $result = $this->requestUrl($url, $this->baseParams);
        if (is_array($result) && ArrayHelper::getValue($result,'code') == 200) {
            $datas = ArrayHelper::getValue($result,'data');
            $res = json_decode($datas, true);
            return ['code' => 200, 'data' => $res];
        }else{
            return ['code' => $result['code'], 'msg' => $result['msg']];
        }
    }

    /**
     * curl $params为空则get方式调用,$params不空为POST调用
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $method
     * @param array $params 查询参数
     * @param int $timeout 过期时间
     * @return mixed
     */
    public function requestUrl($url, $params = [], $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->result = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->curlError(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $data = json_decode($this->result, true);
        //解密数据并重新赋值data
        if (!empty($data['data'])) {
            $data['data'] = $this->AES256ECBDecrypt($data['data']);
        }
        return $data;
    }

    /**
     * curl 错误处理
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
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
     * 记录查询日志，按需重载
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
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

    /**
     * 数据加密方法
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $data
     * @return string
     */
    public function AES256ECBEncrypt($data)
    {
        $decrypted = openssl_encrypt($data, 'AES-256-ECB', $this->appkey, OPENSSL_RAW_DATA);
        return base64_encode($decrypted);
    }

    /**
     * 数据解密方法
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $data
     * @return false|string
     */
    public function AES256ECBDecrypt($data)
    {
        $decrypted = openssl_decrypt(base64_decode($data), 'AES-256-ECB', $this->appkey, OPENSSL_RAW_DATA);
        return $decrypted;
    }

    /**
     * 获取要加密的参数
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $data
     * @return string[]
     */
    public function getEncryptArray($data)
    {
        return [
            'data' => $this->AES256ECBEncrypt(json_encode($data))
        ];
    }

    /**
     * 获取解密后的数据
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $string
     * @param int $type
     * @return array|mixed|null
     */
    public function getDecryptedArray($string, $type = 1)
    {
        $string = urldecode($string);
        $string = str_replace(' ', '+', $string);
        switch ($type) {
            case 1: // 解密响应的数据{data:"加密串"}
                $string = json_decode($string, true);
                $string = isset($string['data']) ? $string['data'] : '';
                break;
            case 2: // 解密请求的数据 data="加密串",传递进来的sting 为加密串
                break;
            default:
                return null;
        }
        //处理get请求时,base64编码中的+被空格替换的情况
        $string = json_decode($this->AES256ECBDecrypt($string), true);
        return is_array($string) ? $string : null;
    }

    /**
     * DES对称 加密
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $data
     * @param $method
     * @param $key
     * @return string
     */
    public function desEncrypt($data, $method, $key)
    {
        $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA);
        return bin2hex($encrypted);
    }

    /**
     * DES对称  解密
     * Author: wanghongying <wanghongying@yuanxinjituan.com>
     * Date: 2021/12/20
     * @param $string
     * @param $key
     * @return false|string
     */
    public function desDecrypt($string, $key)
    {
        $encrypted = hex2bin($string);
        $decrypted = openssl_decrypt($encrypted, 'DES-ECB', $key, OPENSSL_RAW_DATA);
        return $decrypted;
    }
}

