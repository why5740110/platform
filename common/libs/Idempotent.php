<?php
/**
 * Created by wangwencai.
 * @file: Idempotent.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-08-09
 */

namespace common\libs;

use Yii;
use yii\redis\Connection;

class Idempotent
{
    /**
     * @param $params
     * @param $second
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-09
     * @return bool
     */
    public static function check($params, $second = 10)
    {
        $idempotentToken = md5(json_encode($params));
        /** @var Connection $redis */
        $redis = Yii::$app->redis_codis;
        if ($redis->get($idempotentToken)) {
            // 锁定续期
            $redis->setex($idempotentToken, $second, 1);
            return false;
        }
        $redis->setex($idempotentToken, $second, 1);
        return true;
    }
}