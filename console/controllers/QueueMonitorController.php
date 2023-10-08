<?php
/**
 * 队列监控
 * @file QueueMonitorController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-08-15
 */
namespace console\controllers;

use common\libs\Log;
class QueueMonitorController extends CommonController
{
    /**
     * 监控队列长度
     * @param int $queueLen
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-08-15
     */
    public function actionQueueMonitor($queueLen = 100)
    {
        $queues = [
            'nisiya.top'  => ['delschedule','delschedule2','addvisitscheduleplan','addclosescheduleplan','delscheduleplan','delschedulecloseplan']
        ];
        $title = "队列有积压,所属项目：：【nisiya.top】" . PHP_EOL;
        $msg = "";
        foreach ($queues as $key => $item) {
            foreach ($item as $value) {

                $command = sprintf('/usr/local/php7/bin/php /data/wwwroot/%s/yii %s/info', $key, $value);
                $result = shell_exec($command);
                if (!$result) {
                    continue;
                }

                $arr = explode('-', $result);
                if (empty($arr) || !isset($arr[1])) {
                    continue;
                }
                $waiting = explode(':', $arr[1]);
                $count   = (int)end($waiting);

                if ($count > $queueLen) {
                    $msg .= sprintf('队列：【%s】,当前数量【%s】条。', $value, $count) . PHP_EOL;
                }
            }
        }

        if (!empty($msg)) {
            Log::sendGuaHaoErrorDingDingNotice($title . $msg);
            echo $title . $msg . PHP_EOL;
        }
    }


    /**
     * 监控队列未消费数量  每15分钟执行一次
     * php ./yii queue-monitor/run
     * @param int $limit
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-09-07
     */
    public function actionRun($limit = 1000)
    {
        $queues = [
            'queue','logqueue','logqueue2','slowqueue','guahaopush','guahaopush2','ghcoopatient','delschedule','delschedule2',
            'addvisitscheduleplan','addclosescheduleplan','delscheduleplan','delschedulecloseplan'
        ];
        $title = "队列有积压,所属项目：【nisiya.top】" . PHP_EOL;
        $msg = "";
        foreach ($queues as $key => $queueName) {
            $queueObj = \Yii::$app->$queueName;
            if(empty($queueObj)){
                return false;
            }

            $prefix = $queueObj->channel;
            $queueTotal = $queueObj->redis->llen("$prefix.waiting");

            if ($queueTotal > $limit) {
                $msg .= $prefix . '队列未消费总数: '.$queueTotal . PHP_EOL;
            }
        }

        if(!empty($msg)) {
            Log::sendGuaHaoErrorDingDingNotice($title . $msg);
            echo $title . $msg . PHP_EOL;
        }
    }
}