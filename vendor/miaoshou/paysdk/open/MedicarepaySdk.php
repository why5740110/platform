<?php
/**
 * 医保移动支付
 * @file MedicarepaySdk.php
 */


namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;

class MedicarepaySdk extends CommonSdk
{
    /**
     * 【1101】查询参保人基本信息
     * @param $params
     * @return bool|mixed
     */
    public function baseinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【2201】门诊挂号登记就诊信息
     * @param $params
     * @return bool|mixed
     */
    public function mdtrtinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【2202】门诊挂号撤销
     * @param $params
     * @return bool|mixed
     */
    public function mdtrtrevoke($params)
    {
        return $this->send($params,__METHOD__,'post');
    }
    /**
     * 【6201】费用明细上传
     * @param $params
     * @return bool|mixed
     */
    public function uldfeeinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【6202】支付下单
     * @param $params
     * @return bool|mixed
     */
    public function payorder($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【6203】医保退费
     * @param $params
     * @return bool|mixed
     */
    public function refundorder($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【6301】医保订单结算结果查询
     * @param $params
     * @return bool|mixed
     */
    public function queryOrderinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【6401】费用明细上传撤销
     * @param $params
     * @return bool|mixed
     */
    public function revokeorder($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【5301】人员慢特病备案查询
     * @param $params
     * @return bool|mixed
     */
    public function querychronicdiseasefiling($params)
    {
        return $this->send($params,__METHOD__,'post');
    }

    /**
     * 【5302】人员定点信息查询
     * @param $params
     * @return bool|mixed
     */
    public function queryfixedpointinfo($params)
    {
        return $this->send($params,__METHOD__,'post');
    }
    
}
