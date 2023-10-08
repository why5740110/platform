<?php

/**
 * 后台权限基类
 * @file baseController.php
 * @author lizhanghu <lizhanghu@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-11-28
 */
namespace backend\controllers;

use backend\filters\AdminPowerFilter;
use nisiya\baseadminsdk\baseadmin\SystemSdk;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\components\AdminMenu;
use common\components\Menu;
use common\libs\CommonFunc;
use yii\web\Cookie;
use yii\helpers\ArrayHelper;
use common\models\GuahaoCooListModel;

class BaseController extends Controller
{
    public $enableCsrfValidation = true;

    /**
     * 菜单列表
     * @var array
     */
    public $menulist;
    public $platform;
    public $coo_platform;


    /**
     * 默认用户信息
     * @var array
     */
    public $userInfo = ['id' => 0, 'username' => '', 'realname' => '', 'role_name' => []];

    /**
     * 验证和设置登录用户信息
     */
    public function behaviors()
    {
        return [
            'class' => AdminPowerFilter::className(),
        ];
    }

    /**
     * 初始化校验权限
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @version 1.0
     * @date 2018-11-28
     * @return
     */
    public function init()
    {
        parent::init();
        $this->platform   = CommonFunc::getTpPlatformNameList(1);
        $this->coo_platform   = GuahaoCooListModel::getCooPlatformList();
    }

    /**
     * 返回json格式
     * @param int $status 操作状态 1成功 2失败
     * @param string $msg 提示信息
     * @param array $data 返回数组
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @version 1.0
     * @date 2018-11-28
     * @return
     */
    public function returnJson($status = 1, $msg = '操作成功', $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => $status,
            'msg' => $msg,
            'data' => $data

        ];
        Yii::$app->response->send();
        exit();
    }

    public function _showMessage($message = '', $redirect = '', $timeout = 2, $type = 'failed')
    {
        exit($this->render('//tips/message', array('content' => $message, 'redirect' => $redirect, 'timeout' => $timeout, 'type' => $type)));
    }

    public function error()
    {
        $url = \Yii::$app->params['BaseAdminDomain'];
        return \Yii::$app->response->redirect($url);
    }

}
