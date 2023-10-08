<?php
/**
 * 文章sdk
 * @file ArticleSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-03-07
 */

namespace nisiya\mallsdk\other;

use nisiya\mallsdk\CommonSdk;

class ArticleSdk extends CommonSdk
{

	/**
	 * 获取文章列表
	 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
	 * @date 2019-04-10
	 * @param int $page 页码
	 * @param int $pagesize 每页显示的条数
	**/
    public function pagelist($page, $pagesize){
    	$params = [
    		'page' => $page,
    		'pagesize' => $pagesize,
    	];
        return $this->send($params,__METHOD__);
    }

    /**
	 * 获取文章详情
	 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
	 * @date 2019-04-10
	 * @param int $page 页码
	 * @param int $pagesize 每页显示的条数
	**/
    public function detail($id){
    	$params = [
    		'id' => $id,
    	];
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取文章详情
     * @author liuminglu <liuminglu@yuanxin-inc.com>
     * @date 2020-04-22
     * @param int $helpId id
     **/
    public function aboutus($helpId){
        $params = [
            'help_id' => $helpId,
        ];
        return $this->send($params,__METHOD__);
    }

}
