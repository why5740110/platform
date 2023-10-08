<?php

namespace common\models;

use yii\data\Pagination;
use common\libs\CommonFunc;
/**
 * This is the model class for table "tb_guahao_schedule_plan".
 * @property int $id  主键id
 * @property int $agency_id  代理商登录id
 * @property int $min_hospital_id 医院id
 * @property string $min_hospital_name  医院名称
 * @property int $min_department_id  民营医院科室id
 * @property int $stop_visit_type  停诊类型 1 全院  2 科室 3 医生
 * @property string $object_name 停诊对象名称
 * @property int $object_id 停诊对象id 全院:0, 科室:对应科室ID，医生:对应医ID
 * @property string $section_cycle_type 门诊周期类型 1 按日期设置 2 按时间段停诊
 * @property string $visit_cycle  出诊周期内容
 * @property string $remark 备注
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人名称
 * @property int $is_delete 是否删除 0 未删除  1 已删除
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class ScheduleClosePlanModel extends \yii\db\ActiveRecord
{
    ##停诊类型 1 全院  2 科室 3 医生
    public static $stopVisitType = [1 => '全院', 2 => '科室', 3 => '医生'];
    public static $sectionCycleType = [1 => '单日停诊', 2 => '时间段停诊'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_schedule_close_plan';
    }

    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::conditionWhere($params);
        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::conditionWhere($params);
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    public static function conditionWhere($params, $field = '*')
    {
        $doctorQuery = self::find()->select($field);
        $doctorQuery->where(['is_delete' => 0]);

        //民营医院id
        if (isset($params['min_hospital_id']) && !empty($params['min_hospital_id'])) {
            $doctorQuery->andWhere(['min_hospital_id' => $params['min_hospital_id']]);
        }

        //代理商登录id
        if (isset($params['agency_id']) && !empty($params['agency_id'])) {
            $doctorQuery->andWhere(['agency_id' => $params['agency_id']]);
        }

        //民营医院科室id
        if (isset($params['min_department_id']) && !empty($params['min_department_id'])) {
            $doctorQuery->andWhere(['min_department_id' => $params['min_department_id']]);
        }

        //搜索关键词 医生或医院名称
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $search_like = ['like', 'object_name', $keyword];
            $doctorQuery->andWhere($search_like);
        }
        return $doctorQuery;
    }

    //获取详情
    public static function getDetail($id)
    {
        $info = self::find()->select("*")->where(['id' => $id])->asArray()->one();
        return $info;
    }

    //保存停诊计划
    public static function addClosePlan($params)
    {
        $model = new self();
        $model->agency_id  = isset($params['agency_id']) ? $params['agency_id'] : 0;
        $model->min_hospital_id  = isset($params['min_hospital_id']) ? $params['min_hospital_id'] : 0;
        $model->min_hospital_name  = isset($params['min_hospital_name']) ? $params['min_hospital_name'] : '';
        $model->object_id  = isset($params['object_id']) ? $params['object_id'] : 0;
        $model->object_name  = isset($params['object_name']) ? $params['object_name'] : '';
        $model->min_department_id  = isset($params['min_department_id']) ? $params['min_department_id'] : 0;
        $model->stop_visit_type  = isset($params['stop_visit_type']) ? $params['stop_visit_type'] : 1;
        $model->section_cycle_type  = isset($params['section_cycle_type']) ? $params['section_cycle_type'] : 0;
        $model->visit_cycle  = isset($params['visit_cycle']) ? $params['visit_cycle'] : '';
        $model->remark  = isset($params['remark']) ? $params['remark'] : '';
        $model->admin_id  = isset($params['admin_id']) ? $params['admin_id'] : 0;
        $model->admin_name  = isset($params['admin_name']) ? $params['admin_name'] : '';
        $model->create_time  = time();

        $res = $model->save();
        if ($res) {
            return $model->attributes['id'];
        } else {
            return 0;
        }
    }

    //更改内容
    public static function updateData($id, $data=[])
    {
        $plan_id = 0;
        $model = self::findOne($id);
        if (!empty($model)) {
            if (isset($data['is_delete']) && !empty($data['is_delete'])) $model->is_delete = $data['is_delete'];
            if (isset($data['min_hospital_name']) && !empty($data['min_hospital_name'])) $model->min_hospital_name = $data['min_hospital_name'];
            if (isset($data['min_department_id']) && !empty($data['min_department_id'])) $model->min_department_id = $data['min_department_id'];
            $model->update_time = time();
            $model->save();
            $plan_id = $model->id;
        } else {
            return false;
        }
        return $plan_id;
    }

    //获取医院下所有包含某个医生的停诊计划时间段
    public static function getCloseSchedule($requestParams)
    {
        $where = [
            'is_delete' => 0,
            'min_hospital_id' => $requestParams['min_hospital_id'],
        ];
        $res = [];
        //获取
        $list = self::find()->where($where)->asArray()->all();
        if (empty($list)) return $res;

        foreach ($list as $val) {
            //stop_visit_type 1 全院 2 科室 3 医生
            if ($val['stop_visit_type'] == 1 ||
                ($val['stop_visit_type'] == 2 && $val['min_department_id'] == $requestParams['min_department_id']) ||
                ($val['stop_visit_type'] == 3 && $val['object_id'] == $requestParams['object_id'])) {
                $res[] = self::getCycleDetail($val);
            }
        }
        $scheduleDate = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                $scheduleDate = array_merge($scheduleDate, $val);
            }
        }
        $scheduleDate = array_unique($scheduleDate);
        return $scheduleDate;
    }

    //获取停诊计划的时间段明细
    public static function getCycleDetail($planInfo)
    {
        $scheduleDate = [];
        if (empty($planInfo)) return $scheduleDate;
        $cycleArr = json_decode($planInfo['visit_cycle'], true);
        if ($planInfo['section_cycle_type'] == 1) {//按日期
            foreach ($cycleArr as $key => $value) {
                foreach ($value as $val) {
                    $scheduleDate[] = $key . " " . $val;
                }
            }
        } else if ($planInfo['section_cycle_type'] == 2) {//按时间段
            list($stime, $etime) = explode("--", current($cycleArr));
            $dateArr = CommonFunc::periodDate($stime, $etime);
            $node = ["上午","下午"];
            foreach ($dateArr as $value) {
                foreach ($node as $val) {
                    $scheduleDate[] = $value . " " . $val;
                }
            }
        }
        return $scheduleDate;
    }

    //格式化停诊情况
    public static function getFormatCycle($planInfo)
    {
        $cycleArr = json_decode($planInfo['visit_cycle'], true);
        $cycle_desc = "";
        if ($planInfo['section_cycle_type'] == 1) {
            foreach ($cycleArr as $k => $v) {
                $cycle_desc .= ($k . ((count($v) > 1) ? "" : $v[0])) . "<br/>";
            }
        } else if ($planInfo['section_cycle_type'] == 2) {
            $cycle_desc .= $cycleArr[0];
        }
        return $cycle_desc;
    }
}
