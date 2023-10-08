<?php
/**
 * 民营医院科室列表、审核
 * @file KeshiMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-18
 */

namespace agencyapi\controllers;

use common\models\minying\MinDepartmentModel;
use Yii;
use yii\data\Pagination;
use common\models\AuditLogModel;
use common\models\TbLog;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoHospitalModel;

class KeshiMinyingCheckController extends CommonController
{

    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        //$requestParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $requestParams['keshi_name'] = (isset($requestParams['keshi_name']) && (!empty($requestParams['keshi_name']))) ? $requestParams['keshi_name'] : '';
        $requestParams['create_time'] = (isset($requestParams['create_time']) && (!empty($requestParams['create_time']))) ? $requestParams['create_time'] : '';
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? $requestParams['check_status'] : '';
        $requestParams['admin_type'] = 1;//1 代理商后台 2 王氏后台
        $requestParams['agency_id']   = $this->user['agency_id'];//代理商id

        $field = ['id','min_hospital_name','check_status','min_minying_fkname','min_minying_skname','miao_first_department_name','miao_second_department_name',
            'first_check_name','first_check_time','second_check_name','second_check_time','fail_reason','create_time','update_time'];
        $list = MinDepartmentModel::getList($requestParams, $field);
        foreach ($list as &$item) {
            $item['check_status_desc'] = MinDepartmentModel::$checklist[$item['check_status']];
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
        }

        $totalCount = MinDepartmentModel::getCount($requestParams);
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
        $field = ['id','min_hospital_name','check_status','fail_reason','min_minying_fkname','min_minying_skname','miao_first_department_name','miao_second_department_name','create_time','second_check_passed_record','update_time'];
        $info = MinDepartmentModel::getDetail($id, $field);
        $info['create_time'] = date("Y-m-d H:i:s", $info['update_time']);
        $info['check_status_desc'] = MinDepartmentModel::$checklist[$info['check_status']];

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
        $info = MinDepartmentModel::findOne($id);
        if ($status == 1 && $info->check_status == 2) {
            return $this->jsonError('该科室一审已通过');
        }

        if ($status == 2 && $info->check_status == 3) {
            return $this->jsonError('该科室一审已拒绝');
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
                    'operate_type' => 2,
                    'operate_id' => $info->id,
                    'audit_uid' => $this->user['account_id'],
                    'audit_name' => $this->user['username'],
                    'audit_status' => $status,
                    'audit_type' => 1,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->user['username'] . "一审{$text} 民营医院科室：[{$info->min_minying_skname}]" ;
                TbLog::addLog($editContent, "民营医院科室一审{$text}", $this->admin_info);
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
     * 获取代理商科室列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionGetDepartmentList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['agency_id'] = $this->user['agency_id'];

        $department = MinDepartmentModel::getAuditList($requestParams);
        $result = [];
        if (!empty($department)) $result = $department;

        return $this->jsonSuccess($result);
    }
}