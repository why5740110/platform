<?php
/**
 *
 * @file DetailLeftWidget.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-03-16
 */

namespace pc\widget;


use yii\base\Widget;

class DoctorService extends Widget
{
    public $doctor_info;

    public function run()
    {
        return $this->render('doctorservice',['doctor_info'=>$this->doctor_info]);
    }

}