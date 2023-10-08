<?php
/**
 * 民营医院科室列表、审核
 * @file KeshiMinyingCheckController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-12
 */

namespace backend\controllers;

use common\models\minying\MinDepartmentModel;
use Yii;
use yii\data\Pagination;
use common\models\AuditLogModel;
use common\models\TbLog;
use common\models\HospitalDepartmentRelation;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\GuahaoHospitalModel;
use common\models\Department;
use yii\helpers\Url;
use common\libs\CommonFunc;

class KeshiMinyingCheckController extends BaseController
{
    public $limit = 10;
    const TP_PLATFORM = 13;

    public function actionList() {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
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
                    return $this->render('list', $data);
                }
            } else {
                return $this->render('list', $data);
            }
        }
        $list = MinDepartmentModel::getList($requestParams);
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
        }

        $totalCount = MinDepartmentModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('list', $data);
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
            $this->_showMessage('缺少科室id', Url::to('list'));
        }
        $info = MinDepartmentModel::getDetail($id);
        if (!$info) {
            $this->_showMessage('未找到审核记录', Url::to('list'));
        }
        $info['create_time'] = date("Y-m-d H:i:s", $info['update_time']);
        $info['min_department'] = $info['min_minying_fkname'] . '>>' . $info['min_minying_skname'];
        $info['miao_department'] = $info['miao_first_department_name'] . '>>' . $info['miao_second_department_name'];
        $info['check_status_desc'] = MinDepartmentModel::$checklist[$info['check_status']];
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
        $info = MinDepartmentModel::findOne($id);
        if ($info->check_status != 2) {
            return $this->returnJson(2, '该科室一审不通过');
        }

        if ($status == 1 && $info->check_status == 4) {
            return $this->returnJson(2, '该科室二审已通过');
        }

        if ($status == 2 && $info->check_status == 5) {
            return $this->returnJson(2, '该科室二审已拒绝');
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
            return $this->returnJson(2, '科室对应的医院未关联或禁用');
        }

        $text = ($status == 1) ? '审核通过' : '审核拒绝';
        $check_status = ($status == 1) ? 4 : 5;

        $time = time();

        ##审操作
        $transition = Yii::$app->getDb()->beginTransaction();
        try{
            //审核通过操作
            $info->check_status = $check_status;
            $info->fail_reason = $fail_reason;
            $info->update_time = $time;
            $info->admin_id = $this->userInfo['id'];
            $info->admin_name = $this->userInfo['realname'];
            if ($status == 1) {
                $info->second_check_passed_record = 1;
            }
            $info->second_check_uid = $this->userInfo['id'];
            $info->second_check_name = $this->userInfo['realname'];
            $info->second_check_time = $time;
            $res = $info->save();

            if ($res) {
                if ($status == 1) {
                    //判断是否已经导入过该科室
                    $depRelation = TbDepartmentThirdPartyRelationModel::find()
                        ->where(['tp_platform' => self::TP_PLATFORM])
                        ->andWhere(['tp_hospital_code' => $info->min_hospital_id])
                        ->andWhere(['tp_department_id' => $id])
                        ->asArray()->one();
                    if ($depRelation) {
                        $departmentRelationModel = HospitalDepartmentRelation::findOne($depRelation['hospital_department_id']);
                        $departmentRelationModel->frist_department_name = $info->min_minying_fkname;
                        $departmentRelationModel->second_department_name = $info->min_minying_skname;
                        $departmentRelationModel->save();
                    } else {
                        //记录关联关系
                        $commonDepartment = Department::getKeshiByDepartmentName($info->min_minying_skname);//判断公共科室是否存在
                        if (empty($commonDepartment)) {
                            //新增
                            $depModel = new Department();
                            $depModel->department_name = $info->min_minying_skname;
                            $depModel->create_time = time();
                        } else {
                            //更改
                            $depModel = Department::findOne($commonDepartment['department_id']);
                        }
                        $depModel->parent_id = 1;
                        $depModel->status = 1;
                        $depModel->is_common = 1;
                        $depModel->is_match = 1;
                        $depModel->miao_first_department_id = $info->miao_first_department_id;
                        $depModel->miao_second_department_id = $info->miao_second_department_id;
                        $depModel->save();

                        //记录科室关联表数据
                        $relationParams['tp_hospital_code'] = strval($info->min_hospital_id);
                        $relationParams['tp_platform']      = self::TP_PLATFORM;
                        $relationParams['hospital_name']    = $info->min_hospital_name;
                        $relationParams['tp_department_id'] = strval($info->id);
                        $relationParams['hospital_id']      = $hosInfo['hospital_id'];
                        $relationParams['first_department_name']  = $info->min_minying_fkname;
                        $relationParams['department_name']  = $info->min_minying_skname;
                        $relationParams['create_time']      = $time;
                        $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);
                        if ($result['code'] != 200) {
                            $info->check_status = 2;
                            $info->second_check_passed_record = 0;
                            $info->save();
                            $transition->commit();
                            return $this->returnJson(2, $result['msg']);
                        }
                        unset($relationParams);
                    }
                }
                //记录审核日志
                $audilLogData = [
                    'operate_type' => 2,
                    'operate_id' => $info->id,
                    'audit_uid' => $this->userInfo['id'],
                    'audit_name' => $this->userInfo['realname'],
                    'audit_status' => $status,
                    'audit_type' => 2,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->userInfo['realname'] . "二审{$text} 民营医院科室：[{$info->min_minying_skname}]" ;
                TbLog::addLog($editContent, "民营医院科室二审{$text}");
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