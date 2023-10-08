<?php
/**
 *
 * @file CardLogSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-07-25
 */

namespace nisiya\mallsdk\user;


use nisiya\mallsdk\CommonSdk;

class CardLogSdk extends CommonSdk
{
    /**
     * 获取明细列表
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-07-25
     * @param $card_log_category_id  种类 1 支出 2 收入
     * @param $page  页码
     * @param $limit  每页显示的条数
     * @return array
     */
    public function pagelist($card_log_category_id, $page, $limit){

        $params = [
            'card_log_category_id' => $card_log_category_id,
            'page' => $page,
            'limit' => $limit
        ];
        return $this->send($params, __METHOD__);
    }

}