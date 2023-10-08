<?php
/**
 * @file OrtherHospital.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/31
 */


namespace pc\widget;


use common\sdks\snisiya\SnisiyaSdk;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class OrtherHospital extends Widget
{

    public $city_id=0;  //城市id
    public $type = '';     //综合/专科
    public $hospital_id = '';

    public function run(){

        $params['city_id'] = $this->city_id;
        $params['hospital_type'] = $this->type;
        $params['pagesize'] = 6;
        $snisiyaSdk = new SnisiyaSdk();
        $res = $snisiyaSdk->getHospitalList($params);
        $data = ArrayHelper::getValue($res,'hospital_list');

        return $this->render('orther_hospital',['data'=>$data,'hospital_id'=>$this->hospital_id]);
    }
}