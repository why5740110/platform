<?php

namespace queues;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use common\models\LogHospitalApiLogNew;

class AsyncLogingJobNew extends BaseObject implements \yii\queue\JobInterface
{
    public $logData;

    public function execute($queue)
    {

        $logSourceData = [];
        if (is_object($this->logData)) {
            $logSourceData = ArrayHelper::toArray($this->logData);
        } elseif (is_array($this->logData)) {
            $logSourceData = $this->logData;
        } elseif (json_decode($this->logData)) {
            $logSourceData = json_decode($this->logData, true);
        } elseif (!empty($this->logData)) {
            $logSourceData['msg'] = $this->logData;
        }
        $logSourceData['platform'] = isset($logSourceData['platform']) && !empty($logSourceData['platform']) ? $logSourceData['platform'] : '100';

        if (!empty($logSourceData)) {
            $now_time = time();
            $logSourceData['logTime'] = date('Y-m-d H:i:s', $now_time);
            $logModel = new LogHospitalApiLogNew();
            $logModel->platform = $logSourceData['platform'];
            $logModel->request_type = isset($logSourceData['request_type']) && !empty($logSourceData['request_type']) ? $logSourceData['request_type'] : '';
            $logModel->index = isset($logSourceData['index']) && !empty($logSourceData['index']) ? $logSourceData['index'] : '';
            $logModel->log_detail = json_encode($logSourceData);
            $logModel->create_time = $now_time;
            $logModel->spend_time = ArrayHelper::getValue($logSourceData, 'cur_log.log_spend_time', 0);
            $logModel->code = ArrayHelper::getValue($logSourceData, 'cur_log.log_code', 0);
            $logModel->save();
        }
    }

}
