<?php
/**
 * 配置类
 * @file Config.php
 * @author niewei <niewei@yuanxin-inc.com>
 * @version 2.0
 * @date 2021-09-29
 */

namespace nisiya\baseapisdk;

class Config
{

    /** 系统配置
     * @var array
     */
    private static $config = [];


    /**
     * 获取配置信息
     * @param string $key 配置键
     * @param string $default 默认值
     * @return mixed
     * @author niewei
     * @date 2021/9/29 15:59
     */
    public static function getConfig($key, $default = '')
    {
        return isset(static::$config[$key]) ? static::$config[$key] : $default;
    }

    /**
     * 设置配置
     * @param array $config
     * @author niewei
     * @date 2021/9/29 15:59
     */
    public static function setConfig($config)
    {
        static::$config = $config;
    }
}