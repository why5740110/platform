<?php
/**
 * @file BaiduGuahaoSdk.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/22
 */


namespace common\sdks;


use common\libs\HashUrl;
use GuzzleHttp\Client;
use common\libs\Log;
use yii\helpers\ArrayHelper;

class BaiduGuahaoSdk
{
    protected static $_instance = null;

    public $domain = '';

    public $msg_id = '';

    /**
     * 百度约定的加密key
     * @var string[]
     */
    public $cipher = ['cipherid' => 'id', 'key' => 'key'];

    /**
     * @var array
     */
    public $cipherKv = [];

    /**
     * @var array 请求接口返回的结果
     */
    protected $result = [];

    public $baseParams = [];

    public function __construct()
    {
        $this->msg_id = md5(uniqid() . microtime() . mt_rand(111111, 999999));

        $this->domain = ArrayHelper::getValue(\Yii::$app->params, 'api_url.baidugh');

        //约定的key
        $this->cipher['cipherid'] = ArrayHelper::getValue(\Yii::$app->params,'baiduguahao.cipherid');
        $this->cipher['key'] = ArrayHelper::getValue(\Yii::$app->params,'baiduguahao.key');
        $this->cipherKv[$this->cipher['cipherid']] = $this->cipher['key'];

        $this->baseParams= [
            'from' => 'nisiya',
            'msg_id' => $this->msg_id,
            'cipherid' => $this->cipher['cipherid'],
            'atime' => time(),
        ];

    }

    /**
     * 单例
     * @return static
     * @author xiujianying
     * @date 2021/6/22
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    //路由配置
    private $maps = [
        'sync_complain' => '/zhuanjia/rsapi/svcsynccomplaint',//推送申诉结果
        /*'doctor' => '/zhuanjia/rsapi/svcnotify',//推送医生   zjsvctp
        'order' => '/zhuanjia/rsapi/svcnotify', //推送订单状态
        'schedule' => '/zhuanjia/rsapi/svcnotify', //推送排班信息*///老的域名 （暂停使用）

        'doctor' => '/tpadapter/rsapi/svcnotify',//推送医生   zjsvctp
        'order' => '/tpadapter/rsapi/svcnotify', //推送订单状态
        'schedule' => '/tpadapter/rsapi/svcnotify', //推送排班信息
    ];

    /**
     * 医生变更/新增 推送给百度
     * @param $id  医生id
     * @param $action 1 新增 2修改  3删除
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/22
     */
    public function pushDoctor($id, $action)
    {
        $op = '';
        if ($action == 'add') {
            $op = '1';
        } elseif ($action == 'edit') {
            $op = '2';
        }elseif ($action == 'del') {
            $op = '3';
        }

//        $id = HashUrl::getIdEncode($id);
        $params = [
            'code' => 1,
            'content' => json_encode(['expertId' => $id, 'op' => $op]),
        ];
        $result = $this->curl($this->maps['doctor'] . '?' . http_build_query($params));
        return $result;
    }

    /**
     *订单变更推送给百度
     * @param $order_sn  挂号订单号
     * @param $tp_status   COMPLETE - 超过就诊时间自动核销完成   TPCANCELRESULT - TP方已取消结果  取消
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/23
     */
    public function pushOrderStatus($order_sn,$tp_status){

        if($order_sn && $tp_status) {
            $params = [
                'code' => 17,
                'content' => json_encode(['order_id' => $order_sn, 'tp_status' => $tp_status, 'reason' => []]),
            ];
            $result = $this->curl($this->maps['order'] . '?' . http_build_query($params));
            return $result;
        }else{
            return [];
        }
    }

    /**
     * 同步给百度申诉结果
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-22
     * @version v1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function syncComplain($params = [])
    {
        $result = $this->curl($this->maps['sync_complain'],$params);
        //记录日志
        $new_job_id = Log::pushLogDataToQueues([
            'platform'=>'301',
            'index'=> (string)ArrayHelper::getValue($params,'tp_complain_id',''),
            'request_type'=> '4',
            'res'=> $result,
            'cur_log' => $params,
        ]);
        return $result;
    }

    /**
     * 排班变更通知
     * @param $schedule
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/26
     */
    public function pushSchedule($schedule)
    {
        $params = [
            'code' => 15,
            'content' => json_encode($schedule),
        ];
        $result = $this->curl($this->maps['schedule'], $params);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data');
        } else {
            return [];
        }
    }


    public function curl($uri, $params = [], $timeout = 10)
    {
        $url = $this->getApiUri($uri);
        $arr = explode('?', $url);
        if (!isset($arr[1])) {
            return false;
        }
        $http_build_query = $arr[1];
        parse_str($http_build_query, $baseParams);
        $sign = $this->makeSign(array_merge($baseParams, $params));
        $this->url = $url . '&sign=' . $sign;
        $this->params = $params;
        //echo $this->url . "\n";

        $log_start_time = microtime(true);
        try {
            $client = new Client([
                'base_uri' => $this->domain,
                'timeout' => $timeout,
            ]);
            if (!empty($params)) {
                $response = $client->post($this->url, ['form_params' => $params]);
            } else {
                $response = $client->get($this->url);
            }
        } catch (\Throwable $e) {
            $log_end_time = microtime(true);
            $log_spend_time = round($log_end_time - $log_start_time, 2);
            //$this->curlError($e->getCode(), $e->getMessage());
            $logData = [
                'curlAction' => '----------------请求第三方接口----------------',
                'curlError' => ['code'=>$e->getCode(),'msg'=>$e->getMessage()],
                'curlUrl' => $this->url,
                'curlParams' => $this->params,
                'log_spend_time' => $log_spend_time,
                'log_code' => 500,
            ];
            \Yii::$app->params['DataToBaiDuRequest'] = $logData;
            return false;
        }
        $this->result = $response->getBody()->getContents();

        $log_end_time = microtime(true);
        $log_spend_time = round($log_end_time - $log_start_time, 2);

        $logData = [
            'curlAction' => '----------------请求第三方接口----------------',
            'curlUrl' => $this->url,
            'curlParams' => $this->params,
            'curlReturnData' => $this->result,
            'log_spend_time' => $log_spend_time,
            'log_code' => 200,
        ];
        \Yii::$app->params['DataToBaiDuRequest'] = $logData;


        return json_decode($this->result, true);
    }

    /**
     * 修饰请求url
     * @param $uri
     * @return string
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-25
     */
    protected function getApiUri($uri)
    {
        if (strpos($uri, '?')) {
            $delimiter = '%s%s&%s';
        }else {
            $delimiter = '%s%s?%s';
        }
        return sprintf(
            $delimiter,
            $this->domain,
            $uri,
            http_build_query($this->baseParams)
        );
    }

    /**
     * @param $data
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/22
     */
    protected function makeSign($data)
    {
        $cipherid = ArrayHelper::getValue($data, 'cipherid');
        $key = ArrayHelper::getValue($this->cipherKv, $cipherid);
        //签名步骤一：按字典序排序参数
        ksort($data);
        //$string = $this->toUrlParams($data);
        $string = http_build_query($data);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        //$result = strtoupper($string);
        return $string;
    }


}