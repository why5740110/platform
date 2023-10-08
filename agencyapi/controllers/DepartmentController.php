<?php
/**
 * 代理商科室
 * @file DepartmentController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-20
 */

namespace agencyapi\controllers;

use common\libs\CommonFunc;
use common\models\AuditLogModel;
use common\models\minying\MinHospitalModel;
use common\models\TbLog;
use Yii;
use common\models\minying\MinDepartmentModel;
use yii\helpers\ArrayHelper;
use common\models\minying\department\AgencyCreateForm;

class DepartmentController extends CommonController
{
    /**
     * 科室列表
     * @return array
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $queryParams['page'] = ArrayHelper::getValue($requestParams, 'page', 1);
        $queryParams['limit'] = ArrayHelper::getValue($requestParams, 'limit', 20);
        //搜索的是二级科室名
        $queryParams['department_name'] = (isset($requestParams['department_name']) && (!empty($requestParams['department_name']))) ? $requestParams['department_name'] : '';
        $queryParams['department_id'] = ArrayHelper::getValue($requestParams, 'department_id', '');
        $queryParams['agency_id'] = $this->user['agency_id'];
        // 按民营医院id搜索科室
        $queryParams['hospital_id'] = ArrayHelper::getValue($requestParams, 'min_hospital_id', '');
        $queryParams['hospital_name'] = ArrayHelper::getValue($requestParams, 'min_hospital_name', '');
        $queryParams['check_status'] = MinDepartmentModel::CHECK_STATUS_SND_PASS;
        $depMode = new MinDepartmentModel();
        $list = $depMode::getList($queryParams,'id department_id,min_hospital_id,min_hospital_name,min_minying_fkname,min_minying_skname,miao_first_department_name,miao_second_department_name,create_time');
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
     * 科室添加
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionCreate(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        $form_model = new AgencyCreateForm();
        $form_model->load($requestParams, '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if ((!empty($form_model->min_minying_fkname) && CommonFunc::checkXss($form_model->min_minying_fkname)) ||
            (!empty($form_model->min_minying_skname) && CommonFunc::checkXss($form_model->min_minying_skname)))
        {
            return $this->jsonError('科室名称不能含有非法脚本！');
        }

        $hospital_info = MinHospitalModel::find()->select('min_hospital_id')->where(['min_hospital_name' => $requestParams['min_hospital_name']])->one();
        if (!$hospital_info){
            return $this->jsonError('该医院不存在！');
        }
        $form_model->min_hospital_id = $hospital_info->min_hospital_id;
        // 所属代理商
        $form_model->agency_id = $this->user['agency_id'];

        // 初审信息
        $form_model->check_status = MinDepartmentModel::CHECK_STATUS_FST_PASS;
        $form_model->first_check_time = time();
        $form_model->first_check_uid = $this->user['agency_id'];
        $form_model->first_check_name = $this->user['username'];

        //一级、二级科室名
        $form_model->miao_first_department_name = CommonFunc::getKeshiName($requestParams['miao_first_department_id']);
        $form_model->miao_second_department_name = CommonFunc::getKeshiName($requestParams['miao_second_department_id']);

        // 添加人信息
        $form_model->admin_role_type = MinDepartmentModel::ADMIN_ROLE_TYPE_AGENCY;
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

        // 代理商端增加一审通过记录
        $auditLogData = [
            'operate_type' => 2,
            'operate_id' => $form_model->id,
            'audit_uid' => $this->user['account_id'],
            'audit_name' => $this->user['username'],
            'audit_status' => 1,
            'audit_type' => 1,
            'audit_remark' => '',
        ];
        AuditLogModel::addLog($auditLogData);

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
     * 科室修改
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-21
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

        if ((!empty($form_model->min_minying_fkname) && CommonFunc::checkXss($form_model->min_minying_fkname)) ||
            (!empty($form_model->min_minying_skname) && CommonFunc::checkXss($form_model->min_minying_skname)))
        {
            return $this->jsonError('科室名称不能含有非法脚本！');
        }

        $hospital_info = MinHospitalModel::find()->select('min_hospital_id')->where(['min_hospital_name' => $params['min_hospital_name']])->one();
        if (!$hospital_info){
            return $this->jsonError('该医院不存在！');
        }

        //一级、二级科室名
        $form_model->miao_first_department_name = CommonFunc::getKeshiName($form_model->getAttribute('miao_first_department_id'));
        $form_model->miao_second_department_name = CommonFunc::getKeshiName($form_model->getAttribute('miao_first_department_id'));

        //记录修改日志
        $logs = '';
        if ($old_data['min_hospital_name'] != $form_model->min_hospital_name){
            $logs .= '民营医院由【'.$old_data['min_hospital_name'].'】改成【'.$form_model->getAttribute('min_hospital_name').'】，';
        }
        if ($old_data['min_minying_fkname'] != $form_model->min_minying_fkname){
            $logs .= '民营一级科室由【'.$old_data['min_minying_fkname'].'】改成【'.$form_model->getAttribute('min_minying_fkname').'】，';
        }
        if ($old_data['min_minying_skname'] != $form_model->min_minying_skname){
            $logs .= '民营二级科室由【'.$old_data['min_minying_skname'].'】改成【'.$form_model->getAttribute('min_minying_skname').'】，';
        }
        if ($old_data['miao_first_department_name'] != $form_model->miao_first_department_name){
            $logs .= '王氏一级科室称由【'.$old_data['miao_first_department_name'].'】改成【'.$form_model->miao_first_department_name.'】，';
        }
        if ($old_data['miao_second_department_name'] != $form_model->miao_second_department_name){
            $logs .= '王氏二级科室称由【'.$old_data['miao_second_department_name'].'】改成【'.$form_model->miao_second_department_name.'】，';
        }
        if ($logs) {
            TbLog::addLog('代理商修改医院科室,',rtrim($logs, '，'),'代理商修改医院科室', ['admin_id' => $this->user['agency_id'], 'admin_name' => $this->user['username']]);
        }

        $form_model->min_hospital_id = $hospital_info->min_hospital_id;
        // 所属代理商
        $form_model->agency_id = $this->user['agency_id'];

        // 初审信息
        $form_model->check_status = MinDepartmentModel::CHECK_STATUS_FST_PASS;
        $form_model->first_check_time = time();
        $form_model->first_check_uid = $this->user['agency_id'];
        $form_model->first_check_name = $this->user['username'];
        // 清空二审信息
        $form_model->second_check_uid = 0;
        $form_model->second_check_name = '';
        $form_model->second_check_time = 0;

        // 修改人信息
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
     * 联想搜索科室（在代理商列表搜索科室）
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionSearchDepartment(){
        $department_name = Yii::$app->request->get('department_name');
        if (!$department_name){
            return $this->jsonSuccess();
        }
        $res = MinDepartmentModel::find()->select('min_minying_skname')->where(['like','min_minying_skname',$department_name])->andWhere(['check_status'=>MinDepartmentModel::CHECK_STATUS_SND_PASS])->asArray()->all();
        if ($res){
            return $this->jsonSuccess($res);
        } else {
            return $this->jsonSuccess();
        }
    }

    /**
     * 联想搜索医院（在代理商添加科室搜索医院，二审通过的医院）
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionSearchHospital(){
        $hospital_name = Yii::$app->request->get('hospital_name');
        if (!$hospital_name){
            return $this->jsonSuccess();
        }
        $res = MinHospitalModel::find()
            ->select('min_hospital_id,min_hospital_name')
            ->where(['like','min_hospital_name',$hospital_name])
            ->andWhere(['check_status' => MinHospitalModel::CHECK_STATUS_SND_PASS])
            ->andWhere(['agency_id' => $this->user['agency_id']])
            ->asArray()
            ->all();
        if ($res){
            return $this->jsonSuccess($res);
        } else {
            return $this->jsonSuccess();
        }
    }

    /**
     * 一级科室列表信息
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionFirstDepartment(){
        $first_keshi = CommonFunc::getFkeshiInfos();
        return $this->jsonSuccess($first_keshi);
    }

    /**
     * 二级科室列表信息
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionSecondDepartment(){
        $first_department_id = Yii::$app->request->get('first_department_id');
        if (!$first_department_id) {
            return $this->jsonSuccess();
        }
        $second_keshi = CommonFunc::getSkeshiInfos($first_department_id);
        return $this->jsonSuccess($second_keshi);
    }

    /**
     * @param $department_id
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-21
     * @return array|null| MinDepartmentModel
     */
    public function findModel($department_id){
        $fields = ['id','min_hospital_name','min_minying_fkname','min_minying_skname','miao_first_department_id','miao_first_department_name','miao_second_department_id','miao_second_department_name', 'check_status', 'second_check_passed_record', 'min_hospital_id'];
        return AgencyCreateForm::find()
            ->where(['id' => $department_id,'agency_id' => $this->user['agency_id']])
            ->select($fields)
            ->limit(1)
            ->one();
    }

}