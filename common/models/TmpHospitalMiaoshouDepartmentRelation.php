<?php


namespace common\models;

use Yii;

class TmpHospitalnisiyaDepartmentRelation extends \common\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "tmp_hospital_nisiya_department_relation";
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
}
