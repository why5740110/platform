<?php
/**
 * Created by wangwencai.
 * @file: DoctorController.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-13
 */

namespace agencyapi\controllers;

use common\libs\Idempotent;
use common\libs\Uploader;
use common\models\AuditLogModel;
use common\models\GuahaoHospitalModel;
use common\models\minying\doctor\CreateForm;
use common\models\minying\MinDepartmentModel;
use common\models\minying\MinDoctorModel;
use common\models\minying\MinHospitalModel;
use common\models\TbLog;
use Yii;
use yii\helpers\ArrayHelper;
use common\libs\CommonFunc;

class DoctorController extends CommonController
{
    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return array
     * @throws \Exception
     */
    public function actionList()
    {
        $params = Yii::$app->request->getQueryParams();

        $params['page'] = ArrayHelper::getValue($params, 'page', 1);
        $params['limit'] = ArrayHelper::getValue($params, 'limit', 10);
        $params['department_id'] = ArrayHelper::getValue($params, 'department_id', '');

        // 查找条件
        $params['agency_id'] = $this->user['agency_id'];
        $params['check_status'] = MinDoctorModel::CHECK_STATUS_SND_PASS;

        $fields = [
            'min_doctor_id', 'min_doctor_name', 'min_job_title', 'min_department_id', 'visit_type', 'create_time', 'check_status', 'cert_status', 'min_hospital_id', 'min_hospital_name',
            'concat("") as min_minying_fkname', 'concat("") as min_minying_skname', // 防止缺少节点返回
        ];

        $list = MinDoctorModel::getList($params, $fields);

        // 处理返回数据
        $department_list = MinDepartmentModel::find()
            ->where(['id' => array_column($list, 'min_department_id')])
            ->select('id,min_minying_fkname,min_minying_skname')
            ->indexBy('id')
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['min_minying_fkname'] = ArrayHelper::getValue($department_list, "{$item['min_department_id']}.min_minying_fkname", '');
            $item['min_minying_skname'] = ArrayHelper::getValue($department_list, "{$item['min_department_id']}.min_minying_skname", '');
            $item['create_time'] = date('Y-m-d H:i:s');
        }

        // 处理返回summary信息
        $total_count = MinDoctorModel::getCount($params);
        $data = [
            'cuurentpage' => $params['page'],
            'pagesize' => $params['limit'],
            'totalcount' => $total_count,
            'totalpage' => ceil($total_count / $params['limit']),
            'list' => $list
        ];

