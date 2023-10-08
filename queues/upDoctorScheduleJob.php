<?php

/**
 * 更新医生排班队列
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-03-19
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\sdks\snisiya\SnisiyaSdk;
use yii\base\BaseObject;

class upDoctorScheduleJob extends BaseObject implements \yii\queue\JobInterface
{
    public $params;

    public function execute($queue)
    {
    	$snisiyaSdk = new SnisiyaSdk();
        $snisiyaSdk->updateScheduleCache($this->params);
    }

}
