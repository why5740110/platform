<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : UseraddressSdk.php
 * @author  : dongyaowei <dongyaowei@yuanxin-inc.com>
 * @date    : 2019-1-20
 */

namespace nisiya\mallsdk\user;

use nisiya\mallsdk\CommonSdk;

class UseraddressSdk extends CommonSdk
{
    public function getuseraddresslist($params=[])
    {

        return $this->send($params, __METHOD__);
    }

    public function getuseraddressinfo($adress_id)
    {
	    $params = [
	        'adress_id'=>$adress_id,
	    ];

	    return $this->send($params, __METHOD__);
    }

       public function adduseraddress($consignee,$province,$city,$district,$address,$mobile,$is_default=0,$lng=0,$lat=0)
    {
        $params = [
            'is_default'=>$is_default,
            'consignee'=>$consignee,
            'province'=>$province,
            'city'=>$city,
            'district'=>$district,
            'address'=>$address,
            'mobile'=>$mobile,
            'lng'=>$lng,
            'lat'=>$lat,
        ];
        //print_R($params);die;
        return $this->send($params, __METHOD__);
    }

     public function updateuseraddress($address_id,$consignee,$province,$city,$district,$address,$mobile,$is_default=0,$lng=0,$lat=0)
    {
        $params = [
            'address_id'=>$address_id,
            'is_default'=>$is_default,
            'consignee'=>$consignee,
            'province'=>$province,
            'city'=>$city,
            'district'=>$district,
            'address'=>$address,
            'mobile'=>$mobile,
            'lng'=>$lng,
            'lat'=>$lat,
        ];
        //print_R($params);die;
        return $this->send($params, __METHOD__);
    }

    public function deluseraddressinfo($address_id)
    {

        $params = ['address_id'=>$address_id];
        return $this->send($params, __METHOD__);
    }

    public function setdefaultuseraddress($address_id)
    {

        $params = ['address_id'=>$address_id];
        return $this->send($params, __METHOD__);
    }

    public function bdupdateuseraddress(array $params)
    {
        return $this->send($params, __METHOD__);
    }
}