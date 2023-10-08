<?php

namespace minyingapi\behaviors;

use common\libs\CryptoTools;

class AesChecker
{
    public $encryptKey;
    public $encryption = '';

    public function __construct($params)
    {
        $this->encryptKey = $params['encryptKey'];
        CryptoTools::setKey($this->encryptKey);
    }

    public function afterAction(&$action)
    {
        $data = $action->result;
        if (is_array($data)) {
            if (isset($data['data'])) {
                $action->result['data'] = CryptoTools::AES256ECBEncrypt(json_encode($data['data']));
            }
        }
    }

    public function beforeAction()
    {
        $getRequestData = \Yii::$app->request->get();
        $getData = \Yii::$app->request->get('data', null);
        if (\Yii::$app->request->isPost) {
            $postData = urldecode(\Yii::$app->request->post('data'));
            if (isset($postData)) {
                $tempData = CryptoTools::getDecryptedArray($postData, 2);
                if (is_array($tempData)) {
                    \Yii::$app->request->setBodyParams($tempData);
                } else {
                    \Yii::$app->request->setBodyParams([]);
                }
            }
        } else {
            if (isset($getData)) {
                $tempData = CryptoTools::getDecryptedArray(urldecode($getData), 2);
                if (is_array($tempData)) {
                    //此处用原始array替换现有array,防止通过解密数据更改参数
                    $getRequestData = array_merge($tempData, $getRequestData);
                }
            }
        }
        \Yii::$app->request->setQueryParams($getRequestData);
    }


}