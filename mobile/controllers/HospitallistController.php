<?php
/**
 * 医生列表页
 * @author lixiaolong
 * @date    2020-08-10
 * @version 1.0
 * @return  [type]     [description]
 */

namespace mobile\controllers;

use common\libs\CommonFunc;
use common\models\Department;
use common\models\HospitalEsModel;
use common\models\DiseaseEsModel;
use common\models\DiseaseModel;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\HttpSdk;
use mobile\widget\WechatShareWidget;
use common\sdks\snisiya\sRpcSdk;
use GuzzleHttp\Client;
use Yii;
use yii\data\Pagination;
use \common\helpers\Url;
use yii\helpers\ArrayHelper;

class HospitallistController extends CommonController
{
    public $pagesize = 20;
    public $maxPage  = 20;
    public $hos_type = [
        0 => '全部',
        1 => '公立',
        2 => '社会办医'
    ];
    public $hosLevel;

    public function init()
    {
        $this->pagesize =  CommonFunc::PAGE_SIZE;
        parent::init();
        $this->seoTitle        = "【全国医院排行榜】大全、预约挂号、哪家好 全国医院统一挂号平台 – 王氏医生";
        $this->seoKeywords     = "全国医院排行榜，全国医院大全，全国医院预约挂号，全国医院哪家好，全国医院统一挂号平台，全国医院在线挂号";
        $this->seoDescription  = "王氏医生为您提供全国医院大全，医院排行榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用全国医院统一挂号平台，找到合适的医生，挂上医院专家号";
        $this->hosLevel = CommonFunc::getHospitalLevel();
    }

