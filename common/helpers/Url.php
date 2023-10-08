<?php
/**
 * URL.
 * @file Url.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-25
 */

namespace common\helpers;

use common\libs\HashUrl;
use yii\helpers\ArrayHelper;

class Url extends \yii\helpers\Url
{

    /**
     * 获取静态资源的URL地址,增加版本号
     * @param $url
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-25
     * @return string
     */
    public static function getStaticUrl($url)
    {
        $url = parent::to("@static/$url");
        if (isset(\Yii::$app->params['version'])) {
            if (stripos($url, '?') !== false) {
                $url = $url . "&v=" . \Yii::$app->params['version'];
            } else {
                $url = $url . "?v=" . \Yii::$app->params['version'];
            }
        }
        return $url;
    }

    public static function getStaticUrlTwo($url)
    {
        $url = parent::to("@staticTwo/$url");
        if (isset(\Yii::$app->params['version'])) {
            if (stripos($url, '?') !== false) {
                $url = $url . "&v=" . \Yii::$app->params['version'];
            } else {
                $url = $url . "?v=" . \Yii::$app->params['version'];
            }
        }
        return $url;
    }

    /**
     * 重新url::to id自动转换加密id
     * @param string $url
     * @param bool $scheme
     * @return string
     * @author xiujianying
     * @date 2020/7/25
     */
    public static function to($url = '', $scheme = false)
    {
//        echo 12322;die();
        if (is_array($url)) {
//            echo 123;die();
            //医生控制器 医生id加密
            $pathArr = explode('/', ltrim($url[0], '/'));
            $controller = ArrayHelper::getValue($pathArr, 0);
            if ($controller == 'doctor') {
                if (isset($url['doctor_id']) && is_numeric($url['doctor_id'])) {
                    $url['doctor_id'] = HashUrl::getIdEncode($url['doctor_id']);
                }
            }

            //医院控制器 医院id加密
            if ($controller == 'hospital' || $controller == 'guahao' ) {
                if (isset($url['hospital_id']) && is_numeric($url['hospital_id'])) {
                    $url['hospital_id'] = HashUrl::getIdEncode($url['hospital_id']);
                }
            }

        }
//        var_dump($url);die();
        return parent::to($url, $scheme);
    }

}
