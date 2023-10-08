<?php
/**
 * @file HospitalSdk.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/10/13
 */

namespace common\sdks;

use yii\helpers\ArrayHelper;

class HospitalSdk extends CenterSDK
{
    //protected $domain = '';

    public function __construct()
    {
        //parent::__construct();
        $this->domain = ArrayHelper::getValue(\Yii::$app->params, 'api_url.base');
    }

    //路由配置
    private $maps = [
        'detail' => '/hospital/getdetail',//医院详情
        'search' => '/hospital/search',//医院搜索
    ];

    /**
     * 医院详情
     * @param $hospital_id
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/10/13
     */
    public function getDetail($hospital_id)
    {
        $params = [
            'hospital_id' => $hospital_id,
        ];
        $result = $this->send($this->maps['detail'] ,$params);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }

    /**
     * 搜索
     * @param array $params 搜索条件  【'id'=>'111','name'=>'xx']
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/10/13
     */
    public function search($params = [])
    {
        $result = $this->send($this->maps['search'] ,$params);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }
}