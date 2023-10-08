<?php
/**
 * @file HospitalNanCrumbs.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/28
 */

namespace pc\widget;


use yii\base\Widget;

class HospitalNavCrumbs extends Widget
{
    public $hosp_data;
    public $hospital_id;
    public $action;

    public function run()
    {
        $data = [
            'hosp_data'=>$this->hosp_data,
            'hospital_id'=>$this->hospital_id,
        ];
        return $this->render('hospital_nav_crumbs',$data);
    }

}