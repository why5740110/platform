<?php
/**
 *
 * @file ProductSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-01-09
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class ProductSdk extends CommonSdk
{

    /**
     * 获取商品列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-09
     * @param $params  参数集合
     * @param int $page 页数
     * @param int $pagesize 单页个数
     * @return bool|mixed
     */
    public function productlist($params)
    {
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取商品列表
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-09
     * @param $params  参数集合
     * @param int $page 页数
     * @param int $pagesize 单页个数
     * @return bool|mixed
     */
    public function productpagelist($params, $page = 1, $pagesize = 20)
    {
        $params['page'] = $page;
        $params['pagesize'] = $pagesize;
        return $this->send($params, __METHOD__);
    }

    /**
     * 单个商店商品销售信息API
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-01-18
     * @param $store_id
     * @param $product_ids  1，2，3，4，5，6，7，6 格式
     * @return bool|mixed
     */
    public function storeproductsalesinfo($store_id, $product_ids)
    {
        $params['store_id'] = $store_id;
        $params['product_ids'] = $product_ids;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取产品详情
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-04-23
     * @param $product_id 1，2，3，4，5，6，7，6 格式
     * @param $store_id 药店id
     * @param $is_water_pic 图片是否展示为有水印
     * @return bool|mixed
     */
    public function productdetail($product_id,$store_id=null,$is_water_pic=true){
        $params['product_id']=$product_id;
        $params['store_id']=$store_id;
        $params['is_water_pic']=$is_water_pic;
        return $this->send($params,__METHOD__);
    }

    /** 批量获取商品详情
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @version 2.0
     * @date 2018/11/7
     * @param $product_ids   1，2，3，4，5，6，7，6 格式
     * @param null $store_id 药店序号
     * @return bool|mixed
     */
    public function productdetails($product_ids, $store_id=null){
        $params['product_ids']=$product_ids;
        $params['store_id']=$store_id;
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取单个商品详情（省级药店）
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2018-01-12
     * @param $product_id 1，2，3，4，5，6，7，6 格式
     * @return bool|mixed
     */
    public function provinceproductdetail($product_id,$store_id=null,$buy_number = 1){
        $params['product_id']=$product_id;
        $params['store_id']=$store_id;
        $params['buy_number']=$buy_number;
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取商品列表(中心仓)
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-10-21
     * @param $params  参数集合
     * @param int $page 页数
     * @param int $pagesize 单页个数
     * @return bool|mixed
     */
    public function provinceproductpagelist($params, $page = 1, $pagesize = 20)
    {
        $params['page'] = $page;
        $params['pagesize'] = $pagesize;
        return $this->send($params, __METHOD__);
    }

     /** 批量获取商品详情（省级药店）
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @version 2.0
     * @date 2019/01/12
     * @param $product_ids   1，2，3，4，5，6，7，6 格式
     * @param null $store_id 药店序号
     * @return bool|mixed
     */
    public function provinceproductdetails($product_ids, $store_id=null){
        $params['product_ids']=$product_ids;
        $params['store_id']=$store_id;
        return $this->send($params,__METHOD__);
    }

    /**
     * 判断用户设置的商品数量是否大于药店库存
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @date 2019-01-18
     * @param string store_ids 药店id,英文逗号分隔
     * @param array products[product_id] = product_num; 
    **/
    public function judgestoreproductquantity($store_ids, $products)
    {
        $params = [
            'store_ids' => $store_ids,
            'products' => $products
        ];
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取商品总价 与
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-01-18
     * @param  $products    格式    products[14704_0_0]=2&products[72653_123_4]=3  商品序号_活动序号_活动类型_数量 （活动类型4是套餐）
     * @param  $store_id    药店序号
     * @param  $is_use_gold 是否使用购物金  0不使用/1使用
     * @param  $pay_way_id  支付方式
     * @param  $delivery_id 配送方式
     * @param  $red_pack_id 使用的红包id
     * @param  $coupon_id   使用的优惠券id
     * @param  $province_id   省份id
     * @param  $city_id       市id
     * @param  $district_id   区县id
     * @param  $township_id   街道id
     * @param  $order_source  订单来源
     * @param  $shippingCouponId 运费券id
     * @param  $buyVipCard   是否购买会员卡
     * @param  $isVipOrder   是否是会员订单
     * @param $isUseTempShippingCoupon 是否使用零时运费券
     **/
    public function productamount($user_id, $products,$store_id,$is_use_gold,$pay_way_id,$delivery_id,$red_pack_id,$coupon_id,$order_source=0,
                                  $shippingCouponId = 0, $buyVipCard = 0, $isVipOrder = 0,$isUseTempShippingCoupon = 0)
    {
        $params = [
            'user_id'     => $user_id,
            'products'    => $products,
            'store_id'    => $store_id,
            'is_use_gold' => $is_use_gold,
            'pay_way_id'  => $pay_way_id,
            'delivery_id' => $delivery_id,
            'red_pack_id' => $red_pack_id,
            'coupon_id'   => $coupon_id,
            'shipping_coupon_id' => $shippingCouponId,
            'is_vip_order' => $isVipOrder,
            'buy_vip_card' => $buyVipCard,
            '$is_use_temp_shipping_coupon' => $isUseTempShippingCoupon,
        ];
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取商品总价 与
     * @author suxingwang <suxingwang@yuanxin-inc.com>
     * @date 2019-01-18
     * @param  $products    格式    products[14704_0_0]=2&products[72653_123_4]=3  商品序号_活动序号_活动类型_数量 （活动类型4是套餐）
     * @param  $store_id    药店序号
     * @param  $is_use_gold 是否使用购物金  0不使用/1使用
     * @param  $pay_way_id  支付方式
     * @param  $delivery_id 配送方式
     * @param  $red_pack_id 使用的红包id
     * @param  $coupon_id   使用的优惠券id
     * @param  $type_province   是否走中心仓，1：走中心仓。0:不走
     **/
    public function productamountnew($user_id, $products,$store_id,$is_use_gold,$pay_way_id,$delivery_id,$red_pack_id,$coupon_id,$outpatient_fee,$type_province=0)
    {
        $params = [
            'user_id'     => $user_id,
            'products'    => $products,
            'store_id'    => $store_id,
            'is_use_gold' => $is_use_gold,
            'pay_way_id'  => $pay_way_id,
            'delivery_id' => $delivery_id,
            'red_pack_id' => $red_pack_id,
            'coupon_id'   => $coupon_id,
            'outpatient_fee'   => $outpatient_fee,
            'type_province'   => $type_province,
        ];
        return $this->send($params,__METHOD__);
    }

    /**
     * @param $product_id
     * @param int $product_browse_count
     * @author xuyi
     * @date 2019/7/12
     * @return bool|mixed
     */
    public function saveproductbrowsecount($product_id,$product_browse_count = 1){
        $params['product_id'] =$product_id;
        $params['product_browse_count']=$product_browse_count;

        return $this->send($params,__METHOD__);
    }

    /**
     * 获取单个商品的包邮信息及基础运费信息
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param int $product_id 商品id
     * @date 2019-05-17
    **/
    public function getproductfreeshipping($product_id)
    {
        return $this->send(['product_id' => $product_id], __METHOD__);
    }


    /**
     * 获取评价列表
     * @param $productId
     * @param $serviceType
     * @param $page
     * @param $pagesize
     * @author xuyi
     * @date 2019/7/24
     * @return bool|mixed
     */
    public function productevaluatelist($productIds, $serviceType, $page, $pagesize)
    {
        $params['product_ids'] =$productIds;
        $params['service_type']=$serviceType;
        $params['page'] = $page;
        $params['pagesize'] = $pagesize;

        return $this->send($params,__METHOD__);
    }

    /**
     * 查询评价总数
     * @param $productId
     * @author xuyi
     * @date 2019/7/24
     * @return bool|mixed
     */
    public function productevaluatecount($productIds)
    {
        $params['product_ids'] = $productIds;
        return $this->send($params,__METHOD__);
    }

    /**
     * 获取单个商品信息 因返回数据包含商品进价 该方法只能内部调用，前端、第三方禁止调用
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @param int $product_id 商品id
     * @date 2019-08-13
    **/
    public function productinfo($productId,$storeId=null)
    {
        $params['product_id']=$productId;
        $params['store_id']=$storeId;
        return $this->send($params, __METHOD__);
    }

    /**
     * 获取药品针对疾病
     * @author wangchenxu <wangchenxu@yuanxin-inc.com>
     * @param array $productIds 商品id
     * @param int $show_num 每个商品显示的疾病个数
     * @date 2019-11-26
    **/
    public function diseaseunique(array $productIds,$show_num=0)
    {
        $params['product_ids']=$productIds;
        $params['show_num']=$show_num;
        return $this->send($params, __METHOD__);
    }

    /**
     * author: huguifeng <huguifeng@yuanxin-inc.com>
     * @param $productId
     * @param null $storeId
     * 根据商品Id门店id获取该商品是否失效  购物车用 $productId 为:商品id_活动id_活动类型id
     * @return bool|mixed
     */
    public function getfailproduct($productId,$storeId=null, $type_province)
    {
        $params['product_id']=$productId;
        $params['store_id']=$storeId;
        $params['type_province']=$type_province;
        return $this->send($params, __METHOD__);
    }


    /**
     * author: dongyaowei <dongyaowei@yuanxin-inc.com>
     * @param $productName  ：药品名称
     * @param $businessName ：厂商名称
     * @param $page         ：页数
     * @param $limit        ：条数
     * 根据药品名称、厂商名称模糊查询商品列表
     * @return bool|mixed
     */
    public function productlistbynameandbusiness($productName='',$businessName='',$page=1,$limit=10)
    {
        $params['product_name']=$productName;
        $params['business_name']=$businessName;
        $params['page']=$page;
        $params['limit']=$limit;
        return $this->send($params, __METHOD__);
    }

    /**
     * author: dongyaowei <dongyaowei@yuanxin-inc.com>
     * @param $productId  ：药品id
     * @param $page         ：页数
     * @param $limit        ：条数
     * 根据药品id查询药店库存
     * @return bool|mixed
     */
    public function productstorequantitybyproductid($productId='',$page=1,$limit=10)
    {
        $params['product_id']=$productId;
        $params['page']=$page;
        $params['limit']=$limit;
        return $this->send($params, __METHOD__);
    }
}