<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "tb_tmp_department_third_party_tmp".
 *
 * @property int $id 主键ID
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 * @property string $tp_hospital_code 第三方医院id
 * @property string $hospital_name 医院名称
 * @property string $third_fkid 第三方一级标准科室ID
 * @property string $third_fkname 第三方一级标准科室名称
 * @property string $third_skid 第三方二级标准科室ID
 * @property string $third_skname 第三方二级标准科室名称
 * @property string $tp_department_id 第三方二级科室ID
 * @property string $department_name 第三方二级科室名称
 * @property int $hospital_department_id 王氏医院科室ID
 * @property int $is_relation 是否已关联 0未关联 1已关联
 * @property int $admin_id 操作人ID
 * @property string $admin_name 操作人
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $tp_open_day 科室放号天数
 * @property string $tp_open_time 科室放号时间
 */
class TbTmpDepartmentThirdPartyTmp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_tmp_department_third_party_tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_platform', 'hospital_department_id', 'is_relation', 'admin_id', 'create_time', 'update_time', 'tp_open_day'], 'integer'],
            [['tp_hospital_code', 'third_fkid', 'third_skid', 'tp_department_id'], 'string', 'max' => 32],
            [['hospital_name'], 'string', 'max' => 100],
            [['third_fkname', 'third_skname', 'department_name'], 'string', 'max' => 50],
            [['admin_name'], 'string', 'max' => 30],
            [['tp_open_time'], 'string', 'max' => 20],
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
            'tp_hospital_code' => 'Tp Hospital Code',
            'hospital_name' => 'Hospital Name',
            'third_fkid' => 'Third Fkid',
            'third_fkname' => 'Third Fkname',
            'third_skid' => 'Third Skid',
            'third_skname' => 'Third Skname',
            'tp_department_id' => 'Tp Department ID',
            'department_name' => 'Department Name',
            'hospital_department_id' => 'Hospital Department ID',
            'is_relation' => 'Is Relation',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'tp_open_day' => 'Tp Open Day',
            'tp_open_time' => 'Tp Open Time',
        ];
    }
}
