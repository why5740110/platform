<?php

namespace api\controllers;

use common\models\DiseaseEsModel;
use common\models\SearchEsModel;

class DoctorlistController extends CommonController
{

    public function actionIndex()
    {
        $keyword = '张三';
        $relation_info = $this->relation_id($keyword);
        echo "<pre>";print_r($relation_info);die();
        return $this->jsonSuccess($list);
    }

    public function relation_id($keyword='')
    {
        if (!$keyword) {
            return [];
        }
        $where                 = [];

        $where['bool']['should'][] = [
            'match' => [
                'doctor_realname' => $keyword,
            ],
        ];
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
        $where['bool']['should'][] = [
            'term' => [
                'disease_keyword' => $keyword,
            ],
        ];

        $model = new SearchEsModel();
        $list  = $model->search_find($where, 0, 5);
        return $list['list'] ?? [];
    }


    private  function getWhere($param = [])
    {
        $keyword = $param['keyword'];
        $where   = [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should'               => [ //筛选三个条件当中至少符合一个的数据
                            [
                                "match_phrase" => [ //优先搜索医生名称
                                    "doctor_realname" => [
                                        "query" => $keyword,
                                    ],
                                ],
                            ],
                            [
                                "match_phrase" => [ //其次搜索医院名称
                                    "hospital_name" => [
                                        "query" => $keyword,
                                    ],
                                ],
                            ],
                            [
                                "match_phrase" => [ //其次搜索医院别名
                                    "hospital_nick_name" => [
                                        "query" => $keyword,
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'disease_keyword' => $keyword,
                                ],
                            ],
                        ],
                        "minimum_should_match" => 1,
                    ],
                ],

            ],
        ];

        return $where;
    }
}
