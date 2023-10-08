<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\DoctorModel;
use common\models\GuahaoScheduleModel;
use common\models\GuahaoScheduleplaceRelation;
use common\models\HospitalDepartmentRelation;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class ScheduleController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size            = 10;
    public $allow_week           = 0;

    public function actionIndex()
    {
        $requestParams                       = Yii::$app->request->getQueryParams();
        $requestParams['page']               = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit']              = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['doctor_id']          = isset($requestParams['doctor_id']) ? trim($requestParams['doctor_id']) : '';
        $requestParams['scheduleplace_name'] = isset($requestParams['scheduleplace_name']) ? trim($requestParams['scheduleplace_name']) : '';

        // //拼装条件
        $field                  = ['a.*', 'b.job_title', 'b.hospital_id', 'c.name hospital_name'];
        $where                  = [];
        $where['a.tp_platform'] = 4;
        //$hospital_table         = 'data_base' . '.' . BaseDoctorHospitals::tableName();  暂停使用
        $query                  = GuahaoScheduleplaceRelation::find()->alias('a')->select($field)->leftJoin(DoctorModel::tableName() . ' b', '`a`.`doctor_id` = `b`.`doctor_id`')->leftJoin($hospital_table . ' c', '`b`.`hospital_id` = `c`.`id`')->where($where);

        if (!empty(trim($requestParams['doctor_id']))) {
            if (is_numeric(trim($requestParams['doctor_id']))) {
                $query->andWhere(['a.doctor_id' => trim($requestParams['doctor_id'])]);
            } else {
                $query->andWhere(['like', 'a.realname', trim($requestParams['doctor_id'])]);
            }
        }

        if (!empty(trim($requestParams['scheduleplace_name']))) {
            $query->andWhere(['like', 'a.scheduleplace_name', trim($requestParams['scheduleplace_name'])]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $requestParams['limit'],
            ],
            'sort'       => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $data = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        return $this->render('index', $data);
    }

    /**
     * 设置排班页面
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSetting()
    {
        $request   = \Yii::$app->request;
        $id        = $request->get('id', '');
        $page      = (int) $request->get('page', 0);
        $daytime   = strtotime(date("Y-m-d", strtotime("+$page week", time())));
        $week_list = CommonFunc::get_week($daytime);
        $data      = [
            'week_list' => $week_list,
            'page'      => $page,
            'id'        => $id,
        ];
        $visits        = array_column($week_list, 'date');
        $placeRelation = GuahaoScheduleplaceRelation::find()->where(['id' => $id])->asArray()->one();
        if (!$placeRelation) {
            return $this->_showMessage('内容不存在！', Yii::$app->urlManager->createUrl('/schedule/index'));
        }
        $data['hospital_id'] = $placeRelation['tp_scheduleplace_id'];
        $data['fkeshi_list'] = HospitalDepartmentRelation::hospitalDepartment($placeRelation['tp_scheduleplace_id']);
        $data['skeshi_list'] = [];

        $scheduleWhere = [
            // 'status'           => 1,
            'tp_platform'      => 4,
            'doctor_id'        => $placeRelation['doctor_id'],
            'scheduleplace_id' => $placeRelation['scheduleplace_id'],
        ];
        $group_list = GuahaoScheduleModel::find()->select('scheduling_id,doctor_id,realname,hospital_id,tp_frist_department_id,tp_frist_department_name,tp_department_id,department_name,visit_cost,visit_time,visit_nooncode,status,count(1) vnum')->where($scheduleWhere)->andWhere(['in', 'visit_time', $visits])->andWhere(['>=', 'status', 0])->indexBy('visit_time')->groupBy('visit_time')->orderBy('visit_time asc')->asArray()->all();

        $gaohao_list = [];
        foreach ($group_list as $key => $value) {
            $guahao_item = [];
            $guahao_item = $value;
            if ($value['vnum'] > 1) {
                $guahao_item['noon_list'] = [1 => 1, 2 => 2];
            } else {
                $guahao_item['noon_list'][$value['visit_nooncode']] = $value['visit_nooncode'];
            }
            $gaohao_list[$key] = $guahao_item;
        }

        $data['gaohao_list']            = $gaohao_list ?? [];
        $lastinfo                       = current($gaohao_list);
        $data['tp_frist_department_id'] = $lastinfo['tp_frist_department_id'] ?? '';
        $data['tp_department_id']       = $lastinfo['tp_department_id'] ?? '';
        if ($data['tp_department_id']) {
            $data['skeshi_list'] = HospitalDepartmentRelation::hospitalDepartment($data['hospital_id'])[$data['tp_frist_department_id']]['second_arr'] ?? [];
        }
        $data['department_name'] = $lastinfo['department_name'] ?? '';
        $data['visit_cost']      = isset($lastinfo['visit_cost']) && !empty($lastinfo['visit_cost']) ? ceil($lastinfo['visit_cost'] * 100 / 100) / 100 : '';
        //$hos_info                = BaseDoctorHospitals::find()->where(['id' => $data['hospital_id']])->asArray()->one();
        $hos_info = BaseDoctorHospitals::getHospitalDetail($data['hospital_id']);
        $data['hospital_name']   = $hos_info['name'] ?? '';
        $data['place_info']      = $placeRelation ?? [];
        $noon_list = array_column($data['gaohao_list'], 'noon_list');
        $node_count = array_map(function ($item){
            return count($item);
        },$noon_list);
        $node_count = array_sum($node_count);
        $data['node_count'] = $node_count ?? 0;
        return $this->render('setting', $data);
        // return $this->renderPartial('setting', $data);
    }

    public function _Filter($value = [])
    {
        if ((isset($value['scheduling_id']) && $value['scheduling_id'] == 0) && !isset($value['visit_nooncode'])) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * 保存设置的排班信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSave()
    {
        return $this->returnJson(0, '此功能已下线！');
        $request     = \Yii::$app->request;
        $requestPost = $request->post();

        $id   = (int) $requestPost['id'] ?? 0;
        $page = (int) $requestPost['page'] ?? 0;
        if ($page < $this->allow_week) {
            return $this->returnJson(0, '只能设置' . $this->allow_week . '周后的排班！');
        }
        $nodes    = $requestPost['nodes'] ?? [];
        $sub_type = (int) ArrayHelper::getValue($requestPost, 'sub_type', 1);
        if ($sub_type == 1) {
            // $nodes = array_filter($nodes, array($this, "_Filter"));
            if (!$nodes) {
                return $this->returnJson(0, '您还没有设置排班呢！');
            }
        }
        $visit_cost = trim($requestPost['visit_cost']) ?? 0;
        if ($visit_cost <= 0) {
            return $this->returnJson(0, '价格设置错误！');
        }
        $excost = explode('.', $visit_cost);
        if (isset($excost[1]) && strlen($excost[1]) > 2) {
            return $this->returnJson(0, '价格设置错误！,最多支持两位小数！');
        }
        $placeRelation = GuahaoScheduleplaceRelation::find()->where(['id' => $id])->asArray()->one();
        if (!$placeRelation) {
            return $this->returnJson(0, '关联医生不存在！');
        }
        $doc_info       = DoctorModel::find()->where(['doctor_id' => $placeRelation['doctor_id']])->asArray()->one();
        $first_practice = $doc_info['hospital_id'] == $placeRelation['tp_scheduleplace_id'] ? 1 : 0;

        if ($nodes) {
            $data = [
                'sub_type'                 => $sub_type,
                'nodes'                    => $nodes,
                'visit_cost'               => $visit_cost,
                'doctor_id'                => ArrayHelper::getValue($placeRelation, 'doctor_id', 0),
                'realname'                 => ArrayHelper::getValue($placeRelation, 'realname', ''),
                'tp_doctor_id'             => ArrayHelper::getValue($placeRelation, 'doctor_id', ''),
                'tp_platform'              => ArrayHelper::getValue($placeRelation, 'tp_platform', 4),
                'hospital_id'              => ArrayHelper::getValue($doc_info, 'hospital_id', 0),
                'scheduleplace_id'         => ArrayHelper::getValue($placeRelation, 'scheduleplace_id', 0),
                'scheduleplace_name'       => ArrayHelper::getValue($placeRelation, 'scheduleplace_name', ''),
                'tp_scheduleplace_id'      => ArrayHelper::getValue($placeRelation, 'tp_scheduleplace_id', ''),
                'first_practice'           => $first_practice,
                'frist_department_id'      => ArrayHelper::getValue($doc_info, 'frist_department_id', 0),
                'second_department_id'     => ArrayHelper::getValue($doc_info, 'second_department_id', 0),
                'frist_department_name'    => ArrayHelper::getValue($doc_info, 'frist_department_name', ''),
                'second_department_name'   => ArrayHelper::getValue($doc_info, 'second_department_name', ''),
                'tp_frist_department_id'   => ArrayHelper::getValue($requestPost, 'tp_frist_department_id', ''),
                'tp_frist_department_name' => ArrayHelper::getValue($requestPost, 'tp_frist_department_name', ''),
                'tp_department_id'         => ArrayHelper::getValue($requestPost, 'tp_department_id', ''),
                'department_name'          => ArrayHelper::getValue($requestPost, 'department_name', ''),
                'admin_id'                 => $this->userInfo['id'],
                'admin_name'               => $this->userInfo['realname'],
            ];
            $res = GuahaoScheduleModel::addMultipleData($data);
            if ($res['code'] == 0) {
                return $this->returnJson(1, '操作成功！');
            } else {
                return $this->returnJson(0, $res['msg'] ?? '操作失败！');
            }
        }
        return $this->returnJson(1, '操作成功！');

    }

}
