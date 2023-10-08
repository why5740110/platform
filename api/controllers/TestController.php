<?php

namespace api\controllers;

use common\models\HospitalEsModel;

class TestController extends CommonController
{

    public function actionHospitalList()
    {

        $offset   = 0;
        $pagesize = 20;
        $dataJson = [];
        $model    = new HospitalEsModel();
        $where    = [
            'bool' => [
                'must' => [
                    // [
                    //     'term' => [
                    //         'hospital_id' => 5,
                    //     ],
                    // ],

                    [
                        'has_child' => [
                            'type'  => 'doctor',
                            'query' => [
                                // 'term' => [
                                //     'hospital_id' => 5,
                                // ],
                                'range' => [
                                    'hospital_id' => [
                                        'gt' => 0,
                                    ],
                                ],
                            ],
                            // 'inner_hits' => (object)[],
                        ],

                    ],

                ],
            ],
        ];

        $dataJson = $model->search($where, $offset, $pagesize);
        return $this->jsonSuccess($dataJson);

    }

    public function actionDoctorList()
    {
        $offset                      = 0;
        $pagesize                    = 20;
        $doctor_id                   = 0;
        $realname                    = '';
        $doctor_frist_department_id  = 0;
        $doctor_second_department_id = 58;
        $hospital_id                 = 0;
        $doctor_title_id             = 1;
        $professional_title          = '';
        $initial                     = '';
        $doctor_disease_id           = 2230;

        $city_id     = 36;
        $province_id = 1;

        $where = compact('doctor_id', 'realname', 'doctor_frist_department_id', 'doctor_second_department_id', 'hospital_id', 'doctor_title_id', 'initial', 'doctor_disease_id');
        $where = array_filter($where); //过滤值为null

        $term = [];
        if ($city_id) {
            $term['term'] = ['city_id' => $city_id];
        } elseif ($province_id) {
            $term['term'] = ['province_id' => $province_id];
        }

        $terms = [];
        //循环条件 组合json
        foreach ($where as $k => $v) {
            $row['terms'] = [$k => [$v]];
            $terms[]      = $row;
        }

        $order = ['doctor_title_id' => 'asc', 'doctor_id' => 'desc'];
        if (!empty($terms)) {
            $joinWhere = [
                "bool" => [
                    'must'   => [
                        /* [
                        'match' => [
                        'realname' => $realname,
                        ]
                        ],*/
                        [
                            'has_parent' => [
                                'parent_type' => 'hospital',
                                'query'       => [
                                    'term' => [
                                        'city_id' => 36,
                                    ],
                                    'term' => [
                                        'province_id' => 1,
                                    ],
                                    //'term'=>$term['term'],
                                    /* 'range'=>[
                                'hospital_id'=>[
                                'gt'=>0
                                ]
                                ]*/
                                ],

                                'inner_hits'  => (object) [

                                ], ##需要父级元素增加这个
                            ],

                        ],
                    ],
                    "filter" => [
                        "bool" => [
                            'must' => [
                                $terms,
                            ],
                        ],
                    ],

                ],

            ];
        } else {
            $joinWhere = [];
        }

        // $model = new HospitalDoctorIndexEsMap();
        // $list = $model->find()->where(['hospital_id'=>4,'doctor_id'=>null])->searchNew();
        $model = new HospitalEsModel();
        $where = [
            'bool' => [

                'must' => [
                    [

                        'match' => [
                            'doctor_second_department_id' => 58,
                        ],

                        'match' => [
                            'doctor_title_id' => 6,
                        ],

                    ],
                    [
                        'has_parent' => [
                            'parent_type' => 'hospital',
                            'query'       => [

                                'term' => [
                                    'city_id' => 36,
                                ],
                                'term' => [
                                    'province_id' => 1,
                                ],
                                /* 'range'=>[
                            'hospital_id'=>[
                            'gt'=>0
                            ]
                            ]*/
                            ],
                            'inner_hits'  => (object) [

                            ], ##需要父级元素增加这个
                        ],

                    ],

                ],
            ],
        ];

        $dataJson = $model->search($joinWhere, $offset, $pagesize, $order);
        return $this->jsonSuccess($dataJson);
    }
}
