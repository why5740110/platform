<?php

namespace api\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use common\sdks\BaiduGuahaoSdk;

class ComplainController extends CommonController
{
    /**
     * 反馈后台申诉处理结果给百度
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-17
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionFeedback()
    {
        $push_url  = '/test/zjsvctp/zhuanjia/rsapi/svcsynccomplaint';
        $request   = Yii::$app->request;
        $post      = $request->post();
        \Yii::warning('推送申诉处理日志:' . json_encode($post), __CLASS__ . '::' . __METHOD__ . ' 推送申诉处理日志');
        $push_data = [
            // 'from'           => 'msys',
            'tp_complain_id' => ArrayHelper::getValue($post, 'tp_complain_id', 0), //TP方的申诉ID
            // 'msg_id'         => $this->requestID, //唯一消息id标示 ，32位
        ];
        //申诉状态信息详情 json
        $status_info              = ArrayHelper::getValue($post, 'status_info', []);
        $push_data['status_info'] = json_encode($status_info);
        $res = BaiduGuahaoSdk::getInstance()->syncComplain($push_data);
        return $this->jsonSuccess($res);
    }

}
