<?php


namespace common\models;

use Yii;

class TmpBaseDepThirdPartyRelationModel extends \common\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "tb_base_department_third_party_relation";
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
}
