<?php
/**
 * 神策数据埋点sdk.
 * @file SensorsSdk.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-05-18
 */

namespace common\sdks\shence;

use Yii;

class SensorsSdk
{
    /**
     * 数据同步推到神策
     * @param string $event
     * @param array $params
     * @return bool
     * @throws SensorsAnalyticsIllegalDataException
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-05-23
     */
    public function track($event = '', $params = [])
    {
        if (empty($event) || empty($params)) {
            return false;
        }
        require_once("SensorsAnalytics.php");
        # 从神策分析配置页面中获取的数据接收的 URL
        $SA_SERVER_URL = 'https://sensors_report.beijingyuanxin.com/sa?project=default';

        # 初始化一个 Consumer，用于数据发送
        # BatchConsumer 是同步发送数据，因此不要在任何线上的服务中使用此 Consumer
        $consumer = new \BatchConsumer($SA_SERVER_URL);
        # 使用 Consumer 来构造 SensorsAnalytics 对象
        $sa = new \SensorsAnalytics($consumer);
        # 支持在构造SensorsAnalytics对象时指定project, 后续所有通过这个SensorsAnalytics对象发送的数据都将发往这个project
        # $sa = new SensorsAnalytics($consumer, "project_name");

        # 以下是触发一个事件测试数据发送
        $distinct_id = 'ABCDEF123456789';
        $sa->track($distinct_id, true, $event, $params);
        $sa->flush();
    }

    /**
     * 通过文件异步推到神策
     * @param string $event
     * @param array $params
     * @return bool|int
     * @throws SensorsAnalyticsIllegalDataException
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-05-23
     */
    public function trackFile($event = '', $params = [], $user_id, $superParams = [])
    {
        if (empty($event) || empty($params)) {
            return false;
        }
        require_once("SensorsAnalytics.php");

        $apth='/data/logs/php/nisiya.top/shence';
        if(!(file_exists($apth) && is_writable($apth))) {
            //创建目录
            if(!is_dir($apth)){
                @mkdir($apth, 0777, true);
                @chmod($apth, 0777);
                @chmod($apth."/sa.log." . date('Y-m-d'), 0777);
                if (!is_dir($apth)){
                    return false;
                }
            }
        }
        $consumer = new \FileConsumer($apth."/sa.log." . date('Y-m-d'));
        # 使用 Consumer 来构造 SensorsAnalytics 对象
        $sa = new \SensorsAnalytics($consumer);
        if ($superParams) {
            $sa->register_super_properties($superParams);
        }
        $identity = new \SensorsAnalyticsIdentity(['$identity_login_id' => $user_id]) ;
        $sa->track_by_id($identity, $event, $params);
        $sa->flush();
    }
}