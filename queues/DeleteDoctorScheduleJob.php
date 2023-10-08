<?php
/**
 * Created by PhpStorm.
 * @file DeleteDoctorScheduleJob.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-12-15
 */


namespace queues;

use common\libs\CommonFunc;
use common\models\GuahaoScheduleModel;
use common\models\TbLog;
use yii\base\BaseObject;



class DeleteDoctorScheduleJob extends BaseObject implements \yii\queue\JobInterface
{
    public $tp_doctor_id;
    public $doctor_id;
    public $tp_platform;
    public $admin_name;
    public $admin_id;

    public function execute($queue)
    {
        GuahaoScheduleModel::deleteByDoctorId($this->tp_doctor_id, $this->doctor_id, $this->tp_platform);
        $adminInfo['admin_name'] =  $this->admin_name;
        $adminInfo['admin_id'] =  $this->admin_id;
        $deleteScheduleContent  = $this->admin_name .'删除医生ID'.strval($this->doctor_id)."排班";
        //TbLog::addLog($deleteScheduleContent, '删除医生排班',$adminInfo);
    }

}
