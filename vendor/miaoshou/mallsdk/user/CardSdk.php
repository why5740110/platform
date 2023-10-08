<?php
/**
 * @project : api.mall.nisiya.top
 * @file    : CardSdk.php
 * @author  : dongyaowei <dongyaowei@yuanxin-inc.com>
 * @date    : 2018-7-16
 */

namespace nisiya\mallsdk\user;

use nisiya\mallsdk\CommonSdk;

class CardSdk extends CommonSdk
{
    public function cardlist($tell,$p=1)
    {

        $params = ['tell' => $tell,'page' => $p];
        return $this->send($params, __METHOD__);
    }

    public function searchcard($card_sn)
    {
	    $params = ['card_sn'=>$card_sn];

	    return $this->send($params, __METHOD__);
    }
    public function cardcount($tell)
    {
        $params = ['tell'=>$tell];
        return $this->send($params, __METHOD__);
    }
    
}