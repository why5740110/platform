<?php

namespace mobile\widget;


use yii\base\Widget;

class SelectWidget extends Widget
{
    public $sanjia;
    public $ua;
    public function run()
    {

        return $this->render('select',['sanjia'=>$this->sanjia,'ua'=>$this->ua]);
    }

}