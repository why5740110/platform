<?php

namespace mobile\widget;


use common\models\DoctorModel;
use common\libs\HashUrl;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class RegisterDetailWidget extends Widget
{
    public $visit_starttime;
    public $visit_endtime;
    public $visit_time;
    public $famark_type;
    public $hospital_name;
    public $department_name;
    public $card_type;
    public $doctor_name;
    public $doctor_title;
    public $visit_cost;
    public $tp_order_id;
    public $create_time;
    public $tp_platform;
    public $visit_nooncode;
    public $allowed_cancel_day;
    public $allowed_cancel_time;
    public $patient_name;
    public $card;
    public $state;
    public $pay_status;
    public $cancel_status;
    public $ua;
    public $doctor_id;
    public $pay_url;
    public $source_from;
    public $symptom;
    public $visit_type;
    public $pay_mode;
    public $times;
    public $is_disable;
    public $hospital_address;
    public $update_time;
    public $tp_guahao_description;
    public $cancel_time;
    public function run()
    {
        //获取主医生id
        $infos = DoctorModel::getInfo($this->doctor_id);
        $primary_id = HashUrl::getIdDecode(ArrayHelper::getValue($infos, 'primary_id'));
        $this->doctor_id = $primary_id ?: $this->doctor_id;

        return $this->render('register_detail',[
            'visit_time'=>$this->visit_time,
            'visit_starttime'=>$this->visit_starttime,
            'visit_endtime'=>$this->visit_endtime,
            'famark_type'=>$this->famark_type,
            'hospital_name'=>$this->hospital_name,
            'department_name'=>$this->department_name,
            'card_type'=>$this->card_type,
            'doctor_name'=>$this->doctor_name,
            'doctor_title'=>$this->doctor_title,
            'visit_cost'=>$this->visit_cost,
            'tp_order_id'=>$this->tp_order_id,
            'create_time'=>$this->create_time,
            'tp_platform'=>$this->tp_platform,
            'visit_nooncode'=>$this->visit_nooncode,
            'allowed_cancel_day'=>$this->allowed_cancel_day,
            'allowed_cancel_time'=>$this->allowed_cancel_time,
            'patient_name'=>$this->patient_name,
            'card'=>$this->card,
            'state'=>$this->state,
            'pay_status'=>$this->pay_status,
            'cancel_status'=>$this->cancel_status,
            'ua'=>$this->ua,
            'doctor_id'=>$this->doctor_id,
            'pay_url'=>$this->pay_url,
            'source_from'=>$this->source_from,
            'symptom'=>$this->symptom,
            'visit_type'=>$this->visit_type,
            'pay_mode'=>$this->pay_mode,
            'times'=>$this->times,
            'is_disable'=>$this->is_disable,
            'hospital_address'=>$this->hospital_address,
            'update_time'=>$this->update_time,
            'tp_guahao_description'=>$this->tp_guahao_description,
            'cancel_time'=>$this->cancel_time,
        ]);
    }

}