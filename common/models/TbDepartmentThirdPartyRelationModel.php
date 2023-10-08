<?php

namespace common\models;

use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use common\models\HospitalDepartmentRelation;

class TbDepartmentThirdPartyRelationModel extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%tb_department_third_party_relation}}';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $route = Yii::$app->controller->route;
            if ($route == 'guahao/generate-department') {
                return true;
            }
            HospitalDepartmentRelation::updateDepartmentCacheById($this->hospital_department_id, 1);
        }
        if ($changedAttributes) {
            if (isset($changedAttributes['hospital_department_id']) && !empty($changedAttributes['hospital_department_id'])) {
                HospitalDepartmentRelation::updateDepartmentCacheById($this->hospital_department_id, 1);
            }
            
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->hospital_department_id) {
            HospitalDepartmentRelation::updateDepartmentCacheById($this->hospital_department_id, 1);
        }
        
    }


}