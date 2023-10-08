<?php
/**
 * 排班计划
 * SchedulePlanController.php
 * @author wanghongying<wanghongying@yuanyinjituan.com>
 * @date 2022-07-19
 */
namespace agencyapi\controllers;

use common\models\SchedulePlanModel;
use common\models\ScheduleClosePlanModel;
use common\models\minying\MinDepartmentModel;
use Yii;
class SchedulePlanController extends CommonController
{
    public $department;

    public function init()
    {
        parent::init();
        $department = MinDepartmentModel::getAllList();
        foreach ($department as $val) {
            $this->department[$val['min_department_id']] = $val['min_department_name'];
        }
    }

    /**
     * 出诊计划列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-19
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? trim($requestParams['min_department_id']) : '';//一级科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词
        $requestParams['valid_status']   = isset($requestParams['valid_status']) ? trim($requestParams['valid_status']) : '';//有效状态 1 未生效  2 生效中 3 已失效
        $requestParams['agency_id']   = $this->user['agency_id'];//代理商id
        $time = time();
        $list = SchedulePlanModel::getList($requestParams);
        foreach ($list as &$item) {
            $item['department'] = $this->department[$item['min_department_id']];
            $item['section_type_desc'] = SchedulePlanModel::$sectionType[$item['section_type']];
            $item['visit_cost_desc'] = '￥' . ($item['visit_cost'] / 100);
            $item['valid_date'] = date('Y-m-d', $item['starttime']) . '--' . date('Y-m-d', $item['endtime']);
            if ($item['starttime'] > $time) {
                $item['valid_status'] = "未生效";
            } else if ($item['endtime'] < $time) {
                $item['valid_status'] = "已失效";
            } else {
                $item['valid_status'] = "生效中";
            }
            //出诊情况
            $item['cycle_desc'] = SchedulePlanModel::getFormatCycle($item);
        }
        $totalCount = SchedulePlanModel::getCount($requestParams);

        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];
        return $this->jsonSuccess($result, '成功');
    }

    /**
     * 停诊计划列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionCloseList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? trim($requestParams['min_department_id']) : '';//民营医院科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词
        $requestParams['agency_id'] = $this->user['agency_id'];//代理商id

        $list = ScheduleClosePlanModel::getList($requestParams);
        foreach ($list as &$item) {
            //获取医院科室数据
            $item['department'] = ($item['min_department_id'] > 0) ? $this->department[$item['min_department_id']] : "";
            $item['stop_section_type_desc'] = ScheduleClosePlanModel::$stopVisitType[$item['stop_visit_type']];
            $item['section_cycle_type_desc'] = ScheduleClosePlanModel::$sectionCycleType[$item['section_cycle_type']];
            //停诊情况
            $item['cycle_desc'] = ScheduleClosePlanModel::getFormatCycle($item);
        }
        $totalCount = ScheduleClosePlanModel::getCount($requestParams);
        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];

        return $this->jsonSuccess($result);
    }
}