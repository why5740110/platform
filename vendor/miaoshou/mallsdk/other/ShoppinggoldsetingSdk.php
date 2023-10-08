<?php
/**
 *
 * @file ShoppinggoldsetingSdk.php
 * @author suxingwang <suxingwang@yuanxin-inc.com>
 * @version 2.0
 * @date 2018/10/17
 */


namespace nisiya\mallsdk\other;


use nisiya\mallsdk\CommonSdk;

class ShoppinggoldsetingSdk extends CommonSdk
{
    public function getconfig(){
        return $this->send([],__METHOD__);
    }
}