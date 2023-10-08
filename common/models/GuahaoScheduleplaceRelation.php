<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tb_guahao_scheduleplace_relation".
 *
 * @property int $id
 * @property int $scheduleplace_id 出诊地ID
 * @property string $tp_scheduleplace_id 第三方出诊地ID
 * @property string $scheduleplace_name 出诊医院(工作室) 
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)
 * @property int $doctor_id 医生id
 * @property int $realname 医生姓名
 * @property int $hospital_department_id 王氏医院科室ID
 * @property int $status 审核状态(-1:审核失败,0:审核中,1:审核成功)
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class GuahaoScheduleplaceRelation extends \yii\db\ActiveRecord
{
    public $job_title;
    public $hospital_id;
    public $hospital_name;

    public static $status = [-1 => '审核失败', 0 => '审核中', 1 => '审核成功',2 =>'用户关闭'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_scheduleplace_relation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['scheduleplace_id', 'tp_platform','doctor_id','hospital_department_id', 'create_time', 'update_time'], 'integer'],
            [['realname','tp_doctor_id'], 'string'],
            [['admin_id','admin_name','status'], 'safe'],
            [['tp_scheduleplace_id'], 'string', 'max' => 32],
            [['scheduleplace_name'], 'string', 'max' => 100]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'scheduleplace_id' => 'Scheduleplace ID',
            'tp_scheduleplace_id' => 'Tp Scheduleplace ID',
            'scheduleplace_name' => 'Scheduleplace Name',
            'tp_platform' => 'Tp Platform',
            'tp_doctor_id' => 'tp doctor_id',
            'doctor_id' => 'Tp Platform',
            'realname' => 'realname',
            'hospital_department_id' => 'hospital_department_id',
            'status' => 'status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',            
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
        ];
    }
}
