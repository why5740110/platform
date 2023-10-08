<?php

/**
 * 验证医生导入数据
 * @file ImportDoctorValidator.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2021-09-15
 */

namespace common\validators;

use common\libs\CommonFunc;
use yii\base\Model;

class ImportDoctorValidator extends Model
{
    public $tp_platform;
    public $tp_hospital_code;
    public $tp_department_id;
    public $tp_frist_department_id;
    public $hospital_id;
    public $hospital_name;
    public $tp_doctor_id;
    public $tp_primary_id;
    public $realname;
    public $source_avatar;
    public $job_title;
    public $hospital_type;
    public $frist_department_id;
    public $frist_department_name;
    public $second_department_id;
    public $second_department_name;
    public $miao_frist_department_id;
    public $miao_second_department_id;
    public $professional_title;

    public function rules()
    {
        return [
            ['tp_platform', 'required', 'message' => '第三方平台类型不能为空'],
            ['tp_platform', 'in', 'range' => array_keys(CommonFunc::getTpPlatformNameList(1)), 'message' => '第三方平台类型有误'],
            ['tp_hospital_code', 'required', 'message' => '第三方医院id不能为空'],
            ['tp_hospital_code', 'string', 'length' => [1, 100], 'message' => '第三方医院id长度过长'],
            ['tp_department_id', 'required', 'message' => '第三方科室id不能为空'],
            ['tp_department_id', 'string', 'length' => [1, 100], 'message' => '第三方科室id长度过长'],
            ['tp_frist_department_id', 'string', 'length' => [1, 100], 'message' => '第三方一级科室id长度过长'],
            ['tp_doctor_id', 'required', 'message' => '第三方医生id不能为空'],
            ['tp_doctor_id', 'string', 'length' => [1, 100], 'message' => '第三方医生id长度过长'],
            ['tp_primary_id', 'string', 'length' => [1, 100], 'message' => '第三方主医生id长度过长'],
            ['realname', 'required', 'message' => '医生姓名不能为空'],
            ['realname', 'string', 'length' => [1, 50], 'message' => '医生姓名长度过长'],
            ['source_avatar', 'string', 'length' => [1, 255], 'message' => '第三方医生头像地址长度过长'],
            ['job_title', 'string', 'length' => [1, 20], 'message' => '职称长度过长'],
            ['hospital_id', 'required', 'message' => '王氏医院id不能为空'],
            ['hospital_id', 'integer', 'message' => '王氏医院id类型错误'],
            ['hospital_name', 'required', 'message' => '医院名称不能为空'],
            ['hospital_name', 'string', 'length' => [1, 50], 'message' => '医院名称长度过长'],
            ['hospital_type', 'required', 'message' => '医院属性不能为空'],
            ['hospital_type', 'in', 'range' => [1, 2], 'message' => '医院属性类型有误'],
            ['frist_department_id', 'required', 'message' => '一级科室id不能为空'],
            ['frist_department_id', 'integer', 'message' => '一级科室id类型错误'],
            ['frist_department_name', 'required', 'message' => '一级科室名称不能为空'],
            ['frist_department_name', 'string', 'length' => [1, 50], 'message' => '一级科室名称长度过长'],
            ['second_department_id', 'required', 'message' => '二级科室id不能为空'],
            ['second_department_id', 'integer', 'message' => '二级科室id类型错误'],
            ['second_department_name', 'required', 'message' => '二级科室名称不能为空'],
            ['second_department_name', 'string', 'length' => [1, 50], 'message' => '二级科室名称长度过长'],
            ['miao_frist_department_id', 'required', 'message' => '王氏一级科室id不能为空'],
            ['miao_frist_department_id', 'integer', 'message' => '王氏一级科室id类型错误'],
            ['miao_second_department_id', 'required', 'message' => '王氏二级科室id不能为空'],
            ['miao_second_department_id', 'integer', 'message' => '王氏二级科室id类型错误'],
            ['professional_title', 'string', 'length' => [1, 20], 'message' => '医生专业职称长度过长'],
        ];
    }
}
