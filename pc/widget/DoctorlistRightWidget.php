<?php

namespace pc\widget;

use yii\base\Widget;
use common\sdks\snisiya\SnisiyaSdk;

class DoctorlistRightWidget extends Widget
{
    public $rightlist;
    public function run()
    {
        $params['page'] =1;
        $params['pagesize'] =15;
        $snisiyaSdk = new SnisiyaSdk();
        $res = $snisiyaSdk->getDoctorList($params);
        $this->rightlist = $res['doctor_list'] ?? [];
        return $this->render('doctorlist_right',['rightlist'=>$this->rightlist]);
    }

}