<?php
/**
 * 民营医院医生列表、审核
 * @file DoctorMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-18
 */

namespace backend\controllers;

use common\models\minying\MinDoctorModel;
use common\models\minying\MinDepartmentModel;
use common\models\minying\ResourceDeadlineModel;
use yii\helpers\Url;
use Yii;
use yii\data\Pagination;
use common\models\AuditLogModel;
use common\models\TbLog;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoHospitalModel;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\DoctorModel;
use common\libs\CommonFunc;

class DoctorMinyingCheckController extends BaseController
{
    public $limit = 10;
    const TP_PLATFORM = 13;

    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['doctor_name'] = (isset($requestParams['doctor_name']) && (!empty($requestParams['doctor_name']))) ? $requestParams['doctor_name'] : '';
        $requestParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $requestParams['create_time'] = (isset($requestParams['create_time']) && (!empty($requestParams['create_time']))) ? $requestParams['create_time'] : '';
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? intval($requestParams['check_status']) : '';
        $requestParams['admin_type'] = 2;//1 代理商后台 2 王氏后台

        //时间格式验证
        if (!empty($requestParams['create_time'])) {
            $pages = new Pagination(['totalCount' => 0, 'pageSize' => $requestParams['limit']]);
            $data =  ['dataProvider' => [], 'requestParams' => $requestParams,'totalCount' => 0, 'pages' => $pages];
            if (strripos($requestParams['create_time'], " - ") !== false) {
                list($stime, $etime) = explode(' - ', $requestParams['create_time']);
                if (!(CommonFunc::checkDate($stime) && CommonFunc::checkDate($etime))) {
                    return $this->render('index', $data);
                }
            } else {
                return $this->render('index', $data);
            }
        }
        $list = MinDoctorModel::getList($requestParams);
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
        }

        $totalCount = MinDoctorModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('index', $data);
    }

    /**
     * 详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-18
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id');
        if (!$id) {
            $this->_showMessage('缺少医生id', Url::to('list'));
        }
        $info = MinDoctorModel::getDetail($id);
        if (!$info) {
            $this->_showMessage('未找到审核记录', Url::to('list'));
        }
        $info['create_time'] = date("Y-m-d H:i:s", $info['update_time']);
        $info['mobile'] = (!empty($info['mobile'])) ? substr_replace($info['mobile'],'****', 3, 4) : "";
        //医生头像
        $info['avatar'] = !empty($info['avatar']) ? \Yii::$app->params['min_doctor_img_oss_url_prefix'] . $info['avatar'] : '';
        $info['check_status_desc'] = MinDoctorModel::$checklist[$info['check_status']];
        $info['min_doctor_tags'] = MinDoctorModel::getTagsInfoById($info['min_doctor_tags']);

        $department = MinDepartmentModel::find()->where(['id' => $info['min_department_id']])->asArray()->one();
        $info['department'] = $department['min_minying_fkname'] . '-' . $department['min_minying_skname'];
        $info['visitType'] = MinDoctorModel::$visitType[$info['visit_type']];
        //身份证有效期
        $info['id_card_begin'] = date("Y.m.d", $info['id_card_begin']);
        $info['id_card_end'] = date("Y.m.d", $info['id_card_end']);
        $info['id_card_file'] = explode(',', $info['id_card_file']);//身份证件图片
        //医师执业证有效期
        $info['practicing_cert_begin'] = date("Y.m.d", $info['practicing_cert_begin']);
        $info['practicing_cert_end'] = date("Y.m.d", $info['practicing_cert_end']);
        $info['practicing_cert_file'] = explode(',', $info['practicing_cert_file']);//医师执业证图片
        //医师资格证有效期
        $info['doctor_cert_begin'] = date("Y.m.d", $info['doctor_cert_begin']);
        $info['doctor_cert_end'] = date("Y.m.d", $info['doctor_cert_end']);
        $info['doctor_cert_file'] = explode(',', $info['doctor_cert_file']);//医师资格证图片
        //专业技术资格证有效期
        $info['professional_cert_begin'] = date("Y.m.d", $info['professional_cert_begin']);
        $info['professional_cert_end'] = date("Y.m.d", $info['professional_cert_end']);
        $info['professional_cert_file'] = explode(',', $info['professional_cert_file']);//专业技术资格证图片
        //多点执业证明有效期
        $info['multi_practicing_cert_begin'] = date("Y.m.d", $info['multi_practicing_cert_begin']);
        $info['multi_practicing_cert_end'] = date("Y.m.d", $info['multi_practicing_cert_end']);
        $info['multi_practicing_cert_file'] = explode(',', $info['multi_practicing_cert_file']);//多点执业证明图片

        $data = ['dataProvider' => $info];
        return $this->render('info', $data);
    }

    /**
     * 审核  通过/拒绝
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-18
     */
    public function actionAudit()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status', 1);//审核状态  1 通过  2 拒绝
        $fail_reason = Yii::$app->request->post('fail_reason', '');
        $info = MinDoctorModel::findOne($id);
        if ($info->check_status != 2) {
            return $this->returnJson(2, '该医生一审不通过');
        }

        if ($status == 1 && $info->check_status == 4) {
            return $this->returnJson(2, '该医生二审已通过');
        }

        if ($status == 2 && $info->check_status == 5) {
            return $this->returnJson(2, '该医生二审已拒绝');
        }

        if ($status == 2 && empty($fail_reason)) {
            return $this->returnJson(2, '拒绝原因不可为空');
        }

        if ($status == 2 && !empty($fail_reason)) {
            $reasonLen = (strlen($fail_reason) + mb_strlen($fail_reason, 'UTF8')) / 2;
            if ($reasonLen > 60) {
                return $this->returnJson(2, '拒绝原因不可超过30个字符');
            }
        }

        //验证医院是否关联
        $hosInfo = GuahaoHospitalModel::find()
            ->where(['tp_hospital_code' => $info->min_hospital_id])
            ->andWhere(['tp_platform' => self::TP_PLATFORM])
            ->andWhere(['status' => 1])
            ->asArray()->one();

        if (empty($hosInfo)) {
            return $this->returnJson(2, '医生对应的医院未关联或禁用');
        }

        $hospitalCache = \common\models\BaseDoctorHospitals::HospitalDetail($hosInfo['hospital_id']);
        $hospital_type = (isset($hospitalCache['kind']) && $hospitalCache['kind'] == '公立') ? 1 : 2;

        $where = [
            'tp_platform' => self::TP_PLATFORM,
            'tp_hospital_code' => $info->min_hospital_id,
            'tp_department_id' => $info->min_department_id
        ];
        $depData = TbDepartmentThirdPartyRelationModel::find()->where($where)->asArray()->one();
        if (empty($depData)) {
            return $this->returnJson(2, '没有关联科室数据');
        }

        //获取科室关联的王氏信息
        $hospitalRelationInfo = HospitalDepartmentRelation::find()->where(['id' => $depData['hospital_department_id']])->asArray()->one();
        if (empty($hospitalRelationInfo)) {
            return $this->returnJson(2, '没有关联王氏医院科室！');
        }

        $text = ($status == 1) ? '审核通过' : '审核拒绝';
        $check_status = ($status == 1) ? 4 : 5;

        $time = time();

        ##审操作
        $transition = Yii::$app->getDb()->beginTransaction();
        try{
            //审核通过操作
            $info->check_status = $check_status;
            $info->cert_status = MinDoctorModel::CERT_STATUS_NORMAL;
            $info->fail_reason = $fail_reason;
            $info->update_time = $time;
            $info->admin_id = $this->userInfo['id'];
            $info->admin_name = $this->userInfo['realname'];
            if ($status == 1) {
                $info->second_check_passed_record = 1;
            }
            $info->second_check_uid = $this->userInfo['id'];
            $info->second_check_uname = $this->userInfo['realname'];
            $info->second_check_time = $time;
            $res = $info->save();

            if ($res) {
                if ($status == 1) {
                    //记录医生表数据
                    $doc['tp_doctor_id'] = strval($info->min_doctor_id);
                    $doc['realname'] = CommonFunc::filterContent($info->min_doctor_name);
                    $doc['job_title'] = (strlen($info->min_job_title) > 20) ? "未知" : $info->min_job_title;
                    $doc['job_title_id'] = $info->min_job_title_id ?? 0;
                    $doc['tp_hospital_code'] = strval($info->min_hospital_id);
                    $doc['hospital_id'] = $hosInfo['hospital_id'];
                    $doc['hospital_name'] = $hosInfo['hospital_name'];
                    $doc['hospital_type'] = $hospital_type;
                    $doc['tp_department_id'] = strval($info->min_department_id) ?? "";
                    $doc['frist_department_id'] = $hospitalRelationInfo['frist_department_id'] ?? 0;
                    $doc['second_department_id'] = $hospitalRelationInfo['second_department_id'] ?? 0;
                    $doc['frist_department_name'] = $hospitalRelationInfo['frist_department_name'] ?? "";
                    $doc['second_department_name'] = $hospitalRelationInfo['second_department_name'] ?? "";
                    $doc['miao_frist_department_id'] = $hospitalRelationInfo['miao_frist_department_id'] ?? 0;
                    $doc['miao_second_department_id'] = $hospitalRelationInfo['miao_second_department_id'] ?? 0;
                    $doc['tp_frist_department_id'] = "";
                    $doc['tp_platform'] = self::TP_PLATFORM;
                    $doc['tp_primary_id'] = '';
                    $doc['profile'] = $info->intro ? $info->intro : '';
                    $doc['good_at'] = $info->good_at ? $info->good_at : '';
                    $doc['source_avatar'] = $info->avatar ? \Yii::$app->params['min_doctor_img_oss_url_prefix'] . $info->avatar : '';
                    $doc['admin_id'] = $this->userInfo['id'];
                    $doc['admin_name'] = $this->userInfo['realname'];
                    //多点执业的医生
                    $primary_id = 0;
                    if ($info->visit_type == 2) {
                        //验证
                        $whereDoc = [
                            'hospital_id' => $info->miao_hospital_id,
                            'realname' => $info->min_doctor_name,
                            'primary_id' => 0,
                            'status' => 1,
                        ];
                        $docData = DoctorModel::find()->where($whereDoc)->asArray()->one();
                        if (!empty($docData)) {
                            $primary_id = $docData['doctor_id'];
                        }
                        //记录多点执业医生数据
                        $doc['visit_type'] = $info->visit_type;
                    }
                    $doc['primary_id'] = $primary_id;

                    //判断是否已经导入过该医生
                    $relationInfo = DoctorModel::find()
                        ->where(['tp_platform' => self::TP_PLATFORM])
                        ->andWhere(['tp_hospital_code' => $doc['tp_hospital_code']])
                        ->andWhere(['tp_doctor_id' => $doc['tp_doctor_id']])
                        ->andWhere(['tp_department_id' => $doc['tp_department_id']])
                        ->asArray()->one();
                    if ($relationInfo) {
                        DoctorModel::updateDoctorInfo($relationInfo['doctor_id'], $doc);
                    } else {
                        $result = DoctorModel::autoImportDoctor($doc);
                        if ($result['code'] != 200) {
                            $transition->rollBack();
                            return $this->returnJson(2, $result['msg']);
                        }
                    }

                    //记录医生各个证件的过期时间
                    $resourceData = [
                        'resource_type' => 2,
                        'resource_id' => $info->min_doctor_id,
                        'admin_id' => $this->userInfo['id'],
                        'admin_name' => $this->userInfo['realname']
                    ];
                    //添加证件1 身份证件
                    $resourceData['resource_minor_id'] = 1;
                    $resourceData['begin_time'] = $info->id_card_begin;
                    $resourceData['end_time'] = $info->id_card_end;
                    ResourceDeadlineModel::addResourceDeadline($resourceData);
                    //添加证件2 医师资格证
                    $resourceData['resource_minor_id'] = 2;
                    $resourceData['begin_time'] = $info->doctor_cert_begin;
                    $resourceData['end_time'] = $info->doctor_cert_end;
                    ResourceDeadlineModel::addResourceDeadline($resourceData);
                    //添加证件3 执业证
                    $resourceData['resource_minor_id'] = 3;
                    $resourceData['begin_time'] = $info->practicing_cert_begin;
                    $resourceData['end_time'] = $info->practicing_cert_end;
                    ResourceDeadlineModel::addResourceDeadline($resourceData);
                    //添加证件4 专业证
                    $resourceData['resource_minor_id'] = 4;
                    $resourceData['begin_time'] = $info->professional_cert_begin;
                    $resourceData['end_time'] = $info->professional_cert_end;
                    ResourceDeadlineModel::addResourceDeadline($resourceData);
                    if ($info->visit_type == 2) {
                        //添加证件5 多点执业证
                        $resourceData['resource_minor_id'] = 5;
                        $resourceData['begin_time'] = $info->multi_practicing_cert_begin;
                        $resourceData['end_time'] = $info->multi_practicing_cert_end;
                        ResourceDeadlineModel::addResourceDeadline($resourceData);
                    }
                }
                //记录审核日志
                $audilLogData = [
                    'operate_type' => 3,
                    'operate_id' => $info->min_doctor_id,
                    'audit_uid' => $this->userInfo['id'],
                    'audit_name' => $this->userInfo['realname'],
                    'audit_status' => $status,
                    'audit_type' => 2,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->userInfo['realname'] . "二审{$text} 民营医院医生：[{$info->min_doctor_name}]" ;
                TbLog::addLog($editContent, "民营医院医生二审{$text}");
                $transition->commit();
            } else {
                $transition->rollBack();
                return $this->returnJson(2, "{$text}失败");
            }
            return $this->returnJson(1, "{$text}成功");
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            return $this->returnJson(2, $msg);
        }
    }

}