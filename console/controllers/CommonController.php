<?php

namespace console\controllers;

class CommonController extends \yii\console\Controller
{
    public $start_time;
    public $platform = [
        'henan' => 1, 'nanjing' => 2, 'haodaifu' => 3, 'jiankang160' => 5,'nisiya'=>6
    ];

    public function init()
    {
        parent::init();
        $this->start_time = microtime(true);
    }

    /**
     * 获取耗时
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-10
     * @version 1.0
     */
    public function afterAction($action, $result)
    {
        $end_time  = microtime(true);
        $diff_time = round(($end_time - $this->start_time) / 60, 2);
        if ($diff_time < 1) {
            $spend_time = round(($end_time - $this->start_time), 2) . '秒';
        } else {
            $spend_time = round(($end_time - $this->start_time) / 60, 2) . '分钟';
        }
        echo "[" . date('Y-m-d H:i:s') . "] 耗时：{$spend_time} 处理完成！\n";
        return parent::afterAction($action, $result);
    }

}
