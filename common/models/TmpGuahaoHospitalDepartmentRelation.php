<?php


namespace common\models;

use Yii;

class TmpGuahaoHospitalDepartmentRelation extends \common\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "tmp_guahao_hospital_department_relation";
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
}
