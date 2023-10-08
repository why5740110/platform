<?php
/**
 * 医院api 基础sdk
 * @file HapiSdk.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2019/9/18
 */

namespace common\sdks;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class HapiSdk extends BaseSdk
{

    protected $domain = '';
    public function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['self'];
    }

    //路由配置
    private $maps = [
        'get-order-detail'=>'/guahao/get-order',//挂号订单详情
        'get-order-list'=>'/guahao/get-order-list',//挂号订单详情
    ];

    /**
     *Notes:获取挂号订单详情
     *User:lixiaolong
     *Date:2020/12/3
     *Time:17:34
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     */
    public function getOrderDetail($params = [])
    {
        $result = $this->curl($this->maps['get-order-detail'] . '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * 获取订单列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-02-01
     * @version 1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getOrderList($params = [])
    {
        $result = $this->curl($this->maps['get-order-list'] . '?' . http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data.list',[]);
        }else{
            return  [];
        }
    }


}