        return $this->jsonSuccess($data);
    }

    /**
     * 科室列表-联想搜索
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-02
     * @return array
     * @throws \Exception
     */
    public function actionSearchDepartment(){
        $requestParams = Yii::$app->request->getQueryParams();
        $queryParams['page'] = ArrayHelper::getValue($requestParams, 'page', 1);
        $queryParams['limit'] = ArrayHelper::getValue($requestParams, 'limit', 20);

        // from:list、create、update
        $from = ArrayHelper::getValue($requestParams, 'from_action', 'list');

        // 添加或编辑时，必须选择医院id
        if (in_array($from, ['create', 'update'])) {
            if (!ArrayHelper::getValue($requestParams, 'hospital_id', '')) {
                return $this->jsonSuccess([]);
            }
            $queryParams['check_status'] = MinDepartmentModel::CHECK_STATUS_SND_PASS;
        }

        $queryParams['department_name'] = (isset($requestParams['department_name']) && (!empty($requestParams['department_name']))) ? $requestParams['department_name'] : '';
        $queryParams['agency_id'] = $this->user['agency_id'];
        // 之前有二审通过的
        $queryParams['second_check_passed_record'] = 1;
        // 按民营医院id搜索科室
        $queryParams['hospital_id'] = ArrayHelper::getValue($requestParams, 'hospital_id', '');
        $depMode = new MinDepartmentModel();
        $list = $depMode::getList($queryParams,'id,min_hospital_id,min_hospital_name,min_minying_fkname,min_minying_skname');

        foreach ($list as &$item) {
            $item['concat_department_name'] = $item['min_minying_fkname'] . '-' . $item['min_minying_skname'] . "({$item['min_hospital_name']})";
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
     *
     * 联想搜索医院（在代理商添加科室搜索医院，二审通过的医院）
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-06
     * @return array
     */
    public function actionSearchHospital()
    {
        $hospital_name = Yii::$app->request->get('hospital_name');
        $hospital_id = Yii::$app->request->get('hospital_id');
        $from_action = Yii::$app->request->get('from_action', 'list');
        if (!$hospital_name && !$hospital_id) {
            return $this->jsonSuccess();
        }
        $query = MinHospitalModel::find()
            ->select('min_hospital_id,min_hospital_name');
        $query->where(['agency_id' => $this->user['agency_id']]);

        // 之前有二审通过的
        $query->andWhere(['second_check_passed_record' => 1]);

        if ($hospital_name) {
            $query->andWhere(['like', 'min_hospital_name', $hospital_name]);
        }

        if ($hospital_id) {
            $query->andWhere(['min_hospital_id' => $hospital_id]);
        }
        if (in_array($from_action, ['update', 'list'])) {
            $query->andWhere(['check_status' => MinHospitalModel::CHECK_STATUS_SND_PASS]);
        }

        $res = $query->asArray()->all();
        if ($res) {
            return $this->jsonSuccess($res);
        } else {
            return $this->jsonSuccess();
        }
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $form_model = new CreateForm();
        $form_model->scenario = CreateForm::SCENARIO_AGENCY;
        $form_model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if (!empty($form_model->min_doctor_name) && CommonFunc::checkXss($form_model->min_doctor_name))
        {
            return $this->jsonError('医生名称不能含有非法脚本！');
        }

        //幂等判断
        $idempotentParams = [
            'min_hospital_id' => $form_model->min_hospital_id,
            'min_department_id' => $form_model->min_department_id,
            'min_doctor_name' => $form_model->min_doctor_name,
            'min_job_title_id' => $form_model->min_job_title_id,
        ];

        if (!Idempotent::check($idempotentParams)) {
            return $this->jsonError('操作太频繁了，请稍后重试');
        }

        // 所属代理商
        $form_model->agency_id = $this->user['agency_id'];

        // 职位名称
        $form_model->min_job_title = ArrayHelper::getValue($form_model, 'jobTitleInfo.name', '');
        // 医院名称
        $form_model->min_hospital_name = ArrayHelper::getValue($form_model, 'hospitalModel.min_hospital_name', '');

        // 多点执业医院名称
        if ($form_model->miao_hospital_id) {
            $form_model->miao_hospital_name = GuahaoHospitalModel::find()
                ->where(['status' => 1, 'hospital_id' => $form_model->miao_hospital_id])
                ->select('hospital_name')->scalar();
        }

        // 如果是本院医生，即使前端传了也不接受多点执业信息，
        if ($form_model->visit_type == MinDoctorModel::VISIT_TYPE_INTERNAL) {
            $form_model->miao_hospital_id = 0;
            $form_model->miao_hospital_name = '';
        }

        // 初审信息
        $form_model->check_status = MinDoctorModel::CHECK_STATUS_FST_PASS;
        $form_model->first_check_time = time();
        $form_model->first_check_uid = $this->user['agency_id'];
        $form_model->first_check_uname = $this->user['username'];

        // 添加人信息
        $form_model->admin_role_type = MinDoctorModel::ADMIN_ROLE_TYPE_AGENCY;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        $form_model->create_time = time();
        $form_model->update_time = time();
        if (!$form_model->save(false)) {
            return $this->jsonError('保存失败，请重试');
        }

        // 代理商端增加一审通过记录
        $auditLogData = [
            'operate_type' => 3,
            'operate_id' => $form_model->min_doctor_id,
            'audit_uid' => $this->user['account_id'],
            'audit_name' => $this->user['username'],
            'audit_status' => 1,
            'audit_type' => 1,
            'audit_remark' => '',
        ];
        AuditLogModel::addLog($auditLogData);

        return $this->jsonSuccess(['doctor_id' => $form_model->min_doctor_id]);
    }

    /**
     * 编辑
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-03
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate()
    {
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $min_doctor_id = Yii::$app->request->post('doctor_id');
        if (!$min_doctor_id) {
            return $this->jsonError('doctor_id不能为空');
        }
        if (!$form_model = $this->findModel($min_doctor_id)) {
            return $this->jsonError('未找到医生信息');
        }

        // 不可编辑状态（1:待一审核,2:待二审）
        if (in_array($form_model->getOldAttribute('check_status'), [MinDoctorModel::CHECK_STATUS_NORMAL, MinDoctorModel::CHECK_STATUS_FST_PASS])) {
            return $this->jsonError('当前状态不可修改');
        }

        // 修改出诊类型时，同步多点执业医院
        $form_model->miao_hospital_id = null;

        $form_model->load(Yii::$app->getRequest()->getBodyParams(), '');
        // 前端可能会回传创建时间
        unset($form_model->create_time);

        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if (!empty($form_model->min_doctor_name) && CommonFunc::checkXss($form_model->min_doctor_name))
        {
            return $this->jsonError('医生名称不能含有非法脚本！');
        }

        // 职位名称
        $form_model->min_job_title = ArrayHelper::getValue($form_model, 'jobTitleInfo.name', '');
        // 医院名称
        $form_model->min_hospital_name = ArrayHelper::getValue($form_model, 'hospitalModel.min_hospital_name', '');

        // 多点执业医院名称
        if ($form_model->miao_hospital_id) {
            $form_model->min_hospital_name = GuahaoHospitalModel::find()
                ->where(['status' => 1, 'hospital_id' => $form_model->miao_hospital_id])
                ->select('hospital_name')->scalar();
        }

        // 如果是本院医生，即使前端传了也不接受多点执业信息，
        if ($form_model->visit_type == MinDoctorModel::VISIT_TYPE_INTERNAL) {
            $form_model->miao_hospital_id = 0;
            $form_model->miao_hospital_name = '';
        }

        if (!$form_model->agency_id) {
            return $this->jsonError('未找到医生所在医院的代理商信息');
        }

        // 二审核通过的记录不允许修改字段：
        if ($form_model->second_check_passed_record == 1) {
            unset($form_model->min_hospital_id, $form_model->min_department_id, $form_model->min_doctor_name);
        }

        // 修改人信息
        $form_model->check_status = MinDoctorModel::CHECK_STATUS_FST_PASS;
        $form_model->admin_role_type = MinDoctorModel::ADMIN_ROLE_TYPE_AGENCY;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        // 清空二审信息
        $form_model->second_check_uid = 0;
        $form_model->second_check_uname = '';
        $form_model->second_check_time = 0;

        $form_model->update_time = time();

        if (!$form_model->save(false)) {
            $error = ArrayHelper::getValue(array_values($form_model->getFirstErrors()),'0', '保存失败，请重试');
            return $this->jsonError($error);
        }
        return $this->jsonSuccess(['doctor_id' => $form_model->min_doctor_id]);
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return array
     * @throws \Exception
     */
    public function actionDetail()
    {
        $min_doctor_id = Yii::$app->request->get('doctor_id');
        $sensitive = Yii::$app->request->get('sensitive', 1);
        if (!$min_doctor_id) {
            return $this->jsonError('doctor_id不能为空');
        }
        if (!$doctor_model = $this->findModel($min_doctor_id)) {
            return $this->jsonError('未找到医生信息');
        }

        // 时间戳等字段人性化处理
        $doctor_model->getHumanFormat();
        $doctor_info = $doctor_model->toArray();

        // 医院信息
        $hospital_name = ArrayHelper::getValue($doctor_model, 'hospitalModel.min_hospital_name', '');
        // 科室信息
        $fst_dpt_name = ArrayHelper::getValue($doctor_model, 'departmentModel.min_minying_fkname', '');
        $snd_dpt_name = ArrayHelper::getValue($doctor_model, 'departmentModel.min_minying_skname', '');
        // 医生职称
        $job_title_name = ArrayHelper::getValue($doctor_model, 'jobTitleInfo.name');
        // 医生标签
        $doctor_tags_info = ArrayHelper::getValue($doctor_model, 'TagsInfo');

        // 如果非编辑时，脱敏展示
        if ($sensitive) {
            $doctor_info['mobile'] = empty($doctor_info['mobile']) ? '' : substr_replace($doctor_info['mobile'], '****', 3, 4);
        }
        $doctor_info['min_hospital_name'] = $hospital_name;
        $doctor_info['min_minying_fkname'] = $fst_dpt_name;
        $doctor_info['min_minying_skname'] = $snd_dpt_name;
        $doctor_info['min_job_title_name'] = $job_title_name;
        $doctor_info['create_time'] = date('Y-m-d H:i:s', $doctor_model->create_time);

        // 返回标签信息，分两个字段
        $doctor_info['min_doctor_tags'] = join('、', array_column($doctor_tags_info, 'name'));
        $doctor_info['min_doctor_tags_ids'] = join(',', array_column($doctor_tags_info, 'id'));

        return $this->jsonSuccess($doctor_info);
    }

    /**
     * 医生职称列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return array
     */
    public function actionJobTitle()
    {
        $tags = array_filter(MinDoctorModel::DoctorJobTitleMap(), function (&$v) {
            return $v['status'] == 1;
        });
        return $this->jsonSuccess($tags);
    }

    /**
     * 医生标签列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return array
     */
    public function actionTags()
    {
        $tags = array_filter(MinDoctorModel::DoctorTagsMap(), function (&$v) {
            return $v['status'] == 1;
        });
        return $this->jsonSuccess($tags);
    }

    /**
     * 上传图片
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-18
     * @return array
     */
    public function actionUploadImage()
    {
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }

        $uploader = new Uploader('file_data');
        $uploader->path = 'min_doctor';
        try {
            $upload_res = $uploader->upload();
            if (!$upload_res) {
                return $this->jsonError($uploader->getError());
            }
            return $this->jsonSuccess($upload_res);
        } catch (\Exception $exception) {
            return $this->jsonError($exception->getMessage());
        }
    }

    /**
     * 查看脱敏信息
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-17
     * @return array
     * @throws \Exception
     */
    public function actionSensitive()
    {
        $valid_fields = ['min_doctor_name', 'mobile'];
        $params = Yii::$app->getRequest()->getQueryParams();
        if (!$id = ArrayHelper::getValue($params, 'min_doctor_id')) {
            return $this->jsonError('缺少参数min_doctor_id');
        }
        if (!$field = ArrayHelper::getValue($params, 'field')) {
            return $this->jsonError('缺少参数field');
        }
        if (!in_array($field, $valid_fields)) {
            return $this->jsonError('field参数不合法');
        }
        if (!$model = $this->findModel($id)) {
            return $this->jsonError('医生信息未找到');
        }

        $info = "{$this->user['username']}(account_id:{$this->user['account_id']}) 查看了医生信息；字段为：" . $model->getAttributeLabel($field);
        TbLog::addLog($info, '民营医院隐秘信息查看', ['admin_id' => $this->user['account_id'], 'admin_name' => $this->user['username']]);

        $data['info'] = ArrayHelper::getValue($model, $field, '');
        return $this->jsonSuccess($data);
    }

    /**
     * @param $doctor_id
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return array|null| MinDoctorModel
     */
    protected function findModel($doctor_id)
    {
        $fields = [
            'min_doctor_id', 'min_doctor_name', 'min_hospital_id', 'mobile', 'avatar', 'min_job_title_id', 'min_doctor_tags', 'min_department_id',
            'visit_type', 'good_at', 'intro', 'miao_hospital_id', 'miao_hospital_name', 'id_card_file', 'id_card_begin', 'id_card_end',
            'doctor_cert_file', 'doctor_cert_begin', 'doctor_cert_end', 'practicing_cert_file', 'practicing_cert_begin', 'practicing_cert_end',
            'professional_cert_file', 'professional_cert_begin', 'professional_cert_end', 'multi_practicing_cert_file',
            'multi_practicing_cert_begin', 'multi_practicing_cert_end', 'create_time', 'agency_id', 'check_status', 'second_check_passed_record', 'min_job_title', 'min_hospital_name'
        ];
        return CreateForm::find()
            ->where(['min_doctor_id' => $doctor_id, 'agency_id' => $this->user['agency_id']])
            ->select(join(',', $fields))
            ->limit(1)
            ->one();
    }
}