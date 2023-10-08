<?php
namespace console\controllers;

use common\models\TbDoctorThirdPartyRelationModel;
use Yii;

class SchedulePlaceRelationController extends CommonController
{
    /**
     * 移动tb_guahao_scheduleplace部分字段到tb_guahao_scheduleplace_relation
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-12
     * @version 1.0
     * @param   integer    $form [description]
     * @return  [type]           [description]
     */
    // public function actionMove()
    // {
    //     $page  = 1;
    //     $limit = 500;
    //     $where = [
    //     ];
    //     $query = GuahaoScheduleplace::find()->where($where);
    //     do {
    //         $offset = max(0, ($page - 1)) * $limit;
    //         $list   = $query->offset($offset)->limit($limit)->asArray()->all();
    //         if (!$list) {
    //             echo ('结束：' . date('Y-m-d H:i:s', time())) . '没有了！' . PHP_EOL;
    //             break;
    //         }
    //         foreach ($list as $key => $doc) {
    //             $relation_where = [
    //                 'scheduleplace_id' => $doc['scheduleplace_id'],
    //             ];
    //             $doctor_id = $doc['doctor_id'];
    //             $RelaModel = GuahaoScheduleplaceRelation::find()->where($relation_where)->one();
    //             if (!$RelaModel) {
    //                 echo ("医生id：{$doctor_id} " . date('Y-m-d H:i:s', time())) . '不存在！' . PHP_EOL;
    //                 continue;
    //             }
    //             ##start 新增字段by yagnquanliang 2020-01-12
    //             $RelaModel->doctor_id = $doc['doctor_id'];
    //             $RelaModel->realname  = $doc['realname'];
    //             ##end 新增字段by yagnquanliang 2020-01-12
    //             $res = $RelaModel->save();
    //             if ($res) {
    //                 echo ("医生id：{$doctor_id} " . date('Y-m-d H:i:s', time())) . '更新成功！' . PHP_EOL;
    //             }
    //         }
    //         $num = count($list);
    //         unset($list);
    //         $page++;
    //     } while ($num > 0);

    //     echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    // }

    /**
     * 获取王氏加号医生出诊机构 php yii schedule-place-relation/get-place 医院医生id,第三方医生id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-24
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionGetPlace($doctor_id = 0, $tp_doctor_id = 0)
    {
        TbDoctorThirdPartyRelationModel::pullVisitPlace($doctor_id, $tp_doctor_id);
    }

    public function actionUpPlace()
    {
        $postData = [
            'tp_hospital_code'            => '129',
            'hospital_name'               => '北京市体检中心',
            'tp_department_id'            => '43',
            'department_name'             => '普外科',
            'tp_first_department_id'      => '2',
            'first_department_name'       => '外科',            
            'tp_frist_department_id'      => '2',
            'frist_department_name'       => '外科',
            'tp_doctor_id'                => '4297248',
            'realname'                    => '田帅',
            'source_avatar'               => 'http://test.u.nisiyacdn.com/avatar/004/29/72/004297248_mid.jpg',
            'good_at'                     => '擅长什么工作擅长什么工作擅长什么工作擅长什么工作擅长什么工作擅长什么工作',
            'profile'                     => '擅长什么工作擅长什么工作擅长什么工作擅长什么工作',
            'job_title'                   => '主任医师',
            'scheduleplace_hospital_id'   => '3',
            'scheduleplace_hospital_name' => '北京天坛医院',
            'scheduleplace_status'        => '0',
            'status'                      => '0',
            'visit_status'                => '1',
        ];
        TbDoctorThirdPartyRelationModel::uPVisitPlace($postData);
    }

}
