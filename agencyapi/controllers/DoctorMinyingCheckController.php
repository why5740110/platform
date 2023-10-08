<?php
/**
 * 民营医院医生列表、审核
 * @file DoctorMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-19
 */

namespace agencyapi\controllers;

use common\models\minying\MinDoctorModel;
use common\models\minying\MinDepartmentModel;
use Yii;
use common\models\AuditLogModel;
use common\models\TbLog;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoHospitalModel;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\libs\CommonFunc;

class DoctorMinyingCheckController extends CommonController
{

    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['doctor_name'] = (isset($requestParams['doctor_name']) && (!empty($requestParams['doctor_name']))) ? $requestParams['doctor_name'] : '';
        $requestParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $requestParams['create_time'] = (isset($requestParams['create_time']) && (!empty($requestParams['create_time']))) ? $requestParams['create_time'] : '';
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? $requestParams['check_status'] : '';
        $requestParams['admin_type'] = 1;//1 代理商后台 2 王氏后台
        $requestParams['agency_id']   = $this->user['agency_id'];//代理商id

        $field = ['min_doctor_id','min_doctor_name','min_hospital_id','min_hospital_name','check_status','first_check_uname','first_check_time','second_check_uname','second_check_time','fail_reason','create_time','update_time'];
        $list = MinDoctorModel::getList($requestParams, $field);
        foreach ($list as &$item) {
            $item['check_status_desc'] = MinDoctorModel::$checklist[$item['check_status']];
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
        }

        $totalCount = MinDoctorModel::getCount($requestParams);
        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];
        return $this->jsonSuccess($result);
    }

    /**
     * 详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-18
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id');
        $info = MinDoctorModel::getDetail($id);
        $domain = \Yii::$app->params['min_doctor_img_oss_url_prefix'];
        $info['create_time'] = date("Y-m-d H:i:s", $info['update_time']);
        $info['mobile'] = (!empty($info['mobile'])) ? substr_replace($info['mobile'],'****', 3, 4) : "";
        //医生头像
        $info['avatar'] = !empty($info['avatar']) ? $domain . $info['avatar'] : '';
        $info['min_doctor_tags'] = MinDoctorModel::getTagsInfoById($info['min_doctor_tags']);
        $info['check_status_desc'] = MinDoctorModel::$checklist[$info['check_status']];

        $department = MinDepartmentModel::find()->where(['id' => $info['min_department_id']])->asArray()->one();
        $info['department'] = $department['min_minying_fkname'] . '-' . $department['min_minying_skname'];
        $info['visitType'] = MinDoctorModel::$visitType[$info['visit_type']];
        //身份证有效期
        $info['id_card_begin'] = date("Y.m.d", $info['id_card_begin']);
        $info['id_card_end'] = date("Y.m.d", $info['id_card_end']);
        $info['id_card_file'] = CommonFunc::getDomainPic($domain, $info['id_card_file']);//身份证件图片
        //医师执业证有效期
        $info['practicing_cert_begin'] = date("Y.m.d", $info['practicing_cert_begin']);
        $info['practicing_cert_end'] = date("Y.m.d", $info['practicing_cert_end']);
        $info['practicing_cert_file'] = CommonFunc::getDomainPic($domain, $info['practicing_cert_file']);//医师执业证图片
        //医师资格证有效期
        $info['doctor_cert_begin'] = date("Y.m.d", $info['doctor_cert_begin']);
        $info['doctor_cert_end'] = date("Y.m.d", $info['doctor_cert_end']);
        $info['doctor_cert_file'] = CommonFunc::getDomainPic($domain, $info['doctor_cert_file']);//医师资格证图片
        //专业技术资格证有效期
        $info['professional_cert_begin'] = date("Y.m.d", $info['professional_cert_begin']);
        $info['professional_cert_end'] = date("Y.m.d", $info['professional_cert_end']);
        $info['professional_cert_file'] = CommonFunc::getDomainPic($domain, $info['professional_cert_file']);//专业技术资格证图片
        //多点执业证明有效期
        $info['multi_practicing_cert_begin'] = date("Y.m.d", $info['multi_practicing_cert_begin']);
        $info['multi_practicing_cert_end'] = date("Y.m.d", $info['multi_practicing_cert_end']);
        $info['multi_practicing_cert_file'] = CommonFunc::getDomainPic($domain, $info['multi_practicing_cert_file']);//多点执业证明图片

        return $this->jsonSuccess($info);
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

        if ($status == 1 && $info->check_status == 2) {
            return $this->jsonError('该医生一审已通过');
        }

        if ($status == 2 && $info->check_status == 3) {
            return $this->jsonError('该医生一审已拒绝');
        }

        if ($status == 2 && empty($fail_reason)) {
            return $this->jsonError('拒绝原因不可为空');
        }

        if ($status == 2 && !empty($fail_reason)) {
            $reasonLen = (strlen($fail_reason) + mb_strlen($fail_reason, 'UTF8')) / 2;
            if ($reasonLen > 60) {
                return $this->jsonError('拒绝原因不可超过30个字符');
            }
        }

        $text = ($status == 1) ? '审核通过' : '审核拒绝';
        $check_status = ($status == 1) ? 2 : 3;

        $time = time();

        ##审操作
        $transition = Yii::$app->getDb()->beginTransaction();
        try{
            //审核通过操作
            $info->check_status = $check_status;
            $info->fail_reason = $fail_reason;
            $info->update_time = $time;
            $info->admin_id = $this->user['account_id'];
            $info->admin_name = $this->user['username'];
            $info->first_check_uid = $this->user['account_id'];
            $info->first_check_uname = $this->user['username'];
            $info->first_check_time = $time;
            $res = $info->save();

            if ($res) {
                //记录审核日志
                $audilLogData = [
                    'operate_type' => 3,
                    'operate_id' => $info->min_doctor_id,
                    'audit_uid' => $this->user['account_id'],
                    'audit_name' => $this->user['username'],
                    'audit_status' => $status,
                    'audit_type' => 1,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->user['username'] . "一审{$text} 民营医院医生：[{$info->min_doctor_name}]" ;
                TbLog::addLog($editContent, "民营医院医生一审{$text}", $this->admin_info);
                $transition->commit();
            } else {
                $transition->rollBack();
                return $this->jsonError("{$text}失败");
            }
            return $this->jsonSuccess([], "{$text}成功");
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            return $this->jsonError($msg);
        }
    }

    /**
     * 获取民营医院医生列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionGetDoctorList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['agency_id'] = $this->user['account_id'];

        $docList = MinDoctorModel::getAuditList($requestParams);
        $result = [];
        if (!empty($docList)) $result = $docList;

        return $this->jsonSuccess($result);
    }

}