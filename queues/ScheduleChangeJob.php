<?php

/**
 * 排班变更队列
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-03-19
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\sdks\snisiya\SnisiyaSdk;
use yii\base\BaseObject;
use common\models\HospitalDepartmentRelation;
use yii\helpers\ArrayHelper;

class ScheduleChangeJob extends BaseObject implements \yii\queue\JobInterface
{
    public $postData;

    public function execute($queue)
    {
        $snisiyaSdk = new SnisiyaSdk();
        $result       = $snisiyaSdk->scheduleChange($this->postData);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
             echo "[" . date('Y-m-d H:i:s') . "] " . "succ！\n";
        } else {
            $msg = ArrayHelper::getValue($result, 'msg', '请求失败！');
            echo "[" . date('Y-m-d H:i:s') . "] " . "error ".$msg."\n";
        }
    }

}
