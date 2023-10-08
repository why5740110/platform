<?php

/**
 * 更新医生保存之后的操作
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-04-20
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\models\DoctorModel;
use yii\base\BaseObject;
use common\models\BuildToEsModel;
use common\sdks\snisiya\SnisiyaSdk;

class AfterSaveDoctorJob extends BaseObject implements \yii\queue\JobInterface
{
    public $doctor_id;
    public $hospital_id;

    public function execute($queue)
    {
    	$model = new DoctorModel();
    	//如果是主医生 更新子医生
    	$ids = $model->find()->where(['status'=>1,'primary_id'=>$this->doctor_id])->select('doctor_id,tp_platform')->asArray()->all();
    	if($ids){
    	    foreach($ids as $v){
                $this->setDoctorEs($v['doctor_id']);
            }
        }
        //如果是民营医院的医生需要自动更新es
        $doctorNum = DoctorModel::find()->where(['doctor_id' => $this->doctor_id, 'tp_platform' => 13])->count();
    	if ($doctorNum > 0) {
            $this->setDoctorEs($this->doctor_id);
        }

        $model->UpdateInfo($this->doctor_id, $this->hospital_id);
        SnisiyaSdk::getInstance()->updateScheduleCache(['doctor_id' => $this->doctor_id]);
    }

    public function setDoctorEs($doctor_id)
    {
        DoctorModel::getInfo($doctor_id,true,0);
        $es_model = new BuildToEsModel();
        $es_model->db2esByIdDoctor($doctor_id);
    }

}
