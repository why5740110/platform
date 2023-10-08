<?php

namespace mobile\controllers;

use common\helpers\Url;
use common\sdks\HapiSdk;
use Yii;

class OrderController extends CommonController
{
    public $pagesize = 10;

    public $visit_nooncode = [1 => '上午', 2 => '下午', 3 => '晚上', 4 => '下午'];

    public function init()
    {
        parent::init();
        $user_id = $this->user_id ?? 0;
        //没有登录去登陆
        if (empty($user_id)) {
            $source_url = Yii::$app->request->urlReferrer ?? '';
            if (!$source_url) {
                $path       = Yii::$app->request->getPathInfo();
                $source_url = Yii::$app->params['domains']['mobile'] . $path;
            }
            $url        = Yii::$app->params['domains']['ucenter'] . "uc/login?goBack=" . $source_url;
            header("location:$url");
        }
    }

    /**
     * 获取挂号订单列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-02-01
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionList()
    {
        $request = Yii::$app->request;
        $page    = (int) $request->get('page', 1);
        $params  = [
            'uid'      => $this->user_id,
            'page'     => $page,
            'pagesize' => $this->pagesize,
        ];

        $week_array = ["日", "一", "二", "三", "四", "五", "六"];

        $order_list = HapiSdk::getInstance()->getOrderList($params);
        if ($order_list) {
            foreach ($order_list as &$o_item) {
                $o_item['visit_time_ymd']      = date("Y年m月d日", strtotime($o_item['visit_time']));
                $o_item['visit_week']          = '周' . $week_array[date("w", strtotime($o_item['visit_time']))];
                $o_item['visit_nooncode_text'] = $this->visit_nooncode[$o_item['visit_nooncode']] ?? '上午';
            }
        }
        $data['list']   = $order_list ?? [];
        $data['page']   = $page;
        $this->seoTitle = '我的预约挂号列表';

        //埋点数据处理
        $eventParam = [
            'page_title' => '我的预约列表',
            'page' => '我的预约列表',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        return $this->render('list', $data);
    }
}
