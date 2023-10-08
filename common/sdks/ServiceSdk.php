<?php
/**
 * 短信
 * @file serviceSdk.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/2/2
 */


namespace common\sdks;


use yii\helpers\ArrayHelper;

class ServiceSdk
{
    // 发送短信错误信息
    public $errorMsg = '';

    public $url = '';
    public $appName = '';
    public $secret = '';
    public $timestamp = '';

    public function __construct()
    {
        $this->url = ArrayHelper::getValue(\Yii::$app->params,'api_url.ServiceApiUrl') . '/sms/send?';
        $this->appName = 'branddoctor';
        $this->secret = '92CpQOMo&&8#1H*5IlI*0EjK!oO$ulCJ';
        $this->timestamp = time();
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $template
     *   guahao_success:预约成功  guahao_cancel:取消
     *   guahao_account_create:民营医院创建账号 guahao_account_reset:民营医院重置账号密码 guahao_hospital_cancel: 民营医院侧取消订单
     * @param $msgCon
     *   guahao_success   %hospital_name% %patient_name% %visit_time% %department_name% %doctor_name% %visit_number_desc%
     *   guahao_cancel    %hospital_name% %patient_name% %visit_time% %department_name% %doctor_name%
     *   guahao_account_create    %enterprise_type% %account_number% %password%
     *   guahao_account_reset    %enterprise_type% %account_number% %password%
     *   guahao_account_reset    %patient_name% %hospital_name% %keshi_name% %doctor_name% %visit_time%
     * @return bool
     * @author xiujianying
     * @date 2021/2/2
     */
    public function send($mobile, $template, $msgCon)
    {
        $params = array(
            'mobile' => trim($mobile),
            'template' => trim($template),
            'timestamp' => $this->timestamp,
            'appname' => $this->appName,
            'token' => md5($this->timestamp . $this->appName . $this->secret),
        );
        if ($msgCon) {
            $params['attr'] = urlencode(json_encode($msgCon));
        }
        $sendUrl = $this->url . http_build_query($params);
        $res = $this->curl($sendUrl);
        if (isset($res['error']) && $res['error'] == 200) {

            return true;
        } else {
            if (isset($res['message'])) {
                $this->errorMsg = "{$res['message']}\r\n curl: {$sendUrl}";
            } else {
                $this->errorMsg = "短信发送失败未知错误\r\n result:" . json_encode($res, JSON_UNESCAPED_UNICODE| JSON_UNESCAPED_SLASHES)."\r\n curl: {$sendUrl}";
            }
            return false;
        }
    }

    /**
     * 发起请求
     * $param array $params 为空则get方式调用,$params不空为POST调用
     * @param string $url $params post数据
     */
    protected function curl($url = '', $params = [])
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面--解决301,302问题
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }


}