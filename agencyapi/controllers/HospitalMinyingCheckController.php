<?php
/**
 * 民营医院列表、审核
 * @file HospitalMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-15
 */
namespace agencyapi\controllers;

use common\models\minying\MinHospitalModel;
use Yii;
use common\models\GuahaoHospitalModel;
use common\models\AuditLogModel;
use common\models\TbLog;
use common\libs\CommonFunc;

class HospitalMinyingCheckController extends CommonController
{

    /**
     * 审核列表
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-15
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $requestParams['hospital_level'] = (isset($requestParams['hospital_level']) && (!empty($requestParams['hospital_level']))) ? $requestParams['hospital_level'] : '';
        $requestParams['create_time'] = (isset($requestParams['create_time']) && (!empty($requestParams['create_time']))) ? $requestParams['create_time'] : '';
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? $requestParams['check_status'] : '';
        $requestParams['admin_type'] = 1;//1 代理商后台 2 王氏后台
        $requestParams['agency_id']   = $this->user['agency_id'];//代理商id

        $field = ['min_hospital_id','min_hospital_name','check_status','min_hospital_level','first_check_name','first_check_time','second_check_name','second_check_time','fail_reason','create_time','update_time'];
        $list = MinHospitalModel::getList($requestParams, $field);
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
            $item['min_hospital_level_desc'] = MinHospitalModel::$levellist[$item['min_hospital_level']];
            $item['check_status_desc'] = MinHospitalModel::$checklist[$item['check_status']];
        }
        $totalCount = MinHospitalModel::getCount($requestParams);

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
     * @date 2022-07-14
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id');
        $info = MinHospitalModel::getDetail($id);
        $domain = \Yii::$app->params['min_hospital_img_oss_url_prefix'];
        $info['min_hospital_logo'] = $domain . $info['min_hospital_logo'];
        $info['create_time'] = date('Y-m-d H:i:s', $info['update_time']);
        $info['min_hospital_level'] = MinHospitalModel::$levellist[$info['min_hospital_level']];
        $info['min_hospital_nature'] = MinHospitalModel::$naturelist[$info['min_hospital_nature']];
        $info['check_status_desc'] = MinHospitalModel::$checklist[$info['check_status']];
        $info['min_hospital_tags'] = MinHospitalModel::getTagsInfo($info['min_hospital_tags']);
        $info['min_hospital_type'] = MinHospitalModel::$TypeList[$info['min_hospital_type']];
        $info['min_business_license'] = CommonFunc::getDomainPic($domain, $info['min_business_license']);//营业执照
        $info['min_medical_license'] = CommonFunc::getDomainPic($domain, $info['min_medical_license']);//医疗许可证件
        $info['min_health_record'] = CommonFunc::getDomainPic($domain, $info['min_health_record']);//卫健委备案
        $info['min_medical_certificate'] = CommonFunc::getDomainPic($domain, $info['min_medical_certificate']);//医疗广告证
        return $this->jsonSuccess($info);
    }

    /**
     * 审核通过
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-15
     */
    public function actionAudit()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status', 1);//审核状态  1 通过  2 拒绝
        $fail_reason = Yii::$app->request->post('fail_reason', '');
        $info = MinHospitalModel::findOne($id);
        if ($status == 1 && $info->check_status == 2) {
            return $this->jsonError('该医院一审已通过');
        }

        if ($status == 2 && $info->check_status == 3) {
            return $this->jsonError('该医院一审已拒绝');
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
            $info->first_check_name = $this->user['username'];
            $info->first_check_time = $time;
            $res = $info->save();

            if ($res) {
                //记录审核日志
                $audilLogData = [
                    'operate_type' => 1,
                    'operate_id' => $info->min_hospital_id,
                    'audit_uid' => $this->user['account_id'],
                    'audit_name' => $this->user['username'],
                    'audit_status' => $status,
                    'audit_type' => 1,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->user['username'] . "一审{$text} 民营医院：[{$info->min_hospital_name}]" ;
                TbLog::addLog($editContent, "民营医院一审{$text}", $this->admin_info);
                $transition->commit();
            } else {
                $transition->rollBack();
                return $this->jsonError(2, "{$text}失败");
            }
            return $this->jsonSuccess([], "{$text}成功");
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            return $this->jsonError(2, $msg);
        }
    }
}