<?php
/**
 * 迁移排班历史数据
 * @filename: GuahaoScheduleMigrationController.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @date 2021/3/19
 * @version v1.0.0
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\GuahaoScheduleModel;
use common\models\GuahaoScheduleHistoryModel;

class GuahaoScheduleMigrationController extends Controller
{
    public function init()
    {
        date_default_timezone_set('PRC');
    }

    /**
     * @param string $date
     * @throws \yii\db\Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/19
     */
    public function actionBegin($date = '')
    {
        if (!empty($date)) {
            $dateDay = date('Y-m', strtotime($date));
        } else {
            $dateDay = date('Y-m', strtotime(" -1 day"));
        }
        $checkDay = date('Y-m');

        if ($dateDay > $checkDay) {
            echo date("只可迁移以前月份的数据\n");
            exit();
        }

        $start_time = microtime(true);
        echo "[" . date('Y-m-d H:i:s') . "] {$dateDay}开始处理数据：\n";
        //创建表
        $this->actionCreateTable($date);

        //迁移数据
        $startDate = date('Y-m-01', strtotime($dateDay));
        if ($dateDay == $checkDay) {
            $endDate = date('Y-m-d');
        } else {
            $endDate = date('Y-m-01', strtotime($dateDay . " +1 month"));
        }

        $query = GuahaoScheduleModel::find()->where(['>=', 'visit_time', $startDate])->andWhere(['<', 'visit_time', $endDate]);
        $num = 0;//计数 单次最多处理10000页
        $pageSize = 1000;
        $execute_num = 0;
        $error_num = 0;

        do {
            $error_arr = [];
            $list = $query->select('scheduling_id')->limit($pageSize)->asArray()->all();
            if (!empty($list)) {
                foreach ($list as $scheduling_id) {
                    $transition = Yii::$app->db->beginTransaction();
                    try {
                        $scheduling = GuahaoScheduleModel::findOne($scheduling_id['scheduling_id']);
                        $scheduling_arr = $scheduling->attributes;

                        $historyModel = new GuahaoScheduleHistoryModel();
                        if (!$historyModel->checkTable($dateDay)) {
                            throw new \Exception('表不存在');
                        }
                        $historyModel->resetTable($dateDay);
                        $historyModel->setAttributes($scheduling_arr);
                        $historyModel->scheduling_id = $scheduling_id['scheduling_id'];

                        if (!$historyModel->validate() || !$historyModel->save()) {
                            throw new \Exception(json_encode($historyModel->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                        $scheduling->delete();

                        $transition->commit();
                    } catch (\Exception $e) {
                        $transition->rollBack();
                        $error_num++;
                        $error_arr[] = ['scheduling_id' => $scheduling_id['scheduling_id'], 'msg' => $e->getMessage(), 'data' => $scheduling_arr ?? []];

                        //如果数据已写入历史表则删除旧数据
                        $checkHistoryModel = GuahaoScheduleHistoryModel::findOne($scheduling_id['scheduling_id']);
                        if (!empty($checkHistoryModel)) {
                            $checkHistoryModel->delete();
                        }
                    }
                    unset($scheduling);
                    unset($scheduling_arr);
                    unset($historyModel);
                }
            } else {
                break;
            }
            $dataCount = count($list);
            $execute_num += $dataCount;
            $num++;
            if (!empty($error_arr)) {
                echo "失败信息：\n";
                print_r($error_arr);
                unset($error_arr);
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第{$num}页 处理完成！\n";
            unset($list);
        } while ($dataCount >= $pageSize && $num < 10000);

        $end_time = microtime(true);
        $spend_time = round(($end_time - $start_time) / 60, 2) . '分钟';
        echo "[" . date('Y-m-d H:i:s') . "] 耗时：{$spend_time} 处理完成！\n";
        echo "处理数量：$execute_num\n";
        echo "失败数量：$error_num\n";
    }

    /**
     * 创建表
     * @param string $date
     * @throws \yii\db\Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/3/19
     */
    public function actionCreateTable($date = '')
    {
        if (!empty($date)) {
            $dateDay = date('Ym', strtotime($date));
        } else {
            $dateDay = date('Ym', strtotime(" -1 day"));
        }
        $tableInfo = " (
  `scheduling_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '排班ID',
  `tp_scheduling_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方排班ID',
  `tp_section_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方时段ID',
  `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)',
  `primary_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '医生主ID',
  `doctor_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院医生ID',
  `tp_doctor_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方医生ID',
  `hospital_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院ID',
  `frist_department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院一级科室ID',
  `second_department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院二级科室ID',
  `realname` varchar(30) NOT NULL DEFAULT '' COMMENT '医生姓名',
  `scheduleplace_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '出诊地ID',
  `tp_scheduleplace_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方出诊地ID',
  `scheduleplace_name` varchar(100) NOT NULL DEFAULT '' COMMENT '出诊医院(工作室) ',
  `tp_frist_department_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方一级科室ID',
  `tp_frist_department_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方一级科室名称',
  `tp_department_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方科室ID',
  `department_name` varchar(100) NOT NULL DEFAULT '' COMMENT '出诊科室',
  `visit_time` date NOT NULL COMMENT '就诊日期(年月日)',
  `visit_nooncode` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '午别 1:上午 2：下午 3:晚上',
  `visit_starttime` varchar(20) NOT NULL DEFAULT '' COMMENT '就诊开始时间',
  `visit_endtime` varchar(20) NOT NULL DEFAULT '' COMMENT '就诊结束时间',
  `visit_valid_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可预约截止时间戳',
  `visit_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '号源类型：1普通，2专家，3专科，4特需，5夜间，6会诊，7老院，8其他',
  `visit_address` varchar(200) NOT NULL DEFAULT '' COMMENT '就诊地址',
  `visit_cost` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '挂号费,分单位制',
  `referral_visit_cost` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复诊挂号费,分单位制',
  `visit_cost_original` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '挂号费原价,分单位制',
  `referral_visit_cost_original` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复诊挂号费原价,分单位制',
  `schedule_available_count` int(10) NOT NULL DEFAULT '-1' COMMENT '剩余号源数量，-1表示无限制',
  `schedule_type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '排班类型:1:挂号,2:加号',
  `pay_mode` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '支付方式(1在线支付，2线下支付，3无需支付)',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '出诊状态(-1:已取消 0约满 1可约 2停诊 3已过期 4其他)',
  `first_practice` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是第一执业0否1是',
  `extended` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展字段，兼容不同来源号源',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作人id',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名',
  PRIMARY KEY (`scheduling_id`) USING BTREE,
  KEY `doctor_id` (`doctor_id`),
  KEY `scheduleplace_id` (`scheduleplace_id`),
  KEY `tp_department_id` (`tp_department_id`),
  KEY `visit_time` (`visit_time`),
  KEY `tp_scheduling_id` (`tp_scheduling_id`),
  KEY `tp_section_id` (`tp_section_id`),
  KEY `idx_hospital_id_frist_id_second_id_status` (`hospital_id`,`frist_department_id`,`second_department_id`,`status`) USING BTREE,
  KEY `idx_tpdoctorid_tpplatform_visittime` (`tp_doctor_id`,`tp_platform`,`visit_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='医生挂号排班历史数据表';";

        $connection = \Yii::$app->log_branddoctor_db;
        // 获取数据库中所有数据表名
        $tables = $connection->createCommand('show tables')->queryColumn();
        // 组装数据表名
        $tableName = 'history_tb_guahao_schedule_' . $dateDay;
        if (!in_array($tableName, $tables)) {
            $transaction = $connection->beginTransaction();
            try {
                $tableSql = "CREATE TABLE `$tableName`" . $tableInfo;
                \Yii::$app->log_branddoctor_db->createCommand($tableSql)->execute();
                $transaction->commit();
                echo date('Y-m-d H:i:s', time()) . '成功创建数据表,表名为:' . $tableName . "\n";
            } catch (\Exception $e) {
                print_r($e);
                $transaction->rollBack();
                exit;
            }
        }
    }
}