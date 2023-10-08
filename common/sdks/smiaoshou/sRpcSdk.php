<?php
/**
 * s.接口相关
 * @file sRpcSdk.php.
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2020-07-30
 * @version 1.0
 */

namespace common\sdks\snisiya;

use common\sdks\HttpBaseSdk;
use yii\helpers\ArrayHelper;


/**
 *  使用示例
 *  $data = [];
    $data['patientInfo'] = [];
    $data['hospitalList'] = [];
    // 生成请求    
    sRpcSdk::getInstance()->patientInfo($data['patientInfo']);
    sRpcSdk::getInstance()->hospitalList($data['hospitalList']);
    sRpcSdk::getInstance()->startAsync();
    echo "<pre>";print_r($data);die();
 */
class sRpcSdk extends HttpBaseSdk
{
    protected $domain = '';
    public function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['sapi'];
    }

    //路由配置
    private $maps = [
        'get_hospital_list' => '/hospital/get-list', //首页广告轮播
        'patient_feed'      => '/patient/feed', //首页广告轮播
        'get_doctor_info'=>'/doctor/get-hospital-doctor-detail',//医生详情
        'doctor_list'       => '/doctor/get-list', //医生列表
        'search_list'       => '/hospital/search', //搜索结果列表
    ];

    /**
     * 获取患端详情测试
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-30
     * @version 1.0
     * @param   [type]     &$result [返回的数据名称]
     * @param   array      $opt  [url拼接参数]
     * @param   array      $params  [post参数，get不用]
     * @return  [type]              [description]
     */
    public function patientInfo(&$result, $opt = [], $params = [])
    {
        $url = $this->maps['patient_feed'] . '?' . http_build_query($opt);
        return $this->getAsync($url, $params, function ($data) use (&$result) {
            if(is_array($data) && ArrayHelper::getValue($data,'code') == 200){
                $data = ArrayHelper::getValue($data,'data');
            }else{
                $data = [];
            }
            $result = $data;
        });
    }

    /**
     * 获取医院列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-30
     * @version 1.0
     * @param   [type]     &$result [返回的数据名称]
     * @param   array      $opt  [参数]
     * @param   array      $params  [post参数，get不用]
     * @return  [type]              [description]
     */
    public function hospitalList(&$result, $opt = [], $params = [])
    {
        $url = $this->maps['get_hospital_list'] . '?' . http_build_query($opt);
        return $this->getAsync($url, $params, function ($data) use (&$result) {
            if(is_array($data) && ArrayHelper::getValue($data,'code') == 200){
                $data = ArrayHelper::getValue($data,'data');
            }else{
                $data = [];
            }
            $result = $data;
        });
    }

    /**
     * 医生列表
     * @param $result
     * @param array $opt
     * @param array $params
     * @return bool|\GuzzleHttp\Promise\PromiseInterface
     * @author xiujianying
     * @date 2020/7/31
     */
    public function doctorList(&$result, $opt = [], $params = [])
    {
        $url = $this->maps['doctor_list'] . '?' . http_build_query($opt);
        return $this->getAsync($url, $params, function ($data) use (&$result) {
            if(is_array($data) && ArrayHelper::getValue($data,'code') == 200){
                $data = ArrayHelper::getValue($data,'data');
            }else{
                $data = [];
            }
            $result = $data;
        });
    }

    /**
     * 获取搜索结果
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-10
     * @version 1.0
     * @param   [type]     &$result [description]
     * @param   array      $opt     [description]
     * @param   array      $params  [description]
     * @return  [type]              [description]
     */
    public function search_list(&$result, $opt = [], $params = [])
    {
        $url = $this->maps['search_list'] . '?' . http_build_query($opt);
        return $this->getAsync($url, $params, function ($data) use (&$result) {
            if(is_array($data) && ArrayHelper::getValue($data,'code') == 200){
                $data = ArrayHelper::getValue($data,'data');
            }else{
                $data = [];
            }
            $result = $data;
        });
    }
}