    /**
     * 医生列表首页
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-27
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionIndex()
    {
        if($this->getUserAgent() == 'patient')
        {
            $this->seoTitle        = '按医院';
        }
        $request       = Yii::$app->request;
        $region        = $request->get('region');
        $sanjia        = $request->get('sanjia', 0);
        $hos_type      = $request->get('hostype', 0);//公立 私立
        $page          = $request->get('page', 1);
        $data          = [];
        $region = $region??0;


        //存储地区
        if($region){
            CommonFunc::auto_dingwei($region);
        }else{
            if($region==='0'){
                CommonFunc::city_cookie();
            }else {
                $selectArr = CommonFunc::get_city_cookie();
                if (ArrayHelper::getValue($selectArr, 'pinyin')) {
                    $region = ArrayHelper::getValue($selectArr, 'pinyin');
                }
            }
        }

        $regionData    = $this->getRegionData($region);
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];
        $hosType       = !empty($this->hos_type[$hos_type]) && $hos_type != 0 ? $this->hos_type[$hos_type]:0;
        $sanjiaStr     = !empty($this->hosLevel[$sanjia]) && $sanjia != 0 ?$this->hosLevel[$sanjia]:0;
        //经纬度
        $cookies = \Yii::$app->request->cookies;
        $lat = $cookies->getValue('lat');
        $lon = $cookies->getValue('lon');

        $docData       = $this->getHospitallist('region', $region, $sanjia, 0, $page,$hos_type != 0?$hosType:'',$lat,$lon);
        $hospital_list = $docData['hospital_list'] ?? [];
        $totalCount    = $docData['totalCount'] ?? 0;
        $data['region']        = $region??0;
        $data['province']      = $province;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['sanjiaStr']     = $sanjiaStr;
        $data['hos_type']      = $hos_type;
        $data['hosTypeStr']      = $hosType;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['hospital_list'] = $hospital_list;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        $data['ua'] = $this->getUserAgent();
        $data['autoArr'] = CommonFunc::get_city_cookie(true); //自动定位

        $shareData = [
            'title'=>$this->seoTitle,
            'link'=> rtrim(ArrayHelper::getValue(\Yii::$app->params,'domains.mobile'),'/').Url::to(['hospitallist/index', 'region' => $province['pinyin'] ?? 0, 'sanjia' => $sanjia,'hostype'=>$hos_type, 'page' => 1]),
            'desc'=>$this->seoDescription,
            'imgUrl'=>'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png',
        ];

        //埋点数据处理
        $eventParam = [
            'page_title' => '医院排行',
            'page' => '医院排行',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        WechatShareWidget::widget([
            'title'=>$shareData['title'],
            'link'=>$shareData['link'],
            'imgUrl'=>$shareData['imgUrl'],
            'description'=>$shareData['desc'],
        ]);

        return $this->render('index', $data);
    }

    /**
     * 按照科室找医院
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-27
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDepartment()
    {
        if($this->getUserAgent() == 'patient')
        {
            $this->seoTitle        = '按科室';
        }
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $keshi_id = $request->get('keshi_id', 0);
        $sanjia   = $request->get('sanjia', 0);
        $hos_type = $request->get('hostype', 0);//公立 私立
        $page     = $request->get('page', 1);
        $data          = [];
        $region = $region??0;
        $regionData    = $this->getRegionData();
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];

        $skeshi_list = [];
        $fkeshi_info = [];
        $skeshi_info = [];
        // $fkeshi_list = Department::department();
        $fkeshi_list = SnisiyaSdk::getInstance()->department();
        if ($keshi_id) {
            $keshi_item = CommonFunc::getKeshiInfo($keshi_id);
            if ($keshi_item && $keshi_item['parent_id'] == 0) {
                $fkeshi_info = [
                    'department_id'   => $keshi_item['department_id'],
                    'department_name' => $keshi_item['department_name'],
                ];
                $skeshi_list = $keshi_item['second_arr'] ?? [];
            }

            if ($keshi_item && $keshi_item['parent_id'] > 0) {
                $parent_item = CommonFunc::getKeshiInfo($keshi_item['parent_id']);
                $fkeshi_info = [
                    'department_id'   => $parent_item['department_id'],
                    'department_name' => $parent_item['department_name'],
                ];
                $skeshi_list = $parent_item['second_arr'] ?? [];
                $skeshi_info = $keshi_item;
            }

        }
        //经纬度
        $cookies = \Yii::$app->request->cookies;
        $lat = $cookies->getValue('lat');
        $lon = $cookies->getValue('lon');

        $hosType = !empty($this->hos_type[$hos_type]) && $hos_type != 0 ? $this->hos_type[$hos_type]:0;
        $sanjiaStr     = !empty($this->hosLevel[$sanjia]) && $sanjia != 0?$this->hosLevel[$sanjia]:0;
        //$docData       = $this->getHospitallist('keshi', $region, $sanjia, $keshi_id, $page,$hos_type != 0?$hosType:'',$lat,$lon);
        //最新科室找医院 科室下对应医生医院
        $docData = $this->actionGetHosByDepartment($region, $sanjia, $keshi_id, $page,$hos_type != 0?$hosType:'',$lat,$lon);
        $hospital_list = $docData['hospital_list'] ?? [];
        $totalCount    = $docData['totalCount'] ?? 0;

        $data['province']      = $province;
        $data['region']        = $region??0;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['sanjiaStr']     = $sanjiaStr;
        $data['hos_type']      = $hos_type;
        $data['hosTypeStr']    = $hosType;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['fkeshi_list']   = $fkeshi_list;
        $data['skeshi_list']   = $skeshi_list;
        $data['fkeshi_info']   = $fkeshi_info;
        $data['skeshi_info']   = $skeshi_info;
        $data['keshi_id']      = $keshi_id;
        $data['hospital_list'] = $hospital_list;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        $data['ua'] = $this->getUserAgent();

        $shareData = [
            'title'=>$this->seoTitle,
            'link'=> rtrim(ArrayHelper::getValue(\Yii::$app->params,'domains.mobile'),'/').Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'hostype'=>$hos_type,'page'=>1]),
            'desc'=>$this->seoDescription,
            'imgUrl'=>'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png',
        ];
        //埋点数据处理
        $eventParam = [
            'page_title' => '科室找医院列表',
            'page' => '科室找医院列表',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        WechatShareWidget::widget([
            'title'=>$shareData['title'],
            'link'=>$shareData['link'],
            'imgUrl'=>$shareData['imgUrl'],
            'description'=>$shareData['desc'],
        ]);

        return $this->render('department', $data);
    }

    /**
     * 疾病列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDiseases()
    {
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $diseases = $request->get('diseases', 0);
        $sanjia   = $request->get('sanjia', 0);
        $dspinyin   = $request->get('dspinyin', 0);
        $page     = $request->get('page', 1);
        $data     = [];

        $regionData    = $this->getRegionData();
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];
        if ($dspinyin) {
            $disease_id = 0;
            $diseaseInfo          = DiseaseEsModel::find()->where(['pinyin'=>$dspinyin])->one();
            if ($diseaseInfo) {
                $disease_id = $diseaseInfo['disease_id'];
                $docData       = $this->getHospitallist('dspinyin', $region, $sanjia, $disease_id, $page);
            }
            
        }else{
            $docData       = $this->getHospitallist('diseases', $region, $sanjia, $diseases, $page);
        }
        //获取首字母对应的疾病
        if($diseases == '0')
        {
            $diseaseListArr = DiseaseEsModel::find()->where(['initial'=>'a'])->limit(100)->asArray()->all();
        }else{
            $diseaseListArr = DiseaseEsModel::find()->where(['initial'=>$diseases])->limit(100)->asArray()->all();
        }

        $hospital_list = $docData['hospital_list'] ?? [];
        $totalCount    = $docData['totalCount'] ?? 0;

        $data['province']      = $province;
        $data['region']        = $region;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['diseases']      = $diseases;
        $data['search_disease_name']      = $diseaseInfo['disease_name'] ?? '';
        $data['hospital_list'] = $hospital_list;
        $data['dspinyin'] = $dspinyin;
        $data['diseases_list']      = $diseaseListArr;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        return $this->render('diseases', $data);
    }

    /**
     *Notes:科室找医院科室列表页面
     *User:lixiaolong
     *Date:2020/12/31
     *Time:11:18
     */
    public function actionDepartmentList()
    {
        if($this->getUserAgent() == 'patient')
        {
            $this->seoTitle        = '选择科室';
        }
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $data          = [];

         //存储地区
        if($region){
            CommonFunc::auto_dingwei($region);
        }else{
            if($region==='0'){
                CommonFunc::city_cookie();
            }else {
                $selectArr = CommonFunc::get_city_cookie();
                if (ArrayHelper::getValue($selectArr, 'pinyin')) {
                    $region = ArrayHelper::getValue($selectArr, 'pinyin');
                }
            }
        }
        $regionData    = $this->getRegionData($region);
        // $regionData    = $this->getRegionData();
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];

