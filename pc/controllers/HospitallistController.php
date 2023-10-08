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
use common\models\HospitalEsModel;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;

class HospitallistController extends CommonController
{
    public $pagesize  = 20;
    public $maxPage   = 20;
    public $levellist;

    public function init()
    {
        $this->levellist = CommonFunc::$level_list;
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

        $docData        = $this->getHospitallist('region', $region, $sanjia, 0, $page);
        $hospital_list  = $docData['hospital_list'] ?? [];
        $totalCount     = $docData['totalCount'] ?? 0;

        $data['region']        = $region;
        $data['province']      = $province;
        $data['city']          = $city;
        $data['sanjia']        = $sanjia;
        $data['province_list'] = $province_list;
        $data['city_list']     = $city_list;
        $data['hospital_list'] = $hospital_list;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);

        $seoTitle       = '全国医院哪家最好_全国医院排名_王氏医生';
        $seoKeywords    = '全国医院,医院哪家好,全国最好的医院,全国医院排行榜';
        $seoDescription = '王氏医生为您提供全国医院大全，医院排名榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用全国医院统一挂号平台，找到合适的医生，挂上医院专家号。';
        $dengji_name = '';
        $region_name = '全国';
        if ($province && $city) {
            $region_name = $city['name'] ?? '';
        } elseif ($province) {
            $region_name = $province['name'] ?? '';
        }
        if ($sanjia) {
            $dengji_name = $this->levellist[$sanjia] ?? '';
        }
        if ($region_name || $dengji_name) {
            $seoTitle       = "{$region_name}{$dengji_name}医院哪家最好_{$region_name}{$dengji_name}医院排名_王氏医生";
            $seoKeywords    = "{$region_name}医院,{$dengji_name}医院哪家好,{$region_name}最好的{$dengji_name}医院,{$region_name}{$dengji_name}医院排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}{$dengji_name}医院大全，{$dengji_name}医院排名榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用{$region_name}{$dengji_name}医院统一挂号平台，找到合适的医生，挂上{$dengji_name}医院专家号。";
        
        }
        $this->seoTitle       = $seoTitle;
        $this->seoKeywords    = $seoKeywords;
        $this->seoDescription = $seoDescription;
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
        $request  = Yii::$app->request;
        $region   = $request->get('region', 0);
        $keshi_id = $request->get('keshi_id', 0);
        $sanjia   = $request->get('sanjia', 0);
        $page     = $request->get('page', 1);
        $dengji_name = '';
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

