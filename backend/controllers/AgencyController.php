<?php

namespace backend\controllers;

use Yii;
use common\models\minying\MinAgencyModel;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AgencyController implements the CRUD actions for MinAgencyModel model.
 */
class AgencyController extends BaseController
{
    /**
     * Lists all MinAgencyModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => MinAgencyModel::find(),
            'sort' => [
                'defaultOrder' => [
                    'agency_id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new MinAgencyModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MinAgencyModel();

        if ($model->load(Yii::$app->request->post())) {
            $model->admin_id = $this->userInfo['id'];
            $model->admin_name = $this->userInfo['realname'];
            if (!$model->save()) {
                $this->_showMessage('添加失败，请重试');
            }
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MinAgencyModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->admin_id = $this->userInfo['id'];
            $model->admin_name = $this->userInfo['realname'];
            if (!$model->save()) {
                $this->_showMessage('更新失败，请重试');
            }
            return $this->redirect('index');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the MinAgencyModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MinAgencyModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MinAgencyModel::findOne($id)) !== null) {
            return $model;
        }

        $this->_showMessage('未找到记录！', 'index');
    }
}
