<?php

namespace common\models;

use common\models\EsBase;

class HospitalDoctorIndexEsMap extends EsBase
{

    public $index;
    public $type;

    public function __construct()
    {
        $db            = (\Yii::$app->get('elasticsearch')->nodes) ?? [];
        $auth          = (\Yii::$app->get('elasticsearch')->auth) ?? [];
        $this->hosts   = array_column($db, 'http_address');
        $this->username   = $auth['username'] ?? '';
        $this->password   = $auth['password'] ?? '';
        $this->index = 'guahao_hospital_doctor_index';

        parent::__construct();

        $this->mapping = [
            'properties' => [
                'hospital_id'                     => ['type' => 'integer'],
                'province_id'                     => ['type' => 'integer'], ##省地区id
                'city_id'                         => ['type' => 'integer'], ##市地区id
                'district_id'                     => ['type' => 'integer'], ##区地区id
                'hospital_name'                   => ['type' => 'text', 'analyzer' => 'ik_max_word'], ##医院名称
                'hospital_name_keyword'           => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]]], ##医院名称
                'hospital_nick_name'              => ['type' => 'text', 'analyzer' => 'ik_max_word'], ##医院别名称
                'hospital_nick_name_keyword'      => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]]], ##医院别名
                'hospital_phone'                  => ['type' => 'keyword'], ##医院电话
                'hospital_address'                => ['type' => 'keyword'], ##医院地址
                'hospital_level'                  => ['type' => 'keyword'], //医院等级名称
                'hospital_level_num'              => ['type' => 'short'], //医院级别对应数字
                'hospital_type'                   => ['type' => 'keyword'], //医院类型综合专科
                'hospital_kind'                   => ['type' => 'keyword'], //医院种类公立私立
                'hospital_good_at'                => ['type' => 'keyword'], //医院擅长疾病标签
                'hospital_tags'                   => ['type' => 'keyword'], //医院标签
                'hospital_disease_id'             => ['type' => 'text', 'analyzer' => 'ik_max_word'], //医院关联疾病id
                'hospital_department_id'          => ['type' => 'text', 'analyzer' => 'whitespace'], //医院关联科室id
                'hospital_department_name'        => ['type' => 'text', 'analyzer' => 'ik_max_word'], //医院关联科室
                'hospital_photo'                  => ['type' => 'keyword'], //医院图片地址
                'hospital_status'                 => ['type' => 'byte'], ##医院状态'状态 0 正常 1未审核',
                'hospital_score'                  => ['type' => 'integer'], //医院级别对应数字
                'hospital_is_plus'                => ['type' => 'byte'], //是否开通加号
                'tp_platform'                     => ['type' => 'byte'], //对应第三方平台
                'tp_hospital_code'                => ['type' => 'keyword'], //对应第三方医院id
                'hospital_location'               => ['type' => 'geo_point'], //经纬度
                'hospital_fudan_order'            => ['type' => 'keyword'], //复旦排行
                'hospital_fudan_honor_score'      => ['type' => 'keyword'], //复旦声誉权重
                'hospital_fudan_scientific_score' => ['type' => 'keyword'], //复旦科研权重
                'hospital_level_alias'            => ['type' => 'keyword'], //等级别名
                'hospital_fudan_score'            => ['type' => 'keyword'], //综合评分
                'hospital_real_plus'              => ['type' => 'byte'],  //根据排班是否有号
                'hospital_logo'                   => ['type' => 'keyword'], //医院icon图片地址
                'hospital_open_day'               => ['type' => 'integer'], //放号天数
                'hospital_open_time'              => ['type' => 'keyword'], //放号时间
                ##医生信息
                'doctor_id'                       => ['type' => 'integer'], ##医生id
                'doctor_realname'                 => ['type' => 'text', 'analyzer' => 'ik_max_word'], ##医生姓名
                'doctor_realname_keyword'         => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]]], ##医生姓名
                'doctor_avatar'                   => ['type' => 'keyword'], ##医生头像
                'doctor_title'                    => ['type' => 'keyword'], ##医生职称
                'doctor_hospital'                 => ['type' => 'keyword'], ##医生所在医院
                'doctor_title_id'                 => ['type' => 'short'], ##医生职称id
                'doctor_good_at'                  => ['type' => 'keyword'], ##医生擅长
                'doctor_tags'                     => ['type' => 'keyword'], ##医生标签
                'doctor_visit_type'               => ['type' => 'keyword'], ##医生出诊类型
                'doctor_profile'                  => ['type' => 'keyword'], ##医生简介
                'doctor_professional_title'       => ['type' => 'keyword'], ##医生专业职称
                'doctor_frist_department_id'      => ['type' => 'integer'], ##医生一级科室id
                'doctor_frist_department_name'    => ['type' => 'keyword'], ##医生一级科室名称
                'doctor_second_department_id'     => ['type' => 'integer'], ##医生二级科室id
                'doctor_second_department_name'   => ['type' => 'keyword'], ##医生二级科室名称
                'doctor_disease_id'               => ['type' => 'text', 'analyzer' => 'whitespace'], //医生关联疾病id
                'doctor_disease_name'             => ['type' => 'keyword'], //医生关联疾病标签
                'doctor_disease_initial'          => ['type' => 'text', 'analyzer' => 'whitespace'], //医生关联疾病id首字母
                'doctor_is_plus'                  => ['type' => 'byte'], //是否开通加号
                'miao_doctor_id'                  => ['type' => 'integer'],
                'doctor_weight'                   => ['type' => 'integer'],
                'miao_frist_department_id'        => ['type' => 'integer'],
                'miao_second_department_id'       => ['type' => 'integer'],
                'tp_department_id'                => ['type' => 'keyword'], //对应第三方科室id
                'tp_doctor_id'                    => ['type' => 'keyword'], //对应第三方医生id
                'doctor_department_relation_id'   => ['type' => 'integer'], //关联的医院科室关系主键
                'tb_doctor_third_scheduleplace'   => ['type' => 'text'],
                'doctor_real_plus'                => ['type' => 'byte'],  //根据排班是否有号
                'doctor_frist_depid_arr'          => ['type' => 'keyword'],
                'doctor_second_depid_arr'         => ['type' => 'keyword'],
                'doctor_min_plus'                 => ['type' => 'byte'],  //是否有民营医院排班
                'doctor_order_count'              => ['type' => 'integer'], //订单量
                'hospital_or_doctor'              => ['type' => 'byte'],  //区分数据医院1 医生2
                'hospital_doctor'                 => [
                    'type'      => 'join',
                    'relations' => [
                        'hospital' => 'doctor',
                    ],
                ],
                'tb_third_doctor_relation'        => [
                    'type'       => 'nested',
                    'properties' => [
                        'tp_doctor_id' => ['type' => 'keyword'], //医生id
                        'tp_department_id' => ['type' => 'keyword'], //第三方二级科室ID
                        'tp_platform'  => ['type' => 'byte'], //来源
                    ],
                ],
            ],
        ];
    }

    # 属性
    public function attributes()
    {
        // return array_keys($this->mapping['properties']);
        $mapConfig = $this->getEsMapping();
        return array_keys($mapConfig[$this->index]['mappings']['properties']);
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

}
