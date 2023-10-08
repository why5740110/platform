<?php
/**
 * 民营医院科室列表、审核
 * @file KeshiMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-19
 */

namespace minyingapi\controllers;

use common\models\minying\MinDepartmentModel;
use Yii;

class KeshiMinyingCheckController extends CommonController
{
    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? $requestParams['check_status'] : '';
        $requestParams['department_id'] = (isset($requestParams['department_id']) && (!empty($requestParams['department_id']))) ? $requestParams['department_id'] : '';
        $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];

        $field = ['id','check_status','min_minying_fkname','min_minying_skname','miao_first_department_name','miao_second_department_name',
            'first_check_name','first_check_time','second_check_name','second_check_time','create_time','update_time'];
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
        $field = ['id','min_minying_fkname','min_minying_skname','miao_first_department_name','miao_second_department_name','second_check_passed_record'];
        $info = MinDepartmentModel::getDetail($id, $field);
        return $this->jsonSuccess($info);
    }

    /**
     * 获取民营医院科室列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionGetDepartmentList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];
        $requestParams['min_department_name'] = (isset($requestParams['min_department_name']) && (!empty($requestParams['min_department_name']))) ? $requestParams['min_department_name'] : '';

        $department = MinDepartmentModel::getAuditList($requestParams);
        $result = [];
        if (!empty($department)) $result = $department;

        return $this->jsonSuccess($result);
    }

}