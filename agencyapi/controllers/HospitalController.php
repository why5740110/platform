<?php
/**
 * 代理商医院
 * @file HospitalController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-18
 */

namespace agencyapi\controllers;

use common\libs\CommonFunc;
use common\libs\Uploader;
use common\models\AuditLogModel;
use common\models\GuahaoHospitalModel;
use common\models\minying\hospital\CreateForm;
use common\models\minying\MinHospitalModel;
use common\models\TbLog;
use Yii;
use yii\helpers\ArrayHelper;


class HospitalController extends CommonController
{
    public $page_size = 10;

    /**
     * 医院列表
     * @return array
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $queryParams['page'] = ArrayHelper::getValue($requestParams, 'page', 1);
        $queryParams['limit'] = ArrayHelper::getValue($requestParams, 'limit', 20);
        $queryParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $queryParams['hospital_type'] = (isset($requestParams['hospital_type']) && (!empty($requestParams['hospital_type']))) ? $requestParams['hospital_type'] : '';
        $queryParams['hospital_level'] = (isset($requestParams['hospital_level']) && (!empty($requestParams['hospital_level']))) ? $requestParams['hospital_level'] : '';
        $queryParams['agency_id'] = $this->user['agency_id'];
        $queryParams['check_status'] = MinHospitalModel::CHECK_STATUS_SND_PASS;

        $hosModel = new MinHospitalModel();
        $list = $hosModel::getList($queryParams,'min_hospital_id,min_hospital_name,min_hospital_type,min_hospital_level,min_hospital_nature,min_hospital_province_name,status');
        foreach ($list as &$item) {
            $item['min_hospital_level'] = MinHospitalModel::$levellist[$item['min_hospital_level']];
            $item['min_hospital_nature'] = MinHospitalModel::$naturelist[$item['min_hospital_nature']];
            $item['min_hospital_type'] = MinHospitalModel::$TypeList[$item['min_hospital_type']];
            $item['status'] = $item['status'] == 1 ? '启用' : '禁用';
        }
        $totalCount = $hosModel::getCount($queryParams);
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
     * 添加医院
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-16
     */
    public function actionCreate(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        $form_model = new CreateForm();
        $form_model->load($requestParams, '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if (!empty($requestParams['min_hospital_name']) && CommonFunc::checkXss($requestParams['min_hospital_name']))
        {
            return $this->jsonError('医院名称不能含有非法脚本！');
        }

        $exist = MinHospitalModel::find()->where(['min_hospital_name' => $requestParams['min_hospital_name']])->exists();
        if ($exist){
            return $this->jsonError('该医院名称已经存在！');
        }

        //匹配省市县
        $form_model->min_hospital_province_name = $this->getProvinceName($requestParams['min_hospital_province_id']);
        $form_model->min_hospital_city_name = $this->getCityName($requestParams['min_hospital_province_id'],$requestParams['min_hospital_city_id']);
        // 县信息非必填
        $form_model->min_hospital_county_name = '';
        if (ArrayHelper::getValue($requestParams, 'min_hospital_county_id')) {
            $form_model->min_hospital_county_name = $this->getDistrict($requestParams['min_hospital_city_id'],$requestParams['min_hospital_county_id']);
        }

        // 所属代理商
        $form_model->agency_id = $this->user['agency_id'];

        // 初审信息(代理商添加默认出身通过)
        $form_model->check_status = MinHospitalModel::CHECK_STATUS_FST_PASS;
        $form_model->first_check_time = time();
        $form_model->first_check_uid = $this->user['agency_id'];
        $form_model->first_check_name = $this->user['username'];

        // 添加人信息
        $form_model->admin_role_type = MinHospitalModel::ADMIN_ROLE_TYPE_AGENCY;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        $form_model->create_time = time();
        $form_model->update_time = time();

        if (!$form_model->save()) {
            var_dump($form_model->getErrors());
            return $this->jsonError('保存失败，请重试');
        }


        // 代理商端增加一审通过记录
        $auditLogData = [
            'operate_type' => 1,
            'operate_id' => $form_model->min_hospital_id,
            'audit_uid' => $this->user['account_id'],
            'audit_name' => $this->user['username'],
            'audit_status' => 1,
            'audit_type' => 1,
            'audit_remark' => '',
        ];
        AuditLogModel::addLog($auditLogData);

        return $this->jsonSuccess(['hospital_id' => $form_model->min_hospital_id]);
    }

    /**
     * 医院修改
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function actionUpdate(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }
        $hospital_id = Yii::$app->request->post('hospital_id');
        if (!$hospital_id) {
            return $this->jsonError('hospital_id不能为空');
        }
        if (!$form_model = $this->findModel($hospital_id)) {
            return $this->jsonError('未找到医院信息');
        }
        $old_data = $form_model->getOldAttributes();
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        $form_model->load($requestParams, '');
        if (!$form_model->validate()) {
            return $this->jsonError(array_values($form_model->getFirstErrors())[0]);
        }

        if (!empty($form_model->min_hospital_name) && CommonFunc::checkXss($form_model->min_hospital_name))
        {
            return $this->jsonError('医院名称不能含有非法脚本！');
        }

        // 不可编辑状态（1:待一审核,2:待二审）
        if (in_array($form_model->getOldAttribute('check_status'), [MinHospitalModel::CHECK_STATUS_NORMAL, MinHospitalModel::CHECK_STATUS_FST_PASS])) {
            return $this->jsonError('当前状态不可修改');
        }

        //判断除了当前医院名称是否还和别的医院重复
        $exist = MinHospitalModel::find()
            ->where(['min_hospital_name' => $form_model->min_hospital_name])
            ->andWhere(['=', 'min_hospital_id', $hospital_id])
            ->limit(1)
            ->one();
        if ($exist && $exist->min_hospital_id != $hospital_id){
            return $this->jsonError('该医院名称已经存在！');
        }

        //匹配省市县
        $form_model->min_hospital_province_name = $this->getProvinceName($requestParams['min_hospital_province_id']);
        $form_model->min_hospital_city_name = $this->getCityName($requestParams['min_hospital_province_id'],$requestParams['min_hospital_city_id']);
        $form_model->min_hospital_county_name = $this->getDistrict($requestParams['min_hospital_city_id'],$requestParams['min_hospital_county_id']);

        // 所属代理商
        $form_model->agency_id = $this->user['agency_id'];

        // 初审信息(代理商修改默认出身通过)
        $form_model->check_status = MinHospitalModel::CHECK_STATUS_FST_PASS;
        $form_model->first_check_time = time();
        $form_model->first_check_uid = $this->user['agency_id'];
        $form_model->first_check_name = $this->user['username'];
        // 清空二审信息
        $form_model->second_check_uid = 0;
        $form_model->second_check_name = '';
        $form_model->second_check_time = 0;

        // 添加人信息
        $form_model->admin_role_type = MinHospitalModel::ADMIN_ROLE_TYPE_AGENCY;
        $form_model->admin_id = $this->user['account_id'];
        $form_model->admin_name = $this->user['username'];
        $form_model->update_time = time();

        if (!$form_model->save()) {
            $error = ArrayHelper::getValue(array_values($form_model->getFirstErrors()),'0', '保存失败，请重试');
            return $this->jsonError($error);
        }
        $log = $this->addLog($form_model, $old_data, $form_model->min_hospital_province_name, $form_model->min_hospital_city_name, $form_model->min_hospital_county_name);
        if ($log) {
            $log = rtrim($log,',');
            TbLog::addLog($log, '代理商修改民营医院信息日志', ['admin_id' => $this->user['agency_id'], 'admin_name' => $this->user['username']]);
        }
        return $this->jsonSuccess(['hospital_id' => $form_model->min_hospital_id]);
    }

    /**
     * 查看详情
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public function actionDetail(){
        $min_hospital_id = Yii::$app->request->get('hospital_id');
        if (!$min_hospital_id) {
            return $this->jsonError('hospital_id不能为空');
        }
        if (!$hospital_model = $this->findModel($min_hospital_id)) {
            return $this->jsonError('未找到医院信息');
        }

        //图片、时间戳字段人性化处理
        $hospital_model->getHumanFormat();
        $hospital_info = $hospital_model->toArray();

        $hospital_info['min_hospital_level_str'] = MinHospitalModel::$levellist[$hospital_info['min_hospital_level']];
        $hospital_info['min_hospital_nature_str'] = MinHospitalModel::$naturelist[$hospital_info['min_hospital_nature']];
        $hospital_info['min_hospital_type_str'] = MinHospitalModel::$TypeList[$hospital_info['min_hospital_type']];
        $hospital_info['min_hospital_tags_str'] = MinHospitalModel::getTagsInfo($hospital_info['min_hospital_tags']);
        $hospital_info['min_treatment_project_str'] = MinHospitalModel::getTreatmentProject($hospital_info['min_treatment_project']);
        $hospital_info['min_business_license'] = implode(',',$hospital_info['min_business_license']);
        $hospital_info['min_medical_license'] = implode(',',$hospital_info['min_medical_license']);
        $hospital_info['min_health_record'] = implode(',',$hospital_info['min_health_record']);
        $hospital_info['min_medical_certificate'] = implode(',',$hospital_info['min_medical_certificate']);
        // 若为0返回空，兼容前端展示
        $hospital_info['min_hospital_county_id'] = $hospital_info['min_hospital_county_id'] == 0 ? '' : $hospital_info['min_hospital_county_id'];
        if ($hospital_info['min_hospital_logo']){
            $hospital_info['min_hospital_logo'] = Yii::$app->params['min_hospital_img_oss_url_prefix'].$hospital_info['min_hospital_logo'];
        }
        return $this->jsonSuccess($hospital_info);
    }

    /**
     * 医院类型
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public function actionType(){
        $type_list_arr = [];
        foreach (MinHospitalModel::$TypeList as $key => $val){
            $type_list_arr[] = ['id'=> $key, 'name' => $val];
        }
        return $this->jsonSuccess($type_list_arr);
    }

    /**
     * 医院性质
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public function actionNature(){
        $nature_list_arr = [];
        foreach (MinHospitalModel::$naturelist as $key => $val){
            $nature_list_arr[] = ['id'=> $key, 'name' => $val];
        }
        return $this->jsonSuccess($nature_list_arr);
    }

    /**
     * 医院级别
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public function actionLevel(){
        $level_list_arr = [];
        foreach (MinHospitalModel::$levellist as $key => $val){
            if (empty($key)) continue;
            $level_list_arr[] = ['id'=> $key, 'name' => $val];
        }
        return $this->jsonSuccess($level_list_arr);
    }

    /**
     * 医院标签
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public function actionTags(){
        $tags_list_arr = [];
        foreach (MinHospitalModel::$hospitalTags as $key => $val){
            $tags_list_arr[] = ['id'=> $key, 'name' => $val];
        }
        return $this->jsonSuccess($tags_list_arr);
    }

    /**
     * 医院添加诊疗项目联想列表
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public function actionTreatmentProject(){
        return $this->jsonSuccess(CommonFunc::getSkeshiInfos());
    }

    /**
     * 上传图片
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public function actionUploadImage(){
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式错误');
        }

        $uploader = new Uploader('file_data');
        $uploader->path = 'min_hospital';
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
        $valid_fields = ['min_hospital_contact_phone'];
        $params = Yii::$app->getRequest()->getQueryParams();
        if (!$id = ArrayHelper::getValue($params, 'min_hospital_id')) {
            return $this->jsonError('缺少参数min_hospital_id');
        }
        if (!$field = ArrayHelper::getValue($params, 'field')) {
            return $this->jsonError('缺少参数field');
        }
        if (!in_array($field, $valid_fields)) {
            return $this->jsonError('field参数不合法');
        }
        if (!$model = $this->findModel($id)) {
            return $this->jsonError('医院信息未找到');
        }

        $info = "{$this->user['username']}(account_id:{$this->user['account_id']}) 查看了医院信息；字段为：" . $model->getAttributeLabel($field);
        TbLog::addLog($info, '民营医院隐秘信息查看', ['admin_id' => $this->user['account_id'], 'admin_name' => $this->user['username']]);

        $data['info'] = ArrayHelper::getValue($model, $field, '');
        return $this->jsonSuccess($data);
    }

    /**
     * 医院信息
     * @param $hospital_id
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     * @return array|null| MinHospitalModel
     */
    public function findModel($hospital_id){
        $fields = [
            'min_hospital_name','min_hospital_logo','min_hospital_type','min_hospital_level','min_hospital_nature','min_hospital_tags','min_hospital_province_id','min_hospital_province_name','min_hospital_city_id','min_hospital_city_name','min_hospital_county_id','min_hospital_county_name',
            'min_hospital_address','min_bus_line','min_hospital_phone','min_hospital_introduce','min_company_name','min_business_license','min_medical_license','min_health_record','min_medical_certificate','min_treatment_project',
            'min_guahao_rule','min_hospital_contact','min_hospital_contact_phone','create_time','min_hospital_id', 'check_status', 'second_check_passed_record'
        ];
        return CreateForm::find()
            ->where(['min_hospital_id' => $hospital_id, 'agency_id' => $this->user['agency_id']])
            ->select(join(',', $fields))
            ->limit(1)
            ->one();
    }

    /**
     * 获取省名
     * @param $province_id
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    private function getProvinceName($province_id){
        $provinceInfo = CommonFunc::getProvince();
        if ($provinceInfo){
            $provinceArr = ArrayHelper::index($provinceInfo,'id');
            $province_name = $provinceArr[$province_id]['name'].$provinceArr[$province_id]['suffix'];
        } else {
            $province_name = '';
        }
        return $province_name;
    }

    /**
     * 获取市名
     * @param $province_id
     * @param $city_id
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    private function getCityName($province_id, $city_id){
        $cityInfo = CommonFunc::getCity($province_id);
        if ($cityInfo){
            $cityArr = ArrayHelper::index($cityInfo,'id');
            $city_name = $cityArr[$city_id]['name'].$cityArr[$city_id]['suffix'];
        } else {
            $city_name = '';
        }
        return $city_name;
    }

    /**
     * 获取县名
     * @param $city_id
     * @param $district_id
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    private function getDistrict($city_id, $district_id){
        if (!$district_id) {
            return '';
        }
        $districtInfo = CommonFunc::getDistrict($city_id);
        if ($districtInfo){
            $districtArr = ArrayHelper::index($districtInfo,'id');
            $district_name = $districtArr[$district_id]['name'].$districtArr[$district_id]['suffix'];
        } else {
            $district_name = '';
        }
        return $district_name;
    }

    /**
     * 获取省列表
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function actionProvince(){
        $provinceInfo = CommonFunc::getProvince();
        if ($provinceInfo){
            return $this->jsonSuccess($provinceInfo);
        } else {
            return $this->jsonSuccess([]);
        }
    }

    /**
     * 根据省id获取城市列表
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function actionCity(){
        $province_id = Yii::$app->request->get('province_id');
        if (!$province_id) {
            return $this->jsonError('province_id不能为空');
        }
        $cityInfo = CommonFunc::getCity($province_id);
        if ($cityInfo){
            return $this->jsonSuccess($cityInfo);
        } else {
            return $this->jsonSuccess([]);
        }
    }

    /**
     * 根据城市id获取县（区）列表
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    public function actionDistrict(){
        $city_id = Yii::$app->request->get('city_id');
        if (!$city_id) {
            return $this->jsonError('city_id不能为空');
        }
        $districtInfo = CommonFunc::getDistrict($city_id);
        if ($districtInfo){
            return $this->jsonSuccess($districtInfo);
        } else {
            return $this->jsonSuccess([]);
        }
    }


    /**
     * 获取王氏医院列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-26
     * @return array
     * @throws \Exception
     */
    public function actionMiaoHospitalList()
    {
        $params = Yii::$app->request->getQueryParams();
        $list = GuahaoHospitalModel::getMiaoHospitalListForApi($params);
        return $this->jsonSuccess($list);
    }

    /**
     * 添加日志
     * @param $new_data
     * @param $old_data
     * @param $province_name
     * @param $city_name
     * @param $county_name
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-22
     */
    private function addLog($new_data, $old_data, $province_name, $city_name ,$county_name){
        $change_date = [
            'min_hospital_name' => '医院名称',
            'min_hospital_logo' => '医院logo',
            'min_treatment_project' => '诊疗项目',
            'min_hospital_address' => '详细地址',
            'min_bus_line' => '乘车路线',
            'min_hospital_phone' => '医院联系电话',
            'min_hospital_introduce' => '医院简介',
            'min_company_name' => '单位名称',
            'min_business_license' => '营业执照图片',
            'min_medical_license' => '医疗许可证件图片',
            'min_health_record' => '卫健委备案图片',
            'min_medical_certificate' => '医疗广告证图片',
            'min_guahao_rule' => '挂号规则',
            'min_hospital_contact' => '医院联系人',
            'min_hospital_contact_phone' => '医院联系人电话',
        ];
        $log = '';
        foreach ($change_date as $key => $val){
            if ($old_data[$key] != $new_data[$key]){
                $log .= $val.'由【'.$old_data[$key].'】改为【'.$new_data[$key].'】,';
            }
        }
        if ($old_data['min_hospital_province_name'] != $province_name){
            $log .= '医院所在省名由【'.$old_data['min_hospital_province_name'].'】改为【'.$province_name.'】,';
        }
        if ($old_data['min_hospital_city_name'] != $city_name){
            $log .= '医院所在省名由【'.$old_data['min_hospital_city_name'].'】改为【'.$city_name.'】,';
        }
        if ($old_data['min_hospital_county_name'] != $county_name){
            $log .= '医院所在省名由【'.$old_data['min_hospital_county_name'].'】改为【'.$county_name.'】,';
        }
        if ($old_data['min_hospital_nature'] != $new_data['min_hospital_nature']){
            $min_hospital_nature_log = $new_data['min_hospital_type'] == 1 ? '由【民营】改为【公立】' : '由【公立】改为【民营】';
            $log .= '医院性质由【'.$min_hospital_nature_log.',';
        }
        if ($old_data['min_hospital_level'] != $new_data['min_hospital_level']){
            $log .= '医院等级由【'.MinHospitalModel::$levellist[$new_data['min_hospital_level']].'】改为【'.MinHospitalModel::$levellist[$old_data['min_hospital_level']].'】,';
        }
        if ($old_data['min_hospital_tags'] != $new_data['min_hospital_tags']){
            $hospital_tags_old_log = MinHospitalModel::getTagsInfo($old_data['min_hospital_tags']);
            $hospital_tags_new_log = MinHospitalModel::getTagsInfo($new_data['min_hospital_tags']);
            $log .= '医院标签由【'.$hospital_tags_old_log.'】改为【'.$hospital_tags_new_log.'】,';
        }
        if ($old_data['min_hospital_type'] != $new_data['min_hospital_type']){
            $hospital_type_old_log = MinHospitalModel::$TypeList[$old_data['min_hospital_type']];
            $hospital_type_new_log =  MinHospitalModel::$TypeList[$new_data['min_hospital_type']];
            $log .= '医院标签由【'.$hospital_type_old_log.'】改为【'.$hospital_type_new_log.'】,';
        }
        return $log;
    }

}