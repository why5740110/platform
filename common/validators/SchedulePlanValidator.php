<?php

/**
 * 验证民营医院排班计划数据
 * @file SchedulePlanValidator.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-11
 */

namespace common\validators;

use yii\base\Model;
use common\libs\CommonFunc;

class SchedulePlanValidator extends Model
{
    public $tp_platform;
    public $doctor_name;
    public $section_type;
    public $visit_type;
    public $stop_visit_type;
    public $stop_visit_list;
    public $visit_content;
    public $starttime;
    public $endtime;
    public $visit_cost;
    public $schedule_count;






    public function rules()
    {
        if ($this->section_type == 1) {
            return [
                ['tp_platform', 'required', 'message' => '第三方平台类型不能为空'],
                ['tp_platform', 'in', 'range' => array_keys(CommonFunc::getTpPlatformNameList(1)), 'message' => '第三方平台类型有误'],//1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号)



                ['tp_hospital_code', 'required', 'message' => '第三方医院id不能为空'],
                ['tp_hospital_code', 'string', 'length' => [1, 100], 'message' => '第三方医院id长度过长'],
                ['tp_department_id', 'required', 'message' => '第三方科室id不能为空'],
                ['tp_department_id', 'string', 'length' => [1, 100], 'message' => '第三方科室id长度过长'],
                ['department_name', 'required', 'message' => '第三方科室名称不能为空'],
                ['department_name', 'string', 'length' => [1, 50], 'message' => '第三方科室名称长度过长'],
                ['tp_frist_department_id', 'string', 'length' => [1, 100], 'message' => '第三方一级科室id长度过长'],
                ['tp_frist_department_name', 'string', 'length' => [1, 50], 'message' => '第三方一级科室名称长度过长'],
                ['hospital_id', 'required', 'message' => '王氏医院id不能为空'],
                ['hospital_id', 'integer', 'message' => '王氏医院id类型错误'],
            ];
        } else {
            return [
                ['tp_platform', 'required', 'message' => '第三方平台类型不能为空'],
                ['tp_platform', 'in', 'range' => array_keys(CommonFunc::getTpPlatformNameList(1)), 'message' => '第三方平台类型有误'],//1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号)



                ['tp_hospital_code', 'required', 'message' => '第三方医院id不能为空'],
                ['tp_hospital_code', 'string', 'length' => [1, 100], 'message' => '第三方医院id长度过长'],
                ['tp_department_id', 'required', 'message' => '第三方科室id不能为空'],
                ['tp_department_id', 'string', 'length' => [1, 100], 'message' => '第三方科室id长度过长'],
                ['department_name', 'required', 'message' => '第三方科室名称不能为空'],
                ['department_name', 'string', 'length' => [1, 50], 'message' => '第三方科室名称长度过长'],
                ['tp_frist_department_id', 'string', 'length' => [1, 100], 'message' => '第三方一级科室id长度过长'],
                ['tp_frist_department_name', 'string', 'length' => [1, 50], 'message' => '第三方一级科室名称长度过长'],
                ['hospital_id', 'required', 'message' => '王氏医院id不能为空'],
                ['hospital_id', 'integer', 'message' => '王氏医院id类型错误'],
            ];
        }
    }
}
