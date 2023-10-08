<?php
/**
 * 探测数据库、es、redis连接是否正常
 * @filename: DetectController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @date 2022/11/14
 * @version v1.0.0
 */

namespace api\controllers;

use yii\web\Controller;
use common\libs\Log;
use common\models\DoctorEsModel;
use yii\helpers\ArrayHelper;

class DetectController extends Controller
{
    public $enableCsrfValidation = false;
    public function init()
    {
        date_default_timezone_set('PRC');
    }

    /**
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-11-14
     */
    public function actionDetect()
    {
        $errArr = [];
        if ($err = $this->checkEs()) $errArr[] = $err;
        if ($err = $this->checkMysql()) $errArr[] = $err;
        if ($err = $this->checkRedis()) $errArr[] = $err;
        \Yii::$app->response->statusCode = 200;
        if ($errArr) {
            //发送钉钉报警
            $title = "探测异常,所属项目：：【nisiya.top】" . PHP_EOL;
            $time = date('Y-m-d H:i:s');
            $msg = "time: {$time}" . PHP_EOL;
            $msg .= implode("\n---", $errArr) . PHP_EOL;
            Log::sendGuaHaoErrorDingDingNotice($title . $msg);
            \Yii::$app->response->statusCode = 500;
            return 'ERROR';
        }
        return 'OK';
    }

    /**
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-11-14
     */
    private function checkEs()
    {
        $esConfig = \Yii::$app->get('elasticsearch')->nodes;
        try {
            $esModel = new DoctorEsModel();
            $esInfo = $esModel->info();
            $host = '';
            if (!empty($esConfig)) {
                $host = isset($esConfig[0]['http_address']) ? $esConfig[0]['http_address'] : '';
            }

            if (empty($esInfo) || !isset($esInfo['cluster_uuid'])) {
                return $this->dingCreateList($esConfig, "elasticsearch err: elasticsearch.{$host} fail");
            }

        } catch (\Exception $e) {
            return $this->dingCreateList($esConfig, 'elasticsearch err: ' . $e->getMessage());
        }
        return '';
    }

    /**
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-11-14
     */
    private function checkMysql()
    {
        $baseConf = [];
        try {
            $dbArr = ['db', 'log_branddoctor_db'];
            foreach ($dbArr as $connection) {
                $dbVersion = $this->getDbName($connection)->createCommand("SELECT VERSION() v")->queryOne();
                $baseConf = [
                    'dsn' => \Yii::$app->get($connection)->dsn,
                    'username' => \Yii::$app->get($connection)->username,
                ];
                if (empty($dbVersion) || empty($dbVersion['v'])) {
                    return $this->dingCreateList($baseConf, "mysql err: database.{$connection} fail");
                }
            }
        } catch (\Exception $e) {
            return $this->dingCreateList($baseConf, 'mysql err: ' . $e->getMessage());
        }
        return '';
    }

    private function getDbName($db = 'db')
    {
        return \Yii::$app->get($db);
    }

    /**
     * @return string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-11-14
     */
    private function checkRedis()
    {
        $key1 = '{dp-hospital-service}-detect-key1';
        $key2 = '{dp-hospital-service}-detect-key2';
        $ttl = 100;
        $val = uniqid();
        $lua = <<<LUA
    redis.call('set', KEYS[1], ARGV[1], 'ex', ARGV[2])
    redis.call('set', KEYS[2], ARGV[1], 'ex', ARGV[2])
    local ret = {}
    ret[1] = redis.call('get', KEYS[1])
    ret[2] = redis.call('get', KEYS[2])
    ret[3] = redis.call('ttl', KEYS[1])
    ret[4] = redis.call('ttl', KEYS[2])
    return ret
LUA;

        try {
            $baseConf = [];
            $redisArr = ['redis_codis', 'redis_queue'];
            foreach ($redisArr as $connection) {
                $baseConf = [
                    'host' => \Yii::$app->get($connection)->hostname,
                    'port' => \Yii::$app->get($connection)->port,
                    'db' => \Yii::$app->get($connection)->database
                ];

                $ret = \Yii::$app->$connection->executeCommand('eval', [$lua, 2, $key1, $key2, $val, $ttl]);

                if (ArrayHelper::getValue($ret, 0) != $val || ArrayHelper::getValue($ret, 1) != $val || ArrayHelper::getValue($ret, 2) != $ttl || ArrayHelper::getValue($ret, 3) != $ttl) {
                    return self::dingCreateList($baseConf, "redis({$connection}) err: ret=" . json_encode($ret));
                }
            }
        } catch (\Exception $e) {
            return $this->dingCreateList($baseConf, 'redis err: ' . $e->getMessage());
        }
        return '';
    }

    /**
     * 钉钉消息组合markdown的list
     * @param array $message
     * @param string $title
     * @return string
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-17
     */
    private function dingCreateList($message, $title = '')
    {
        $text = $title ? "\n# {$title}" : '';
        foreach ($message as $k1 => $m1) {
            $m1 = is_array($m1) ? json_encode($m1, 256) : $m1;
            $text .= "\n- {$k1}: {$m1}";
        }
        return $text;
    }
}
