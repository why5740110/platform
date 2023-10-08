<?php
/**
 * 创建hospital log日志表
 * @filename: CreateTableController.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @date 2021/1/6
 * @version v1.0.0
 */

namespace console\controllers;

use yii\console\Controller;

class CreateTableController extends Controller
{
    public function init()
    {
        date_default_timezone_set('PRC');
    }

    public function actionCreate()
    {
        $dateDays = [];
        for ($i = 0; $i < 6; $i++) {
            $dateDays[] = date('Ymd', strtotime("+ $i day"));
        }
        $tableInfo = " (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '当天日志序列号',
  `platform` int(4) unsigned NOT NULL DEFAULT '0' COMMENT '平台类型',
  `request_type` varchar(100) NOT NULL DEFAULT '' COMMENT '请求接口类型',
  `index` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '索引，根据接口不同定义',
  `log_detail` mediumtext NOT NULL COMMENT '日志详情',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '接口请求记录时间',
  `spend_time` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '请求时长',
  `code` int(10) NOT NULL DEFAULT '0' COMMENT '状态码200成功；400失败；500接口请求失败',
  PRIMARY KEY (`id`),
  KEY `platform` (`platform`),
  KEY `request_type` (`request_type`),
  KEY `index` (`index`),
  KEY `spend_time` (`spend_time`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Hospital接口日志';";

        $connection = \Yii::$app->log_branddoctor_db;
        // 获取数据库中所有数据表名
        $tables = $connection->createCommand('show tables')->queryColumn();
        // 组装数据表名
        foreach ($dateDays as $dateDay) {
            $tableName = 'log_hospital_' . $dateDay;
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
                }
            }
        }
    }

}
