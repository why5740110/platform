<?php

/**
* 更新医院科室缓存
* @author yangquanliang <yangquanliang@yuanxin-inc.com>
* @date    2021-03-19
* @version 1.0
* @param   [type]     $queue [description]
* @return  [type]            [description]
*/

namespace queues;

use common\libs\CommonFunc;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class DeparmentCacheJob extends BaseObject implements \yii\queue\JobInterface
{
    public $doctor_id;
    public $hospital_id;

    public function execute($queue) 
    {
        // CommonFunc::UpdateInfo($this->doctor_id,$this->hospital_id);
        CommonFunc::UpHospitalCache($this->hospital_id);
    }

}