<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%tb_disease_department}}".
 *
 * @property int $id 主键id
 * @property int $disease_id 疾病ID（家庭医生疾病ID）
 * @property string $disease_name 疾病名称
 * @property string $initial 首字符
 * @property int $frist_department_id 一级科室ID
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $second_department_id 二级科室ID
 * @property int $create_time 创建时间
 */
class DiseaseDepartmentModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_disease_department}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['disease_id', 'frist_department_id', 'status', 'second_department_id', 'create_time'], 'integer'],
            [['disease_name'], 'string', 'max' => 50],
            [['initial'], 'string', 'max' => 1],
            [['pinyin'], 'string', 'max' => 150],
            [['disease_id', 'frist_department_id', 'second_department_id'], 'unique', 'targetAttribute' => ['disease_id', 'frist_department_id', 'second_department_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'disease_id'           => 'Disease ID',
            'disease_name'         => 'Disease Name',
            'initial'              => 'Initial',
            'pinyin'               => 'Pinyin',
            'frist_department_id'  => 'Frist Department ID',
            'status'               => 'Status',
            'second_department_id' => 'Second Department ID',
            'create_time'          => 'Create Time',
        ];
    }

    /**
     * 获取科室下的疾病列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-25
     * @version 1.0
     * @param   string     $frist_department_id  [description]
     * @param   string     $second_department_id [description]
     * @param   string     $initial              [description]
     * @return  [type]                           [description]
     */
    public static function diseases_list_by_keshi_initial($frist_department_id = '', $second_department_id = '', $initial = '')
    {
        if (!$frist_department_id) {
            return [];
        }
        $query = DiseaseDepartmentModel::find()->select('disease_id,disease_name,initial,pinyin')->where(['status' => 1]);
        if ($frist_department_id) {
            $query->andWhere(['frist_department_id' => $frist_department_id]);
        }
        if ($second_department_id) {
            $query->andWhere(['second_department_id' => $second_department_id]);
        }
        if ($initial) {
            $query->andWhere(['initial' => $initial]);
        }
        $diseases_list = $query->asArray()->all();

        return $diseases_list;
    }
}
