<?php

namespace common\models;

use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\sdks\snisiya\SnisiyaSdk;
use common\validators\ImportDepartmentValidator;
use Yii;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\DoctorModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_hospital_department_relation".
 *
 * @property int $id 科室唯一ID
 * @property int $frist_department_id 一级科室ID
 * @property int $second_department_id 二级科室ID
 * @property string $frist_department_name 一级科室名称
 * @property string $second_department_name 二级科室名称
 * @property int $hospital_id 医院库ID
 * @property int $doctors_num 医生数
 * @property string $related_disease 相关疾病,通过疾病找科室
 * @property int $is_recommend 是否为重点科室(0:不是,1:是)
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $create_time 创建时间
 */
class HospitalDepartmentRelation extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_hospital_department_relation';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $route = Yii::$app->controller->route;
            if ($route == 'guahao/generate-department' || $route == 'guahao/platfrom-del-data' ) {
                return true;
            }
            HospitalDepartmentRelation::hospitalDepartment($this->hospital_id,1);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $route = Yii::$app->controller->route;
        if ($route == 'guahao/generate-department' || $route == 'guahao/platfrom-del-data' ) {
            return true;
        }
        HospitalDepartmentRelation::hospitalDepartment($this->hospital_id,1);
    }

    /**
     * 更新科室医生数量
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-21
     * @version v1.0
     * @param   integer    $hospital_id          [description]
     * @param   integer    $frist_department_id  [description]
     * @param   integer    $second_department_id [description]
     */
    public static function UpdepartmentDocNum($hospital_id = 0, $frist_department_id = 0, $second_department_id = 0)
    {
        $keshiRelationModel = HospitalDepartmentRelation::find()->where([
            'hospital_id' => $hospital_id,
            'frist_department_id' => $frist_department_id,
            'second_department_id' => $second_department_id
        ])->one();
        if ($keshiRelationModel) {
            $doctors_num = DoctorModel::find()->where(['hospital_id'=>$hospital_id,'frist_department_id'=>$frist_department_id,'second_department_id'=>$second_department_id])->count();
            $keshiRelationModel->doctors_num = $doctors_num;
            $keshiRelationModel->save();
        }
    }

    /**
     * 医院下科室缓存
     * @param $hospital_id
     * @param bool $update_cache
     * @return array|bool|mixed
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function hospitalDepartment($hospital_id, $update_cache = false)
    {
        $hospital_key = 'hospital_department_relation_' . $hospital_id;
        $data = [];
        if(!$update_cache){
            $sdk = SnisiyaSdk::getInstance();
            $hash_id = HashUrl::getIdEncode($hospital_id);
            $data = $sdk->hospital_department(['hospital_id'=>$hash_id]);
        }

        if (!$data || $update_cache) {
            $department = HospitalDepartmentRelation::find()->where(['hospital_id' => $hospital_id])->select('id,frist_department_id,frist_department_name,second_department_id,second_department_name,doctors_num,is_recommend,miao_frist_department_id,miao_second_department_id,address')->asArray()->all();
            if (!$department) {
                CommonFunc::setCodisCache($hospital_key, []);
                return [];
            }
            $department_data = [];
            if ($department) {
                foreach ($department as $v) {
                    $second_row['id'] = $v['id'];
                    $second_row['second_department_id'] = $v['second_department_id'];
                    $second_row['second_department_name'] = $v['second_department_name'];
                    $second_row['doctors_num'] = $v['doctors_num'];
                    $second_row['is_recommend'] = $v['is_recommend'];
                    $second_row['miao_frist_department_id'] = $v['miao_frist_department_id'];
                    $second_row['miao_second_department_id'] = $v['miao_second_department_id'];
                    $second_row['address'] = $v['address'];
                    $relationInfo = TbDepartmentThirdPartyRelationModel::find()->select('tp_platform,tp_department_id')->where([
                        'hospital_department_id'=>$v['id'],
                    ])->asArray()->all();
                    if($relationInfo){
                        $second_row['tb_third_party_relation'] = $relationInfo;
                    }
                    //根据排班是否有号
                    $snisiyaSdk = new SnisiyaSdk();
                    $department_real_plus = $snisiyaSdk->getRealPlus(['hospital_id' => $hospital_id, 'department_id' => $v['id']]);
                    $second_row['has_paiban'] = $department_real_plus > 0 ? 1 : 0;

                    $department_data[$v['frist_department_id']]['frist_department_id'] = $v['frist_department_id'];
                    $department_data[$v['frist_department_id']]['frist_department_name'] = $v['frist_department_name'];
                    $department_data[$v['frist_department_id']]['miao_frist_department_id'] = $v['miao_frist_department_id'];

                    $department_data[$v['frist_department_id']]['second_arr'][] = $second_row;
                }

            }

            if ($department_data) {
                CommonFunc::setCodisCache($hospital_key, $department_data);
            }
            return $department_data;
        } else {
            return $data;
        }

    }


    public static function getKeshi($hospital_id = 0,$fkeshi = 0,$skid = 0)
    {
        $department = HospitalDepartmentRelation::find()->where(['hospital_id' => $hospital_id,'frist_department_id'=>$fkeshi,'second_department_id'=>$skid])->select('frist_department_id,frist_department_name,second_department_id,second_department_name,miao_frist_department_id,miao_second_department_id,doctors_num,is_recommend')->asArray()->all();

        return $department;

    }

    /**
     * 根据科室ID更新缓存
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/28
     */
    public static function updateDepartmentCacheById($id)
    {
        $department = self::findOne($id);
        if (!empty($department)) {
            HospitalDepartmentRelation::hospitalDepartment($department->hospital_id, 1);
        }
    }

    /**
     * 导入科室
     * @param $data
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/13
     */
    public static function autoImportDepartment($data)
    {
        $return = [
            'code' => 404,
            'data' => [],
            'msg' => '请求失败'
        ];

        //格式化字段内容
        if (!is_array($data) || empty($data)) {
            return $return;
        }
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = CommonFunc::formatImportContent($value);
            }
        }

        //验证数据格式
        $validator = new ImportDepartmentValidator();
        $validator->load($data, '');
        if (!$validator->validate()) {
            $error = array_values($validator->getErrors());
            $return['msg'] = json_encode($error, JSON_UNESCAPED_UNICODE);
            return $return;
        }

        //判断是否已经导入过该科室
        $depRelation = TbDepartmentThirdPartyRelationModel::find()
            ->where(['tp_platform' => $data['tp_platform']])
            ->andWhere(['tp_hospital_code' => $data['tp_hospital_code']])
            ->andWhere(['tp_department_id' => $data['tp_department_id']])
            ->one();
        if ($depRelation) {
            $return['code'] = 400;
            $return['msg'] = '该科室已存在';
            return $return;
        }

        //判断公共科室是否存在
        $commonDepartment = Department::getKeshiByDepartmentName($data['department_name']);
        if (empty($commonDepartment)) {
            $parent_id = 0;
            if (!empty(ArrayHelper::getValue($data, 'tp_frist_department_name'))) {
                $parent_id = Department::createDepartment($data['tp_frist_department_name'], 0, 'parent');
            }
            $parent_id = $parent_id == 0 ? '-1' : $parent_id;
            Department::createDepartment($data['department_name'], $parent_id);

            $return['code'] = 400;
            $return['msg'] = '公共科室未匹配';
            return $return;
        } else {
            if ($commonDepartment['status'] != 1) {
                $return['code'] = 400;
                $return['msg'] = '公共科室已被禁用';
                return $return;
            }

            if ($commonDepartment['is_match'] != 1) {
                $return['code'] = 400;
                $return['msg'] = '公共科室未匹配';
                return $return;
            }

            if ($commonDepartment['parent_id'] <= 0 || empty($commonDepartment['miao_first_department_id']) || empty($commonDepartment['miao_second_department_id'])) {
                $return['code'] = 404;
                $return['msg'] = '公共科室数据错误';
                return $return;
            }

            //查询一级科室
            $fristDepartment = Department::getKeshi($commonDepartment['parent_id']);
            if (empty($fristDepartment)) {
                $return['code'] = 404;
                $return['msg'] = '公共一级科室数据错误';
                return $return;
            }
            if ($data['tp_platform'] == 13) {
                $fristDepartment = (isset($data['first_department_name']) && !empty($data['first_department_name'])) ? $data['first_department_name'] : $fristDepartment;
            }
        }

        //导入科室
        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            //查询是否已存在科室
            $depament_res = true;
            $relationModel = HospitalDepartmentRelation::find()
                ->where([
                    'frist_department_id' => $commonDepartment['parent_id'],
                    'second_department_id' => $commonDepartment['department_id'],
                    'hospital_id' => $data['hospital_id'],
                    'status' => 1,
                ])
                ->one();
            if (empty($relationModel)) {
                $relationModel = new HospitalDepartmentRelation();
                $relationModel->frist_department_id = $commonDepartment['parent_id'];
                $relationModel->second_department_id = $commonDepartment['department_id'];
                $relationModel->frist_department_name = $fristDepartment;
                $relationModel->second_department_name = $data['department_name'];
                $relationModel->hospital_id = $data['hospital_id'];
                $relationModel->miao_frist_department_id = $commonDepartment['miao_first_department_id'];
                $relationModel->miao_second_department_id = $commonDepartment['miao_second_department_id'];
                $relationModel->related_disease = '';
                $relationModel->create_time = time();
                $relationModel->admin_id = 0;
                $relationModel->admin_name = 'system';
                $depament_res = $relationModel->save();
            }

            if ($depament_res) {
                $tmp_deparment_relation = new TbDepartmentThirdPartyRelationModel();
                $tmp_deparment_relation->hospital_department_id = $relationModel->attributes['id'];
                $tmp_deparment_relation->tp_platform = $data['tp_platform'];
                $tmp_deparment_relation->tp_department_id = $data['tp_department_id'];
                $tmp_deparment_relation->create_time = time();
                $tmp_deparment_relation->status = 1;
                $tmp_deparment_relation->tp_hospital_code = $data['tp_hospital_code'];
                $tmp_deparment_relation->save();
            }
            $transition->commit();
            $return['code'] = 200;
            $return['msg'] = '导入成功';
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . $msg);
            $return['msg'] = $msg;
        }

        return $return;
    }
}
