<?php


namespace common\models;

use Yii;
use yii\data\Pagination;

class TmpDepartmentThirdPartyModel extends \common\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "tb_tmp_department_third_party";
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
    
    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::find()
            ->select('*');
        if (!empty($params['is_relation'])) {
            if($params['is_relation']==1){
                $doctorQuery->where(['is_relation'=>1]);
            }else{
                $doctorQuery->where(['is_relation'=>0]);
            }
        }

        if (isset($params['tp_platform']) && !empty(trim($params['tp_platform']))) {
            $doctorQuery->andWhere(['tp_platform' => trim($params['tp_platform'])]);
        }

        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like','hospital_name',trim($params['hospital_name'])]);
        }

        if (!empty($params['hospital_level'])) {
            $doctorQuery->andWhere(['like','hospital_level',trim($params['hospital_level'])]);
        }
        if (!empty($params['keshi'])) {
            $doctorQuery->andWhere(['like','department_name',trim($params['keshi'])]);
        }


        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('id desc')->asArray()->all();
        //echo  $doctorQuery->createCommand()->getRawSql();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::find()->select('*');
        if (!empty($params['is_relation'])) {
            if($params['is_relation']==1){
                $doctorQuery->where(['is_relation'=>1]);
            }else{
                $doctorQuery->where(['is_relation'=>0]);
            }
        }

        if (isset($params['tp_platform']) && !empty(trim($params['tp_platform']))) {
            $doctorQuery->andWhere(['tp_platform' => trim($params['tp_platform'])]);
        }

        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like','hospital_name',trim($params['hospital_name'])]);
        }
        if (!empty($params['hospital_level'])) {
            $doctorQuery->andWhere(['like','hospital_level',trim($params['hospital_level'])]);
        }
        if (!empty($params['keshi'])) {
            $doctorQuery->andWhere(['like','department_name',trim($params['keshi'])]);
        }
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }
}