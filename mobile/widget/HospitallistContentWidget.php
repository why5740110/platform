<?php

namespace mobile\widget;


use yii\base\Widget;

class HospitallistContentWidget extends Widget
{
    public $hospital_list;
    public $pagination;
    public $totalCount;
    public $page;
    public $city = [];
    public $region;
    public $sanjia;
    public $keshi_id = 0;

    public function run()
    {
        $this->pagination->totalCount = $this->pagination->totalCount >= 400 ? 400 : $this->pagination->totalCount;
        if ($this->page > 20) {
            $this->hospital_list = [];
        }
        return $this->render('hospital_content',['hospital_list'=>$this->hospital_list,'totalCount'=>$this->totalCount,'pagination'=>$this->pagination,'page'=>$this->page,'region'=>$this->region,'sanjia'=>$this->sanjia,'keshi_id'=>$this->keshi_id,'city'=>$this->city]);
    }

}