<?php
/**
 * 首页
 * @file IndexController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-10
 */

namespace pc\controllers;

use yii\web\Controller;
use common\models\DoctorEsModel;
use common\models\BaseDoctorHospitals;
use common\sdks\question\QuestionSdk;
use common\sdks\BapiAdSdkModel;
use common\models\Department;
use common\libs\CommonFunc;
use common\models\HospitalEsModel;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\snisiya\sRpcSdk;
use GuzzleHttp\Client;

class IndexController extends CommonController
{
    private $pchome_cache_key = 'wwwnisiya.top|homeCache';

    /**
     * 首页
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-25
     * @return string
     */
    public function actionIndex()
    {
        $type = php_sapi_name();
        $home_cache = \Yii::$app->redis_codis->get($this->pchome_cache_key);
        $flush = \Yii::$app->request->get('flush', '');
        if ($type != 'cli' && $home_cache && $flush != 'flushCacheByHospitalDoctor') {
            return json_decode($home_cache, true);
        }
        if ($type == 'cli' || !$home_cache || $flush == 'flushCacheByHospitalDoctor') {


        //首页轮播
        $badSdkModel = new BapiAdSdkModel();
        $lunbo = $badSdkModel->getPcLunBo(10, '126', '医院挂号首页', '轮播图');
        $lunbo = $lunbo ?? [];//顶部轮播图

        $queList = $this->getQuestion();//问答
        $docList = $this->getDocByKeshi();//医生
        $hosList = $this->getHosByKeshi();//医院
        $keshi = $this->getKeshi();

        $this->seoTitle = "网上预约挂号,在线咨询医生,就医挂号服务平台,王氏医生";
        $this->seoKeywords = "网上预约挂号,在线咨询医生,就医挂号服务平台,王氏医生";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

        $data = [
            'ques_list' => $queList['list']??[],
            'doc_list' => $docList,
            'lunbo' => $lunbo,
            'keshi' => $keshi,
            'hos_list' => $hosList,
        ];

        $return = $this->render('index', $data);
        //设置缓存
        $redis = \Yii::$app->redis_codis;
        $setRedisCache = $redis->set($this->pchome_cache_key, json_encode($return));
        return $return;
        }
    }

    /**
     * 首页问答
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/31
     */
    public function getQuestion()
    {
        $params = [
            'fkid' => 0,
            'skid' => 0,
            'orderField' => 'create_time',
            'page' => 1,
            'pageSize' => 15,
            'keshi_limit' => 0, //科室条数
        ];
        $list = QuestionSdk::getInstance()->asklistes($params);
        return $list;
    }

    /**
     * 首页科室医生
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/31
     */
    public function getDocByKeshi()
    {
        $keshi = [
            '内科' => 1, '外科' => 2, '妇产科' => 3,'男科' => 4, '生殖健康' => 5, '眼科' => 58, '儿科' => 6, '五官科' => 7,
            '肿瘤科' => 8, '皮肤性病科' => 9, '精神心理科' => 10,
        ];
        //$docModel = new DoctorEsModel();
        $snisiyaSdk = new SnisiyaSdk();
        foreach ($keshi as $k => $v) {
            if ($v < 29) {
                $docList[$v] = $snisiyaSdk->getDoctorList(['fkid' => $v, 'page' => 1, 'pagesize' => 6])['doctor_list']??[];
            } else {
                $docList[$v] = $snisiyaSdk->getDoctorList(['skid' => $v, 'page' => 1, 'pagesize' => 6])['doctor_list']??[];
            }
        }

        return $docList ?? [];
    }

    /**
     * 首页科室医院
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/31
     */
    public function getHosByKeshi()
    {
        $keshi = [
            '内科' => 30, '外科' => 53, '妇产科' => 3, '男科' => 4, '生殖健康' => 5, '眼科' => 58,'儿科' => 56, '五官科' => 61,
            '肿瘤科' => 8, '皮肤性病科' => 63, '精神心理科' => 65,
        ];

        $snisiyaSdk = new SnisiyaSdk();
        foreach ($keshi as $k => $v) {
            $docList[$k] = $snisiyaSdk->getHospitalList(['type' => 'keshi', 'sanjia' => 2, 'relation_id' => $v, 'page' => 1, 'pagesize' => 12])['hospital_list']??[];
        }
        return $docList ?? [];
    }

    /**
     * 首页科室
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/31
     */
    public function getKeshi()
    {
        $keshi = [
            '心血管' => 1, '妇科' => 3, '儿科' => 6, '外科' => 2, '口腔科' => 7,
        ];
        foreach ($keshi as $k => $v) {
            $keshi_item[$k] = CommonFunc::getKeshiInfo($v);

        }

        return $keshi_item;

    }

}

?>
