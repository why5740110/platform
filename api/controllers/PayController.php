<?php
/**
 * @file PayController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/12/2
 */


namespace api\controllers;


use common\libs\CommonFunc;
use common\libs\Log;
use nisiya\paysdk\CryptoTools;
use yii\helpers\ArrayHelper;

class PayController extends CommonController
{

    public $data;

    public function init()
    {
        parent::init();
        //解析支付回调 传参
        $requestData = \Yii::$app->request->post('data');
        $this->data = CommonFunc::validatorBackData($requestData);

    }

    /**
     * 支付异步回调接口
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/2
     */
    public function actionPayBack()
    {
        $logs = '';
        $miao_order_id_sn = ArrayHelper::getValue($this->data, 'pay_out_trade_no');
        //$pay_fee = ArrayHelper::getValue($this->data, 'pay_true_fee');
        $logs .= 'orderid:'.$miao_order_id_sn.'-json:'.json_encode($this->data);
        //处理回调
        $res = CommonFunc::payBack($this->data);
        //$res['code'] = 1;
        if ($res['code'] == 1) {
            $return = 'success';
            $logs_res = '-success';
        } else {
            $return = $this->jsonError($res['msg']);
            $logs_res = '-error-'.$res['msg'];
        }
        $logs = '支付回调-'.$logs.$logs_res;

        $logData['request_type'] = 'PayBack';
        $logData['log'] = $logs;
        Log::pushLogDataToQueues($logData);

        return $return;
    }

    /**
     * 退款回调
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/2
     */
    public function actionRefundBack()
    {
        $logs = '';
        //$type = \Yii::$app->request->get('type'); //好大夫：hdf
        $miao_order_id_sn = ArrayHelper::getValue($this->data, 'pay_out_trade_no');
        $logs .= 'order_id:'.$miao_order_id_sn;
        //处理回调
        $res = CommonFunc::refundBack($miao_order_id_sn);
        $logs .= '--res:'.json_encode($res).'--data:'.json_encode($this->data).'--requestdata:'.\Yii::$app->request->get('data');

        $logs .= '--post:'.json_encode(\Yii::$app->request->post());

        $logData['request_type'] = 'RefundBack';
        $logData['platform'] = '200';
        $logData['log'] = $logs;
        Log::pushLogDataToQueues($logData);

        if ($res['code'] == 1) {
            return 'success';
        } else {
            return $this->jsonError($res['msg']);
        }

    }
//    public function actionGetredis(){
//        $type = \Yii::$app->request->get('type', 'pay');
//        $redis = \Yii::$app->redis_codis;
//        echo '<pre>';
//        if($type=='haodaifu'){
//            $arr = $redis->LRANGE('haodaifu:log',0,100);
//            print_r($arr);
//            exit;
//        }
//        $arr = $redis->LRANGE('key1',0,-1);
//        print_r($arr);
//
//        exit;
//    }
}