<?php

namespace minyingapi\behaviors;

use common\libs\CommonFunc;
use Yii;
use common\libs\Log;

class RecordLoging extends \yii\base\Behavior
{

    public function events()
    {
        return [
            \yii\web\Controller::EVENT_AFTER_ACTION => 'afterAction',
            \yii\web\Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    /**
     * action生命周期结束时拼装需要的日志数据
     */
    public function afterAction($action)
    {
        $logData = Yii::$app->params['DataToHospitalRequest'];
        $logData['platform'] = $logData['platform'] ?? '100';

        $logData['responseData'] = $action->result;
        $logData['endAction'] = '----------------请求结束----------------';
        //unableLog=true  不记录日志
        if (!(isset($logData['unableLog']) && $logData['unableLog'])) {
            Log::pushLogDataToQueues($logData);
        }
    }

    /**
     * action生命周期开始时拼装需要的日志数据
     */
    public function beforeAction($action)
    {
        $request = Yii::$app->request;
        $requestLog['beginAction'] = '----------------请求开始----------------';
        $requestLog['TargetLink'] = '/' . ltrim($request->getPathInfo(), '/');
        $requestLog['ip'] = CommonFunc::getRealIpAddressForNginx();
        $requestLog['getParams'] = $request->get();
        $requestLog['postData'] = $request->post();
        Yii::$app->params['DataToHospitalRequest'] = $requestLog;

        //Log::pushLogDataToQueues($requestLog);
    }

    /**
     * 格式化post请求日志.
     * @param object $request 当前请求和响应
     * @return array $requestLog  格式化日志结果
     * @throws NotFoundHttpException
     * @author yueyuchao <yueyuchao@yuanxin-inc.com>
     * @time 2019/10/24
     */
    protected static function formatPostRequestLog(object $request)
    {
        $requestLog['beginAction'] = '----------------请求开始----------------';
        $requestLog['TargetLink'] = $request->url;
        $requestLog['ip'] = $request->getRemoteIP();
        $requestLog['getParams'] = $request->get();
        $requestLog['postData'] = $request->post();

        return $requestLog;
    }


}