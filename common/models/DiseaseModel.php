<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%tb_disease}}".
 *
 * @property int $disease_id 疾病ID（家庭医生疾病ID）
 * @property string $disease_name 疾病名称
 * @property string $initial 首字符
 * @property string $pinyin 拼音
 * @property int $frist_department_id 一级科室ID
 * @property int $second_department_id 二级科室ID
 * @property int $source_doctors_num 来源网站的医生数
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $create_time 创建时间
 */
class DiseaseModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_disease}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['frist_department_id', 'second_department_id', 'source_doctors_num', 'status', 'create_time'], 'integer'],
            [['disease_name'], 'string', 'max' => 50],
            [['initial'], 'string', 'max' => 1],
            [['pinyin'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'disease_id' => 'Disease ID',
            'disease_name' => 'Disease Name',
            'initial' => 'Initial',
            'pinyin' => 'Pinyin',
            'frist_department_id' => 'Frist Department ID',
            'second_department_id' => 'Second Department ID',
            'source_doctors_num' => 'Source Doctors Num',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}
