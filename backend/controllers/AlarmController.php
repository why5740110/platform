<?php

namespace backend\controllers;

use backend\models\DeadlineSearchModel;
use common\models\minying\MinAgencyModel;
use common\models\minying\MinDoctorModel;
use common\models\minying\MinHospitalModel;
use common\models\minying\ResourceDeadlineModel;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Response;

class AlarmController extends BaseController
{
    /**
     * 医院预警列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-22
     * @return string
     * @throws \Exception
     */
    public function actionHospital()
    {
        $searchModel = new DeadlineSearchModel();
        $searchModel->load(Yii::$app->request->queryParams);
        $page_size = Yii::$app->request->get('limit', 10);

        $query = ResourceDeadlineModel::find();
        $query->andWhere(['resource_type' => ResourceDeadlineModel::RESOURCE_TYPE_HOSPITAL]);
        $query->andWhere(['<', 'end_time', time() + ResourceDeadlineModel::ALARM_THRESHOLD]);

        // 代理商筛选
        if ($filterAgencyId = ArrayHelper::getValue($searchModel, 'agency_id')) {
            $filterAgencyHospitalId = MinHospitalModel::find()->where(['agency_id' => $filterAgencyId])->select('min_hospital_id')->asArray()->column();
            $filterResourceId = $filterAgencyHospitalId;
        }

        // 医院筛选
        if ($filterHospitalId = ArrayHelper::getValue($searchModel, 'hospital_id')) {
            $filterResourceId = $filterHospitalId;
        }

        if (isset($filterResourceId)) {
            $query->andWhere(['resource_id' => $filterResourceId]);
        }

        $query->orderBy('end_time asc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $page_size,
            ]
        ]);

        // 医院信息
        $hospitalIds = ArrayHelper::getColumn($dataProvider->getModels(), 'resource_id');
        $hospitalList = MinHospitalModel::find()
            ->where(['min_hospital_id' => $hospitalIds])
            ->select('min_hospital_id,min_hospital_name,agency_id')
            ->indexBy('min_hospital_id')->asArray()->all();

        // 代理商信息
        $agencyList = MinAgencyModel::find()
            ->where(['agency_id' => array_column($hospitalList, 'agency_id')])
            ->select('agency_id,agency_name')
            ->indexBy('agency_id')->asArray()->all();

        foreach ($dataProvider->getModels() as $model) {
            $model->min_hospital_name = ArrayHelper::getValue($hospitalList, "{$model->resource_id}.min_hospital_name", '');
            $agency_id = ArrayHelper::getValue($hospitalList, "{$model->resource_id}.agency_id", '');
            $model->agency_name = ArrayHelper::getValue($agencyList, "{$agency_id}.agency_name", '');
        }

        $data = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

        return $this->render('hospital', $data);
    }

    /**
     * 医生预警列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-26
     * @return string
     * @throws \Exception
     */
    public function actionDoctor()
    {
        $searchModel = new DeadlineSearchModel();
        $searchModel->load(Yii::$app->request->queryParams);
        $page_size = Yii::$app->request->get('limit', 10);

        $query = ResourceDeadlineModel::find()->where(['resource_type' => ResourceDeadlineModel::RESOURCE_TYPE_DOCTOR]);
        $query->andWhere(['<', 'end_time', time() + ResourceDeadlineModel::ALARM_THRESHOLD]);
        // 证件类型
        if (ArrayHelper::getValue($searchModel, 'doctor_cert_id', '')) {
            $query->andWhere(['resource_minor_id' => $searchModel->doctor_cert_id]);
        }
        // 医生
        if (ArrayHelper::getValue($searchModel, 'doctor_id', '')) {
            $query->andWhere(['resource_id' => $searchModel->doctor_id]);
        }
        $query->orderBy('end_time asc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $page_size,
            ]
        ]);
        $doctorIds = ArrayHelper::getColumn($dataProvider->getModels(), 'resource_id');
        $doctorList = MinDoctorModel::find()->where(['min_doctor_id' => $doctorIds])
            ->select('min_doctor_id,min_doctor_name,min_hospital_name')
            ->indexBy('min_doctor_id')->asArray()->all();

        /** 追加医生信息 @var ResourceDeadlineModel $model */
        foreach ($dataProvider->getModels() as $model) {
            $model->min_hospital_name = ArrayHelper::getValue($doctorList, "{$model->resource_id}.min_hospital_name", '');
            $model->min_doctor_name = ArrayHelper::getValue($doctorList, "{$model->resource_id}.min_doctor_name", '');
        }

        return $this->render('doctor', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * select2 搜索ajax返回
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-23
     * @return mixed
     */
    public function actionAjaxGetDoctors()
    {
        $keyword = Yii::$app->request->get('q', '');
        Yii::$app->response->format = Response::FORMAT_JSON;

        $keyword = trim($keyword);
        $query = MinDoctorModel::find();
        if (is_numeric($keyword)) {
            $query->andWhere(['min_doctor_id' => $keyword]);
        } elseif ($keyword) {
            $query->andWhere(['like', 'min_doctor_name', $keyword]);
        }
        $data['results'] = $query->select(['min_doctor_id id', 'concat_ws("-", min_hospital_name, min_doctor_name) as text'])
            ->asArray()
            ->all();
        foreach ($data['results'] as &$item) {
            $item['text'] = Html::encode($item['text']);
        }
        return $data;
    }
}
