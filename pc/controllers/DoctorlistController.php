<?php
/**
 * 医生列表页
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2020-07-27
 * @version 1.0
 * @return  [type]     [description]
 */

namespace pc\controllers;

use common\libs\CommonFunc;
use common\models\Department;
use common\models\DiseaseEsModel;
use common\models\DoctorEsModel;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;

class DoctorlistController extends CommonController
{
    public $pagesize  = 20;
    public $maxPage   = 20;
    public $titlelist;

    public function init()
    {
        $this->titlelist = CommonFunc::$title_list;
        parent::init();
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
        $request       = Yii::$app->request;
        $region        = $request->get('region', 0);
        $sanjia        = $request->get('sanjia', 0);
        $page          = $request->get('page', 1);
        $data          = [];
        $regionData    = $this->getRegionData();
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];

        $docData               = $this->getDoctorlist('region', $region, $sanjia, 0, $page);
        $doctorlist            = $docData['doctorlist'] ?? [];
        $totalCount            = $docData['totalCount'] ?? 0;
        $data['region']        = $region;
        $data['province']      = $province;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['doctorlist']    = $doctorlist;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);

        $seoTitle       = "全国哪家医院最好_全国医院排名_王氏医生";
        $seoKeywords    = "全国医院医生在线咨询,全国专家在线咨询,全国医生网上预约挂号,全国医生排行榜";
        $seoDescription = "王氏医生为您提供全国医院医生大全，医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看医生，在线预约电话咨询，找到合适的医生专家。";
        $dengji_name    = '';
        $region_name    = '全国';
        if ($province && $city) {
            $region_name = $city['name'] ?? '';
        } elseif ($province) {
            $region_name = $province['name'] ?? '';
        }
        if ($sanjia) {
            $dengji_name = $this->titlelist[$sanjia] ?? '';
        }
        if ($region_name || $dengji_name) {
            $seoTitle       = "{$region_name}医院专家_{$dengji_name}医生在线咨询_预约挂号_哪个好_{$region_name}专家排名_王氏医生";
            $seoKeywords    = "{$region_name}医院{$dengji_name}医生在线咨询,{$region_name}专家在线咨询,{$region_name}{$dengji_name}医生网上预约挂号,{$region_name}{$dengji_name}医生排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}医院{$dengji_name}医生大全，{$dengji_name}医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看{$dengji_name}医生，在线预约电话咨询，找到合适的{$dengji_name}医生专家。";

        }
        $this->seoTitle       = $seoTitle;
        $this->seoKeywords    = $seoKeywords;
        $this->seoDescription = $seoDescription;
        return $this->render('index', $data);
    }

    /**
     * 按照科室找医生
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-27
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDepartment()
    {
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $keshi_id = $request->get('keshi_id', 0);
        $sanjia   = $request->get('sanjia', 0);
        $page     = $request->get('page', 1);

        $data          = [];
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

        $docData    = $this->getDoctorlist('keshi', $region, $sanjia, $keshi_id, $page);
        $doctorlist = $docData['doctorlist'] ?? [];
        $totalCount = $docData['totalCount'] ?? 0;

        $data['province']      = $province;
        $data['region']        = $region;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['fkeshi_list']   = $fkeshi_list;
        $data['skeshi_list']   = $skeshi_list;
        $data['fkeshi_info']   = $fkeshi_info;
        $data['skeshi_info']   = $skeshi_info;
        $data['keshi_id']      = $keshi_id;
        $data['doctorlist']    = $doctorlist;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);

        $seoTitle       = "全国哪家医院最好_全国医院排名_王氏医生";
        $seoKeywords    = "全国医院医生在线咨询,全国专家在线咨询,全国医生网上预约挂号,全国医生排行榜";
        $seoDescription = "王氏医生为您提供全国医院医生大全，医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看医生，在线预约电话咨询，找到合适的医生专家。";
        $dengji_name = '';
        $region_name = '全国';
        $keshi_name = '';
        if ($province && $city) {
            $region_name = $city['name'] ?? '';
        } elseif ($province) {
            $region_name = $province['name'] ?? '';
        }
        if ($sanjia) {
            $dengji_name = $this->levellist[$sanjia] ?? '';
        }
        if ($fkeshi_info && $skeshi_info) {
            $keshi_name = $skeshi_info['department_name'] ?? '';
        }elseif ($fkeshi_info) {
            $keshi_name = $fkeshi_info['department_name'] ?? '';
        }
        if ($region_name || $dengji_name || $keshi_name) {
            $seoTitle       = "{$region_name}{$keshi_name}医院专家_{$dengji_name}医生在线咨询_预约挂号_哪个好_{$region_name}{$keshi_name}专家排名_王氏医生";
            $seoKeywords    = "{$region_name}{$keshi_name}医院{$dengji_name}医生在线咨询,{$region_name}{$keshi_name}专家在线咨询,{$region_name}{$keshi_name}{$dengji_name}医生网上预约挂号,{$region_name}{$dengji_name}医生排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}{$keshi_name}医院{$dengji_name}医生大全，{$dengji_name}医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看{$dengji_name}医生，在线预约电话咨询，找到合适的{$dengji_name}医生专家。";

        }
        $this->seoTitle       = $seoTitle;
        $this->seoKeywords    = $seoKeywords;
        $this->seoDescription = $seoDescription;
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
        $dspinyin = $request->get('dspinyin', 0);
        $sanjia   = $request->get('sanjia', 0);
        $page     = $request->get('page', 1);
        $data     = [];

        $regionData    = $this->getRegionData();
        $province_list = $regionData['province_list'] ?? [];
        $city_list     = $regionData['city_list'] ?? [];
        $province      = $regionData['province'] ?? [];
        $city          = $regionData['city'] ?? [];

        if ($dspinyin) {
            $disease_id  = 0;
            $diseaseInfo = DiseaseEsModel::find()->where(['pinyin' => $dspinyin])->one();
            if ($diseaseInfo) {
                $disease_id = $diseaseInfo['disease_id'];
                $docData    = $this->getDoctorlist('dspinyin', $region, $sanjia, $disease_id, $page);
            }

        } else {
            $docData = $this->getDoctorlist('diseases', $region, $sanjia, $diseases, $page);
        }

        $doctorlist = $docData['doctorlist'] ?? [];
        $totalCount = $docData['totalCount'] ?? 0;

        $data['province']            = $province;
        $data['region']              = $region;
        $data['city']                = $city;
        $data['sanjia']              = $sanjia;
        $data['province_list']       = $province_list;
        $data['city_list']           = $city_list;
        $data['diseases']            = $diseases;
        $data['doctorlist']          = $doctorlist;
        $data['search_disease_name'] = $diseaseInfo['disease_name'] ?? '';
        $data['dspinyin']            = $dspinyin;
        $data['page']                = $page;
        $data['totalCount']          = $totalCount;
        $data['pagination']          = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
       $seoTitle       = "全国哪家医院最好_全国医院排名_王氏医生";
        $seoKeywords    = "全国医院医生在线咨询,全国专家在线咨询,全国医生网上预约挂号,全国医生排行榜";
        $seoDescription = "王氏医生为您提供全国医院医生大全，医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看医生，在线预约电话咨询，找到合适的医生专家。";
        $dengji_name = '';
        $region_name = '全国';
        $disease_name = $data['search_disease_name'] ?? '';
        if ($province && $city) {
            $region_name = $city['name'] ?? '';
        } elseif ($province) {
            $region_name = $province['name'] ?? '';
        }
        if ($sanjia) {
            $dengji_name = $this->levellist[$sanjia] ?? '';
        }
        if ($region_name || $dengji_name) {
            $seoTitle       = "{$region_name}医院专家_{$dengji_name}医生在线咨询_预约挂号_哪个好_{$region_name}专家排名_王氏医生";
            $seoKeywords    = "{$region_name}医院{$dengji_name}医生在线咨询,{$region_name}专家在线咨询,{$region_name}{$dengji_name}医生网上预约挂号,{$region_name}{$dengji_name}医生排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}医院{$dengji_name}医生大全，{$dengji_name}医生排名榜、预约挂号、专家挂号等，百万患者真实评价打造实力排名，助您轻轻松松看{$dengji_name}医生，在线预约电话咨询，找到合适的{$dengji_name}医生专家。";
        }
        if ($disease_name) {
            $seoTitle       = "{$region_name}{$disease_name}医院专家_{$dengji_name}医生在线咨询_预约挂号_哪个好_{$region_name}{$disease_name}专家排名_王氏医生";
            $seoKeywords    = "{$region_name}治疗{$disease_name}医院最好的专家,{$region_name}治疗{$disease_name}专家排行榜,{$region_name}{$disease_name}专家网上预约挂号";
            $seoDescription = "王氏医生为您提供{$region_name}{$disease_name}医院{$dengji_name}医生排名榜，{$dengji_name}医生大全、预约挂号、专家挂号、专家门诊，百万患者真实评价打造实力排名，助您在第一时间知道{$region_name}{$disease_name}看什么{$dengji_name}医生，预约在线咨询，找{$dengji_name}医生挂号。";
        }
        $this->seoTitle       = $seoTitle;
        $this->seoKeywords    = $seoKeywords;
        $this->seoDescription = $seoDescription;
        return $this->render('diseases', $data);
    }

    /**
     * 获取地区信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @return  [type]     [description]
     */
    public function getRegionData()
    {
        $request = Yii::$app->request;
        $region  = $request->get('region', 0);
        $data    = [];
        // $province_list = Department::district();
        $province_list = SnisiyaSdk::getInstance()->getDistrict();
        $city_list     = [];
        $province      = [];
        $city          = [];
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
        ];

    }

    public function getDoctorlist($type = '', $region_pinyin = '', $sanjia = 0, $relation_id = '', $page = 1)
    {
        $params                  = [];
        $params['type']          = $type;
        $params['region_pinyin'] = $region_pinyin;
        $params['relation_id']   = $relation_id;
        $params['page']          = $page;
        $params['pagesize']      = $this->pagesize;
        if ($sanjia) {
            $params['doctor_title_id'] = $sanjia;
        }
        if ($relation_id) {
            if ($type == 'keshi') {
                $keshi_item = CommonFunc::getKeshiInfo($relation_id);
                if ($keshi_item && $keshi_item['parent_id'] == 0) {
                    $params['fkid'] = $relation_id;
                } else {
                    $params['skid'] = $relation_id;
                }
            } elseif ($type == 'diseases') {
                $params['initial'] = $relation_id;
            } elseif ($type == 'dspinyin') {
                $params['disease_id'] = $relation_id;
            }
        }
        $snisiyaSdk = new SnisiyaSdk();
        $res          = $snisiyaSdk->getDoctorList($params);
        $doctorlist   = $res['doctor_list'] ?? [];
        $totalCount   = $res['totalCount'] ?? 0;
        return [
            'doctorlist' => $doctorlist,
            'totalCount' => $totalCount,
        ];
    }

    /**
     * 获取医生列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @param   string     $type        [description]
     * @param   integer    $region_id   [description]
     * @param   string     $relation_id [description]
     * @param   integer    $page        [description]
     * @return  [type]                  [description]
     */
    public function getDoctorlist2($type = '', $region_pinyin = '', $sanjia = 0, $relation_id = '', $page = 1)
    {
        $regioninfo                              = Department::pinyin2id($region_pinyin);
        $region_key                              = '';
        $region_id                               = '';
        $has_parent                              = [];
        $where                                   = [];
        $where['bool']['must']                   = [];
        $has_parent['has_parent']['parent_type'] = 'hospital';
        $has_parent['has_parent']['query']       = [];
        if ($regioninfo) {
            if ($regioninfo['c_id'] == 0) {
                $region_key = 'province_id';
                $region_id  = $regioninfo['p_id'];
            } else {
                $region_key = 'city_id';
                $region_id  = $regioninfo['c_id'];
            }
        }

        if ($region_id) {
            if ($region_key == 'province_id') {
                $has_parent['has_parent']['query'][]['term'] = [
                    'province_id' => $region_id,
                ];
            } else {
                $has_parent['has_parent']['query'][]['term'] = [
                    'city_id' => $region_id,
                ];
            }
        } else {
            $has_parent['has_parent']['query'][]['range'] = [
                'hospital_id' => [
                    'gt' => 0,
                ],
            ];
        }

        if ($sanjia) {
            if ($sanjia == 1) {
                $title_id = 1;
            } else {
                $title_id = 6;
            }
            $where['bool']['must'][] = [
                'term' => [
                    'doctor_title_id' => $title_id,
                ],
            ];
        }
        if ($relation_id) {
            if ($type == 'keshi') {
                $keshi_item = CommonFunc::getKeshiInfo($relation_id);
                if ($keshi_item && $keshi_item['parent_id'] == 0) {
                    $where['bool']['must'][] = [
                        'term' => [
                            'doctor_frist_department_id' => $relation_id,
                        ],
                    ];
                } else {
                    $where['bool']['must'][] = [
                        'term' => [
                            'doctor_second_department_id' => $relation_id,
                        ],
                    ];
                }
            } elseif ($type == 'diseases') {
                $where['bool']['must'][] = [
                    'match' => [
                        'doctor_disease_initial' => $relation_id,
                    ],
                ];
            } elseif ($type == 'dspinyin') {
                $where['bool']['must'][] = [
                    'match' => [
                        'doctor_disease_id' => $relation_id,
                    ],
                ];
            }
        }
        if ($has_parent['has_parent']['query']) {
            $where['bool']['must'][] = $has_parent;
        }
        $page   = $page <= $this->maxPage ? $page : $this->maxPage;
        $offset = max(0, ($page - 1)) * $this->pagesize;
        $model  = new DoctorEsModel();

        $doctorlist = $model->find()->where([])->query($where)->offset($offset)->limit($this->pagesize)->orderBy('doctor_title_id asc')->all();
        $totalCount = $model->find()->where([])->query($where)->count();
        return [
            'doctorlist' => $doctorlist,
            'totalCount' => $totalCount,
        ];
    }

}