        $skeshi_list = [];
        $fkeshi_info = [];
        $skeshi_info = [];
        $fkeshi_list = SnisiyaSdk::getInstance()->department();

        $config_keshi_list = SnisiyaSdk::getInstance()->configDepartment();

        $data['province']      = $province;
        $data['region']        = $region;
        $data['city']          = $city;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['fkeshi_list']   = $fkeshi_list;
        $data['config_keshi_list']   = $config_keshi_list;
        $data['ua'] = $this->getUserAgent();
        //埋点数据处理
        $eventParam = [
            'page_title' => '科室列表',
            'page' => '科室列表',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        return $this->render('department_list', $data);
    }


    /**
     * 获取地区信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @return  [type]     [description]
     */
    public function getRegionData($region='')
    {
        $request       = Yii::$app->request;
        if(!$region) {
            $region = $request->get('region', 0);
        }
        // $province_list = Department::district();
        $province_list = SnisiyaSdk::getInstance()->getDistrict();
        $city_list     = [];
        $province      = [];
        $city          = [];
        if ($region) {
            // $regioninfo = Department::pinyin2id($region);
            $regioninfo = SnisiyaSdk::getInstance()->getRegionInfo(['region'=>$region]);
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
        ];

    }

    /**
     * 获取医院列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @param   string     $type        [description]
     * @param   integer    $region_id   [description]
     * @param   string     $relation_id [description]
     * @param   integer    $page        [description]
     * @return  [type]                  [description]
     */
    public function getHospitallist($type = 'region', $region_pinyin = '', $sanjia = 0, $relation_id = '', $page = 1,$hos_type = 0,$lat = '',$lon = '')
    {
        $params = [];
        $params['type'] = $type;
        $params['region_pinyin'] = $region_pinyin;
        $params['sanjia'] = $sanjia;
        $params['relation_id'] = $relation_id;
        $params['page'] = $page;
        $params['pagesize'] = $this->pagesize;
        // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
        $tmpLatLon = CommonFunc::gaode2BaiduGnote($lon, $lat);
        if ($tmpLatLon) {
            $lat = $tmpLatLon['lat'];
            $lon = $tmpLatLon['lng'];
        }
        $params['lat'] = $lat;
        $params['lon'] = $lon;
        $params['hospital_kind'] = $hos_type;
        $snisiyaSdk = new SnisiyaSdk();
        $res = $snisiyaSdk->getHospitalList($params);
        $hospital_list = $res['hospital_list'] ?? [];
        if ($hospital_list) {
            foreach ($hospital_list as &$value) {
                if (isset($value['hospital_logo']) && !empty($value['hospital_logo'])) {
                    $value['hospital_photo'] = $value['hospital_logo'] ?? '';
                }
            }
        }
        $totalCount = $res['totalCount'] ?? 0;
        return [
            'hospital_list' => $hospital_list,
            'totalCount'    => $totalCount,
        ];
    }    

