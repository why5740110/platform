<?php

namespace common\models;

use common\models\DoctorEsModel;
use common\models\DoctorModel;
use common\models\BaseDoctorHospitals;
use common\models\HospitalDepartmentRelation;
use common\models\EsBase;

class HospitalEsModel extends EsBase
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
                'hospital_real_plus'              => ['type' => 'byte'], //根据排班是否有号
                'hospital_logo'                   => ['type' => 'keyword'], //医院icon图片地址
                'hospital_open_day'               => ['type' => 'integer'], //放号天数
                'hospital_open_time'              => ['type' => 'keyword'], //放号时间
                'hospital_or_doctor'              => ['type' => 'byte'],  //区分数据医院1 医生2
                'hospital_doctor'                 => [
                    'type'      => 'join',
                    'relations' => [
                        'hospital' => 'doctor',
                    ],
                ],
                'tb_third_party_relation'         => [
                    ['type' => 'nested'],
                    'properties' => [
                        'tp_doctor_id' => ['type' => 'keyword'], //医生id
                        'tp_platform'  => ['type' => 'byte'], //来源
                    ],
                ],
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
     * 查询医院下医生es查询条件
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @param   integer    $hospital_id [description]
     * @return  [type]                  [description]
     */
    public static function getHospitalDoctorQuery($hospital_id = 0)
    {
        $query                   = [];
        $query['bool']['must'][] = [
            'term' => [
                'hospital_id' => $hospital_id,
            ],
        ];
        $query['bool']['must'][] = [
            'range' => [
                'doctor_id' => [
                    'gt' => 0,
                ],
            ],
        ];
        return $query;
    }

    /**
     * 删除医院es数据以及医院下医生数据
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @param   integer    $hospital_id [description]
     * @return  [type]                  [description]
     */
    public static function deleteHospitalEsData($hospital_id = 0,$delete_doctor = 0)
    {
        $hospitalEsModel = new HospitalEsModel();
        $has_hospital    = $hospitalEsModel->findOne($hospital_id);
        if ($has_hospital) {
            return static::getInstance()->deleteDocument($hospital_id);
        }
        return true;

    }

    /**
     * 根据医院id更新医院医生数据
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-21
     * @version v1.0
     * @param   integer    $hospital_id   [description]
     * @param   integer    $update_doctor [是否更新医生]
     * @return  [type]                    [description]
     */
    public static function updateHospitalDoctorEsDataByHospital($hospital_id = 0, $update_doctor = 1)
    {
        $model = new BuildToEsModel();
        $res = $model->db2esByIdHospital($hospital_id);
        if ($res['code'] == 1) {
            echo $hospital_id . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        } else {
            echo $hospital_id . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        }

        $doc_num = 0;
        $limit   = 100;
        $total   = DoctorModel::find()->where(['hospital_id' => $hospital_id, 'status' => 1])->count();
        if ($total && $update_doctor) {
            $query = DoctorModel::find()->select('doctor_id')->where(['hospital_id' => $hospital_id, 'status' => 1]);
            $page  = ceil($total / $limit);
            for ($i = 0; $i <= $page; $i++) {
                $offset  = $i * $limit;
                $doclist = $query->limit($limit)->offset($offset)->asArray()->all();
                if ($doclist) {
                    $doc_model = new BuildToEsModel();
                    foreach ($doclist as $v) {
                        $doc_res = $doc_model->db2esByIdDoctor($v['doctor_id']);
                        if ($doc_res['code'] == 1) {
                            echo '医生id:'.$v['doctor_id'] . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                        } else {
                            echo '医生id:'.$v['doctor_id'] . $doc_res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                        }
                        DoctorModel::getInfo($v['doctor_id'], true);
                        $doc_num++;
                    }
                }
            }
        }
        if ($doc_num) {
            echo "处理医生数量：$doc_num\n";
        }
        BaseDoctorHospitals::HospitalDetail($hospital_id, true);
        //医院科室缓存
        HospitalDepartmentRelation::hospitalDepartment($hospital_id,true);

        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 根据医院id删除医院和医院下医生数据
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-21
     * @version v1.0
     * @param   integer    $hospital_id [description]
     * @return  [type]                  [description]
     */
    public static function deleteDoctorEsDataByHospitalId($hospital_id = 0,$delete_doctor = 1)
    {
        $doc_num = 0;
        $doc_total = DoctorModel::find()->where(['hospital_id' => $hospital_id, 'status' => 1])->count();
        if ($delete_doctor && $doc_total) {
            $page  = 1;
            $limit = 100;
            $model = new DoctorEsModel();
            $query = self::getHospitalDoctorQuery();
            do {
                $offset   = max(0, ($page - 1)) * $limit;
                $doc_list = $model->find()->where([])->query($query)->offset($offset)->limit($limit)->orderBy('doctor_id asc')->all();
                if (!$doc_list) {
                    echo ('结束：' . date('Y-m-d H:i:s', time())) . '没有了！' . PHP_EOL;
                    break;
                }
                foreach ($doc_list as $doc_info) {
                    try {
                        DoctorEsModel::deleteDoctorEsData($doc_info['doctor_id']);
                        echo '删除医院医生 ' . $doc_info['hospital_id'] . '-' . $doc_info['doctor_id'] . '-' . date("Y-m-d H:i:s") . " [成功]\n\r";
                        $doc_num++;
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医院医生不存在失败');
                        echo '删除医院医生 ' . $doc_info['hospital_id'] . '-' . $doc_info['doctor_id'] . '-' . date("Y-m-d H:i:s") . " [失败！]" . 'msg --' . $msg . "\n\r";
                        continue;
                    }
                }
                $num = count($doc_list);
                unset($doc_list);
                $page++;
            } while ($num > 0);
        }
        if ($delete_doctor) {
            echo "处理医生数量：$doc_num\n";
        }
        self::deleteHospitalEsData($hospital_id);
        return true;
    }

}
