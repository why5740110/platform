<?php
/**
 * 帖子
 * @file PostSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\question;

use nisiya\branddoctorsdk\CommonSdk;

class PostSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'askapi';
        parent::__construct();
    }

    /**
     * 根据ID获取帖子详情页信息
     * @param $post_id
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function detail($post_id)
    {
        $params = [
            'id' => $post_id,
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

    /**
     * 更新帖子虚拟浏览数
     * @param string $post_id 问答帖子id
     * @param int $views 新增虚拟浏览数
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @date 2020-07-02
     */
    public function updateVirtualViews($post_id, $views)
    {
        $params = [
            'post_id' => $post_id,
            'views' => $views
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}