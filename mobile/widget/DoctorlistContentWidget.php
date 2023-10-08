<?php

namespace mobile\widget;


use yii\base\Widget;

class DoctorlistContentWidget extends Widget
{
    public $doctorlist;
    public $pagination;
    public $totalCount;
    public $page;
    public $region;
    public $sanjia;

    public function run()
    {
        $this->pagination->totalCount = $this->pagination->totalCount >= 400 ? 400 : $this->pagination->totalCount;
        if ($this->page > 20) {
            $this->doctorlist = [];
        }
        return $this->render('doctorlist_content',['doctorlist'=>$this->doctorlist,'totalCount'=>$this->totalCount,'pagination'=>$this->pagination,'page'=>$this->page,'region'=>$this->region,'sanjia'=>$this->sanjia]);
    }

}