<?php

namespace common\models\minying;

use common\libs\CommonFunc;
use common\libs\Log;
use common\models\DoctorModel;
use common\models\GuahaoHospitalModel;
use common\models\TbLog;
use Yii;

/**
 * This is the model class for table "tb_min_resource_deadline".
 * @property int $id  主键id
 * @property int $resource_type 医院账号/医生证件等资源类型 1:合作医院;2:医生证件
 * @property int $resource_minor_id 资源的补充id（如医生的多个证件）医生证件：1身份证;2医师执业证;3医师资格证;4专业技术资格证;多点执业证
 * @property int $resource_id 医院账号/医生等资源id
 * @property int $begin_time 开始时间
 * @property int $end_time 结束时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 * @property int $created_time 创建时间
 * @property int $update_time 更改时间
 */
class ResourceDeadlineModel extends \yii\db\ActiveRecord
{
    public $pagination;
    public $listData;

    public $min_hospital_name;
    public $min_doctor_name;
    public $agency_name;

    // 提醒阈值 60天
    const ALARM_THRESHOLD = 5184000;

    // 医院资源
    const RESOURCE_TYPE_HOSPITAL = 1;
    // 医生资源
    const RESOURCE_TYPE_DOCTOR = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_resource_deadline';
    }

    //保存审核记录
    public static function addResourceDeadline($params)
    {
        $resource_type = isset($params['resource_type']) ? $params['resource_type'] : 0;
        $resource_minor_id  = isset($params['resource_minor_id']) ? $params['resource_minor_id'] : 0;
        $resource_id  = isset($params['resource_id']) ? $params['resource_id'] : 0;

        //先验证是否存在
        $where = [
            'resource_id' => $resource_id,
            'resource_type' => $resource_type,
            'resource_minor_id' => $resource_minor_id
        ];
        $resourceDeadline = self::find()->where($where)->asArray()->one();
        if (!empty($resourceDeadline)) {
            $model = self::findOne($resourceDeadline['deadline_id']);
        } else {
            $model = new self();
            $model->created_time  = time();
        }

        $model->resource_type  = $resource_type;
        $model->resource_minor_id  = $resource_minor_id;
        $model->resource_id  = $resource_id;
        $model->begin_time  = isset($params['begin_time']) ? $params['begin_time'] : 0;
        $model->end_time  = isset($params['end_time']) ? $params['end_time'] : 0;
        $model->admin_id  = isset($params['admin_id']) ? $params['admin_id'] : 0;
        $model->admin_name  = isset($params['admin_name']) ? $params['admin_name'] : '';
        $model->update_time  = time();
        $res = $model->save();
        if ($res) {
            return $model->attributes['deadline_id'];
        } else {
            return false;
        }
    }

    /**
     * 1身份证;2医师执业证;3医师资格证;4专业技术资格证;多点执业证
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-22
     * @return array
     */
    public static function certTypeMaps()
    {
        return [
            1 => '身份证',
            2 => '医师执业证',
            3 => '医师资格证',
            4 => '专业技术资格证',
            5 => '多点执业证'
        ];
    }

    /**
     * 到期禁用医院
     * @param self $model
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-12
     */
    public static function freezeHospital(self $model)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($m_hospital = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $model->resource_id, 'tp_platform' => 13, 'status' => [0, 1]])->one()) {
                $m_hospital->status = 2;
                $m_hospital->save(false);
                // 删除医院排班
                CommonFunc::deleteScheduleJob($m_hospital->tp_platform, $m_hospital->tp_hospital_code, 'system', 0);
                //记录操作日志
                TbLog::addLog("系统脚本禁用了医院id:{$model->resource_id}", '民营医院禁用', ['admin_id' => 0, 'admin_name' => 'system']);
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            Log::sendExceptionMessage($exception);
            echo "[" . date('Y-m-d H:i:s') . "] 处理resource_type: {$model->resource_type}，resource_id: {$model->resource_id} 错误！error: " . $exception->getMessage() . PHP_EOL;
            $transaction->rollBack();
        }
    }

    /**
     * 到期禁用医生
     * @param self $model
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-12
     */
    public static function freezeDoctor(self $model)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($m_doctor = DoctorModel::find()->where(['tp_doctor_id' => $model->resource_id, 'tp_platform' => 13, 'status' => 1])->one()) {
                $m_doctor->status = 0;
                $m_doctor->save(false);
                // 修改民营医院医生表中的证书状态
                $min_m_doctor = MinDoctorModel::findOne(['min_doctor_id' => $model->resource_id]);
                $min_m_doctor->cert_status = MinDoctorModel::CERT_STATUS_EXPIRED;
                $min_m_doctor->save(false);
                // 记录日志
                TbLog::addLog("系统脚本禁用了医生id:{$model->resource_id}", '民营医院医生禁用', ['admin_id' => 0, 'admin_name' => 'system']);
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            Log::sendExceptionMessage($exception);
            echo "[" . date('Y-m-d H:i:s') . "] 处理resource_type: {$model->resource_type}，resource_id: {$model->resource_id} 错误！error: " . $exception->getMessage() . PHP_EOL;
            $transaction->rollBack();
        }
    }
}
