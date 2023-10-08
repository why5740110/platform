<?php
/**
 * 文章
 * @file ArticleSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\article;

use nisiya\branddoctorsdk\CommonSdk;

class ArticleSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'artapi';
        parent::__construct();
    }

    /**
     * 根据ID获取文章详情页信息
     * @param $article_id
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function detail($article_id)
    {
        $params = [
            'id' => $article_id,
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

    /**
     * 更新帖子虚拟浏览数
     * @param string $article_id 文章帖子id
     * @param int $views 新增虚拟浏览数
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @date 2020-07-02
     */
    public function updateVirtualViews($article_id, $views)
    {
        $params = [
            'article_id' => $article_id,
            'views' => $views
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}