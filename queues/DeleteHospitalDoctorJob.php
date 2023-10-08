<?php

/**
 * 删除医院以及医院下的医生
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-04-20
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\models\HospitalEsModel;
use yii\base\BaseObject;

class DeleteHospitalDoctorJob extends BaseObject implements \yii\queue\JobInterface
{
    public $hospital_id;
    public $delete_doctor;

    public function execute($queue)
    {
        HospitalEsModel::updateHospitalDoctorEsDataByHospital($this->hospital_id, $this->delete_doctor);
    }

}
