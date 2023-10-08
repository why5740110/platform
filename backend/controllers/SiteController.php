<?php
/**
 * 登录首页
 * @file siteController.php
 * @author lizhanghu <lizhanghu@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-11-28
 */
namespace backend\controllers;
use nisiya\baseadminsdk\baseadmin\SystemSdk;
use Yii;
class SiteController extends BaseController
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
    }

    /**
     * 默认欢迎页面
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @version 1.0
     * @date 2018-11-28
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 登录页面
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @version 1.0
     * @date 2018-11-28
     * @return string
     */
    public function actionLogin()
    {
        $this->redirect(Yii::$app->params['basenisiyaDomain']);
    }

    /**
     * 退出登录
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-15
     */
    public function actionLogout()
    {
        $ucLoginKey = Yii::$app->params['api_url']['base_new']['cookie_login_key'];
        $loginUrl = Yii::$app->params['api_url']['base_new']['logout'];
        $token = Yii::$app->request->cookies->getValue($ucLoginKey, '');
        if (empty($token)) {
            return $this->redirect($loginUrl);
        }

        $sdk = new SystemSdk(Yii::$app->params['api_url']['base_new']['system_api']);
        $res = $sdk->checkLogOut($token);
        if ($res['success'] == false) {
            $message = $res['message'] ?? '退出操作异常，请重试';
            $this->_showMessage($message, '/');
        }
        return $this->redirect($loginUrl);
    }

}
