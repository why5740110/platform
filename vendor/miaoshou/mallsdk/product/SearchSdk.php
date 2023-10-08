<?php
/**
 *
 * @file SearchSdk.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-05-17
 */

namespace nisiya\mallsdk\product;


use nisiya\mallsdk\CommonSdk;

class SearchSdk extends CommonSdk
{

    /**
     * 搜索接口
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2018-05-17
     * @param $params
     * @return mixed
     * store_id              必选    int       药店ID
     * keyword               必选    string    搜索关键词
     * line_status           可以    int       上线状态 (0:上线,-1:未上线) [默认空]
     * is_chufang            可选    int       是否处方药 (0:不是,1:是) [默认空]
     * price_above           可选    double    商品最小价格   [默认空]
     * price_under           可选    double    商品最大价格   [默认空]
     * quantity_above        可选    double    商品最小库存   [默认空]
     * quantity_under        可选    double    商品最大库存   [默认空]
     * dosage_from_ids       可选    string    剂型[多选]     [默认空]
     * business_ids          可选    string    生产厂家[多选] [默认空]
     * category_id           可选    int       商品一级分类ID [默认空]
     * category_id_2         可选    int       商品二级分类ID [默认空]
     * category_id_3         可选    int       商品三级分类ID [默认空]
     * filter_category_ids_3 可选    string    过滤商品三级分类IDS[多选] [默认空] (eg:221,794)
     * sort                  可选    int       排序(1:虚拟销量倒序,2:最新倒序,3:售卖价格倒序,4:虚拟销量正序,5:最新正序,6:价格正序,7:商品ID顺序排序 【默认0,没有排序】)
     * page                  可选    int       页码数 [默认1]
     * pagesize              可选    int       单页数据量（可选）[默认20]
     */

    public function index($params){
        return $this->send($params,__METHOD__);
    }



    /**
     * 省级药店库搜索
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2019-01-11
     * @param $params
     * @return mixed
     * store_id              必选    int       药店ID
     * keyword               必选    string    搜索关键词
     * line_status           可以    int       上线状态 (0:上线,-1:未上线) [默认空]
     * is_chufang            可选    int       是否处方药 (0:不是,1:是) [默认空]
     * price_above           可选    double    商品最小价格   [默认空]
     * price_under           可选    double    商品最大价格   [默认空]
     * quantity_above        可选    double    商品最小库存   [默认空]
     * quantity_under        可选    double    商品最大库存   [默认空]
     * dosage_from_ids       可选    string    剂型[多选]     [默认空]
     * business_ids          可选    string    生产厂家[多选] [默认空]
     * category_id           可选    int       商品一级分类ID [默认空]
     * category_id_2         可选    int       商品二级分类ID [默认空]
     * category_id_3         可选    int       商品三级分类ID [默认空]
     * filter_category_ids_3 可选    string    过滤商品三级分类IDS[多选] [默认空] (eg:221,794)
     * sort                  可选    int       排序(1:虚拟销量倒序,2:最新倒序,3:售卖价格倒序,4:虚拟销量正序,5:最新正序,6:价格正序,7:商品ID顺序排序 【默认0,没有排序】)
     * page                  可选    int       页码数 [默认1]
     * pagesize              可选    int       单页数据量（可选）[默认20]
     */

    public function provinceproductsearch($params){
        return $this->send($params,__METHOD__);
    }

    /**
     * 通过关键词获取关联词
     * @author dongyaowei <dongyaowei@yuanxin-inc.com>
     * @date 2019-11-06
     * @param $keyword  必选    string    搜索关键词
     */

    public function getsuggestlist($keyword){
        $params = [
            'keywords' => $keyword,
        ];
        return $this->send($params,__METHOD__);
    }
}