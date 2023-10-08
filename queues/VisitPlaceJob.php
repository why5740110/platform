<?php

/**
 * 异步拉取第三方执业地列表
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-03-19
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\models\TbDoctorThirdPartyRelationModel;
use yii\base\BaseObject;

class VisitPlaceJob extends BaseObject implements \yii\queue\JobInterface
{
    public $doctor_id;
    public $tp_doctor_id;

    public function execute($queue)
    {
        TbDoctorThirdPartyRelationModel::pullVisitPlace($this->doctor_id, $this->tp_doctor_id);
    }

}
