<?php

/**
 * 民营排班计划
 * @author wanghongying<wanghongying@yuanyinjituan.com>
 * @date 2022-07-20
 * @version 1.0
 * @param   [type]     $queue [description]
 * @return  [type]            [description]
 */

namespace queues;

use common\models\minying\MinDepartmentModel;
use common\models\minying\MinDoctorModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\base\BaseObject;
use common\models\SchedulePlanModel;
use common\models\ScheduleClosePlanModel;
use common\models\GuahaoScheduleModel;
use common\models\DoctorModel;
use common\models\GuahaoOrderModel;
use common\libs\CommonFunc;
use common\libs\Log;
use common\models\BuildToEsModel;
use common\models\BaseDoctorHospitals;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoHospitalModel;

class SchedulePlanJob extends BaseObject implements \yii\queue\JobInterface
{
    /**
     * 业务id
     * @var
     */
    public $id;

    /**
     * 类型  1：添加出诊计划  2：添加停诊计划  3：删除出诊计划 4 删除停诊计划
     * @var
     */
    public $type;

    public $tp_platform = 13;
    public $nooncode = ['上午' => 1, '下午' => 2];
    //public $noon = [1 => '8:00-12:00', 2 => '14:00-18:00'];

    public function execute($queue)
    {
        if ($this->type == 1) {//添加出诊计划
            $this->visitPlan();
        }else if ($this->type == 2) {//添加停诊计划
            $this->closePlan();
        } else if ($this->type == 3) {//删除出诊计划
            $this->deletePlan();
        } else if ($this->type == 4) {//删除停诊计划
            $this->deleteClosePlan();
        }
    }

