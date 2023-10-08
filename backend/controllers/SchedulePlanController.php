<?php
/**
 * 排班计划
 * SchedulePlanController.php
 * @author wanghongying<wanghongying@yuanyinjituan.com>
 * @date 2022-07-19
 */
namespace backend\controllers;

use common\models\SchedulePlanModel;
use common\models\ScheduleClosePlanModel;
use Yii;
use yii\data\Pagination;
use common\models\minying\MinDepartmentModel;
class SchedulePlanController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $limit = 10;
    public $department;
    public $departmentSearch;

    public function init()
    {
        parent::init();
        $department = MinDepartmentModel::getAllList();
        foreach ($department as $val) {
            $this->department[$val['min_department_id']] = $val['min_department_name'];
            $this->departmentSearch[$val['min_department_id']] = $val['min_department_name'] . "({$val['min_hospital_name']})";
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
        $requestParams['visit_type']   = isset($requestParams['visit_type']) ? intval($requestParams['visit_type']) : '';//出诊类型 1 出诊 2 停诊
        $requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? intval($requestParams['min_department_id']) : '';//民营医院科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词
        $requestParams['is_done']   = isset($requestParams['is_done']) ? $requestParams['is_done'] : '';//执行状态 0 未执行  1 已执行
        //$time = time();

        $list = SchedulePlanModel::getList($requestParams);
        foreach ($list as &$item) {
            //获取医院科室数据
            $item['department'] = ($item['min_department_id'] > 0) ? $this->department[$item['min_department_id']] : "";
            $item['section_type_desc'] = SchedulePlanModel::$sectionType[$item['section_type']];
            $item['visit_cost_desc'] = '￥' . ($item['visit_cost'] / 100);
            $item['valid_date'] = date('Y-m-d', $item['starttime']) . '--' . date('Y-m-d', $item['endtime']);
            /*if ($item['starttime'] > $time) {
                $item['valid_status'] = "未生效";
            } else if ($item['endtime'] < $time) {
                $item['valid_status'] = "已失效";
            } else {
                $item['valid_status'] = "生效中";
            }*/
            $item['done_status'] = SchedulePlanModel::$doneType[$item['is_done']];
            //出诊情况
            $item['cycle_desc'] = SchedulePlanModel::getFormatCycle($item);
        }
        $totalCount = SchedulePlanModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        $data['department']      = $this->departmentSearch;
        return $this->render('index', $data);
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
        //$requestParams['min_department_id']   = isset($requestParams['min_department_id']) ? trim($requestParams['min_department_id']) : '';//民营医院科室id
        $requestParams['keyword']   = isset($requestParams['keyword']) ? trim($requestParams['keyword']) : '';//搜索关键词

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
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        $data['department']      = $this->department;
        return $this->render('close', $data);
    }
}