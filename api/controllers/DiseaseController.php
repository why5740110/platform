<?php

namespace api\controllers;

use common\libs\CommonFunc;
use common\models\DiseaseEsModel;
use common\models\DiseaseModel;
use Yii;

class DiseaseController extends CommonController
{
    public function actionList()
    {
        $request   = Yii::$app->request;
        $doctor_id = $request->get('doctor_id', 0);
        $fkeshi_id = $request->get('fkeshi_id', 1);
        $skeshi_id = $request->get('skeshi_id', 37);
        $initial   = $request->get('initial', '');
        if (!$fkeshi_id) {
            return $this->jsonSuccess([]);
        }
        $list      = CommonFunc::getDiseasesBykeshiID($fkeshi_id, $skeshi_id, $initial);
        return $this->jsonSuccess($list);
    }

    public function actionEsList()
    {
        $request   = Yii::$app->request;
        $fkeshi_id = $request->get('fkeshi_id', '');
        $skeshi_id = $request->get('skeshi_id', '');
        $initial   = $request->get('initial', '');

        $list      = [];
        $list = DiseaseEsModel::find()->where(['initial'=>'a'])->offset(0)->limit(3)->asArray()->all();
        //$list = DiseaseModel::find()->where(['disease_id'=>5])->asArray()->all();

        return $this->jsonSuccess($list);
    }
}