    public function getHospitallist2($type = '', $region_pinyin = '', $sanjia = 0, $relation_id = '', $page = 1)
    {
        $regioninfo              = [];
        $region_key              = '';
        $region_id               = '';
        $where                   = [];
        $where['bool']['must']   = [];
        $where['bool']['must'][] = [
            'term' => [
                'hospital_kind' => '公立',
            ],
        ];        
        $where['bool']['must'][] = [
            'range' => [
                'hospital_level_num' => [
                    'gt' => 0//查医生数据
                ]
            ]
        ];
        $has_child['has_child']['type']  = 'doctor';
        $has_child['has_child']['query'] = [];
        if ($region_pinyin) {
            $regioninfo = Department::pinyin2id($region_pinyin);
            if ($regioninfo) {
                if ($regioninfo['c_id'] == 0) {
                    $region_key = 'province_id';
                    $region_id  = $regioninfo['p_id'];
                } else {
                    $region_key = 'city_id';
                    $region_id  = $regioninfo['c_id'];
                }
            }
        }
        if ($region_id) {
            if ($region_key == 'province_id') {
                $where['bool']['must'][] = [
                    'term' => [
                        'province_id' => $region_id,
                    ],
                ];
            } else {
                $where['bool']['must'][] = [
                    'term' => [
                        'city_id' => $region_id,
                    ],
                ];
            }
        }

        if ($sanjia) {
            $where['bool']['must'][] = [
                'term' => [
                    'hospital_level_num' => (int)$sanjia,
                ],
            ];
        }

        if ($relation_id) {
            if ($type == 'keshi') {
                $keshi_item = CommonFunc::getKeshiInfo($relation_id);
                if ($keshi_item && $keshi_item['parent_id'] == 0) {
                    $has_child['has_child']['query'][]['term'] = [
                        'doctor_frist_department_id' => $relation_id,
                    ];
                } else {
                    $has_child['has_child']['query'][]['term'] = [
                        'doctor_second_department_id' => $relation_id,
                    ];
                }
            }elseif ($type == 'diseases') {
                $has_child['has_child']['query'][]['match'] = [
                        'doctor_disease_initial' => $relation_id,
                    ];
            }elseif ($type == 'dspinyin') {
                $has_child['has_child']['query'][]['match'] = [
                        'doctor_disease_id' => $relation_id,
                    ];
            }
        }

        if ($has_child['has_child']['query']) {
            $where['bool']['must'][] = $has_child;
        }
        $page   = $page <= $this->maxPage ? $page : $this->maxPage;
        $offset = max(0, ($page - 1)) * $this->pagesize;
        $model  = new HospitalEsModel();

        $hospital_list = $model->find()->where([])->query($where)->offset($offset)->limit($this->pagesize)->orderBy('hospital_level_num asc,hospital_id asc')->all();
        $totalCount    = $model->find()->where([])->query($where)->count();
        return [
            'hospital_list' => $hospital_list,
            'totalCount'    => $totalCount,
        ];
    }

