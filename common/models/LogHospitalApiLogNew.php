<?php

namespace common\models;

use Yii;

date_default_timezone_set('PRC');

/**
 * This is the model class for table "log_hospital_20210101".
 *log_hospital_
 * @property int $id 当天日志序列号
 * @property int $platform (平台,1:河南 2:南京 3:好大夫 101:第三方请求hospital)
 * @property string $request_type 请求接口类型
 * @property string $index 索引，根据接口不同定义
 * @property string $log_detail 日志详情
 * @property int $create_time 接口请求记录时间
 * @property float $spend_time
 * @property int $code
 */
class LogHospitalApiLogNew extends \yii\db\ActiveRecord
{
    private static $tableName = null; // 需要查询的数据表

    public function resetTable(string $dateStr)
    {
        self::$tableName = 'log_hospital_' . date('Ymd', strtotime($dateStr));
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
        $tableName = 'log_hospital_' . date('Ymd', strtotime($dateStr));
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
            $dateSrt = date('Ymd', time());
            return 'log_hospital_' . $dateSrt;
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
            [['platform', 'create_time'], 'integer'],
            [['log_detail'], 'required'],
            [['log_detail'], 'string'],
            [['request_type', 'index'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform' => 'Platform',
            'request_type' => 'Request Type',
            'index' => 'Index',
            'log_detail' => 'Log Detail',
            'create_time' => 'Create Time',
            'spend_time' => 'Spend Time',
            'code' => 'Code',
        ];
    }

}
