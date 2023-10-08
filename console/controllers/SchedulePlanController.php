<?php
/**
 * 民营医院出诊计划定时脚本任务 schedule-plan/visit-plan
 * SchedulePlanController.php
 * @author wanghongying<wanghongying@yuanyinjituan.com>
 * @date 2022-07-21
 */
namespace console\controllers;

use common\models\SchedulePlanModel;
use common\models\ScheduleClosePlanModel;
use common\models\GuahaoOrderModel;
use queues\SchedulePlanJob;
class SchedulePlanController extends \yii\console\Controller
{
    public $start_time;

    public $limit  = 50;


    public function init()
    {
        parent::init();
        $this->start_time = microtime(true);
    }

    //处理出诊计划
    public function actionVisitPlan()
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：" . PHP_EOL;
        $page = 1;
        do {
            $param = [
                "is_done" => "0",
                "page" => $page,
                "limit" => $this->limit,
            ];

            $list = SchedulePlanModel::getList($param);

            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $info) {
                    //队列生成
                    $queue['id'] = $info['id'];
                    $queue['type'] = 1;//出诊计划操作
                    $job_id = \Yii::$app->addvisitscheduleplan->delay(10)->push(new SchedulePlanJob($queue));

                    //更改停诊计划状态 is_done=1
                    $planModel = SchedulePlanModel::findOne($info['id']);
                    $planModel->is_done = 1;
                    $planModel->save();

                    echo "[" . date('Y-m-d H:i:s') . "] 出诊计划ID:{$info['id']} 处理完成，异步任务ID：{$job_id} " . PHP_EOL;
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！" . PHP_EOL;
            $page++;
            $dataCount = count($list);
        } while ($dataCount > 0);
        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！" . PHP_EOL;
    }
}
