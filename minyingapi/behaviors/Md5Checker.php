<?php

namespace minyingapi\behaviors;

class Md5Checker
{
    private $encryptKey;
    public $validateList = [
        'validateNonceStr',
        'validateSign',
    ];

    public function __construct($params)
    {
        $this->encryptKey = $params['encryptKey'];
    }

    public function validateNonceStr()
    {
        $request = \Yii::$app->request;
        $nonceStr = $request->get('noncestr');
        if (empty($nonceStr)) {
            $nonceStr = $request->post('noncestr');
        }
        if (empty($nonceStr)) {
            return ['noncestr', 'noncestr 不能为空'];
        }
        if (strlen($nonceStr) !== 6) {
            return ['noncestr', 'noncestr 只接受6位字符串'];
        }
    }

    public function validateSign()
    {
        $request = \Yii::$app->request;
        $requestData = $request->get();
        if (!isset($requestData['sign'])) {
            $requestData['sign'] = $request->post('sign');
        }
        if (!isset($requestData['sign'])) {
            return ['sign', 'sign不存在'];
        }
        $sign = $requestData['sign'];
        if ($request->isPost) {
            $requestData = array_merge($requestData, $request->post());
        }
        unset($requestData['sign']);
        $sysSign = $this->makeSign($requestData);
        if ($sign != $sysSign) {
            return ['sign', 'sign校验失败'];
        }
    }

    /**
     * 生成签名
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-08-31
     * @param $data
     * @return string
     */
    protected function makeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->encryptKey;
        //header("string:".$string);
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-08-31
     * @param $data
     * @return string
     */
    public function toUrlParams($data)
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

    public function beforeAction()
    {

    }

    public function afterAction()
    {

    }
}