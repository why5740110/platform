<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%tb_doctor_primary}}".
 *
 * @property int $doctor_id 医生自增ID
 * @property int $primary_id 医生主ID
 * @property string $realname 医生姓名
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 * @property string $avatar 头像图片url
 * @property string $source_avatar 第三方头像图片url
 * @property int $job_title_id 职称ID
 * @property string $job_title 职称
 * @property int $hospital_id 医院ID
 * @property int $frist_department_id 一级科室ID
 * @property string $frist_department_name 一级科室名称
 * @property int $second_department_id 二级科室ID
 * @property string $second_department_name 二级科室名称
 * @property int $miao_doctor_id 王氏医生ID(默认是没有关联)
 * @property int $miao_frist_department_id 王氏一级科室ID
 * @property int $miao_second_department_id 王氏二级科室ID
 * @property string $tp_hospital_code 第三方医院id
 * @property string $tp_doctor_id 第三方医生ID
 * @property string $tp_frist_department_id 第三方一级科室ID
 * @property string $tp_department_id 第三方二级科室ID
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $is_plus 是否关联第三方
 * @property int $weight 权重值
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class DoctorPrimaryModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_doctor_primary}}';
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'doctor_id' => 'Doctor ID',
            'primary_id' => 'Primary ID',
            'realname' => 'Realname',
            'tp_platform' => 'Tp Platform',
            'avatar' => 'Avatar',
            'source_avatar' => 'Source Avatar',
            'job_title_id' => 'Job Title ID',
            'job_title' => 'Job Title',
            'hospital_id' => 'Hospital ID',
            'hospital_name' => 'Hospital Name',
            'hospital_type' => 'Hospital Type',
            'frist_department_id' => 'Frist Department ID',
            'frist_department_name' => 'Frist Department Name',
            'second_department_id' => 'Second Department ID',
            'second_department_name' => 'Second Department Name',
            'miao_doctor_id' => 'Miao Doctor ID',
            'miao_frist_department_id' => 'Miao Frist Department ID',
            'miao_second_department_id' => 'Miao Second Department ID',
            'tp_hospital_code' => 'Tp Hospital Code',
            'tp_doctor_id' => 'Tp Doctor ID',
            'tp_frist_department_id' => 'Tp Frist Department ID',
            'tp_department_id' => 'Tp Department ID',
            'status' => 'Status',
            'is_plus' => 'Is Plus',
            'weight' => 'Weight',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
        ];
    }
}
