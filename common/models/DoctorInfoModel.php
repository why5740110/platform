<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tb_doctor_info".
 *
 * @property int $doctor_id 医生自增ID
 * @property string $good_at 擅长
 * @property string $profile 个人简介
 * @property string $professional_title 医生专业职称
 * @property string $related_disease 相关疾病(通过疾病找医生)英文逗号分隔
 * @property string $initial 疾病拼音首字母组合
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class DoctorInfoModel extends \yii\db\ActiveRecord
{
    /**
     * 基础附属信息
     * @var [type]
     */
    public static $attributeInfo = ['good_at','profile'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_doctor_info';
    }

    /**
     * {@inheritdoc}
     */
    // public function rules()
    // {
    //     return [
    //         [['profile', 'related_disease'], 'string'],
    //         [['create_time', 'update_time', 'admin_id'], 'integer'],
    //         [['good_at'], 'string', 'max' => 255],
    //         [['professional_title'], 'string', 'max' => 20],
    //         [['initial'], 'string', 'max' => 100],
    //         [['admin_name'], 'string', 'max' => 50],
    //     ];
    // }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'doctor_id' => 'Doctor ID',
            'good_at' => 'Good At',
            'profile' => 'Profile',
            'professional_title' => 'Professional Title',
            'related_disease' => 'Related Disease',
            'initial' => 'Initial',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
        ];
    }

    /**
     * @获取属性
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @return void
     */
    public static function attribute()
    {
        $info_model = new DoctorInfoModel();
        $info_item = array_keys($info_model->attributeLabels());
        return $info_item;
    }

    /**
     * @获取简要附属属性
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @return void
     */
    public static function attributeInfo()
    {
       return ['good_at','profile'];
    }
}