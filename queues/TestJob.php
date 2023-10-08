<?php
/**
 *
 * @file TestJob.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-07-28
 */

namespace queues;

use yii\base\BaseObject;

class TestJob extends BaseObject implements \yii\queue\JobInterface
{
    public $filename;

    public function execute($queue)
    {
        $file = \Yii::$app->getRuntimePath() . DIRECTORY_SEPARATOR . 'logs/' . $this->filename;
        $time = date('Y-m-d H:i:s', time());
        file_put_contents($file, $time."\r\n", FILE_APPEND);
    }

}