<?php
/**
 * Created by PhpStorm.
 * @file AliVerification.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-03-04
 */

namespace common\libs;


use yii\helpers\ArrayHelper;

class AliVerification

{
    private $_key = ""; // 这个待确定约定的key

    public function __construct($key=null)
    {
        $this->_key = ArrayHelper::getValue(\Yii::$app->params, 'ali_healthy.encryptKey');
    }
    public function makeSign($data,$params='')
    {
        $keyData = [];
        unset($data['sign']);
        foreach($data as $key=>$val){
            array_push($keyData, $key);
        }
        sort($keyData);
        $signParamsKV = [];
        foreach ($keyData as $k=>$v) {
            array_push($signParamsKV,$v.strval($data[$v]));
        }
        $buff = implode('',$signParamsKV);

        if ($params) {
            $buff .= $params;
        }
        $str = str_replace(array("\r\n", "\r", "\n"), "", $this->_key.$buff.$this->_key);
        $sign =  strtoupper(md5($str));
        return $sign;
    }

}