<?php
namespace nisiya;

namespace nisiya\paysdk\open;

use nisiya\paysdk\CommonSdk;
use nisiya\paysdk\Config;
use nisiya\paysdk\CryptoTools;

class AesSdk extends CommonSdk
{
    /**
     * 解密数据
     * @param $data
     * @author xuyi
     * @date 2019/8/29
     * @return array|bool
     */
   public function getNotifyData($data='')
   {
       if(empty($data)){
           # post
           $data = empty($_POST['data']) ? '' : $_POST['data'];
           # get
           if(empty($data)) {
               $data = empty($_GET['data']) ? '' : $_GET['data'];
           }
       }

       $appKey = Config::getConfig('appkey');
       CryptoTools::setKey($appKey);
       $result = CryptoTools::getDecryptedArray($data,2);
       if(empty($result)) {
            return FALSE;
       }

       return $result;
   }
}
