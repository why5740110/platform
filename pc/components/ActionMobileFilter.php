<?php
/**
 * 手机端过滤器
 * @file ActionMobileFilter.php.
 * @author niewei <niewei@yuanxin-inc.com>
 * @version 2.0
 * @date 2018/5/22
 */

namespace pc\components;

use Yii;
use common\libs\ClientSnifferHelper;
use yii\base\ActionFilter;


class ActionMobileFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        //如果是手机端访问PC站跳转到对应M站页面
        if(ClientSnifferHelper::isMobile())
        {
            $uri = \Yii::$app->request->getUrl();
            $host = \Yii::$app->request->getHostInfo();
            
            if(preg_match('/(http[s]?):\/\/(\w+)\.www\.\w+\.\w+/', $host, $match) > 0){
                $url = $match[1].'://'.$match[2].'.m.nisiya.top'.$uri;
            }else{
                //$uri = preg_replace('/_\d+/', '', $uri);
                $url = \Yii::$app->params['domains']['mall_wap'].$uri;
            }
            \Yii::$app->response->redirect($url, 301)->send();
            return false;
        }else{
            return true;
        }
    }
}