    /**
     * 出诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-21
     * 出诊时间段的明细  $scheduleDate  格式
     * [
        [0] => 2020-12-13 上午
        [1] => 2020-12-13 下午
        [2] => 2020-12-14 上午
        [3] => 2020-12-08 上午
        [4] => 2020-12-15 上午
        [5] => 2020-12-08 下午
        [6] => 2020-12-15 下午
        ]
     */
    public function visitPlan()
    {
        try {
            $info = SchedulePlanModel::getDetail($this->id);
            $tp_doctor_id = $info['min_doctor_id'];
            //获取民营医院科室
            $minDepartment = MinDepartmentModel::getDetail($info['min_department_id']);
            //获取是否为多点执业
            $minDocInfo = MinDoctorModel::find()->where(['min_doctor_id' => $tp_doctor_id])->asArray()->one();
            $payModel = [1 => 2, 2 => 1];

            $docWhere = [
                'tp_doctor_id' => $tp_doctor_id,
                'tp_platform' => $this->tp_platform
            ];
            $doc_info = DoctorModel::find()->where($docWhere)->asArray()->one();
            $doctor_id = isset($doc_info['doctor_id']) ? $doc_info['doctor_id'] : 0;

            //获取医生的所有停诊计划时间范围
            $info['object_id'] = $info['min_doctor_id'];
            $scheduleDateClose = ScheduleClosePlanModel::getCloseSchedule($info);

            //获取出诊时间段的明细
            $scheduleDate = SchedulePlanModel::getCycleDetail($info);
            //通过出诊时间段明细查询对应的排班， 如果存在已停诊排班，过滤，，如果不存在排班，新增一条出诊排班
            foreach ($scheduleDate as $visit) {
                $log_start_time = microtime(true);
                $requestType = "createSchedule";
                list($visit_time, $visit_nooncode_desc) = explode(" ", $visit);
                $visit_nooncode = $this->nooncode[$visit_nooncode_desc];
                /*$tp_section_id = $this->noon[$visit_nooncode];
                list($visit_starttime, $visit_endtime)  = explode("-", $tp_section_id);*/
                //生成排班数据
                $scheduleParam = [
                    'tp_platform'              => $this->tp_platform,
                    'doctor_id'                => $doctor_id,
                    'primary_id'               => $doc_info['primary_id'] > 0 ? $doc_info['primary_id'] : $doctor_id,
                    'tp_doctor_id'             => $tp_doctor_id,
                    'realname'                 => $doc_info['realname'],
                    'hospital_id'              => $doc_info['hospital_id'],
                    'frist_department_id'      => $doc_info['frist_department_id'],
                    'second_department_id'     => $doc_info['second_department_id'],
                    'scheduleplace_id'         => 0,
                    'scheduleplace_name'       => $info['min_hospital_name'],
                    'tp_scheduleplace_id'      => $info['min_hospital_id'],
                    'tp_frist_department_id'   => 0,
                    'tp_frist_department_name' => $minDepartment['min_minying_fkname'],
                    'tp_department_id'         => $info['min_department_id'],
                    'department_name'          => $minDepartment['min_minying_skname'],
                    'visit_time'               => $visit_time,
                    'visit_nooncode'           => $visit_nooncode,
                    'tp_section_id'            => '',
                    'visit_starttime'          => '',
                    'visit_endtime'            => '',
                    'schedule_available_count' => $info['schedule_count'],
                    'visit_type'               => $info['section_type'],
                    'visit_address'            => '',
                    'visit_cost'               => $info['visit_cost'],
                    'schedule_type'            => 1,//类型 1 挂号 2 加号
                    'first_practice'           => $minDocInfo['visit_type'] == 2 ? 0 : 1,
                    'pay_model'                => isset($payModel[$info['pay_type']]) ? $payModel[$info['pay_type']] : 1,
                    'status'                   => 1,
                ];
                //出诊时间段在停诊时间段范围内  新增停诊排班数据
                if (in_array($visit, $scheduleDateClose)) {
                    $scheduleParam['status'] = 2;
                    $requestType = "stopSchedule";
                }

                $scheduling_id = GuahaoScheduleModel::addSchedule($scheduleParam);
                $log_end_time = microtime(true);
                if ($scheduling_id) {
                    //更新排班缓存
                    $snisiyaSdk = new SnisiyaSdk();
                    $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
                }

                //记录日志
                $scheduleParam['log_code'] = 200;
                //记录请求时长
                $log_spend_time = round($log_end_time - $log_start_time, 2);
                $scheduleParam['log_spend_time'] = $log_spend_time;
                $this->addQueueLog($doctor_id, $requestType, ['scheduling_id' => $scheduling_id], $scheduleParam);
                echo "出诊计划ID:{$this->id}, 创建排班ID:{$scheduling_id} 成功！" . PHP_EOL;
            }
            /*//更新缓存
            $this->updateHospitalDepartmentCache($doctor_id);*/
            echo "出诊计划ID:{$this->id} 执行完成！" . PHP_EOL;
            return true;
        } catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * 停诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-21
     */
    public function closePlan()
    {
        try {
            $info = ScheduleClosePlanModel::getDetail($this->id);

            //获取停诊时间段的明细
            $scheduleDate = ScheduleClosePlanModel::getCycleDetail($info);
            $scheduleWhere = [//按全院
                'tp_platform' => $this->tp_platform,
                'status' => 1,
                'tp_scheduleplace_id' => $info['min_hospital_id']
            ];
            if ($info['stop_visit_type'] == 2) {//按科室
                $scheduleWhere['tp_department_id'] = $info['min_department_id'];
            }
            if ($info['stop_visit_type'] == 3) {//按医生
                $scheduleWhere['tp_department_id'] = $info['min_department_id'];
                $scheduleWhere['tp_doctor_id'] = $info['object_id'];
            }

            foreach ($scheduleDate as $visit) {
                list($visit_time, $visit_nooncode_desc) = explode(" ", $visit);
                $visit_nooncode = $this->nooncode[$visit_nooncode_desc];
                $scheduleWhere['visit_time'] = $visit_time;
                $scheduleWhere['visit_nooncode'] = $visit_nooncode;
                //获取排班ID
                $schedulings = GuahaoScheduleModel::getScheduleByTpDoctorId($scheduleWhere);
                if (!empty($schedulings)) {
                    foreach ($schedulings as $scheduling) {
                        $scheduling_id = $scheduling['scheduling_id'];
                        if ($scheduling_id > 0) {
                            $log_start_time = microtime(true);
                            //排班停诊
                            $res = GuahaoScheduleModel::updateAll(['status' => 2],['scheduling_id' => $scheduling_id]);
                            if ($res) {
                                //更新排班缓存
                                $snisiyaSdk = new SnisiyaSdk();
                                $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
                            }

                            //获取该排班下已预约成功的订单
                            $orderList = GuahaoOrderModel::find()
                                ->alias('ord')
                                ->select(['ord.id','ord.order_sn'])
                                ->join('LEFT JOIN', ['tpr' => 'tb_guahao_order_info'], 'ord.id=tpr.order_id')
                                ->where(['ord.tp_platform'=>$this->tp_platform])
                                ->andWhere(['ord.state'=> 0])
                                ->andWhere(['ord.tp_hospital_code'=> $info['min_hospital_id']])
                                ->andWhere(['ord.doctor_id'=> $scheduling['doctor_id']])
                                ->andWhere(['>=', 'ord.visit_time', date('Y-m-d', strtotime("+1 day"))])
                                ->andWhere(['tpr.scheduling_id'=> $scheduling_id])
                                ->asArray()
                                ->all();
                            if (!empty($orderList)) {
                                foreach ($orderList as $order) {
                                    $orderModel = GuahaoOrderModel::findOne($order['id']);
                                    $orderModel->state = 1;//预约记录状态(0:下单成功 1:取消 2:停诊 3:已完成 4:爽约 5:待支付，6：无效，7：下单中,8:待审核)
                                    $res = $orderModel->save();
                                    if ($res) {
                                        //发送停诊短信
                                        $stop_type = '停诊';
                                        $stop_desc = '您预约的号源停诊，给您带来的不便敬请理解。';
                                        CommonFunc::guahaoSendSms('guahao_stop', $order['order_sn'], $stop_type, $stop_desc);
                                        //发送取消短信
                                        if (!CommonFunc::minCancelOrderSendSms('cancel', $order['order_sn'])) {
                                            Log::sendGuaHaoErrorDingDingNotice("民营医院报警-删除排班取消订单【{$orderModel->order_sn}】通知用户失败\r\n错误信息：". CommonFunc::$orderSendSmsErrorMsg);
                                        }
                                    }
                                }
                            }

                            //记录日志
                            $scheduleWhere['log_code'] = 200;
                            //记录请求时长
                            $log_end_time = microtime(true);
                            $log_spend_time = round($log_end_time - $log_start_time, 2);
                            $scheduleWhere['log_spend_time'] = $log_spend_time;
                            $this->addQueueLog($scheduling['doctor_id'], "stopSchedule", ['scheduling_id' => $scheduling_id], $scheduleWhere);
                            echo "停诊计划ID:{$this->id}, 排班ID:{$scheduling_id} 停诊成功！" . PHP_EOL;
                        }
                    }
                }
            }
            echo "停诊计划ID:{$this->id} 执行完成！" . PHP_EOL;
            return true;
        } catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * 删除出诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function deletePlan()
    {
        try{
            $info = SchedulePlanModel::getDetail($this->id);
            $scheduleDate = SchedulePlanModel::getCycleDetail($info);

            foreach ($scheduleDate as $visit) {
                list($visit_time, $visit_nooncode_desc) = explode(" ", $visit);
                $visit_nooncode = $this->nooncode[$visit_nooncode_desc];
                $scheduleWhere = [
                    'tp_platform' => $this->tp_platform,
                    'tp_scheduleplace_id' => $info['min_hospital_id'],
                    'tp_doctor_id' => $info['min_doctor_id'],
                    'visit_time' => $visit_time,
                    'visit_nooncode' => $visit_nooncode,
                    'status' => [1,2]
                ];

                //获取排班ID
                $schedulings = GuahaoScheduleModel::getScheduleByTpDoctorId($scheduleWhere);
                if (!empty($schedulings)) {
                    foreach ($schedulings as $scheduling) {
                        $log_start_time = microtime(true);
                        $scheduling_id = $scheduling['scheduling_id'];
                        //删除排班
                        $res = GuahaoScheduleModel::updateAll(['status' => 4],['scheduling_id' => $scheduling_id]);
                        if ($res) {
                            //更新排班缓存
                            $snisiyaSdk = new SnisiyaSdk();
                            $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
                        }

                        //获取该排班下已预约成功的订单
                        $orderList = GuahaoOrderModel::find()
                            ->alias('ord')
                            ->select(['ord.id','ord.order_sn'])
                            ->join('LEFT JOIN', ['tpr' => 'tb_guahao_order_info'], 'ord.id=tpr.order_id')
                            ->where(['ord.tp_platform' => $this->tp_platform])
                            ->andWhere(['ord.state'=> 0])
                            ->andWhere(['ord.tp_hospital_code'=> $info['min_hospital_id']])
                            ->andWhere(['ord.doctor_id'=> $scheduling['doctor_id']])
                            ->andWhere(['tpr.scheduling_id'=> $scheduling_id])
                            ->asArray()
                            ->all();
                        if (!empty($orderList)) {
                            foreach ($orderList as $order) {
                                $orderModel = GuahaoOrderModel::findOne($order['id']);
                                $orderModel->state = 1;//预约记录状态(0:下单成功 1:取消 2:停诊 3:已完成 4:爽约 5:待支付，6：无效，7：下单中,8:待审核)
                                $res = $orderModel->save();
                                if ($res) {
                                    //发送取消短信
                                    if (!CommonFunc::minCancelOrderSendSms('cancel', $order['order_sn'])) {
                                        Log::sendGuaHaoErrorDingDingNotice("民营医院报警-删除排班取消订单【{$orderModel->order_sn}】通知用户失败\r\n错误信息：". CommonFunc::$orderSendSmsErrorMsg);
                                    }
                                }
                            }
                        }
                        //记录日志
                        $scheduleWhere['log_code'] = 200;
                        //记录请求时长
                        $log_end_time = microtime(true);
                        $log_spend_time = round($log_end_time - $log_start_time, 2);
                        $scheduleWhere['log_spend_time'] = $log_spend_time;
                        $this->addQueueLog($scheduling['doctor_id'], "cancelSchedule", ['scheduling_id' => $scheduling_id], $scheduleWhere);
                        echo "删除出诊计划ID:{$this->id}, 排班ID:{$scheduling_id} 取消成功！" . PHP_EOL;
                    }
                }
            }
            echo "删除出诊计划ID:{$this->id} 执行完成！" . PHP_EOL;

            /*$docWhere = [
                'tp_doctor_id' => $info['min_doctor_id'],
                'tp_platform' => $this->tp_platform
            ];
            $doc_info = DoctorModel::find()->where($docWhere)->asArray()->one();
            $doctor_id = isset($doc_info['doctor_id']) ? $doc_info['doctor_id'] : 0;
            $this->updateHospitalDepartmentCache($doctor_id);*/

            return true;
        } catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * 删除停诊计划
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function deleteClosePlan()
    {
        try{
            $info = ScheduleClosePlanModel::getDetail($this->id);
            $scheduleDate = ScheduleClosePlanModel::getCycleDetail($info);

            $scheduleWhere = [//按全院
                'tp_platform' => $this->tp_platform,
                'status' => 2,
                'tp_scheduleplace_id' => $info['min_hospital_id']
            ];
            if ($info['stop_visit_type'] == 2) {//按科室
                $scheduleWhere['tp_department_id'] = $info['min_department_id'];
            }
            if ($info['stop_visit_type'] == 3) {//按医生
                $scheduleWhere['tp_department_id'] = $info['min_department_id'];
                $scheduleWhere['tp_doctor_id'] = $info['object_id'];
            }

            foreach ($scheduleDate as $visit) {
                list($visit_time, $visit_nooncode_desc) = explode(" ", $visit);
                $visit_nooncode = $this->nooncode[$visit_nooncode_desc];
                $scheduleWhere['visit_time'] = $visit_time;
                $scheduleWhere['visit_nooncode'] = $visit_nooncode;
                //获取排班ID
                $schedulings = GuahaoScheduleModel::getScheduleByTpDoctorId($scheduleWhere);
                if (!empty($schedulings)) {
                    foreach ($schedulings as $scheduling) {
                        $scheduling_id = $scheduling['scheduling_id'];
                        if ($scheduling_id > 0) {
                            $log_start_time = microtime(true);
                            //停诊的排班改为出诊状态
                            $res = GuahaoScheduleModel::updateAll(['status' => 1],['scheduling_id' => $scheduling_id]);
                            if ($res) {
                                //更新排班缓存
                                $snisiyaSdk = new SnisiyaSdk();
                                $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
                            }
                            //记录日志
                            $scheduleWhere['log_code'] = 200;
                            //记录请求时长
                            $log_end_time = microtime(true);
                            $log_spend_time = round($log_end_time - $log_start_time, 2);
                            $scheduleWhere['log_spend_time'] = $log_spend_time;
                            $this->addQueueLog($scheduling['doctor_id'], "cancelSchedule", ['scheduling_id' => $scheduling_id], $scheduleWhere);
                            echo "删除停诊计划ID:{$this->id}, 排班ID:{$scheduling_id} 更改出诊成功！" . PHP_EOL;
                        }
                    }
                }
            }
            echo "删除停诊计划ID:{$this->id} 执行完成！" . PHP_EOL;
            return true;
        }catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
        }
    }

    private function addQueueLog($index, $request_type, $res, $cur_log)
    {
        Log::pushLogDataToQueues([
            'platform' => $this->tp_platform,
            'index'=> (string)$index,
            'request_type' => (string)$request_type,
            'res' => $res,
            'cur_log' => $cur_log,
        ], 'logqueue2');
    }

    //更新医院科室缓存
    private function updateHospitalDepartmentCache($doctor_id)
    {
        $doctorModel = DoctorModel::findOne($doctor_id);
        //更新医生es
        $es_model = new BuildToEsModel();
        $es_model->db2esByIdDoctor($doctor_id);

        //更新医院缓存
        $es_model->db2esByIdHospital($doctorModel->hospital_id);

        //医院详情缓存
        BaseDoctorHospitals::HospitalDetail($doctorModel->hospital_id,true);
        //医院科室缓存
        HospitalDepartmentRelation::hospitalDepartment($doctorModel->hospital_id,true);

        //更新第三方医院缓存
        GuahaoHospitalModel::getTpHospitalCache($doctorModel->tp_platform, $doctorModel->tp_hospital_code, true);

        return true;
    }
}
