<?php

namespace pc\controllers;

use common\helpers\Url;
use common\libs\CommonFunc;
use common\models\DiseaseEsModel;
use common\models\SearchEsModel;
use common\sdks\snisiya\sRpcSdk;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\helpers\Html;

class SearchController extends CommonController
{
    public $enableCsrfValidation = false;
    public $pagesize             = 20;
    public $maxPage              = 20;

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
                $res['data'] = ['pinyin' => '', 'url' => Url::to([$url_header, 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => $jibing, 'page' => 1])];
            }
            echo json_encode($res);die();
        } else {
            $res['code'] = 400;
            $res['data'] = ['url' => Url::to(['index/index'])];
            echo json_encode($res);die();
            throw new NotFoundHttpException();
        }

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
        $keyword = $request->get('keyword', 0);
        $data    = [];
        if (!$keyword || CommonFunc::checkXss($keyword)) {
            throw new NotFoundHttpException();
        }

        $keyword = trim($keyword);
        $search_type_list = ['hospital', 'doctor', 'disease'];
        $reslist          = [];
        foreach ($search_type_list as $value) {
            sRpcSdk::getInstance()->search_list($$value, ['type' => $value, 'keyword' => $keyword, 'pagesize' => 6, 'page' => 1]);
        }
        sRpcSdk::getInstance()->startAsync();
        $data['hospital_list'] = $hospital['list'] ?? [];
        $data['doctor_list']   = $doctor['list'] ?? [];
        $data['disease_list']  = $disease['list'] ?? [];
        $data['keyword']       = $keyword ?? '';
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

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
        $page    = $request->get('page', 1);
        $type    = $request->get('type', 'hospital');
        $data    = [];
        if (!$keyword || CommonFunc::checkXss($keyword)) {
            throw new NotFoundHttpException();
        }
        $data = SnisiyaSdk::getInstance()->getSearchList(['type'=>$type,'keyword'=>$keyword,'pagesize'=>$this->pagesize,'page'=>$page]);
        // $data       = $this->getSearchList($keyword, $type, $page, $this->pagesize);
        $list       = $data['list'] ?? [];
        $totalCount = $data['totalCount'] ?? 0;
        $totalCount = $totalCount > 400 ? 400 : $totalCount;
        if ($page > $this->maxPage) {
            $list = [];
        }
        $data['list']       = $list;
        $data['type']       = $type;
        $data['keyword']    = $keyword ?? '';
        $data['page']       = $page;
        $data['totalCount'] = $totalCount;
        $data['pagination'] = new Pagination([
            'totalCount'      => $totalCount,
            'defaultPageSize' => $this->pagesize,
        ]);
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

        return $this->render('list', $data);
    }

    /**
     * 获取搜索结果
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-10
     * @version 1.0
     * @param   string     $keyword  [description]
     * @param   string     $type     [description]
     * @param   integer    $page     [description]
     * @param   integer    $pagesize [description]
     * @return  [type]               [description]
     */
    public function getSearchList($keyword = '', $type = 'hospital', $page = 1, $pagesize = 6)
    {
        if (!$keyword) {
            return [];
        }
        $sort        = '';
        $disease_id  = 0;
        $diseaseInfo = DiseaseEsModel::find()->where(['disease_keyword' => $keyword])->one();
        if ($diseaseInfo) {
            $disease_id = $diseaseInfo['disease_id'];
        }
        $where                 = [];
        $where['bool']['must'] = [];
        if ($type == 'hospital') {
            $where['bool']['must'][] = [
                'term' => [
                    'hospital_kind' => '公立',
                ],
            ];
            $where['bool']['must'][] = [
                'range' => [
                    'hospital_level_num' => [
                        'gt' => 0, //过滤掉医院等级为空的
                    ],
                ],
            ];

            if ($disease_id) {
                $has_child['has_child']['type']             = 'doctor';
                $has_child['has_child']['query'][]['match'] = [
                    'doctor_disease_id' => $disease_id,
                ];
                $where['bool']['should'][] = $has_child;
                $sort                      = 'hospital_level_num asc,hospital_id asc';
            } else {
                $where['bool']['should'][] = [
                    'match' => [
                        'hospital_name' => $keyword,
                    ],
                ];
                $where['bool']['should'][] = [
                    'match' => [
                        'hospital_nick_name' => $keyword,
                    ],
                ];
                $where['bool']['minimum_should_match'] = 1;
            }
        } elseif ($type == 'doctor') {
            $where['bool']['should'][] = [
                'match' => [
                    'doctor_realname' => $keyword,
                ],
            ];
            $where['bool']['should'][] = [
                'match' => [
                    'doctor_hospital' => $keyword,
                ],
            ];
            if ($disease_id) {
                $where['bool']['should'][] = [
                    'match' => [
                        'doctor_disease_id' => $disease_id,
                    ],
                ];
                $sort = 'doctor_title_id asc,doctor_id asc';
            }
            $where['bool']['minimum_should_match'] = 1;

        } elseif ($type == 'disease') {
            $where['bool']['should'][] = [
                'match' => [
                    'disease_keyword' => $keyword,
                ],
            ];
        } else {
            return [];
        }
        // echo "<pre>";print_r($where);die();

        $model    = new SearchEsModel();
        $page     = $page <= $this->maxPage ? $page : $this->maxPage;
        $pagesize = $pagesize ? $pagesize : $this->pagesize;
        $offset   = max(0, ($page - 1)) * $pagesize;
        if ($sort) {
            $list = $model->find()->query($where)->offset($offset)->limit($pagesize)->orderBy($sort)->all();
        } else {
            $list = $model->find()->query($where)->offset($offset)->limit($pagesize)->all();
        }

        $totalCount = $model->find()->query($where)->count();
        return [
            'list'       => $list,
            'totalCount' => $totalCount,
        ];
    }

}
