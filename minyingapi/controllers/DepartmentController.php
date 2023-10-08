<?php
/**
 * 民营医院科室
 * @file DepartmentController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-20
 */

namespace minyingapi\controllers;

use common\libs\CommonFunc;
use common\models\minying\department\MinCreateForm;
use common\models\minying\MinDepartmentModel;
use common\models\TbLog;
use Yii;
use yii\helpers\ArrayHelper;

class DepartmentController extends CommonController
{
    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $queryParams['page'] = ArrayHelper::getValue($requestParams, 'page', 1);
        $queryParams['limit'] = ArrayHelper::getValue($requestParams, 'limit', 20);
        //搜索的是二级科室名
        $queryParams['department_name'] = (isset($requestParams['department_name']) && (!empty($requestParams['department_name']))) ? $requestParams['department_name'] : '';
        $queryParams['department_id'] = ArrayHelper::getValue($requestParams, 'department_id', '');
        $queryParams['hospital_id'] = $this->user['min_hospital_id'];
        $queryParams['check_status'] = MinDepartmentModel::CHECK_STATUS_SND_PASS;
        $depMode = new MinDepartmentModel();
        $list = $depMode::getList($queryParams,'id department_id,min_minying_fkname,min_minying_skname,miao_first_department_name,miao_second_department_name,create_time');
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
        }

        $totalCount = $depMode::getCount($queryParams);
        $result = [
            'currentpage' => $queryParams['page'],
            'pagesize' => $queryParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $queryParams['limit']),
            'list' => $list
        ];
        return $this->jsonSuccess($result);
    }

    /**
     * 添加民营医院科室
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionCreate(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        $form_model = new MinCreateForm();
        $form_model->load($requestParams, '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if ((!empty($form_model->min_minying_fkname) && CommonFunc::checkXss($form_model->min_minying_fkname)) ||
            (!empty($form_model->min_minying_skname) && CommonFunc::checkXss($form_model->min_minying_skname)))
        {
            return $this->jsonError('科室名称不能含有非法脚本！');
        }

        //一级、二级科室名
        $form_model->miao_first_department_name = CommonFunc::getKeshiName($requestParams['miao_first_department_id']);
        $form_model->miao_second_department_name = CommonFunc::getKeshiName($requestParams['miao_second_department_id']);

        // 关联信息
        $form_model->min_hospital_id = $this->user['min_hospital_id'];
        $form_model->agency_id = ArrayHelper::getValue($form_model, 'hospitalModel.agency_id', '');
        $form_model->min_hospital_name = ArrayHelper::getValue($form_model, 'hospitalModel.min_hospital_name', '');
        if (!$form_model->agency_id) {
            return $this->jsonError('未找到医生所在医院的代理商信息');
        }

        // 添加人信息
        $form_model->admin_role_type = MinDepartmentModel::ADMIN_ROLE_TYPE_HOSPITAL;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        $form_model->create_time = time();
        $form_model->update_time = time();

        // 同一医院下科室名不可重复
        $exist_dpt = MinDepartmentModel::find()->where([
            'min_hospital_id' => $form_model->min_hospital_id,
            'min_minying_fkname' => $form_model->min_minying_fkname,
            'min_minying_skname' => $form_model->min_minying_skname
        ])->limit(1)->one();
        if ($exist_dpt) {
            return $this->jsonError("【{$form_model->min_minying_fkname}-{$form_model->min_minying_skname}】科室名称已经存在");
        }

        if (!$form_model->save()) {
            return $this->jsonError('保存失败，请重试');
        }
        return $this->jsonSuccess(['id' => $form_model->id]);
    }

    /**
     * 科室详情
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-27
     */
    public function actionDetail(){
        $department_id = Yii::$app->request->get('department_id');
        if (!$department_id) {
            return $this->jsonError('department_id不能为空');
        }
        if (!$form_model = $this->findModel($department_id)) {
            return $this->jsonError('未找到科室信息');
        }
        $department_info = $form_model->toArray();
        return $this->jsonSuccess($department_info);
    }

    /**
     * 修改科室
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionUpdate(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $department_id = Yii::$app->request->post('department_id');
        if (!$department_id) {
            return $this->jsonError('department_id不能为空');
        }
        if (!$form_model = $this->findModel($department_id)) {
            return $this->jsonError('未找到科室信息');
        }

        // 不可编辑状态（1:待一审核,2:待二审）
        if (in_array($form_model->getOldAttribute('check_status'), [MinDepartmentModel::CHECK_STATUS_NORMAL, MinDepartmentModel::CHECK_STATUS_FST_PASS])) {
            return $this->jsonError('当前状态不可修改');
        }

        $old_data = $form_model->getOldAttributes();
        $params = Yii::$app->getRequest()->getBodyParams();
        $form_model->load($params, '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if ((!empty($form_model->getAttribute('min_minying_fkname')) && CommonFunc::checkXss($form_model->getAttribute('min_minying_fkname'))) ||
            (!empty($form_model->getAttribute('min_minying_skname')) && CommonFunc::checkXss($form_model->getAttribute('min_minying_skname'))))
        {
            return $this->jsonError('科室名称不能含有非法脚本！');
        }

        //一级、二级科室名
        $form_model->miao_first_department_name = CommonFunc::getKeshiName($params['miao_first_department_id']);
        $form_model->miao_second_department_name = CommonFunc::getKeshiName($params['miao_second_department_id']);

        $logs = '';
        if ($old_data['min_minying_fkname'] != $form_model->min_minying_fkname){
            $logs .= '民营一级科室由【'.$old_data['min_minying_fkname'].'】改成【'.$form_model->getAttribute('min_minying_fkname').'】，';
        }
        if ($old_data['min_minying_skname'] != $form_model->min_minying_skname){
            $logs .= '民营二级科室由【'.$old_data['min_minying_skname'].'】改成【'.$form_model->getAttribute('min_minying_skname').'】，';
        }
        if ($old_data['miao_first_department_name'] != $form_model->miao_first_department_name){
            $logs .= '王氏一级科室称由【'.$old_data['min_minying_fkname'].'】改成【'.$form_model->miao_first_department_name.'】，';
        }
        if ($old_data['miao_second_department_name'] != $form_model->miao_second_department_name){
            $logs .= '王氏二级科室称由【'.$old_data['miao_second_department_name'].'】改成【'.$form_model->miao_second_department_name.'】，';
        }
        if ($logs) {
            TbLog::addLog('民营医院修改科室,'.rtrim($logs, '，'),'民营医院修改科室', ['admin_id' => $this->user['min_hospital_id'], 'admin_name' => $this->user['username']]);
        }

        // 关联信息
        $form_model->min_hospital_id = $this->user['min_hospital_id'];
        $form_model->agency_id = ArrayHelper::getValue($form_model, 'hospitalModel.agency_id', '');
        $form_model->min_hospital_name = ArrayHelper::getValue($form_model, 'hospitalModel.min_hospital_name', '');
        if (!$form_model->agency_id) {
            return $this->jsonError('未找到医生所在医院的代理商信息');
        }

        // 清空一、二审信息
        $form_model->first_check_uid = 0;
        $form_model->first_check_name = '';
        $form_model->first_check_time = 0;
        $form_model->second_check_uid = 0;
        $form_model->second_check_name = '';
        $form_model->second_check_time = 0;
        // 修改人信息
        $form_model->check_status = MinDepartmentModel::CHECK_STATUS_NORMAL;
        $form_model->admin_role_type = MinDepartmentModel::ADMIN_ROLE_TYPE_HOSPITAL;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        $form_model->update_time = time();

        // 同一医院下科室名不可重复
        $exist_dpt = MinDepartmentModel::find()->where([
            'min_hospital_id' => $form_model->min_hospital_id,
            'min_minying_fkname' => $form_model->min_minying_fkname,
            'min_minying_skname' => $form_model->min_minying_skname
        ])->limit(1)->one();
        if ($exist_dpt && $exist_dpt->id != $form_model->id) {
            return $this->jsonError("【{$form_model->min_minying_fkname}-{$form_model->min_minying_skname}】科室名称已经存在");
        }

        if (!$form_model->save()) {
            $error = ArrayHelper::getValue(array_values($form_model->getFirstErrors()),'0', '保存失败，请重试');
            return $this->jsonError($error);
        }
        return $this->jsonSuccess(['id' => $form_model->id]);
    }

    /**
     * 一级科室列表信息
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionFirstDepartment()
    {
        $first_keshi = CommonFunc::getFkeshiInfos();
        return $this->jsonSuccess($first_keshi);
    }

    /**
     * 二级科室列表信息
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionSecondDepartment()
    {
        $first_department_id = Yii::$app->request->get('first_department_id');
        if (!$first_department_id) {
            return $this->jsonSuccess();
        }
        $second_keshi = CommonFunc::getSkeshiInfos($first_department_id);
        return $this->jsonSuccess($second_keshi);
    }

    /**
     * @param $department_id
     * @return array|\yii\db\ActiveRecord|null
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-21
     */
    public function findModel($department_id){
        $fields = ['id','min_minying_fkname','min_minying_skname','miao_first_department_id','miao_first_department_name','miao_second_department_id','miao_second_department_name', 'check_status', 'second_check_passed_record', 'min_hospital_name', 'min_hospital_id'];
        return MinCreateForm::find()
            ->where(['id' => $department_id,'min_hospital_id' => $this->user['min_hospital_id']])
            ->select(join(',', $fields))
            ->limit(1)
            ->one();
    }

}