<?php
/**
 * 科室接口
 * @file DepartmentSdk.php
 * @author wangchenxu <wangchenxu@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-11-12
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class DepartmentSdk extends CommonSdk
{

	/**
	 * 获取医院科室详细信息
	**/
	public function info($department_id)
	{
		$params = [
            'department_id' => $department_id,
        ];
        return $this->send($params, __METHOD__);
	}

    /**
     * 获取当前科室对应的药品三级分类
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-11-12
     */
    public function threecategorylist($department_id)
    {
        $params = [
            'department_id' => $department_id,
        ];
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取三级分类下商品列表
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-11-12
    **/
    public function productlist($department_id, $category_id_3, $sort = 0)
    {
    	$params = [
    		'department_id' => $department_id,
    		'category_id_3' => $category_id_3,
    		'sort' => $sort
    	];
    	return $this->send($params, __METHOD__);
    }

    /**
     * 搜索
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-11-12
    **/
    public function search($department_id, $keyword)
    {
    	$params = [
    		'department_id' => $department_id,
    		'keyword' => $keyword,
    	];
    	return $this->send($params, __METHOD__);
    }

}