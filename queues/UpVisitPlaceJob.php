<?php

/**
 * 更新第三方医生出诊机构状态
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-03-19
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\models\TbDoctorThirdPartyRelationModel;
use yii\base\BaseObject;

class UpVisitPlaceJob extends BaseObject implements \yii\queue\JobInterface
{
    public $postData;

    public function execute($queue)
    {
        TbDoctorThirdPartyRelationModel::uPVisitPlace($this->postData);
    }

}
