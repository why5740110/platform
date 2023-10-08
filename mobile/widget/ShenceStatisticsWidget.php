<?php
/**
 * 神策埋点小部件
 * @file ShenceStatisticsWidget.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-05-16
 */

namespace mobile\widget;


use yii\base\Widget;
use yii\web\View;

class ShenceStatisticsWidget extends Widget
{
    public $type;
    public $data;

    public function run()
    {
        $sensorsUrl = YII_ENV == 'prod' ? 'https://datasink.beijingyuanxin.com/sa?project=production' : 'https://sensors_report.beijingyuanxin.com/sa';
        //初始化神策埋点参数
        if(empty($this->data) && empty($this->type)){
            $content = $this->getStatisticsCodeInit($sensorsUrl);
            return $this->view->registerJs($content,View::POS_END);
        }else{
            //根据参数进行数据埋点
            $content = $this->getStatisticsCode($this->type,$this->data,$sensorsUrl);
            $this->view->registerJs($content,View::POS_END);
        }
    }

    /** 推送神策日志js部分
     * @param $type
     * @param $data
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-05-16
     */
    public function getStatisticsCode($type,$data,$sensorsUrl)
    {
        $content=<<<content
(function() {
    var sensors = window['sensorsDataAnalytic201505'];
    sensors.registerPage({
        platform_type: "App",
        source_channel: "ghapp",
        operating_system: "Android/IOS",
        application_version: "13.2.3"
    });
    sensors.init({
        server_url: '{$sensorsUrl}',
        heatmap:{scroll_notice_map:'not_collect'},
        is_track_single_page:true,
        use_client_time:true,
        send_type:'beacon',
        app_js_bridge:true
    });
    sensors.track('{$type}', {$data});
})();
content;
        return $content;
    }


    public function getStatisticsCodeInit($sensorsUrl)
    {
        $content=<<<content
    var sensors = window['sensorsDataAnalytic201505'];
    sensors.registerPage({
        platform_type: "App",
        source_channel: "ghapp",
        operating_system: "Android/IOS",
        application_version: "13.2.3"
    });
    sensors.init({
        server_url: '{$sensorsUrl}',
        use_client_time:true,
        send_type:'beacon',
        show_log:false,
        app_js_bridge:true
    });
content;
        return $content;
    }

}