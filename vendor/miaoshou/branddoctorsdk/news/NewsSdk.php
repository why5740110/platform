<?php
/**
 * 资讯
 * @file NewsSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\news;

use nisiya\branddoctorsdk\CommonSdk;

class NewsSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'newsapi';
        parent::__construct();
    }

    /**
     * 根据ID获取资讯详情页信息
     * @param $news_id
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function getNewsDetail($news_id)
    {
        $params = [
            'id' => $news_id,
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}