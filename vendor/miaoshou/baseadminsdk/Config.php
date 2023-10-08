<?php
/**
 * 配置类
 */

namespace nisiya\baseadminsdk;

class Config
{

    /** 系统配置
     * @var array
     */
    private static $config = [];


    /**
     * 获取配置信息
     *
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public static function getConfig($key, $default = '')
    {
        return isset(static::$config[$key]) ? static::$config[$key] : $default;
    }

    /**
     * 设置配置
     *
     * @param $config
     */
    public static function setConfig($config)
    {
        static::$config = $config;
    }
}
