<?php
/**
 * 删除缓存任务
 * @file DeleteCacheController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-03-17
 */

namespace console\controllers;

use Yii;


class DeleteCacheController extends CommonController
{

    private $queue_keys = ['queue','guahao_order_queue','guahao_log_queue','guahao_schedule_es_queue','guahao_doctor_schedule_es_queue',
        'guahao_order_update_queue','guahao_get_tp_schedule_queue','guahao_get_tp_schedule_queue_async','guahao_get_henan_schedule_queue',
        'guahao_get_nanjing_schedule_queue','guahao_get_jiankang160_schedule_queue','guahao_get_shaanxi_schedule_queue','guahao_get_jiankangzhilu_schedule_queue','guahao_get_sichuan_schedule_queue'];

    /** 删除挂号系统hyperf框架redis队列timeout的内容
     * @param $queue_key
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-03-17
     */
    public function actionDeleteQueueTimeout($queue_key){
        if (in_array($queue_key,$this->queue_keys)){
            //判断环境
            $env = YII_ENV == 'test' ? '_test' : '';
            $redis = \Yii::$app->redis_queue;
            //redis队列里有超时、失败等，这里是释放超时队列内容
            $queue_key = $queue_key.$env.':timeout';
            if (!$redis->exists($queue_key)){
                echo "该队列key" . $queue_key . " 不存在\n";
                exit;
            }
            echo '队列key: '.$queue_key.PHP_EOL;
            $redis->del($queue_key);
        }else{
            echo "该队列key" . $queue_key . " 不在队列里\n";
            exit;
        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;

    }

}