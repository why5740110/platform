<?php

namespace common\models;

use Yii;

date_default_timezone_set('PRC');

/**
 * This is the model class for table "tb_guahao_schedule".
 *
 * @property int $scheduling_id 排班ID
 * @property string $tp_scheduling_id 第三方排班ID
 * @property string $tp_section_id 第三方时段ID
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)
 * @property int $primary_id 王氏医院医生主ID
 * @property int $doctor_id 王氏医院医生ID
 * @property string $tp_doctor_id 第三方医生ID
 * @property int $hospital_id 王氏医院ID
 * @property int $frist_department_id 王氏医院一级科室ID
 * @property int $second_department_id 王氏医院二级科室ID
 * @property string $realname 医生姓名
 * @property int $scheduleplace_id 出诊地ID
 * @property string $tp_scheduleplace_id 第三方出诊地ID
 * @property string $scheduleplace_name 出诊医院(工作室)
 * @property string $tp_frist_department_id 第三方一级科室ID
 * @property string $tp_frist_department_name 第三方一级科室名称
 * @property string $tp_department_id 第三方科室ID
 * @property string $department_name 出诊科室
 * @property string $visit_time 就诊日期(年月日)
 * @property int $visit_nooncode 午别 1:上午 2：下午 3:晚上
 * @property string $visit_starttime 就诊开始时间
 * @property string $visit_endtime 就诊结束时间
 * @property int $visit_valid_time 可预约截止时间戳
 * @property int $visit_type 号源类型：1普通，2专家，3专科，4特需，5夜间，6会诊，7老院，8其他
 * @property string $visit_address 就诊地址
 * @property int $visit_cost 挂号费,分单位制
 * @property int $referral_visit_cost 复诊挂号费,分单位制
 * @property int $visit_cost_original 挂号费原价,分单位制
 * @property int $referral_visit_cost_original 复诊挂号费原价,分单位制
 * @property int $schedule_available_count 剩余号源数量，-1表示无限制
 * @property int $schedule_type 排班类型:1:挂号,2:加号
 * @property int $pay_mode 支付方式(1在线支付，2线下支付，3无需支付)
 * @property int $status 出诊状态(-1:已取消 0约满 1可约 2停诊 3已过期 4其他)
 * @property int $first_practice 是否是第一执业0否1是
 * @property string $extended 扩展字段，兼容不同来源号源
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class GuahaoScheduleHistoryModel extends \yii\db\ActiveRecord
{
    private static $tableName = null; // 需要查询的数据表

    public function resetTable(string $dateStr)
    {
        self::$tableName = 'history_tb_guahao_schedule_' . date('Ym', strtotime($dateStr));
    }

    /**
     * 判断表是否存在
     * @param string $dateStr
     * @return bool
     * @throws \yii\db\Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/4/15
     */
    public function checkTable(string $dateStr)
    {
        $tableName = 'history_tb_guahao_schedule_' . date('Ym', strtotime($dateStr));
        $tables = self::getDb()->createCommand("SHOW TABLES LIKE '$tableName'")->queryAll();
        if (is_array($tables) && count($tables) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        if (empty(self::$tableName)) {
            $dateSrt = date('Ym', time());
            return 'history_tb_guahao_schedule_' . $dateSrt;
        } else {
            return self::$tableName;
        }
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('log_branddoctor_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_platform', 'primary_id', 'doctor_id', 'hospital_id', 'frist_department_id', 'second_department_id', 'scheduleplace_id', 'visit_nooncode', 'visit_valid_time', 'visit_type', 'visit_cost', 'referral_visit_cost', 'visit_cost_original', 'referral_visit_cost_original', 'schedule_available_count', 'schedule_type', 'pay_mode', 'status', 'first_practice', 'create_time', 'update_time', 'admin_id'], 'integer'],
            [['visit_time'], 'required'],
            [['visit_time'], 'safe'],
            [['tp_doctor_id', 'tp_scheduling_id', 'tp_section_id', 'scheduleplace_name', 'department_name'], 'string', 'max' => 100],
            [['tp_scheduleplace_id', 'tp_frist_department_id', 'tp_department_id'], 'string', 'max' => 32],
            [['realname'], 'string', 'max' => 30],
            [['tp_frist_department_name', 'admin_name'], 'string', 'max' => 50],
            [['visit_starttime', 'visit_endtime'], 'string', 'max' => 20],
            [['visit_address', 'extended'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scheduling_id' => 'Scheduling ID',
            'tp_scheduling_id' => 'Tp Scheduling ID',
            'tp_section_id' => 'Tp Section ID',
            'tp_platform' => 'Tp Platform',
            'primary_id' => 'Primary ID',
            'doctor_id' => 'Doctor ID',
            'tp_doctor_id' => 'Tp Doctor ID',
            'hospital_id' => 'Hospital ID',
            'frist_department_id' => 'Frist Department ID',
            'second_department_id' => 'Second Department ID',
            'realname' => 'Realname',
            'scheduleplace_id' => 'Scheduleplace ID',
            'tp_scheduleplace_id' => 'Tp Scheduleplace ID',
            'scheduleplace_name' => 'Scheduleplace Name',
            'tp_frist_department_id' => 'Tp Frist Department ID',
            'tp_frist_department_name' => 'Tp Frist Department Name',
            'tp_department_id' => 'Tp Department ID',
            'department_name' => 'Department Name',
            'visit_time' => 'Visit Time',
            'visit_nooncode' => 'Visit Nooncode',
            'visit_starttime' => 'Visit Starttime',
            'visit_endtime' => 'Visit Endtime',
            'visit_valid_time' => 'Visit Valid Time',
            'visit_type' => 'Visit Type',
            'visit_address' => 'Visit Address',
            'visit_cost' => 'Visit Cost',
            'referral_visit_cost' => 'Referral Visit Cost',
            'visit_cost_original' => 'Visit Cost Original',
            'referral_visit_cost_original' => 'Referral Visit Cost Original',
            'schedule_available_count' => 'Schedule Available Count',
            'schedule_type' => 'Schedule Type',
            'pay_mode' => 'Pay Mode',
            'status' => 'Status',
            'first_practice' => 'First Practice',
            'extended' => 'extended',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
        ];
    }
}
