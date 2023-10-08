<?php

namespace common\models;

use Yii;

class ThirdDepartmentPartyRelationModel extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%tb_base_department_third_party_relation}}';
    }


    public function rules()
    {
        return [
            [['third_fkid', 'third_fkname', 'third_skid', 'third_skname', 'miao_fkid','miao_fkname','miao_skid','miao_skname','source'], 'required'],
            [['third_fkname', 'third_skname','miao_fkname','miao_skname'], 'string','max' => 50],
            [['third_fkid', 'third_skid', 'miao_fkid', 'miao_skid', 'source','create_time'], 'integer'],
        ];
    }
}
