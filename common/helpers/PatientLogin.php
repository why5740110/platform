<?php
/**
 * @file PatientLogin.php
 * @author xiujianying
 * @version 1.0
 * @date 2019/1/18
 */


namespace common\helpers;

use nisiya\ucentersdk\ucenter\LoginSdk;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;

class PatientLogin
{
    const LOGIN_COOKIE_NAME = 'uc_login_key';
    /**
     * 判断患者是否登陆
     * @author xiujianying
     * @date 2019/1/18
     * @return bool|mixed
     */
    public static function isLogin(){
        $cookies = \Yii::$app->request->cookies;
        $user_id = $cookies->getValue('user_id', '');
        $userToken = $cookies->getValue('token', '');

        //获取当前app token
        $appToken = isset($_COOKIE['uc_login_key'])?$_COOKIE['uc_login_key']:'';
        if($appToken){
            $logined = $cookies->getValue('uc_logined', '');
            //已登录token 和 app当前token不想打
            if($logined!=$appToken){
                self::removeLoginCookie();//先清除之前存在的cookie信息
                return self::goLogin();
            }
        }

        if($user_id && $userToken){
            $sign = self::returnSign($user_id);
            if($userToken==$sign){
                return $user_id;
            }else{
                return self::goLogin();
            }
        }else{
            return self::goLogin();
        }
    }

    /**
     * @return bool
     * @author xiujianying
     * @date 2019/12/10
     */
    public static function goLogin(){
        //未登录 验证url cookie 设置登录
        //小程序
        $login_key = \Yii::$app->request->get('login_key_expire');
        if ($login_key) {
            return self::setLogin($login_key);
        }
        //app
        if(isset($_COOKIE['uc_login_key'])) {
            $login_key = $_COOKIE['uc_login_key'];
            if ($login_key) {
                $expire = time()+7 * 3600;
                return self::setLogin($login_key . '_' . $expire);
            }
        }
        return false;
    }

    /**
     * 获取cookie的值
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date 2019/03/27
     */
    public static function get_cookie_params($key = ''){
        $cookies = \Yii::$app->request->cookies;
        $param = $cookies->getValue($key, '');
        return $param ?? '';

    }

    /**
     * cookie token加密方式
     * @param $param
     * @return string
     * @author xiujianying
     * @date 2019/12/9
     */
    public static function returnSign($param){
        $key = 'yunxinKj~%^key*#@!'.YII_ENV;
        return md5(md5($param).$key);
    }

    /**
     * 登陆 设置cookie
     * @param $uc_login_key  //用户中心校验登录串
     * @author xiujianying
     * @date 2019/1/18
     * @return bool
     */
    public static function setLogin($uc_login_key){
        //$uc_login_key = \Yii::$app->request->get('login_key_expire'); //0be4dd0c161ab92a670431e618211b4e_1548386104
        if($uc_login_key){
            $loginArr = explode('_',$uc_login_key);
            $login_key = ArrayHelper::getValue($loginArr,'0');

            //验证是否登录
            $loginSdk = new LoginSdk();
            $httpData =  $loginSdk->verificationlogin($login_key);
            $httpData = json_decode($httpData,true);
            $user_id = 0;
            if(isset($httpData['code']) && $httpData['code']==200 ){
                $info = ArrayHelper::getValue($httpData,'data');
                if($info){
                    $user_id = ArrayHelper::getValue($info,'uid');
                    $cookieInfo['uid'] = ArrayHelper::getValue($info,'uid');
                    $cookieInfo['username'] = ArrayHelper::getValue($info,'username');
                    $cookieInfo['nickname'] = ArrayHelper::getValue($info,'nickname');
                    $cookieInfo['nisiya_id'] = ArrayHelper::getValue($info,'nisiya_id');
                    //医生信息
                    $user_info = json_encode($cookieInfo);

                    //token
                    $token = self::returnSign($user_id);

                    $cookies = \Yii::$app->response->cookies;
                    $expire = ArrayHelper::getValue($loginArr,'1');
                    if(!$expire){
                        $expire = time()+7 * 3600;
                    }
                    //设置患者cookie
                    $cookies->add(new Cookie([
                        'name' => 'user_id',
                        'value' => $user_id,
                        'expire' => $expire,
                        'domain' => '.nisiya.net'
                    ]));
                    $cookies->add(new Cookie([
                        'name' => 'token',
                        'value' => $token,
                        'expire' => $expire,
                        'domain' => '.nisiya.net'
                    ]));
                    //已登录的 uc_login_key
                    $cookies->add(new Cookie([
                        'name' => 'uc_logined',
                        'value' => $login_key,
                        'expire' => $expire,
                        'domain' => '.nisiya.net'
                    ]));
                    $cookies->add(new Cookie([
                        'name' => 'user_info',
                        'value' => $user_info,
                        'expire' => $expire,
                        'domain' => '.nisiya.net'
                    ]));
                }
            }
            return $user_id;

        }else{
            return false;
        }
    }

    /**
     * 清除登录cookie
     * @author xiujianying
     * @date 2019/12/11
     */
    public static function removeLoginCookie()
    {
        $cookies = \Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => 'user_id',
            'value' => '',
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => 'token',
            'value' => '',
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => 'uc_logined',
            'value' => '',
            'domain' => '.nisiya.net'
        ]));
        $cookies->add(new Cookie([
            'name' => 'user_info',
            'value' => '',
            'domain' => '.nisiya.net'
        ]));
    }
}