    /**
     *Notes:加载更多
     *User:lixiaolong
     *Date:2020/8/10
     *Time:14:33
     * @return string|null
     */
    public function actionAjaxlist()
    {
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $keshi_id = $request->get('keshi_id', 0);
        $diseases = $request->get('diseases', 0);
        $sanjia   = $request->get('sanjia', 0);
        $dspinyin   = $request->get('dspinyin', 0);
        $page     = $request->get('page', 1);
        $type     = $request->get('type', 1);
        $data     = [];
        if($type == 1)
        {
            $docData       = $this->getHospitallist('region', $region, $sanjia, 0, $page);
        }elseif ($type == 2)
        {
            $docData       = $this->getHospitallist('keshi', $region, $sanjia, $keshi_id, $page);
        }elseif ($type == 3)
        {
            if ($dspinyin) {
                $disease_id = 0;
                $diseaseInfo          = DiseaseEsModel::find()->where(['pinyin'=>$dspinyin])->one();
                if ($diseaseInfo) {
                    $disease_id = $diseaseInfo['disease_id'];
                    $docData       = $this->getHospitallist('dspinyin', $region, $sanjia, $disease_id, $page);
                }

            }else{
                $docData       = $this->getHospitallist('diseases', $region, $sanjia, $diseases, $page);
            }
        }
        if ($docData['hospital_list']) {
            return $this->getHtmlStr($docData['hospital_list'],$page);
        }
        return null;
    }

    public function getHtmlStr($list,$page)
    {
        $str = '';
        if (isset($list) && count($list) > 0) {
            $rankNum = 20*($page - 1);
            foreach ($list as $key => $value) {
                $rankNum++;
                $str .= '<li>
                        <a href="'. Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]).'">
                            <div class=item>
                                <div class=detail>
                                    <div class=detailImg>
                                        <img src="'.$value['hospital_photo'].'" onerror="this.src='.$value['hospital_photo'].'" alt="'.$value['hospital_name'].'">
                                    </div>
                                    <div class="detailContent detailContent2">
                                        <p class=detailScipe>'.$value['hospital_name'].'</p>
                                        <div class=detailContentLabel>';
                                        if($value['hospital_level'])
                                        {
                                            $str .= '<span>'.$value['hospital_level'].'</span>';
                                        }
                                        if($value['hospital_type'])
                                        {
                                            $str .= '<span>'.$value['hospital_type'].'</span>';
                                        }
                                        if($value['hospital_kind'])
                                        {
                                            $str .= '<span>'.$value['hospital_kind'].'</span>';
                                        }
                                        $str .= '</div>
                                        <p>地址：'.$value['hospital_address'].'</p>

                                    </div>

                                </div>
                                <div class=grade>
                                    <p>评分：</p>
                                    <span>8.9</span>
                                </div>
                            </div>
                        </a>
                        <div class=rankNum>
                            <span><i>'. $rankNum .'</i></span>
                        </div>
                    </li>';
            }
        }

        return $str;
    }

    /**
     *Notes:获取一级地区的二级地区
     *User:lixiaolong
     *Date:2020/8/10
     *Time:15:41
     * @return mixed|string
     */
    public function actionAjaxregion()
    {
        $request       = Yii::$app->request;
        $region        = $request->get('region', 0);
        $regionData    = $this->getRegionData();
        if($regionData['city_list'])
        {
            $arr = [
                [
                    'id' => '',
                    'name' => '不限',
                    'parentid' => '',
                    'code' => '',
                    'order' => 1,
                    'parentcode' => '',
                    'suffix' =>'',
                    'pinyin' => $region,
                ]

            ];
            $arr_region = array_merge($arr,$regionData['city_list']);
            return json_encode($arr_region);
        }else{
            return '';
        }

    }

