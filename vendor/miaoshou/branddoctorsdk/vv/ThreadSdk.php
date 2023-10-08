<?php
/**
 * 音视频
 * @file ThreadSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\vv;

use nisiya\branddoctorsdk\CommonSdk;

class ThreadSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'vapi';
        parent::__construct();
    }

    /**
     * 根据ID和类型获取音视频详情页信息
     * @param string $tid 音频视频id
     * @param int $typeid 类型（2:音频，3:视频）
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function detail($tid, $typeid)
    {
        $params = [
            'tid' => $tid,
            'typeid' => $typeid
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }


    /**
     * 更新帖子虚拟浏览数
     * @param string $tid 音频视频id
     * @param int $views 新增虚拟浏览数
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @date 2020-07-01
     */
    public function updateVirtualViews($tid, $views)
    {
        $params = [
            'tid' => $tid,
            'views' => $views
        ];
        return $this->sendHttpRequest($params, __METHOD__);
    }

}