<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\web\ServerErrorHttpException;

class HomeCacheController extends Controller
{
    private $caches_keys = [
        'pchome' => 'wwwnisiya.top|homeCache',
    ];

    protected $url = null;
    protected $params = null;
    protected $result = null;

    public function actionHandleHomeCache($site = 'pchome', $handle = 'create')
    {
        switch ($site) {
            case 'pchome':
                $domain = Yii::$app->params['cacheDomain']['pchome'];
                $cache_key = $this->caches_keys['pchome'];
                $res = $this->handleCache($domain, $cache_key, $handle);
                echo $res;
        }
    }

    public function handleCache($domain, $cache_key, $handle)
    {
        $time = date('Y-m-d H:i:s', time());
        $params['flush'] = 'flushCacheByHospitalDoctor';
        $domain = $domain . '?' . http_build_query($params);
        $redis = \Yii::$app->redis_codis;
        switch ($handle) {
            case 'create':
                $dom = $this->curl($domain, $params);
                $setRedisCache = $redis->set($cache_key, json_encode($dom));
                if ($setRedisCache) {
                    $redis->expire($cache_key, \Yii::$app->params['homeCacheTime']);
                }
                return $time . '成功创建首页缓存' . "\n";
            case 'delete':
                $haved_cache = $redis->EXISTS($cache_key);
                $delete_status = 0;
                if ($haved_cache) {
                    $delete_status = $redis->DEL($cache_key);
                }
                return $time . '成功删除首页缓存' . "\n";
        }
    }

    public function curl($url = '', $params = ['flush' => 1], $type = 'get')
    {
        $timeout = YII_ENV == 'prod' ? '5' : '30';
        $this->url = $url;
        $this->params = $params;
        $this->result = null;
        $startime = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // echo $url."\n";
        // die;
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $this->result = curl_exec($ch);
        $error = '';
        if (curl_errno($ch)) {
            $this->curlError(curl_error($ch), curl_errno($ch));
            $error = curl_error($ch);
            throw new ServerErrorHttpException();
        }
        curl_close($ch);

        $logData = [
            'error' => $error,
            'usetime' => microtime(true) - $startime,
            'url' => $url,
            'origindata' => $this->result,
//            'returndata' => $data
        ];
        return $this->result;
    }

    /**
     * curl 错误处理
     * @param   $e
     */
    public function curlError($errCode, $errMsg)
    {
        $errors = sprintf(
            "cURL Error:\nCode: %s\nMessage: %s\n",
            $errCode,
            $errMsg
        );
        //$this->log($errors);
    }
}
