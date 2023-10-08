<?php
/**
 * Created by PhpStorm.
 * User: suxingwang
 * Date: 2019/8/5
 * Time: 14:25
 */
namespace nisiya\mallsdk\store;

use nisiya\mallsdk\CommonSdk;

class StorelistSdk extends CommonSdk
{
    /**
     * 药店列表
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-8-5
     *
     *
    $params['page']  =1;
    $params['limit'] =20;
    $params['key']['store_id'] = 1;
    $params['key']['name']     = '恒金堂';
    $params['key']['erp_code'] = 'dddd';
     **/
    public function storelist($data=[])
    {
        $params = $data;
        return $this->send($params, __METHOD__);
    }
    /**
     * 获取一条药店信息
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     **/
    public function storeinfobystoreid($storeId)
    {
        $params['store_id'] = $storeId;
        return $this->send($params, __METHOD__);
    }

    /**
     * 添加药店
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     **/
    public function addstore($data=[])
    {
        $params['data'] = $data;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 编辑药店
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     **/
    public function editstore($storeId,$data=[])
    {
        $params['store_id'] = $storeId;
        $params['data']     = $data;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 重置密码
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     **/
    public function resetpwd($mobile)
    {

        $params['mobile']  = $mobile;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 修改子药店映射关系
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * store_id 药店id
     * map_ids  子药店 1,2,33
     * data['admin_id']   = 1;
     * data['admin_name'] = 谁;
     * data['last_mod_time'] = 123123123123;
     * data['real_price_type'] = 1;
     * data['store_price_type'] = 2;
     * data['sync_product'] = 1;
     **/

    public function editstoremap($storeId,$mapIds,$data)
    {
        $params['store_id']  = $storeId;
        $params['map_ids']   = $mapIds;
        $params['data']      = $data;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 获取子药店映射关系
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * store_id 药店id
     **/
    public function getmapinfo($storeId)
    {
        $params['store_id']  = $storeId;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 根据子药店 获取所有关联的 父药店
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * store_id 药店id
     **/

    public function getparentstoreidbystoreid($storeId)
    {
        $params['store_id']  = $storeId;
        return $this->send($params, __METHOD__,'post');
    }

    /**
     * 编辑药店状态
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-4-15
     * store_id 药店id
     **/

    public function updatestatus($storeId,$updateData)
    {
        $params['store_id']  = $storeId;
        $params['data']      = $updateData;
        return $this->send($params, __METHOD__,'post');
    }

}