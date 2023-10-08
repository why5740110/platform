<?php
namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\models\GuahaoScheduleModel;
use common\models\HospitalDepartmentRelation;
use common\models\minying\MinAgencyModel;
use common\models\minying\MinHospitalModel;
use common\models\minying\ResourceDeadlineModel;
use common\models\TbLog;
use common\models\GuahaoHospitalModel;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use \yii\helpers\ArrayHelper;
use common\models\BuildToEsModel;
use yii\helpers\Url;
use yii\web\Response;
use common\components\Excel;

class HospitalRelationController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size            = 10;


    public function actionList()
    {
        $requestParams              = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['status']   = isset($requestParams['status']) ? $requestParams['status'] : '';
        $requestParams['tp_platform']   = isset($requestParams['tp_platform']) ? $requestParams['tp_platform'] : '';

        $hosModel = new GuahaoHospitalModel();

        $list = $hosModel::getList($requestParams);

        // 找出民营医院的合作时间
        $min_hospital_list = array_filter($list, function($item) {
            return $item['tp_platform'] == 13;
        });
        $min_hospital_ids = array_column($min_hospital_list, 'tp_hospital_code');;
        $min_hospital_deadline = ResourceDeadlineModel::find()
            ->where(['resource_type' => ResourceDeadlineModel::RESOURCE_TYPE_HOSPITAL, 'resource_id' => $min_hospital_ids])
            ->select(['resource_id', 'begin_time', 'end_time'])
            ->asArray()->indexBy('resource_id')->all();

        foreach ($list as &$item) {
            //$item['tp_platform_name'] = $this->platform[$item['tp_platform']]??'';
            $item['re_hospital_name'] = BaseDoctorHospitals::getList($item['hospital_id'])['name']??'';
            $item['re_hospital_id'] = $item['hospital_id'] ??'';
            $item['deadline_start'] = $item['deadline_end'] = '';
            if ($item['tp_platform'] == 13) {
                if ($begin_time = ArrayHelper::getValue($min_hospital_deadline, "{$item['tp_hospital_code']}.begin_time", '')) {
                    $item['deadline_start'] = date('Y-m-d', $begin_time);
                }
                if ($end_time = ArrayHelper::getValue($min_hospital_deadline, "{$item['tp_hospital_code']}.end_time", '')) {
                    $item['deadline_end'] = date('Y-m-d', $end_time);
                }
            }
        }

        $totalCount = $hosModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('index', $data);
    }

    public function actionRelation()
    {
        $request = \Yii::$app->request;
        $tp_hospital_code=$request->get('tp_hospital_code','');
        $t_id=$request->get('t_id','');
        $data['tp_hospital_info'] = GuahaoHospitalModel::find()->where(['id' => $t_id])->asArray()->one();
        $data['hospital_name'] = isset($data['hospital_name']) ? trim($data['hospital_name']) : '';
        return $this->renderPartial('relation',$data);
    }

    public function actionRelationList()
    {
        $request = \Yii::$app->request;
        $hospital_id=$request->get('hospital_id','');
        $data['hospital_list'] = GuahaoHospitalModel::find()->where(['hospital_id' => $hospital_id,'status'=>1])->asArray()->all();
        foreach ($data['hospital_list'] as &$item) {
            $item['tp_platform_name'] = $this->platform[$item['tp_platform']]??'未知';
        }
        return $this->renderPartial('relation_list',$data);
    }

    public function actionAjaxGetInfo()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            $hospital_id=$request->get('hospital_id','');
            $hospital = BaseDoctorHospitals::getList($hospital_id);
            if($hospital){
                $hospital['hospital'] = ArrayHelper::getValue($hospital, 'name');
                return $this->returnJson(1, '',$hospital);
            }
            return $this->returnJson(2, '');
        }
    }

    /**
     * 关联医院
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/26
     */
    public function actionUpdateRelation()
    {
        $request = \Yii::$app->request;
        $tp_hospital_code=$request->post('tp_hospital_code','');
        $hospital_id=$request->post('hospital_id','');
        $t_id= (int)$request->post('t_id','');
        $info = GuahaoHospitalModel::find()->where(['id' => $t_id])->one();
        if (!$info) {
            return $this->returnJson(2, '该医院不存在！');
        }
        if(!$hospital_id){
            return $this->returnJson(2, '关联医院不能为空！');
        }
        //$hosInfo = BaseDoctorHospitals::find()->where(['id'=>$hospital_id])->one();
        $hosInfo = BaseDoctorHospitals::getHospitalDetail($hospital_id);
        if(!$hosInfo){
            return $this->returnJson(2, '关联医院不存在！');
        }
        if ($info->status == 2) {
            return $this->returnJson(2, '该医院被禁用不能操作！');
        }
        $info->hospital_id = $hospital_id;
        $info->status = 1;
        //关联完 改为未导入
        $info->has_import = 0;
        if($info->save()){
            $editContent  = $this->userInfo['realname'] . '第三方医院 ' . $tp_hospital_code.$info['hospital_name']. ' 关联王氏医院 ' . $hospital_id.$hosInfo['name'];
            TbLog::addLog($editContent, '医院关联添加');
            //更新缓存es
            CommonFunc::UpHospitalCache($hospital_id);
            return $this->returnJson(1, '关联成功');
        }
        return $this->returnJson(2, '关联失败3');
    }

    /**
     * 禁用操作
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-21
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDisabledRelation()
    {
        $request = \Yii::$app->request;
        $t_id= (int)$request->post('t_id','');
        $dis_type= (int)$request->post('dis_type',0);
        $remarks= trim($request->post('remarks',''));
        if (mb_strlen($remarks) >100) {
            return $this->returnJson(2, '不能超过100个字！');
        }
        $info = GuahaoHospitalModel::find()->where(['id' => $t_id])->one();
        if (!$info) {
            return $this->returnJson(2, '该医院不存在！');
        }
        if ($dis_type == 1 && $info->status == 0) {
            return $this->returnJson(2, '已经被启用了，无需操作！');
        }
        if ($dis_type == 0 && $info->status == 2) {
            return $this->returnJson(2, '已经被禁用了，无需操作！');
        }
        $dis_text = '禁用';
        if ($dis_type == 1) {
            $info->status = 0;
            $dis_text = '启用';
        }else{
            $info->status = 2;
            $info->has_import = 0;
            $info->remarks = $remarks;
        }
        $info->hospital_id = 0;

        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            if ($info->save()) {
                if (intval($dis_type) == 0) {
                    // 同步禁用开放医院
                    GuahaoPlatformRelationHospitalModel::updateHospitalNoOpen($info->tp_hospital_code, $info->tp_platform);
                    CommonFunc::deleteScheduleJob($info->tp_platform, $info->tp_hospital_code, $this->userInfo['realname'], $this->userInfo['id']);
                }
                if ($info->tp_platform == 13){
                    MinHospitalModel::updateHospitalStatus($info->tp_hospital_code,$dis_type);
                }
                $editContent = $this->userInfo['realname'] . $dis_text . '了第三方医院:' . $info['hospital_name'];
                TbLog::addLog($editContent, '医院关联操作');
                $transition->commit();
            } else {
                return $this->returnJson(2, '操作失败');
            }
            return $this->returnJson(1, '操作成功');
        }catch (\Exception $e){
            $transition->rollBack();
            $msg = $e->getMessage();
            return $this->jsonError($msg);
        }
    }

    /**
     * 取消关联关系
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/26
     */
    public function actionCancleRelation(){
        /*$request = \Yii::$app->request;
        $tp_hospital_code=$request->get('tp_hospital_code','');
        $info = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $tp_hospital_code])->one();
        $info->hospital_id = 0;
        $info->status = 0;
        $relationInfo = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $tp_hospital_code])->one();
        if($info->save() && $relationInfo->delete()){
            return $this->returnJson(1, '取消关联成功');
        }
        return $this->returnJson(2, '取消关联失败');*/
    }

    /**
     * 新增160医院数据
     * @return array|string
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/24
     */
    public function actionAdd160()
    {
        $tp_platform = 5;
        $code = \Yii::$app->request->get('code');
        $select = \Yii::$app->request->get('select');
        $add = \Yii::$app->request->get('add');
        $code = trim($code);
        if ($code) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if(!is_numeric($code)){
                return ['code' => 0, 'msg' => '必须为纯数字'];
            }
            $exists = GuahaoHospitalModel::find()->where(['tp_platform' => 5, 'tp_hospital_code' => $code])->exists();
            if ($exists) {
                return ['code' => 0, 'msg' => '已添加过了'];
            }

            $byidArr['tp_platform'] = $tp_platform;
            $byidArr['tp_hospital_code'] = $code;

            $hospBase = SnisiyaSdk::getInstance()->getGuahaoHospital($byidArr);
            $list = ArrayHelper::getValue($hospBase, 'list');
            $hospId = ArrayHelper::getValue($list, '0.tp_hospital_code');
            if (ArrayHelper::getValue($hospBase, 'total') > 0 && $hospId) {
                $row = ArrayHelper::getValue($list, '0');
                $hospRow = SnisiyaSdk::getInstance()->getHospitalByid($byidArr);
                if ($hospRow) {
                    $row = array_merge($row, $hospRow);
                }
                if ($select) {
                    return ['code' => 1, 'data' => $row];
                }
                if ($add) {
                    $hospModel = new GuahaoHospitalModel();
                    $hospModel->hospital_name = ArrayHelper::getValue($row, 'hospital_name');
                    $hospModel->tp_hospital_code = ArrayHelper::getValue($row, 'tp_hospital_code');
                    $hospModel->create_time = time();
                    $hospModel->status = 0;
                    $hospModel->tp_platform = $tp_platform;
                    $hospModel->tp_guahao_verify = ArrayHelper::getValue($row, 'tp_guahao_verify', '');
                    $hospModel->tp_guahao_description = ArrayHelper::getValue($row, 'tp_guahao_description', '');
                    $hospModel->province = ArrayHelper::getValue($row, 'province','');
                    $hospModel->tp_hospital_level = ArrayHelper::getValue($row, 'tp_hospital_level');
                    $hospModel->save();
                    return ['code' => 1, 'msg' => '添加成功'];
                }
            } else {
                return ['code' => 0, 'msg' => '数据不存在'];
            }
        }
        return $this->renderPartial('add160');
    }

    /**
     * 导出未关联医院
     * @author wanghongying
     * @param $tp_platform 平台类型
     * @date 2021/9/28
     */
    public function actionExport()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $tp_platform = isset($requestParams['tp_platform']) ? $requestParams['tp_platform'] : "";
        $tp_platform_desc = ($tp_platform>0) ? $this->platform[$tp_platform] : "全部";
        $model = new GuahaoHospitalModel();
        $where['status'] = 0;
        if ($tp_platform) {
            $where['tp_platform'] = $tp_platform;
        }
        $field                = 'id,tp_hospital_code,tp_platform,hospital_name';
        $data = $model->find()->select($field)->where($where)->orderBy('create_time desc')->asArray()->all();
        foreach($data as &$v){
            $v['tp_platform'] = $this->platform[$v['tp_platform']] ?? '未知';
            $v['hospital_id'] = '';
        }
        $excel  = new Excel();
        $header = [
            '序号ID' => 'id',
            '第三方医院ID' => 'tp_hospital_code',
            '医院名称' => 'hospital_name',
            '来源' => 'tp_platform',
            '王氏医院ID' => 'hospital_id'
        ];
        $fileName =  $tp_platform_desc . "_" . date('YmdHi'). '_hospital.xlsx';
        $excel->export($data, $header)->downFile($fileName);
        exit;
    }

    /**
     * 导入需要关联的医院
     * @author wanghongying
     * @param $csv_str  导入csv文件内容
     * @date 2021/9/28
     * //$hospitalKey = ['id', 'tp_hospital_code', 'hospital_name', 'tp_platform', 'hospital_id'];
     */
    public function actionImport()
    {
        $excelData = Yii::$app->request->post('excel_data');
        if (empty($excelData)) return $this->returnJson(2, '没有获取到文件内容');
        $data = json_decode($excelData, true);
        if (!empty($data)) {
            //验证第一列(表医院主id)或第五列(王氏医院id)
            $successNum = 0;
            foreach ($data as $key => &$val) {
                $id = isset($val['序号ID']) ? CommonFunc::filterContent($val['序号ID']) : 0;
                $hospital_id = isset($val['王氏医院ID']) ? CommonFunc::filterContent($val['王氏医院ID']) : 0;
                $line = $key + 1;
                if (!empty($id) && $id <=0) {
                    return $this->returnJson(2, "第{$line}行第一列(表医院主id)存在不合法内容");
                }
                if (!empty($hospital_id)) {
                    if($hospital_id <= 0) {
                        $this->returnJson(2, "第{$line}行第五列(王氏医院id)存在不合法内容");
                    } else {
                        $hospitalCache = \common\models\BaseDoctorHospitals::HospitalDetail($hospital_id);
                        if (empty($hospitalCache)) {
                            $this->returnJson(2, "第{$line}行第五列(王氏医院id)存在不合法或存在的医院无缓存");
                        }
                    }
                }
            }

            foreach ($data as $key => $val) {
                $id = isset($val['序号ID']) ? CommonFunc::filterContent($val['序号ID']) : 0;
                $hospital_id = isset($val['王氏医院ID']) ? CommonFunc::filterContent($val['王氏医院ID']) : 0;
                if ($id <=0 || $hospital_id <= 0) continue;
                $query = GuahaoHospitalModel::findOne($id);
                if (!empty($query) && $query->hospital_id <= 0 && $query->status == 0) {
                    $query->hospital_id = $hospital_id;
                    $query->status = 1;
                    $query->save();
                    $successNum ++;
                    //记录日志
                    $editContent  = $this->userInfo['realname'] . '上传excel文件导入关联医院:' . $val['医院名称'] . ',关联的王氏医院ID为:'.$hospital_id;
                    TbLog::addLog($editContent, '医院导入关联操作');
                }
            }
            return $this->returnJson(1, "导入关联{$successNum}家医院成功");
        } else {
            return $this->returnJson(2, '导入失败');
        }
    }

    /**
     *  备注功能
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public function actionUpdateRemarks()
    {
        $request = \Yii::$app->request;
        $id= (int)$request->post('id','');
        $remarks= (string)$request->post('remarks');

        $info = GuahaoHospitalModel::find()->where(['id' => $id])->one();
        if (!$info) {
            return $this->returnJson(2, '该医院不存在！');
        }
        $oldRemarks = $info->remarks;
        $info->remarks = $remarks;

        if($info->save()){
            $updateRemarksContent  = $this->userInfo['realname'] .'修改了第三方医院:'.$info['hospital_name']."的备注，由【".$oldRemarks.'】修改为【'.$remarks.'】';
            TbLog::addLog($updateRemarksContent, '医院管理备注');
            return $this->returnJson(1, '操作成功');
        }
        return $this->returnJson(2, '操作失败');
    }

    /**
     * 民营医院添加设置时间
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-26
     */
    public function actionSetTime(){
        $request = \Yii::$app->request;
        $tp_hospital_code = (int)$request->get('tp_hospital_code','');
        if (!$tp_hospital_code){
            return $this->returnJson(2, '医院id不能为空！');
        }
        $begin_time = (string)$request->get('begin_time');
        $end_time = (string)$request->get('end_time');
        if ($begin_time > $end_time){
            return $this->returnJson(2, '开始时间不能大于结束时间！');
        }
        $resourceData = [
            'resource_type' => 1,
            'resource_id' => $tp_hospital_code,
            'admin_id' => $this->userInfo['id'],
            'admin_name' => $this->userInfo['realname'],
            'begin_time' => strtotime($begin_time),
            'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime($end_time))),
        ];
        $res = ResourceDeadlineModel::addResourceDeadline($resourceData);
        if ($res){
            return $this->returnJson(1, '操作成功');
        }
    }

    /**
     * 医院详情页
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-27
     */
    public function actionDetail(){
        if (!$id = Yii::$app->request->get('id', '')) {
            $this->_showMessage('id不能为空', Url::to('list'));
        }
        $hospital_model = GuahaoHospitalModel::findOne(['id' => $id]);
        if (!$hospital_model) {
            $this->_showMessage('医院信息不存在!', Url::to('list'));
        }

        $info_data = [
            'min_hospital_id' => '',
            'min_hospital_name' => '',
            'create_time' => '',
            'min_hospital_type' => '',
            'min_hospital_level' => '',
            'min_hospital_nature' => '',
            'min_hospital_tags' => '',
            'min_hospital_province_name' => '',
            'min_hospital_city_name' => '',
            'min_hospital_county_name' => '',
            'min_hospital_address' => '',
            'min_bus_line' => '',
            'min_hospital_phone' => '',
            'min_hospital_introduce' => '',
            'min_company_name' => '',
            'min_business_license' => [],
            'min_medical_license' => [],
            'min_health_record' => [],
            'min_medical_certificate' => [],
            'min_treatment_project' => '',
            'min_guahao_rule' => '',
            'min_hospital_contact' => '',
            'min_hospital_contact_phone' => '',
            'begin_time' => '',
            'end_time' => '',
            'agency_name' => ''
        ];
        // 医院扩展信息
        if ($hospital_model->tp_platform == 13) {
            $min_hospital_model = $hospital_model->minHospital->getHumanFormat();
            $info_data['min_hospital_contact_phone'] = substr_replace($min_hospital_model->min_hospital_contact_phone, '****', 3, 4);
            $info_data['min_hospital_id'] = $min_hospital_model->min_hospital_id;
            $info_data['min_hospital_name'] = $min_hospital_model->min_hospital_name;
            $info_data['create_time'] = $min_hospital_model->create_time;
            $info_data['min_hospital_type'] = MinHospitalModel::$TypeList[$min_hospital_model->min_hospital_type];
            $info_data['min_hospital_level'] = MinHospitalModel::$levellist[$min_hospital_model->min_hospital_level];
            $info_data['min_hospital_nature'] = MinHospitalModel::$naturelist[$min_hospital_model->min_hospital_nature];
            $info_data['min_hospital_tags'] = MinHospitalModel::getTagsInfo($min_hospital_model->min_hospital_tags);
            $info_data['min_hospital_province_name'] = $min_hospital_model->min_hospital_province_name;
            $info_data['min_hospital_city_name'] = $min_hospital_model->min_hospital_city_name;
            $info_data['min_hospital_county_name'] = $min_hospital_model->min_hospital_county_name;
            $info_data['min_hospital_address'] = $min_hospital_model->min_hospital_address;
            $info_data['min_bus_line'] = $min_hospital_model->min_bus_line;
            $info_data['min_hospital_phone'] = $min_hospital_model->min_hospital_phone;
            $info_data['min_hospital_introduce'] = $min_hospital_model->min_hospital_introduce;
            $info_data['min_company_name'] = $min_hospital_model->min_company_name;
            $info_data['min_business_license'] = $min_hospital_model->min_business_license;
            $info_data['min_medical_license'] = $min_hospital_model->min_medical_license;
            $info_data['min_health_record'] = $min_hospital_model->min_health_record;
            $info_data['min_medical_certificate'] = $min_hospital_model->min_medical_certificate;
            $info_data['min_treatment_project'] = MinHospitalModel::getTreatmentProject($min_hospital_model->min_treatment_project);
            $info_data['min_guahao_rule'] = $min_hospital_model->min_guahao_rule;
            $info_data['min_hospital_contact'] = $min_hospital_model->min_hospital_contact;

            $agency_info = MinAgencyModel::find()->select('agency_name')->where(['agency_id' => $min_hospital_model->agency_id])->one();
            if ($agency_info){
                $info_data['agency_name'] = $agency_info->agency_name;
            }

            //医院有效时间
            $resource_deadline_info = ResourceDeadlineModel::find()->select('begin_time,end_time')->where(['resource_id'=>$hospital_model->tp_hospital_code,'resource_type'=>1])->one();
            if ($resource_deadline_info){
                $info_data['begin_time'] = date('Y-m-d',$resource_deadline_info->begin_time);
                $info_data['end_time'] = date('Y-m-d',$resource_deadline_info->end_time);
            }
        }
        return $this->render('detail', [
            'base_model' => $hospital_model,
            'info_data' => $info_data
        ]);
    }
}