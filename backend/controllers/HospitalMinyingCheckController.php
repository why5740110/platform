<?php
/**
 * 民营医院列表、审核
 * @file HospitalMinyingCheckController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-11
 */

namespace backend\controllers;


use common\libs\CommonFunc;
use common\models\minying\MinHospitalModel;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use common\models\GuahaoHospitalModel;
use common\models\AuditLogModel;
use common\models\TbLog;

class HospitalMinyingCheckController extends BaseController
{
    public $limit = 10;
    const TP_PLATFORM = 13;

    /**
     * 审核列表
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-11
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['hospital_name'] = (isset($requestParams['hospital_name']) && (!empty($requestParams['hospital_name']))) ? $requestParams['hospital_name'] : '';
        $requestParams['hospital_level'] = (isset($requestParams['hospital_level']) && (!empty($requestParams['hospital_level']))) ? $requestParams['hospital_level'] : '';
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
        $list = MinHospitalModel::getList($requestParams,'*');
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
        }

        $totalCount = MinHospitalModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('index', $data);
    }

    /**
     * 详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-14
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id');
        if (!$id) {
            $this->_showMessage('缺少医院id', Url::to('list'));
        }
        $info = MinHospitalModel::getDetail($id);
        if (!$info) {
            $this->_showMessage('未找到审核记录', Url::to('list'));
        }
        $info['create_time'] = date('Y-m-d H:i:s', $info['update_time']);
        $info['min_hospital_level'] = MinHospitalModel::$levellist[$info['min_hospital_level']];
        $info['min_hospital_nature'] = MinHospitalModel::$naturelist[$info['min_hospital_nature']];
        $info['check_status_desc'] = MinHospitalModel::$checklist[$info['check_status']];
        $info['min_hospital_type'] = MinHospitalModel::$TypeList[$info['min_hospital_type']];
        $info['min_hospital_tags'] = MinHospitalModel::getTagsInfo($info['min_hospital_tags']);

        $info['min_business_license'] = explode(',', $info['min_business_license']);//营业执照
        $info['min_medical_license'] = explode(',', $info['min_medical_license']);//医疗许可证件
        $info['min_health_record'] = explode(',', $info['min_health_record']);//卫健委备案
        $info['min_medical_certificate'] = explode(',', $info['min_medical_certificate']);//医疗广告证
        $info['min_hospital_contact_phone'] = (!empty($info['min_hospital_contact_phone'])) ? substr_replace($info['min_hospital_contact_phone'],'****', 3, 4) : "";

        $data = ['dataProvider' => $info];
        return $this->render('info', $data);
    }

    /**
     * 审核 通过/拒绝
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-14
     */
    public function actionAudit()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status', 1);//审核状态  1 通过  2 拒绝
        $fail_reason = Yii::$app->request->post('fail_reason', '');//审核失败原因
        $info = MinHospitalModel::findOne($id);
        if ($info->check_status != 2) {
            return $this->returnJson(2, '该医院一审不通过');
        }

        if ($status == 1 && $info->check_status == 4) {
            return $this->returnJson(2, '该医院二审已通过');
        }

        if ($status == 2 && $info->check_status == 5) {
            return $this->returnJson(2, '该医院二审已拒绝');
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
                    //记录tb_guahao_hospital表数据
                    $hosWhere = [
                        'tp_platform' => self::TP_PLATFORM,
                        'tp_hospital_code' => $id,
                    ];
                    $hosRes = GuahaoHospitalModel::find()->where($hosWhere)->asArray()->one();
                    if (!empty($hosRes)) {
                        $hosmodel = GuahaoHospitalModel::findOne($hosRes['id']);
                    } else {
                        $hosmodel = new GuahaoHospitalModel();
                        $hosmodel->create_time = $time;
                        $hosmodel->status = 0;
                        $hosmodel->remarks = '';
                        $hosmodel->tp_platform = self::TP_PLATFORM;
                        $hosmodel->tp_guahao_section = 0;
                        $hosmodel->tp_guahao_description = '';
                        $hosmodel->tp_guahao_verify = '';
                        $hosmodel->tp_allowed_cancel_day = 1;
                        $hosmodel->tp_allowed_cancel_time = '12:00';
                        $hosmodel->tp_open_day = 0;
                        $hosmodel->tp_open_time = '';
                    }
                    $hosmodel->province = $info->min_hospital_province_name;
                    $hosmodel->city_code = $info->min_hospital_city_id;
                    $hosmodel->hospital_name = $info->min_hospital_name;
                    $hosmodel->tp_hospital_code = $info->min_hospital_id;
                    $hosmodel->tp_guahao_description = $info->min_guahao_rule;
                    $hosmodel->tp_hospital_level = MinHospitalModel::$levellist[$info->min_hospital_level];
                    $hosmodel->save();
                }
                //记录审核日志
                $audilLogData = [
                    'operate_type' => 1,
                    'operate_id' => $info->min_hospital_id,
                    'audit_uid' => $this->userInfo['id'],
                    'audit_name' => $this->userInfo['realname'],
                    'audit_status' => $status,
                    'audit_type' => 2,
                    'audit_remark' => $fail_reason,
                ];
                AuditLogModel::addLog($audilLogData);
                //记录操作日志
                $editContent  = $this->userInfo['realname'] . "二审{$text} 民营医院：[{$info->min_hospital_name}]" ;
                TbLog::addLog($editContent, "民营医院二审{$text}");
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