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

class DoctorInfoMenu extends Widget
{
    public $url;
    public $id;
    public function run()
    {
        return $this->render('doctorinfomenu',['url'=>$this->url,'id'=>$this->id]);
    }

}