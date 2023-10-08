<?php
/**
 *
 * @file Config.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2018-02-24
 */

namespace nisiya\ucentersdk;


class Config
{

    /** 系统配置
     * @var array
     */
    private static $config = [];
    /** 用户的登录令牌
     * @var string
     */
    private static $memberLoginToken='';

    public static function getConfig($key)
    {
        return static::$config[$key];
    }

    public static function setConfig($config)
    {
        static::$config = $config;
    }

    public static function setMemberLoginToken($memberLoginToken){
        static::$memberLoginToken = $memberLoginToken;
    }
    public static function getMemberLoginToken(){
        return static::$memberLoginToken;
    }

}