        $docData       = $this->getHospitallist('keshi', $region, $sanjia, $keshi_id, $page);
        $hospital_list = $docData['hospital_list'] ?? [];
        $totalCount    = $docData['totalCount'] ?? 0;

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
        $data['hospital_list'] = $hospital_list;
        $data['page']          = $page;
        $data['totalCount']    = $totalCount;
        $data['pagination']    = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        $seoTitle       = '全国医院哪家最好_全国医院排名_王氏医生';
        $seoKeywords    = '全国医院,医院哪家好,全国最好的医院,全国医院排行榜';
        $seoDescription = '王氏医生为您提供全国医院大全，医院排名榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用全国医院统一挂号平台，找到合适的医生，挂上医院专家号。';
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
            $seoTitle       = "{$region_name}{$keshi_name}{$dengji_name}医院哪家最好_{$region_name}{$keshi_name}{$dengji_name}医院排名_王氏医生";
            $seoKeywords    = "{$region_name}{$keshi_name}{$dengji_name}医院,{$dengji_name}医院哪家好,{$region_name}{$keshi_name}最好的{$dengji_name}医院,{$region_name}{$keshi_name}{$dengji_name}医院排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}{$keshi_name}{$dengji_name}医院大全，{$dengji_name}医院排名榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用{$region_name}{$keshi_name}{$dengji_name}医院统一挂号平台，找到合适的医生，挂上{$dengji_name}医院专家号。";
        
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
        $sanjia   = $request->get('sanjia', 0);
        $dspinyin = $request->get('dspinyin', 0);
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
                $docData    = $this->getHospitallist('dspinyin', $region, $sanjia, $disease_id, $page);
            }

        } else {
            $docData = $this->getHospitallist('diseases', $region, $sanjia, $diseases, $page);
        }

        $hospital_list = $docData['hospital_list'] ?? [];
        $totalCount    = $docData['totalCount'] ?? 0;

        $data['province']            = $province;
        $data['region']              = $region;
        $data['city']                = $city;
        $data['sanjia']              = $sanjia;
        $data['province_list']       = $province_list;
        $data['city_list']           = $city_list;
        $data['diseases']            = $diseases;
        $data['search_disease_name'] = $diseaseInfo['disease_name'] ?? '';
        $data['hospital_list']       = $hospital_list;
        $data['dspinyin']            = $dspinyin;
        $data['page']                = $page;
        $data['totalCount']          = $totalCount;
        $data['pagination']          = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        $seoTitle       = '全国哪家医院最好_全国医院排名_王氏医生';
        $seoKeywords    = '全国医院,全国看病哪家医院好,全国治疗疾病最好的医院排行榜';
        $seoDescription = '王氏医生为您提供全国医院排名榜，医院大全、预约挂号、治疗哪家好等，百万患者真实评价打造实力排名，助您在快速找到全国治疗最好的医院统一挂号平台，找到合适的医生，挂上医院专家号。';
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
            $seoTitle       = "{$region_name}{$dengji_name}医院哪家最好_{$region_name}{$dengji_name}医院排名_王氏医生";
            $seoKeywords    = "{$region_name}{$dengji_name}医院,{$dengji_name}医院哪家好,{$region_name}最好的{$dengji_name}医院,{$region_name}{$dengji_name}医院排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}{$dengji_name}医院大全，{$dengji_name}医院排名榜、预约挂号、哪家好等，百万患者真实评价打造实力排名，助您在第一时间使用{$region_name}{$dengji_name}医院统一挂号平台，找到合适的医生，挂上{$dengji_name}医院专家号。";
        }
        if ($disease_name) {
            $seoTitle       = "{$region_name}治疗{$disease_name}哪家{$dengji_name}医院最好_{$region_name}{$disease_name}{$dengji_name}医院排名_王氏医生";
            $seoKeywords    = "{$region_name}{$disease_name}{$dengji_name}医院,{$region_name}看{$disease_name}哪家{$dengji_name}医院好,{$region_name}治疗{$disease_name}最好的{$dengji_name}医院,{$disease_name}{$dengji_name}医院排行榜";
            $seoDescription = "王氏医生为您提供{$region_name}{$disease_name}{$dengji_name}医院排名榜，{$dengji_name}医院大全、预约挂号、治疗{$disease_name}哪家好等，百万患者真实评价打造实力排名，助您在快速找到{$region_name}治疗{$disease_name}最好的{$dengji_name}医院统一挂号平台，找到合适的医生，挂上{$dengji_name}医院专家号。";
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
    public function getHospitallist($type = 'region', $region_pinyin = '', $sanjia = 0, $relation_id = '', $page = 1)
    {
        $params                  = [];
        $params['type']          = $type;
        $params['region_pinyin'] = $region_pinyin;
        $params['sanjia']        = $sanjia;
        $params['relation_id']   = $relation_id;
        $params['page']          = $page;
        $params['pagesize']      = $this->pagesize;
        $snisiyaSdk            = new SnisiyaSdk();
        $res                     = $snisiyaSdk->getHospitalList($params);
        $hospital_list           = $res['hospital_list'] ?? [];
        $totalCount              = $res['totalCount'] ?? 0;
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
                    'gt' => 0, //查医生数据
                ],
            ],
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
                    'hospital_level_num' => (int) $sanjia,
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
            } elseif ($type == 'diseases') {
                $has_child['has_child']['query'][]['match'] = [
                    'doctor_disease_initial' => $relation_id,
                ];
            } elseif ($type == 'dspinyin') {
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

}
