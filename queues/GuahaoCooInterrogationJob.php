<?php

/**
 * 合作方用户信息
 * @file GuahaoCooInterrogationJob.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2021-06-23
 */

namespace queues;

use yii\base\BaseObject;
use common\models\GuahaoCooInterrogationModel;

class GuahaoCooInterrogationJob extends BaseObject implements \yii\queue\JobInterface
{
    public $data;

    public function execute($queue)
    {
        GuahaoCooInterrogationModel::addInfo($this->data);
    }
}
