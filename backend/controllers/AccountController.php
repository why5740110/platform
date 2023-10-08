<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\libs\Log;
use common\models\minying\account\CreateForm;
use common\models\minying\ResourceDeadlineModel;
use Yii;
use common\models\minying\MinAccountModel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * AccountController implements the CRUD actions for AccountModel model.
 */
class AccountController extends BaseController
{
    /**
     * Lists all AccountModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = MinAccountModel::find();
        $query->orderBy('account_id desc')->with('agencyModel')->with('hospitalModel');
        $page_size = Yii::$app->request->get('limit', 10);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $page_size,
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new AccountModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $form_model = new CreateForm();
        if ($form_model->load(Yii::$app->request->post())) {
            try {
                // 提交时随机生成密码
                $password = $form_model::generatePassword();

                $form_model->password = $password;
                $form_model->enterprise_id = $form_model->enterprise_agency ?: $form_model->enterprise_hospital;
                $form_model->enterprise_name = $form_model->enterprise_agency ? ArrayHelper::getValue($form_model, 'agencyModel.agency_name', '') : ArrayHelper::getValue($form_model, 'hospitalModel.min_hospital_name', '');
                $form_model->account_number = $form_model->contact_mobile;
                $form_model->admin_id = $this->userInfo['id'];
                $form_model->admin_name = $this->userInfo['realname'];
                // 返回ajax验证提示
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($form_model);
                }
                // 医院必须设置合作时间
                if ($form_model->type == MinAccountModel::TYPE_HOSPITAL) {
                    $deadline = ResourceDeadlineModel::find()
                        ->where(['resource_type' => ResourceDeadlineModel::RESOURCE_TYPE_HOSPITAL, 'resource_id' => $form_model->enterprise_hospital])
                        ->limit(1)->one();
                    if (!$deadline) {
                        $this->_showMessage('医院未设置合作时间');
                    }
                }
                // 一个机构只能有一个账号限制
                if (MinAccountModel::accountLimit($form_model->enterprise_id, $form_model->type)) {
                    $this->_showMessage('当前机构已经存在账号，不可多开！');
                }
                if (!$form_model->save()) {
                    $this->_showMessage(array_values($form_model->getFirstErrors())[0], 'create');
                }

                if (!CommonFunc::minPasswordSendSms('create', $form_model, ['password' => $password])) {
                    Log::sendGuaHaoErrorDingDingNotice("民营医院报警-创建账号: 【{$form_model->contact_mobile}】，通知用户失败\r\n错误信息：". CommonFunc::$passwordSendSmsErrorMsg);
                }

            } catch (\Exception $exception) {
                $this->_showMessage($exception->getMessage(), 'index');
            }
            return $this->redirect('index');
        }
        return $this->render('create', [
            'model' => $form_model,
        ]);
    }

    /**
     * Updates an existing AccountModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        /** @var MinAccountModel $form_model */
        $form_model = CreateForm::find()->where(['account_id' => $id])->one();
        if (!$form_model) {
            $this->_showMessage('未找到记录！');
        }
        if ($form_model->load(Yii::$app->request->post())) {
            // 页面暂不可编辑
            return false;
            try {
                $form_model->enterprise_id = $form_model->enterprise_agency ?: $form_model->enterprise_hospital;
                $form_model->admin_id = $this->userInfo['id'];
                $form_model->admin_name = $this->userInfo['realname'];
                // 返回ajax验证提示
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($form_model);
                }
                // 密码不修改
                unset($form_model->password);

                // 一个机构只能有一个账号限制
                $exist_model = MinAccountModel::accountLimit($form_model->enterprise_id, $form_model->type);
                if ($exist_model && $exist_model->account_id != $id) {
                    $this->_showMessage('当前机构已经存在账号，不可多开！');
                }

                if (!$form_model->save()) {
                    $this->_showMessage(array_values($form_model->getFirstErrors())[0], 'update');
                }

            } catch (\Exception $exception) {
                $this->_showMessage($exception->getMessage(), 'index');
            }

            return $this->redirect('index');
        }
        if ($form_model->type == MinAccountModel::TYPE_AGENCY) {
            $form_model->enterprise_agency = $form_model->enterprise_id;
        }
        if ($form_model->type == MinAccountModel::TYPE_HOSPITAL) {
            $form_model->enterprise_hospital = $form_model->enterprise_id;
        }
        return $this->render('update', [
            'model' => $form_model,
        ]);
    }

    /**
     * 医院状态修改
     * @param $id
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-27
     * @return Response
     */
    public function actionUpdateStatus($id)
    {
        try {
            $model = MinAccountModel::findOne($id);
            if ($model->status == 1) {
                $model->status = 2;
            } else {
                $model->status = 1;
            }
            if (!$model->save()) {
                $error = array_values($model->getFirstErrors());
                $this->_showMessage($error[0]);
            }
        } catch (\Exception $e) {
            $this->_showMessage($e->getMessage());
        }
        return $this->redirect('index');
    }

    /**
     * 账号密码重置
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-27
     * @throws \yii\base\Exception
     */
    public function actionResetPassword()
    {
        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            $this->returnJson(0, '请求不合法');
        }
        if (!$id = Yii::$app->request->post('id')) {
            $this->returnJson(0, '缺少参数id');
        }
        $model = MinAccountModel::findOne($id);
        if (!$model) {
            $this->returnJson(0, '记录不存在');
        }
        $password = MinAccountModel::generatePassword();
        $model->password = Yii::$app->security->generatePasswordHash($password . $model->salt);;
        if (!$model->save()) {
            $this->returnJson(0, '重置失败，请重试');
        }
        // 发送短信
        if (!CommonFunc::minPasswordSendSms('reset', $model, ['password' => $password])) {
            Log::sendGuaHaoErrorDingDingNotice("民营医院报警-账号【{$model->contact_mobile}】密码重置，通知用户失败\r\n错误信息：". CommonFunc::$passwordSendSmsErrorMsg);
        }
        $this->returnJson(1, '密码重置完成');
    }
}
