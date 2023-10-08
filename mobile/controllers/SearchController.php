<?php

namespace mobile\controllers;

use common\helpers\Url;
use common\libs\CommonFunc;
use common\models\DiseaseEsModel;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\snisiya\sRpcSdk;
use mobile\widget\WechatShareWidget;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class SearchController extends CommonController
{
    public $enableCsrfValidation = false;
    public $pagesize             = 15;
    public $maxPage              = 20;
    public $levellist;
    public $titlelist;

    public function init()
    {
        $this->levellist = CommonFunc::$level_list;
        $this->titlelist = CommonFunc::$title_list;
        parent::init();
    }

    /**
     * 搜索展示页
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-10
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionShow()
    {
        $request = Yii::$app->request;
        $keyword = $request->get('keyword', '');
        $data    = [];
        $data['has_data'] = 1;
        if (!$keyword) {
            throw new NotFoundHttpException();
        }
        $search_type_list = ['hospital', 'doctor'];//, 'disease'
        $reslist          = [];
        foreach ($search_type_list as $value) {
            sRpcSdk::getInstance()->search_list($$value, ['type' => $value, 'keyword' => $keyword, 'pagesize' => 3, 'page' => 1, 'tag' => 'span', 'class' => 'guanjianci']);
        }
        sRpcSdk::getInstance()->startAsync();
        $data['hospital_list'] = $hospital['list'] ?? [];
        $data['doctor_list']   = $doctor['list'] ?? [];
        $data['disease_list']  = $disease['list'] ?? [];
        $data['keyword']       = $keyword ?? '';
        if (!$data['hospital_list'] && !$data['doctor_list'] && !$data['disease_list']) {
            $data['has_data'] = 0;
        }
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

        $shareData = [];
        $shareData['title'] = $this->seoTitle;
        $shareData['link'] = rtrim(ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile'), '/') . Url::to(['search/show', 'keyword' => $keyword]);
        $shareData['desc'] = $this->seoDescription;
        $shareData['imgUrl'] = 'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png';
        $data['shareData'] = $shareData;

        WechatShareWidget::widget([
            'title' => $shareData['title'],
            'link' => $shareData['link'],
            'imgUrl' => $shareData['imgUrl'],
            'description' => $shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'page_title' => '搜索全部',
            'page' => '搜索全部',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        return $this->render('show', $data);
    }

    /**
     * 搜索列表页
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-10
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSo()
    {
        $request = Yii::$app->request;
        $keyword = $request->get('keyword', '');
        $page    = (int) $request->get('page', 1);
        $region  = $request->get('region', 0);
        $sanjia  = $request->get('sanjia', 0);
        $type    = $request->get('type', 'all');
        if (!$keyword && $type != 'all') {
            throw new NotFoundHttpException();
        }

        $data = [];
        $selectArr = CommonFunc::get_city_cookie();  //选择的定位
        if ($keyword) {
            $data = SnisiyaSdk::getInstance()->getSearchList(['type' => $type, 'keyword' => $keyword, 'region_pinyin' => $region, 'sanjia' => $sanjia, 'pagesize' => $this->pagesize, 'page' => $page, 'tag' => 'span', 'class' => 'guanjianci']);
        }
        $list       = $data['list'] ?? [];
        $totalCount = $data['totalCount'] ?? 0;
        $totalCount = $totalCount > 400 ? 400 : $totalCount;
        if ($page > $this->maxPage) {
            $list = [];
        }

        $regionData = $this->getRegionData();
        $regioninfo = $regionData['regioninfo'] ?? [];
        if ($region && !$regioninfo) {
            throw new NotFoundHttpException();
        }
        $province_list         = $regionData['province_list'] ?? [];
        $city_list             = $regionData['city_list'] ?? [];
        $province              = $regionData['province'] ?? [];
        $city                  = $regionData['city'] ?? [];
        $data['region']        = $region;
        $data['province']      = $province;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;

        $region_name = '全国';
        if ($province && $city) {
            $region_name = $city['name'] ?? '';
        } elseif ($province) {
            $region_name = $province['name'] ?? '';
        }

        $data['doctor_list'] =  [];
        $data['hospital_list'] =  [];
        $data['region_name']  = $region_name;
        $data['list']         = $list;
        $data['type']         = $type;
        $data['keyword']      = $keyword ?? '';
        $data['page']         = $page;
        $data['titlelist']    = $this->titlelist;
        $data['levellist']    = $this->levellist;
        $data['totalCount']   = $totalCount;
        $this->seoTitle       = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords    = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

        $shareData = [];
        $shareData['title'] = $this->seoTitle;
        if ($type == 'all') {
            $url_params = ['search/so'];
        } else {
            $url_params = ['search/so', 'type' => $type, 'keyword' => $keyword];
        }
        $shareData['link'] = rtrim(ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile'), '/') . Url::to($url_params);
        $shareData['desc'] = $this->seoDescription;
        $shareData['imgUrl'] = 'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png';
        $data['shareData'] = $shareData;
        WechatShareWidget::widget([
            'title' => $shareData['title'],
            'link' => $shareData['link'],
            'imgUrl' => $shareData['imgUrl'],
            'description' => $shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'page_title' => '搜索页面',
            'page' => '搜索页面',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        if (!$keyword) {
            $where = [];
            if ($selectArr['city_pid'] && $selectArr['city_cid']) {
                if ($selectArr['city_pid'] != '-') {
                    $where['province_id'] = $selectArr['city_pid'];
                }
                if ($selectArr['city_cid'] != '-') {
                    $where['city_id'] = $selectArr['city_cid'];
                }
            }
            $where['pagesize'] = 10;

            $cookies = \Yii::$app->request->cookies;
            $lat = $cookies->getValue('lat');
            $lon = $cookies->getValue('lon');
            $where['lat'] = $lat;
            $where['lon'] = $lon;
            // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
            $tmpLatLon = CommonFunc::gaode2BaiduGnote($lon, $lat);
            if ($tmpLatLon) {
                $where['lat'] = $tmpLatLon['lat'];
                $where['lon'] = $tmpLatLon['lng'];
            }
            $snisiyaSdk = new SnisiyaSdk();
            //$doc_res = $snisiyaSdk->getDoctorList($params);
            $hos_res = $snisiyaSdk->getIndex($where);
            $data['doctor_list'] = [];
            $data['hospital_list'] = ArrayHelper::getValue($hos_res, 'hospital.hospital_list', []);
            return $this->render('default', $data);
        }

        return $this->render('list', $data);
    }

    /**
     * 获取地区信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @return  [type]     [description]
     */
    public function getRegionData($region_local='')
    {
        $request = Yii::$app->request;
        $region  = $request->get('region', 0);
        if($region_local){
            $region = $region_local;
        }
        // $province_list = Department::district();
        $province_list = SnisiyaSdk::getInstance()->getDistrict();
        $city_list     = [];
        $province      = [];
        $city          = [];
        $regioninfo    = [];
        if ($region) {
            // $regioninfo = Department::pinyin2id($region);
            $regioninfo = SnisiyaSdk::getInstance()->getRegionInfo(['region' => $region]);
            if ($regioninfo && $regioninfo['c_id'] == 0) {
                $province  = $province_list[$regioninfo['p_id']];
                $city_list = $province['city_arr'] ?? [];
            }
            if ($regioninfo && $regioninfo['c_id'] > 0) {
                $province  = $province_list[$regioninfo['p_id']];
                $city_list = $province['city_arr'] ?? [];
                if ($city_list) {
                    foreach ($city_list as $key => $value) {
                        if ($value['id'] == $regioninfo['c_id']) {
                            $city = $value;
                            break;
                        }
                    }
                }
            }
        }

        return [
            'province_list' => $province_list,
            'city_list'     => $city_list,
            'province'      => $province,
            'city'          => $city,
            'regioninfo'    => $regioninfo,
        ];

    }

    /**
     * 搜索疾病信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-29
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionIndex()
    {
        $res         = [];
        $res['code'] = 400;
        $res['msg']  = '';
        $res['data'] = [];
        $request     = Yii::$app->request;
        if ($request->isPost && $request->isAjax) {
            $jibing      = $request->post('jibing', '');
            $search_type = $request->post('search_type', 'doctor');
            $jibing      = strip_tags(trim($jibing));
            if ($search_type == 'doctor') {
                $url_header = 'doctorlist/diseases';
            } else {
                $url_header = 'hospitallist/diseases';
            }
            $diseaseInfo = DiseaseEsModel::find()->where(['disease_keyword' => $jibing])->one();
            if ($diseaseInfo) {
                $res['code'] = 200;
                $res['data'] = ['pinyin' => $diseaseInfo['pinyin'], 'url' => Url::to([$url_header, 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => $diseaseInfo['pinyin'], 'page' => 1])];
            } else {
                $res['code'] = 400;
                $res['data'] = ['pinyin' => $diseaseInfo['pinyin'], 'url' => Url::to([$url_header, 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => $jibing, 'page' => 1])];
            }
            echo json_encode($res);die();
        } else {
            $res['code'] = 400;
            $res['data'] = ['url' => Url::to(['index/index'])];
            echo json_encode($res);die();
            throw new NotFoundHttpException();
        }

    }

}
