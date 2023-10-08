<?php
/**
 *
 * @file WechatSdkModel.php
 * @author xieyuqiu <xieyuqiu@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-03-12
 */

namespace common\models;

use common\sdks\wechat\WechatSDK;
use Yii;
use yii\helpers\ArrayHelper;

class WechatSdkModel
{
    /**
     * @param  $url
     * @调取分享接口
     */
    public function share($url){
        $params=[];
        $params['url']=$url;
        $wxchatSdk = WechatSDK::getInstance();
        $result = $wxchatSdk->getJsConfig(['url'=>$url]);
        $msg = ArrayHelper::getValue($result, 'msg', '调用远程接口错误');
        $data = ArrayHelper::getValue($result, 'data');
        if($data){
            return $data;
        }
        return null;
    }

}