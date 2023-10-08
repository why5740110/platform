<?php
/**
 * 王氏调用百度IM咨询相关接口
 * @file ToBaiduSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\im;

use nisiya\branddoctorsdk\CommonSdk;

class ToBaiduSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'openim';
        parent::__construct();
    }

    /**
     * 4.实时更新医生信息API（非定向 | 定向）
     * @param int $doctor_id （专家）医生ID（必填）
     * @param string $expert_data 推送需要更新专家数据，json格式,⽀持更新的字段：（必填）
     *          consult_price（ 图⽂咨询价格，单位分）
     *          is_open_consult（是否开通图⽂咨询，1=开通，0=关闭）
     *          数据样例
     *          {
     *              "consult_price":50,
     *              "is_open_consult":0
     *          }
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function expertprice($doctor_id, $expert_data)
    {
        $params = [
            'doctor_id' => intval($doctor_id),
            'expert_data' => $expert_data,
        ];
        return $this->sendHttpRequest($params, __METHOD__, 'post');
    }


}