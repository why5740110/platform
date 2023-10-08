<?php

namespace common\models;

use common\models\EsBase;

class DoctorEsModel extends EsBase
{

    public $index;
    public $type;
    public $routing;

    public function __construct()
    {
        $db            = (\Yii::$app->get('elasticsearch')->nodes) ?? [];
        $auth          = (\Yii::$app->get('elasticsearch')->auth) ?? [];
        $this->hosts   = array_column($db, 'http_address');
        $this->username   = $auth['username'] ?? '';
        $this->password   = $auth['password'] ?? '';
        $this->index   = 'guahao_hospital_doctor_index';
        $this->routing = 1;

        parent::__construct();

        $this->mapping = [

            'properties' => [
                'hospital_id'                   => ['type' => 'integer'], ##医院id
                'hospital_level'                => ['type' => 'keyword'], //医院等级名称
                'doctor_id'                     => ['type' => 'integer'], ##医生id
                'doctor_realname'               => ['type' => 'text', 'analyzer' => 'ik_max_word'], ##医生姓名
                'doctor_realname_keyword'       => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]]], ##医生姓名
                'doctor_avatar'                 => ['type' => 'keyword'], ##医生头像
                'doctor_title'                  => ['type' => 'keyword'], ##医生职称
                'doctor_hospital'               => ['type' => 'keyword'], ##医生所在医院
                'doctor_title_id'               => ['type' => 'short'], ##医生职称id
                'doctor_good_at'                => ['type' => 'keyword'], ##医生擅长
                'doctor_tags'                   => ['type' => 'keyword'], ##医生标签
                'doctor_visit_type'             => ['type' => 'keyword'], ##医生出诊类型
                'doctor_profile'                => ['type' => 'keyword'], ##医生简介
                'doctor_professional_title'     => ['type' => 'keyword'], ##医生专业职称
                'doctor_frist_department_id'    => ['type' => 'integer'], ##医生一级科室id
                'doctor_frist_department_name'  => ['type' => 'keyword'], ##医生一级科室名称
                'doctor_second_department_id'   => ['type' => 'integer'], ##医生二级科室id
                'doctor_second_department_name' => ['type' => 'keyword'], ##医生二级科室名称
                'doctor_disease_id'             => ['type' => 'text', 'analyzer' => 'whitespace'], //医生关联疾病id
                'doctor_disease_name'           => ['type' => 'keyword'], //医生关联疾病标签
                'doctor_disease_initial'        => ['type' => 'text', 'analyzer' => 'whitespace'], //医生关联疾病id首字母
                'doctor_is_plus'                => ['type' => 'byte'], //是否开通加号
                'miao_doctor_id'                => ['type' => 'integer'], ##医生管理王氏id
                'doctor_weight'                 => ['type' => 'integer'], ##医生权重
                'tp_platform'                   => ['type' => 'byte'], //对应第三方平台
                'tp_hospital_code'              => ['type' => 'keyword'], //对应第三方医院id
                'miao_frist_department_id'      => ['type' => 'integer'], ##王氏一级科室
                'miao_second_department_id'     => ['type' => 'integer'], ##王氏二级科室
                'tp_department_id'              => ['type' => 'keyword'], //对应第三方科室id
                'tp_doctor_id'                  => ['type' => 'keyword'], //对应第三方医生id
                'doctor_department_relation_id' => ['type' => 'integer'], //关联的医院科室关系主键
                'tb_doctor_third_scheduleplace' => ['type' => 'text'],
                'doctor_real_plus'              => ['type' => 'byte'],  //根据排班是否有号 0无 1有
                'doctor_min_plus'               => ['type' => 'byte'],  //是否有民营医院排班
                'doctor_frist_depid_arr'          => ['type' => 'keyword'],
                'doctor_second_depid_arr'         => ['type' => 'keyword'],
                'doctor_order_count'            => ['type' => 'integer'], //订单量
                'hospital_or_doctor'            => ['type' => 'byte'],  //区分数据医院1 医生2
                'hospital_doctor'               => [
                    'name'   => 'doctor',
                    'parent' => ['type' => 'integer'],
                ],
                'tb_third_doctor_relation'      => [
                    ['type' => 'nested'],
                    'properties' => [
                        'tp_doctor_id' => ['type' => 'keyword'], //医生id
                        'tp_department_id' => ['type' => 'keyword'], //第三方二级科室ID
                        'tp_platform'  => ['type' => 'byte'], //来源
                    ],
                ]
            ],
        ];
    }

    # 属性
    public function attributes()
    {
        return array_keys($this->mapping['properties']);
        // $mapConfig = $this->getEsMapping();
        // return array_keys($mapConfig[$this->index]['mappings']['properties']);
    }

    /**
     * Set (update) mappings for this model
     */
    public static function updateMapping()
    {
        return static::getInstance()->updateEsMapping();
    }

    /**
     * @return mixed
     */
    public static function getMapping()
    {
        return static::getInstance()->getEsMapping();
    }

    /**
     * 删除es数据
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-13
     * @version 1.0
     * @param   integer    $doctor_id   [description]
     * @return  [type]                  [description]
     */
    public static function deleteDoctorEsData($doctor_id = 0)
    {
        $doctorEsModel = new DoctorEsModel();
        $res           = $doctorEsModel->findOne('doctor-' . $doctor_id);
        if ($res) {
            return static::getInstance()->deleteDocument('doctor-' . $doctor_id);
        }
        return true;
    }

    public function selectEs($where = [], $page = 1, $pagesize = 10, $order = ['doctor_title_id' => 'asc', 'doctor_id' => 'desc'])
    {
        if ($page > 20) {
            $page = 20;
        }
        $offset       = max(($page - 1), 0) * $pagesize;
        $term['term'] = [];
        if (isset($where['province_id']) && !empty($where['province_id'])) {
            $term['term'] = ['province_id' => $where['province_id']];
        } elseif (isset($where['city_id']) && !empty($where['city_id'])) {
            $term['term'] = ['city_id' => $where['city_id']];
        } elseif (isset($where['district_id']) && !empty($where['district_id'])) {
            $term['term'] = ['district_id' => $where['district_id']];
        } elseif (isset($where['hospital_id']) && !empty($where['hospital_id'])) {
            $term['term'] = ['hospital_id' => $where['hospital_id']];
        }
        unset($where['province_id']);
        unset($where['city_id']);
        unset($where['district_id']);
        unset($where['hospital_id']);
        if (!empty($term['term'])) {
            $area_term = [
                'has_parent' => [
                    'parent_type' => 'hospital',
                    'query'       => [
                        'term' => $term['term'],
                    ],

                    'inner_hits'  => (object) [

                    ], ##需要父级元素增加这个
                ],

            ];
        } else {
            $area_term = [];
        }

        $joinWhere = [];
        $terms = [];
        //循环条件 组合json
        foreach ($where as $k => $v) {
            $row['terms'] = [$k => [$v]];
            //$terms[]      = $row;
            $joinWhere['bool']['must'][] = $row;
        }

        if (isset($where['doctor_realname']) && !empty($where['doctor_realname'])) {
           /* $joinWhere = [
                "bool" => [
                    'must' => [
                        [
                            'match' => [
                                'doctor_realname' => $where['doctor_realname'],
                            ],
                        ],
                    ],
                ],
            ];*/
            $joinWhere['bool']['must'][] = [
                'match' => [
                    'doctor_realname' => $where['doctor_realname'],
                ],
            ];
        } else {
            if (!empty($terms) && !empty($term['term'])) {
                /*$joinWhere = [
                    "bool" => [
                        'must' => [
                            $terms,
                            $area_term,
                        ],
                    ],
                ];*/
                $joinWhere['bool']['must'][] = $area_term;

            } elseif (!empty($terms)) {
                //科室 疾病 职称
                /*$joinWhere = [
                    "bool" => [
                        'must' => [
                            $terms,

                        ],
                    ],
                ];*/
                $joinWhere['bool']['must'][] = $terms;
            } elseif (!empty($term['term'])) {
                ##省市区 医院
                /*$joinWhere = [
                    "bool" => [
                        'must' => [
                            $area_term,
                        ],
                    ],
                ];*/
                $joinWhere['bool']['must'][] = $area_term;
            } else {
                /*$joinWhere = [
                    "bool" => [
                        'must' => [
                            'range' => [
                                'doctor_id' => [
                                    'gt' => 0, //查医生数据
                                ],
                            ],
                        ],
                    ],
                ];*/
                $joinWhere['bool']['must'][] = [
                    'range' => [
                        'doctor_id' => [
                            'gt' => 0, //查医生数据
                        ],
                    ],
                ];
            }
        }

        $res = self::search_find($joinWhere, $offset, $pagesize, $order);
        return $res;
    }

}
