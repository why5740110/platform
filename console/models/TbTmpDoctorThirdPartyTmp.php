<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "tb_tmp_doctor_third_party_tmp".
 *
 * @property int $id 主键ID
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 * @property string $tp_doctor_id 第三方医生ID
 * @property string $tp_primary_id 第三方医生主ID
 * @property string $realname 医生姓名
 * @property string $source_avatar 第三方头像图片url
 * @property string $good_at 擅长
 * @property string $profile 个人简介
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区
 * @property int $job_title_id
 * @property string $job_title 职称
 * @property string $professional_title
 * @property string $tp_hospital_code 第三方医院id
 * @property string $hospital_name 医院名称
 * @property string $tp_frist_department_id 第三方一级科室ID
 * @property string $frist_department_name 第三方一级科室名称
 * @property string $tp_department_id 第三方二级科室ID
 * @property string $second_department_name 第三方二级科室名称
 * @property int $doctor_id tb_doctor主键ID
 * @property int $is_relation 是否已关联 0未关联 1已关联
 * @property int $status 状态(1:正常,0:禁用)
 * @property int $admin_id 操作人ID
 * @property string $admin_name 操作人
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class TbTmpDoctorThirdPartyTmp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_tmp_doctor_third_party_tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_platform', 'job_title_id', 'doctor_id', 'is_relation', 'status', 'admin_id', 'create_time', 'update_time'], 'integer'],
            [['good_at', 'profile'], 'required'],
            [['good_at', 'profile'], 'string'],
            [['tp_doctor_id', 'tp_primary_id', 'tp_hospital_code', 'tp_frist_department_id', 'tp_department_id'], 'string', 'max' => 32],
            [['realname', 'frist_department_name', 'second_department_name'], 'string', 'max' => 50],
            [['source_avatar'], 'string', 'max' => 500],
            [['province', 'city', 'district', 'admin_name'], 'string', 'max' => 30],
            [['job_title', 'professional_title'], 'string', 'max' => 20],
            [['hospital_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tp_platform' => 'Tp Platform',
            'tp_doctor_id' => 'Tp Doctor ID',
            'tp_primary_id' => 'Tp Primary ID',
            'realname' => 'Realname',
            'source_avatar' => 'Source Avatar',
            'good_at' => 'Good At',
            'profile' => 'Profile',
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'job_title_id' => 'Job Title ID',
            'job_title' => 'Job Title',
            'professional_title' => 'Professional Title',
            'tp_hospital_code' => 'Tp Hospital Code',
            'hospital_name' => 'Hospital Name',
            'tp_frist_department_id' => 'Tp Frist Department ID',
            'frist_department_name' => 'Frist Department Name',
            'tp_department_id' => 'Tp Department ID',
            'second_department_name' => 'Second Department Name',
            'doctor_id' => 'Doctor ID',
            'is_relation' => 'Is Relation',
            'status' => 'Status',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
