<?php
/**
 * 禁用医院后的操作
 * @file AfterDisableHospitalJob.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-12-08
 */

namespace queues;

use common\models\GuahaoScheduleModel;
use yii\base\BaseObject;
use common\sdks\snisiya\SnisiyaSdk;

class DeleteScheduleJob extends BaseObject implements \yii\queue\JobInterface
{
    public $tp_hospital_code;
    public $tp_platform;
    public $admin_name;
    public $admin_id;

    public function execute($queue)
    {
        $scheduleWhere = [
            'tp_platform' => $this->tp_platform,
            'tp_scheduleplace_id' => $this->tp_hospital_code,
            'status' => [0,1,2,3]
        ];
        $scheduling_ids = GuahaoScheduleModel::find()->select('scheduling_id')->where($scheduleWhere)->asArray()->column();
        if (!empty($scheduling_ids)) {
            GuahaoScheduleModel::updateAll(
                ['status' => 4],
                $scheduleWhere
            );

            $scheduling_id = implode(',', $scheduling_ids);
            //更新排班缓存
            $snisiyaSdk = new SnisiyaSdk();
            $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
        }
    }
}
