<?php
/**
 * 配置信息
 * @file Config.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk;

class Config
{

    /** 系统配置
     * @var array
     */
    private static $config = [];
    /**
     * @var array 域名HOST
     */
    private static $apiHost = [
        'dev' => [
            'askapi' => 'http://test.askapi.nisiya.net',
            'vapi'  => 'http://test.vapi.nisiya.net',
            'artapi' =>  'http://test.artapi.nisiya.net',
            'bapi' => 'http://test.bapi.nisiya.net',
            'newsapi' => 'http://test.newsapi.nisiya.top',
            'cooperation' => 'http://test.cooperation.nisiya.net',
            'openim' => 'http://test.openim.nisiyaapi.com',
            'jiancha' => 'http://test.jcapi.nisiya.net',
        ],
        'test' => [
            'askapi' => 'http://test.askapi.nisiya.net',
            'vapi'  => 'http://test.vapi.nisiya.net',
            'artapi' =>  'http://test.artapi.nisiya.net',
            'bapi' => 'http://test.bapi.nisiya.net',
            'newsapi' => 'http://test.newsapi.nisiya.top',
            'cooperation' => 'http://test.cooperation.nisiya.net',
            'openim' => 'http://test.openim.nisiyaapi.com',
            'jiancha' => 'http://test.jcapi.nisiya.net',
        ],
        'prod' => [
            'askapi' => 'http://askapi.nisiya.net',
            'vapi'  => 'http://vapi.nisiya.net',
            'artapi' =>  'http://artapi.nisiya.net',
            'bapi' => 'http://bapi.nisiya.net',
            'newsapi' => 'http://newsapi.nisiya.top',
            'cooperation' => 'http://cooperation.nisiya.net',
            'openim' => 'http://openim.nisiyaapi.com',
            'jiancha' => 'http://jcapi.nisiya.net',
        ],
    ];

    /**
     * 设置配置信息
     * @param $config
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     */
    public static function setConfig($config)
    {
        static::$config = $config;
    }

    /**
     * 获取配置信息
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * 获取域名
     * @param string $key
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return mixed
     */
    public static function getHostName($key)
    {
        return isset(self::$apiHost[self::$config['env']][$key]) ? self::$apiHost[self::$config['env']][$key] : '';
    }

}