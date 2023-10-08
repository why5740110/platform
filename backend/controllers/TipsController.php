<?php
/**
 * 信息提示控制器
 * @file siteController.php
 * @author lizhanghu <lizhanghu@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-11-30
 */
namespace backend\controllers;

use Yii;
use yii\web\Controller;

use common\libs\Log;

/**
 * TipsController
 */
class TipsController extends Controller
{

    public function init()
    {
        parent::init();
    }

    /**
     * 404错误页面
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @version 1.0
     * @date 2018-11-30
     * @return string
     */
    public function actionError404()
    {
       $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('site/index');
       return $this->render('//tips/error404',['message'=>'哎呀！网页未找到！','redirect'=>$url]);
    }

    /**
     * 500错误页面增加日志
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-18
     * @return string
     */
    public function actionError500()
    {
        $exception = \Yii::$app->errorHandler->exception;
        Log::sendExceptionMessage($exception);

        $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('site/index');
        return $this->render('//tips/error500',['message'=>'哎呀！有些不对劲！','redirect'=>$url]);
    }

}
