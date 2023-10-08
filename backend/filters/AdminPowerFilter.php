<?php
/**
 * 权限过滤器，兼容老系统
 * @file OldsystemController.php
 */

namespace backend\filters;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;
use yii\base\ActionFilter;
use common\components\Menu;
use nisiya\baseadminsdk\baseadmin\SystemSdk;
use yii\web\Response;

class AdminPowerFilter extends ActionFilter
{
    public function t($arr, $pid = 0, $lev = 0)
    {
        $list = array();
        foreach ($arr as $v) {
            if ($v['fid'] == $pid) {
                $v['child'] = $this->t($arr, $v['id'], $lev + 1);
                $list[] = $v;
            }
        }
        return $list;
    }

    /**
     * 获取所有权限路由
     * @param array $routers
     * @return array
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-07-30
     */
    public function allRouters(array $routers): array
    {
        $allRouters = [];
        foreach ($routers as $v) {
            $allRouters[] = [
                'id' => $v['id'],
                'fid' => $v['parent_id'],
                'name' => $v['meta']['title'] ?? '',
                'url' => $v['path'],
                'is_show' => $v['meta']['hidden'] ? 0 : 1,
            ];
            if (!empty($v['children'])) {
                array_push($allRouters, ...$this->allRouters($v['children']));
            }
        }
        return $allRouters;
    }

    /**
     * 动作前置过滤器验证用户是否登录
     * @param $action
     * @return bool|\yii\base\Action|Yii\console\Response|Yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-07-30
     */
    public function beforeAction($action)
    {
        $ucLoginKey = Yii::$app->params['api_url']['base_new']['cookie_login_key'];
        $token = Yii::$app->request->get('token', '');//登录时携带token
        if (!empty($token)) {
            //存储token到Client
            $expire = 60 * 60 * 24; //24小时过期
            $cookie = \Yii::$app->response->cookies;
            $cookie->add(new Cookie([
                'name' => $ucLoginKey,
                'value' => $token,
                'expire' => time() + $expire,
                'domain' => 'nisiya.top',
                'httpOnly' => false,
            ]));
            return \Yii::$app->response->redirect(Yii::$app->params['AdminDomain']);
        }
        $tokenCookie = Yii::$app->request->cookies->getValue($ucLoginKey, '');
        if (empty($token)) {//get参数中没有token, 取cookie中token
            $token = $tokenCookie;
            if (empty($token)) {//没有登录或登录过期
                return $this->redirectLogin();
            }
        }
        //token获取权限
        $sdk = new SystemSdk(Yii::$app->params['api_url']['base_new']['system_api']);
        $res = $sdk->checkToken($token, Yii::$app->params['api_url']['base_new']['role_keyword']);
        if (empty($res['success'])) {
            return $this->redirectLogin();
        }

        $userInfo = $res['data']['user'];
        $userInfo['roles'] = $res['data']['roles'];

        //设置的登录用户cookie并赋值登录用户信息
        $action->controller->userInfo = $this->getLoginUserInfo($ucLoginKey, $token, $userInfo);
        $allRouters = $this->allRouters($res['data']['routers'] ?? []);
        array_multisort(array_column($allRouters, 'id'), $allRouters);

        $controllerID = \Yii::$app->controller->id;
        $actionID = \Yii::$app->controller->action->id;
        $roleUrl = $controllerID . '/' . $actionID;

        if ($controllerID != 'login' && $controllerID != 'site' && $roleUrl != 'site/error' && !in_array($roleUrl, array_column($allRouters, 'url'))) {
            if (isset(Yii::$app->request->isAjax) && Yii::$app->request->isAjax) {
                $this->returnJson(2, '没有权限访问此功能');
            } else {
                $url = \Yii::$app->params['api_url']['base_new']['login'];
                return $this->_showMessage('没有权限访问此功能', '/', $timeout = 2);
            }
        }

        $roleArr = $this->t($allRouters);
        if ($roleArr) {
            $fid = \Yii::$app->request->get('fid');
            if (isset($fid)) {
                $firstM = $fid;
            } else {
                $url = \Yii::$app->request->getPathInfo();
                $firstM = Menu::getCurrentUrl(Menu::getTop($roleArr), $url);
                if (!$firstM) {
                    $firstM = current($roleArr);
                    $firstM = ArrayHelper::getValue($firstM, 'id');
                }
            }

            $roleList_ = [];
            $accessUrlArr = [];
            foreach ($roleArr as $item) {
                if ($item['id'] == $firstM) {
                    $roleList_ = $item['child'];
                    $roleList_['top_id'] = $item['id'];
                }

                $accessUrlArr[] = $item['url'];
                if (isset($item['child'])) {
                    foreach ($item['child'] as $v) {
                        $accessUrlArr[] = $v['url'];
                    }
                }
            }

            foreach ($roleList_ as &$item) {
                if (isset($item['child'])) {
                    foreach ($item['child'] as $v) {
                        $roleList_[] = $v;
                        $accessUrlArr[] = $v['url'];
                    }
                    unset($item['child']);
                }
            }

            $action->controller->menulist = Menu::getMenuTree($roleArr, $firstM);
            return parent::beforeAction($action);
        }

        return $action;
    }

    /**
     * 未登录跳转到BASE后台登录
     * @author chongxiaowei <chongxiaowei@yuanxinjituan.com>
     * @date  2022-08-02
     * @version v1.0
     * @return boolean
     */
    public function redirectLogin()
    {
        if (isset(Yii::$app->request->isAjax) && Yii::$app->request->isAjax) {
            $this->returnJson(2, '你还没有登录或登录已失效，请先登录');
        }else {
            $url = \Yii::$app->params['api_url']['base_new']['login'];
            \Yii::$app->response->redirect($url);
            return false;
        }
    }

    /**
     * 设置登录用户信息
     * @param $userInfo array ['id' => 1, 'username' => '', 'realname' => ''] 用户信息
     * @param $expire int 过期时间 ，单位秒，0：不过期，大于0：过期多少秒
     * @author chongxiaowei <chongxiaowei@yuanxinjituan.com>
     * @date  2022-08-02
     * @version v1.0
     * @return boolean
     */
    public function getLoginUserInfo($ucLoginKey, $token, $userInfo, $expire = -1)
    {
        if(empty($userInfo)){
            return Yii::$app->controller->userInfo;
        }

        if($expire == -1) {
            $expire = 60 * 60 * 24; //24小时过期
        }

        //存储token到Client
        $cookie = \Yii::$app->response->cookies;
        $cookie->add(new Cookie([
            'name' => $ucLoginKey,
            'value' => $token,
            'expire' => time() + $expire,
            'domain' => 'nisiya.top',
            'httpOnly' => false,
        ]));

        $cookie->add(new Cookie([
            'name' => 'name',
            'value' => $userInfo['username'],
            'expire' => time() + 3600 * 24,
            'domain' => 'nisiya.top',
            'httpOnly' => false,
        ]));
        $cookie->add(new Cookie([
            'name' => 'uid',
            'value' => $userInfo['id'],
            'expire' => time() + 3600 * 24,
            'domain' => 'nisiya.top',
            'httpOnly' => false,
        ]));

        $userInfo['role_name'] = $userInfo['roles'];
        unset($userInfo['roles']);
        return $userInfo;

    }

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
        exit(\Yii::$app->controller->render('//tips/message', array('content' => $message, 'redirect' => $redirect, 'timeout' => $timeout, 'type' => $type)));
    }
}