    /**
     *Notes:ajax获取一级科室的二级科室
     *User:lixiaolong
     *Date:2020/8/10
     *Time:17:55
     */
    public function actionAjaxkeshi()
    {
        $request  = Yii::$app->request;
        $keshi_id = $request->get('keshi_id', 0);
        $keshi_item = CommonFunc::getKeshiInfo($keshi_id);
        if ($keshi_item && $keshi_item['parent_id'] == 0) {
            $skeshi_list = $keshi_item['second_arr'] ?? [];
        }

        if ($keshi_item && $keshi_item['parent_id'] > 0) {
            $parent_item = CommonFunc::getKeshiInfo($keshi_item['parent_id']);
            $skeshi_list = $parent_item['second_arr'] ?? [];
        }
        if(!empty($skeshi_list))
        {
            return json_encode($skeshi_list);
        }else{
            return json_encode('');
        }
    }

    /**
     *Notes:获取首字母对应的疾病
     *User:lixiaolong
     *Date:2020/8/11
     *Time:14:17
     */
    public function actionAjaxgetdisease()
    {
        $request  = Yii::$app->request;
        $diseases = $request->get('diseases', 0);
        if($diseases == '0')
        {
            $diseaseListArr = DiseaseEsModel::find()->where(['initial'=>'a'])->limit(100)->asArray()->all();
        }else{
            $diseaseListArr = DiseaseEsModel::find()->where(['initial'=>$diseases])->limit(100)->asArray()->all();
        }
        if(!empty($diseaseListArr))
        {
            return json_encode($diseaseListArr);
        }else{
            return '';
        }
    }

    /**
     *Notes:通过科室查找医院
     *User:lixiaolong
     *Date:2021/1/4
     *Time:17:24
     * @param $region 地区选择地区拼音
     * @param $sanjia 医院等级
     * @param $keshi_id 科室id
     * @param $page    页码
     * @param $hos_type 医院属性 公立 私立
     * @param $lat
     * @param $lon
     * @return array
     */
    public function actionGetHosByDepartment($region, $sanjia, $keshi_id, $page,$hos_type,$lat,$lon)
    {
        $params = [];
        $params['region_pinyin'] = $region;
        $params['sanjia'] = $sanjia;

        if ($keshi_id) {
            $keshi_item = CommonFunc::getKeshiInfo($keshi_id);
            if ($keshi_item && $keshi_item['parent_id'] == 0) {
                $params['fkid'] = $keshi_id;
            }

            if ($keshi_item && $keshi_item['parent_id'] > 0) {
                $params['skid'] = $keshi_id;
            }

        }
        $params['page'] = $page;
        $params['pagesize'] = $this->pagesize;
        $params['lat'] = $lat;
        $params['lon'] = $lon;

        // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
        $tmpLatLon = CommonFunc::gaode2BaiduGnote($lon, $lat);
        if ($tmpLatLon) {
            $params['lat'] = $tmpLatLon['lat'];
            $params['lon'] = $tmpLatLon['lng'];
        }

        $params['hospital_kind'] = $hos_type;
        $snisiyaSdk = new SnisiyaSdk();
        $res = $snisiyaSdk->getListByDepartment($params);
        $hospital_list = $res['hospital_list'] ?? [];
        if ($hospital_list) {
            foreach ($hospital_list as &$value) {
                if ($value['hospital_logo']) {
                    $value['hospital_photo'] = $value['hospital_logo'];
                }
            }
        }
        $totalCount = $res['totalCount'] ?? 0;
        return [
            'hospital_list' => $hospital_list,
            'totalCount'    => $totalCount,
        ];
    }

}
