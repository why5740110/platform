<?php

namespace common\models;

use common\helpers\Url;
use common\models\DoctorInfoModel;
use common\models\minying\MinDoctorModel;
use common\validators\ImportDoctorValidator;
use Yii;
use yii\data\Pagination;
use common\models\BaseDoctorHospitals;
use common\models\DoctorEsModel;
use common\models\BuildToEsModel;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoScheduleModel;
use queues\upDoctorScheduleJob;
use common\libs\CommonFunc;
use common\models\TbLog;
use common\libs\HashUrl;
use pc\controllers\CommonController;
use common\sdks\snisiya\SnisiyaSdk;
use common\models\GuahaoHospitalModel;
use common\models\TmpDoctorThirdPartyModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_doctor".
 *
 * @property int $doctor_id 医生自增ID
 * @property int $primary_id 医生主ID
 * @property string $realname 医生姓名
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 * @property string $avatar 头像图片url
 * @property string $source_avatar 第三方头像图片url
 * @property int $job_title_id 职称ID
 * @property string $job_title 职称
 * @property int $hospital_id 医院ID
 * @property string $hospital_name 医院名称
 * @property int $hospital_type 医院属性 1公立 2非公立
 * @property int $frist_department_id 一级科室ID
 * @property string $frist_department_name 一级科室名称
 * @property int $second_department_id 二级科室ID
 * @property string $second_department_name 二级科室名称
 * @property int $miao_doctor_id 王氏医生ID(默认是没有关联)
 * @property int $miao_frist_department_id 王氏一级科室ID
 * @property int $miao_second_department_id 王氏二级科室ID
 * @property string $tp_hospital_code 第三方医院id
 * @property string $tp_doctor_id 第三方医生ID
 * @property string $tp_frist_department_id 第三方一级科室ID
 * @property string $tp_department_id 第三方二级科室ID
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $is_plus 是否关联第三方
 * @property int doctor_real_plus 根据排班是否有号 0无 1只有加号排班 2有挂号排班
 * @property int $weight 权重值
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class DoctorModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_doctor}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['primary_id', 'tp_platform', 'job_title_id', 'hospital_id', 'frist_department_id', 'second_department_id', 'miao_doctor_id', 'miao_frist_department_id', 'miao_second_department_id', 'status', 'is_plus', 'weight', 'create_time', 'update_time', 'admin_id','hospital_type'], 'integer'],
            [['realname', 'frist_department_name', 'second_department_name', 'admin_name', 'hospital_name'], 'string', 'max' => 50],
            [['avatar', 'source_avatar'], 'string', 'max' => 255],
            [['job_title'], 'string', 'max' => 20],
            [['tp_hospital_code', 'tp_doctor_id', 'tp_frist_department_id', 'tp_department_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'doctor_id' => 'Doctor ID',
            'primary_id' => 'Primary ID',
            'realname' => 'Realname',
            'tp_platform' => 'Tp Platform',
            'avatar' => 'Avatar',
            'source_avatar' => 'Source Avatar',
            'job_title_id' => 'Job Title ID',
            'job_title' => 'Job Title',
            'hospital_id' => 'Hospital ID',
            'hospital_name' => 'Hospital Name',
            'hospital_type' => '医院属性 1公立 2非公立',
            'frist_department_id' => 'Frist Department ID',
            'frist_department_name' => 'Frist Department Name',
            'second_department_id' => 'Second Department ID',
            'second_department_name' => 'Second Department Name',
            'miao_doctor_id' => 'Miao Doctor ID',
            'miao_frist_department_id' => 'Miao Frist Department ID',
            'miao_second_department_id' => 'Miao Second Department ID',
            'tp_hospital_code' => 'Tp Hospital Code',
            'tp_doctor_id' => 'Tp Doctor ID',
            'tp_frist_department_id' => 'Tp Frist Department ID',
            'tp_department_id' => 'Tp Department ID',
            'status' => 'Status',
            'is_plus' => 'Is Plus',
            'weight' => 'Weight',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
        ];
    }

     /**
     * @获取属性
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @return void
     */
    public static function attribute()
    {
        $info_model = new DoctorModel();
        $attribute = array_keys($info_model->attributeLabels());
        return $attribute;
    }

    /**
     * 格式日志对应字段说明
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-11
     * @version v1.0
     * @return  [type]     [description]
     */
    public static function attributeNode()
    {
        return [
            'doctor_id' => '医生ID',
            'primary_id' => '医生主ID',
            'realname' => '医生姓名',
            'tp_platform' => '医生来源',
            'avatar' => '医生头像',
            'source_avatar' => '医生原始头像',
            'job_title_id' => '医生职称ID',
            'job_title' => '医生职称',
            'hospital_id' => '医院id',
            'hospital_name' => '医院名称',
            'frist_department_id' => '一级科室ID',
            'frist_department_name' => '一级科室名称',
            'second_department_id' => '二级科室ID',
            'second_department_name' => '二级科室名称',
            'miao_doctor_id' => '王氏ID',
            'miao_frist_department_id' => '王氏一级科室ID',
            'miao_second_department_id' => '王氏二级科室ID',
            'tp_hospital_code' => '第三方医院id',
            'tp_doctor_id' => '第三方医生id',
            'tp_frist_department_id' => '第三方一级科室ID',
            'tp_department_id' => '第三方二级科室ID',
            'status' => '状态',
            'is_plus' => '是否关联第三方',
            'weight' => '权重值',
            'good_at' => '擅长',
            'profile' => '个人简介',
            'professional_title' => '医生专业职称',
            'related_disease' => '相关疾病',
            'initial' => '疾病拼音首字母组合',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'admin_id' => '操作人id',
            'admin_name' => '操作人姓名',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $action = 'edit';
        if ($insert) {
            $keshiRelationModel = HospitalDepartmentRelation::find()->where([
                'hospital_id' => $this->hospital_id,
                'frist_department_id' => $this->frist_department_id,
                'second_department_id' => $this->second_department_id
            ])->one();
            if ($keshiRelationModel) {
                $keshiRelationModel->doctors_num++;
                $keshiRelationModel->save();
            }
            ##如果新增且关联王氏id设置王氏医生id对应医院医生id
            if ($this->miao_doctor_id) {
                CommonFunc::setMiaoid2HospitalDoctorID($this->miao_doctor_id,$this->doctor_id);
            }
            ##如果新增的是王氏异步拉取出诊机构
            if ($this->doctor_id && $this->tp_doctor_id && $this->tp_platform == 6) {
                CommonFunc::getDoctorVisitPlace($this->doctor_id, $this->tp_doctor_id);
            }
            DoctorModel::updateIsPlus($this->primary_id);##更新is_plus
            $action = 'add';
        }
        if ($changedAttributes) {
            ##如果医生修改时改变了医院，删除原来es医院医生 by yangquanliang 2020-10-13
            if (isset($changedAttributes['hospital_id']) && !empty($changedAttributes['hospital_id']) && $changedAttributes['hospital_id'] !=$this->hospital_id) {
                // DoctorEsModel::deleteDoctorEsData($this->doctor_id);
                ##拉取医生排班队列
                $params = ['doctor_id'=>$this->doctor_id];
                \Yii::$app->slowqueue->push(new upDoctorScheduleJob(['params' => $params]));
            }
            ##更改医院或者科室重新计算原科室下医生数量
            if (isset($changedAttributes['hospital_id']) && isset($changedAttributes['frist_department_id']) && isset($changedAttributes['second_department_id'])) {
                if ($changedAttributes['hospital_id'] && $changedAttributes['frist_department_id'] && $changedAttributes['second_department_id']) {
                    $old_department_str = $changedAttributes['hospital_id'].$changedAttributes['frist_department_id'].$changedAttributes['second_department_id'];
                    $new_department_str = $this->hospital_id.$this->frist_department_id.$this->second_department_id;
                    if ($old_department_str != $new_department_str) {
                        HospitalDepartmentRelation::UpdepartmentDocNum($changedAttributes['hospital_id'],$changedAttributes['frist_department_id'],$changedAttributes['second_department_id']);
                        HospitalDepartmentRelation::UpdepartmentDocNum($this->hospital_id,$this->frist_department_id,$this->second_department_id);
                    }
                }
                
            }
            if (isset($changedAttributes['status'])) {
                if ($this->status != 1) {
                    ##禁用医生后，删除该医生索引
                    if ($this->miao_doctor_id) {
                        CommonFunc::setMiaoid2HospitalDoctorID($this->miao_doctor_id,0);
                    }
                    DoctorEsModel::deleteDoctorEsData($this->doctor_id);
                    GuahaoScheduleModel::cancelPaibanByDoctorID($this->doctor_id);##取消医生排班
                    $action = 'del';
                }
            }
           
            ##start 修改王氏医生id时删除旧的设置新的缓存 by yangquanliang 2020-12-10
            if (isset($changedAttributes['miao_doctor_id'])) {
                if (!empty($changedAttributes['miao_doctor_id'])) {
                    CommonFunc::setMiaoid2HospitalDoctorID($changedAttributes['miao_doctor_id'],0);
                }
                
            }
            if (isset($changedAttributes['status']) || isset($changedAttributes['miao_doctor_id'])) {
                if ($this->miao_doctor_id > 0 && $this->status == 1) {
                    CommonFunc::setMiaoid2HospitalDoctorID($this->miao_doctor_id,$this->doctor_id);
                }
            }
            ##end 修改王氏医生id时删除旧的设置新的缓存 by yangquanliang 2020-12-10
            ##如果取消主子关联关系更新原主子两个医生信息
            if (isset($changedAttributes['primary_id']) && $changedAttributes['primary_id'] > 0) {
                // $this->UpdateInfo($changedAttributes['primary_id'],$this->hospital_id);
                CommonFunc::upAfterSaveJobData($changedAttributes['primary_id'],$this->hospital_id);
            }
        }
        ##异步调用
        CommonFunc::upAfterSaveJobData($this->doctor_id,$this->hospital_id);

        //医生更改新增 异步通知第三方
        CommonFunc::guahaoPushQueue($this->doctor_id,1,$action,$this->tp_platform);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        DoctorModel::UpdateInfo($this->doctor_id); // 更新缓存 lyw 2021.08.31
        $doc_info = DoctorInfoModel::findOne($this->doctor_id);
        if ($doc_info) {
            $doc_info->delete();
        }
    }


    /**
     * 更新缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-13
     * @version 1.0
     * @param   integer    $doctor_id   [description]
     * @param   integer    $hospital_id [description]
     */
    public function UpdateInfo($doctor_id = 0, $hospital_id = 0)
    {
        ##异步更新
        DoctorModel::getInfo($doctor_id, true);
        $model = new BuildToEsModel();
        $model->db2esByIdDoctor($doctor_id);
        ##医院科室异步处理太耗时间
        if ($hospital_id) {
            $model->db2esByIdHospital($hospital_id);
            BaseDoctorHospitals::HospitalDetail($hospital_id,true);
        }
    }



    public static function getInfo($id,$update_cache=false,$updatePrimary=1){
        $snisiyaSdk = new SnisiyaSdk();
        $key = sprintf(Yii::$app->params['cache_key']['hospital_doctor_info'], $id);
        //$data = CommonFunc::getCodisCache($key);
        //用户id
        $uid = 0;
        $data = [];
        if (!$update_cache) {
            $data = $snisiyaSdk->getDoctorInfo(['doctor_id'=>HashUrl::getIdEncode($id),'uid'=>$uid]);
        }
        if (!isset($data['doctor_id'])||$update_cache) {
            $data = self::find()->select('hospital_id,status,
            doctor_id,
            primary_id,
            realname doctor_realname,
            avatar doctor_avatar,
            job_title doctor_title,
            job_title_id doctor_title_id,
            frist_department_id doctor_frist_department_id,
            second_department_id doctor_second_department_id,
            frist_department_name doctor_frist_department_name,
            second_department_name doctor_second_department_name,
            miao_doctor_id,is_plus doctor_is_plus,
            tp_platform,tp_doctor_id,tp_department_id,hospital_name,tp_hospital_code,
            miao_frist_department_id,
            miao_second_department_id,
            second_department_id,
            frist_department_name
            ')->where(['doctor_id'=>$id])->asArray()->one();

            if ($data) {
                $primary_id = $data['primary_id'];
                if($data['status']==1) {

                    if($primary_id){
                        $priData = DoctorModel::find()->where(['doctor_id'=>$primary_id])->select('realname doctor_realname,avatar doctor_avatar,job_title doctor_title,
                            job_title_id doctor_title_id')->asArray()->one();
                        if($priData){
                            $data = ArrayHelper::merge($data,$priData);
                        }
                    }
                    $primary_id = $primary_id ? $primary_id : $id;
                    $docInfo = DoctorInfoModel::find()->where(['doctor_id' => $primary_id])->select('professional_title doctor_professional_title,good_at doctor_good_at,profile doctor_profile,related_disease doctor_disease_id,initial doctor_disease_initial')->asArray()->one();
                    if ($docInfo) {
                        $data = ArrayHelper::merge($data, $docInfo);
                    }
                    $data['doctor_hospital'] = BaseDoctorHospitals::HospitalDetail($data['hospital_id'])['name'] ?? '';
                    $data['doctor_id'] = HashUrl::getIdEncode($id);
                    $data['doctor_avatar'] = $data['doctor_avatar'] == '' ? 'https://u.nisiyacdn.com/avatar/default_2.jpg' : \Yii::$app->params['avatarUrl'] . $data['doctor_avatar'];


                    $data['doctor_is_plus'] = $data['doctor_is_plus'];
                    $priRow = [
                        'doctor_id' => $id,
                        'second_department_name' => $data['doctor_second_department_name'],
                        'tp_platform' => $data['tp_platform'],
                        'tp_doctor_id' => $data['tp_doctor_id'],
                        'tp_department_id' => $data['tp_department_id'],
                        'hospital_name' => $data['hospital_name'],
                        'tp_hospital_code' => $data['tp_hospital_code'],
                        'miao_frist_department_id' =>$data['miao_frist_department_id'],
                        'miao_second_department_id'=>$data['miao_second_department_id'],
                        'second_department_id'=>$data['second_department_id'],
                        'frist_department_name'=>$data['frist_department_name'],
                        'hospital_id'=>$data['hospital_id'],
                    ];
                    $doctors = [];
                    if ($data['primary_id'] == 0) {
                        $doctors = DoctorModel::find()->where(['primary_id' => $id, 'status' => 1])->select('doctor_id,
                        tp_platform,
                        tp_doctor_id,
                        tp_department_id,
                        second_department_name,
                        status,
                        hospital_name,
                        tp_hospital_code,
                        miao_frist_department_id,
                        miao_second_department_id,
                        second_department_id,
                        frist_department_name,
                        hospital_id
                        ')->indexBy('doctor_id')->asArray()->all();
                    } else {
                        //更新主医生信息  批量更新时不更新
                        if($updatePrimary) {
                            self::getInfo($data['primary_id'], true);
                        }
                    }
                    $doctors[$id] = $priRow;
                    $doctors = array_values($doctors);
                    $data['tb_third_party_relation'] = $doctors;
                    if ($data['primary_id']) {
                        $data['primary_id'] = HashUrl::getIdEncode($data['primary_id']);
                    }

                    //更新民营医院医生标签到缓存中
                    $data['doctor_tags'] = "";
                    $data['doctor_visit_type'] = "";
                    if ($data['tp_platform'] == 13) {
                        $minDoctorModel = MinDoctorModel::findOne($data['tp_doctor_id']);
                        $data['doctor_tags'] = $minDoctorModel->min_doctor_tags ? MinDoctorModel::getTagsInfoById($minDoctorModel->min_doctor_tags) : "";
                        $data['doctor_visit_type'] = ($minDoctorModel->visit_type == 2) ? MinDoctorModel::$visitType[$minDoctorModel->visit_type] : "";
                    } else {
                        //获取所有民营子医生的标签
                        $childDoctor = DoctorModel::find()->where(['status'=>1,'primary_id'=>$id,'tp_platform' => 13])->select('doctor_id,tp_doctor_id')->asArray()->all();
                        if (!empty($childDoctor)) {
                            $childTags = "";
                            foreach ($childDoctor as $child) {
                                $minDoctorModel = MinDoctorModel::findOne($child['tp_doctor_id']);
                                $doctor_tags = $minDoctorModel->min_doctor_tags ? MinDoctorModel::getTagsInfoById($minDoctorModel->min_doctor_tags) : "";
                                if (!empty($doctor_tags)) {
                                    if (empty($childTags)) {
                                        $childTags = $doctor_tags;
                                    } else {
                                        $childTags .= "、" . $doctor_tags;
                                    }
                                }
                            }
                            if (!empty($childTags)) {
                                $data['doctor_tags'] = implode('、', array_unique(explode('、', trim($childTags, '、'))));
                            }
                            $data['doctor_visit_type'] = MinDoctorModel::$visitType[2];
                        }
                    }

                    CommonFunc::setCodisCache($key, $data);
                }else{
                    CommonFunc::setCodisCache($key, []);
                    return [];
                }
            }else{
                CommonFunc::setCodisCache($key, []);
                return [];
            }
        }
        if($data){
            return $data;
        }
        return [];
    }

    /**
     * @统一保存医生以及医生附属信息方法
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @param array $data
     * @return void
     */
    public static function saveDoctor($data = [],$write_log = true)
    {
        $res = [ 'doctor_id'=>0, 'msg'=>'', 'status'=>0];
        if (!$data) {
            $res['msg'] = '无数据内容';
            return $res;
        }
        $doctor_id = $data['doctor_id'] ?? 0;
        $data['source_avatar'] = $data['source_avatar'] ?? '';
        $data['source_avatar'] = CommonFunc::filterSourceAvatar($data['source_avatar']);
        $attributeInfo = DoctorInfoModel::attribute();
        $doc_attribute = DoctorModel::attribute();
        $doc_attribute_node = DoctorModel::attributeNode();
        $admin_info = [];
        if ($write_log) {
            $admin_info = CommonFunc::getAdminInfo();
        }
        $admin_id = $admin_info['admin_id'] ?? 0;
        $admin_name = $admin_info['admin_name'] ?? '';
        if (ArrayHelper::getValue($data,'tp_platform') > 0) {
            $data['is_plus'] = 1;
        }
        $logInfo = [];
        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            if ($doctor_id) {
                $doctor_model = DoctorModel::find()->where(['doctor_id'=>$doctor_id])->one();
                $info_model = DoctorInfoModel::find()->where(['doctor_id'=>$doctor_id])->one();
                if (!$doctor_model || !$info_model) {
                    throw new \Exception('医生信息不存在！');
                }
                $description = '医生修改';
                $editContent = $admin_name . '修改了id为' .$doctor_id . '的医生:';
                unset($data['doctor_id'],$data['source_avatar']);
            }else{
                $description = '医生添加';
                $doctor_model = new DoctorModel();
                $info_model = new DoctorInfoModel();
                $doctor_model->create_time = time();
                $info_model->create_time = time();
                $info_model->profile =  $data['profile'] ?? '';
                $info_model->related_disease =  $data['related_disease'] ?? '';
            }
            ##如果有医院无医院名称时自动追加医院名称
            if (ArrayHelper::getValue($data,'hospital_id') && !ArrayHelper::getValue($data,'hospital_name')) {
                $hos_info = BaseDoctorHospitals::getInfo($data['hospital_id']);
                $data['hospital_name'] = ArrayHelper::getValue($hos_info,'name','');
                $data['hospital_type'] = ArrayHelper::getValue($hos_info,'kind') == '公立' ? 1 : 2;
            }

            $doctor_model->admin_id = $admin_id ?? 0;
            $doctor_model->admin_name = $admin_name ?? '';
            $info_model->admin_id = $admin_id ?? 0;
            $info_model->admin_name = $admin_name ?? '';

            $doctor_model->update_time = time();
            $info_model->update_time = time();
            foreach ($data as $v_key => $v_item) {
                if (in_array($v_key,$attributeInfo)) {
                    $info_model->$v_key = $v_item;
                    if ($doctor_id) {
                        $old_content = $info_model->getOldAttribute($v_key);
                        if ($old_content != $v_item) {
                            $upk_name = $doc_attribute_node[$v_key] ?? $v_key;
                            $logInfo[] = [$upk_name, $old_content, $v_item];
                        }
                    }
                    
                }elseif (in_array($v_key,$doc_attribute)) {
                    $doctor_model->$v_key = $v_item;
                    if ($doctor_id) {
                        $old_content = $doctor_model->getOldAttribute($v_key);
                        if ($old_content != $v_item) {
                            $upk_name = $doc_attribute_node[$v_key] ?? $v_key;
                            $logInfo[] = [$upk_name, $old_content, $v_item];
                        }
                    }
                }
            }
            $doc_res = $doctor_model->save();
            if (!$doc_res) {
                throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            if(!$doctor_id){
                $doctor_id = $doctor_model->attributes['doctor_id'];
                $info_model->doctor_id = $doctor_id;
                $editContent = $admin_name . '添加了医生:' . 'id:' . $doctor_id . '名称为:' . $doctor_model->realname;
            }
            $info_res = $info_model->save();
            if (!$info_res) {
                throw new \Exception(json_encode($info_model->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $transition->commit();
            if ($write_log) {
                if ($logInfo) {
                    $editContent .= TbLog::formatLog($logInfo);
                    TbLog::addLog($editContent, $description);
                }elseif ($description == '医生添加') {
                    TbLog::addLog($editContent, $description);
                }
            }
            $res['doctor_id'] = $doctor_id;
            $res['status'] = 1;
            return $res;
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
            $res['msg'] = $msg;
            return $res;
        }
    }

    /**
     * @获取医生和医生附属信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @param integer $doctor_id
     * @param string $fields
     * @param array $extraWhere 额外条件
     * @return void
     */
    public static function getDcotorItem($doctor_id = 0,$fields='*',$extraWhere = [])
    {
        $where = ['doctor_id' => $doctor_id];
        if($extraWhere && is_array($where)){
            $where = array_merge($where,$extraWhere);
        }
        $doc_item =  DoctorModel::find()->where($where)->select($fields)->asArray()->one();
        if (!$doc_item) {
            return [];
        }
        $doc_info = self::getDcotorInfoItem($doctor_id);
        $doc_item = array_merge($doc_item,$doc_info);
        return $doc_item;
    }

    /**
     * @获取医生附属表信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @param integer $doctor_id
     * @return void
     */
    public static function getDcotorInfoItem($doctor_id = 0)
    {
        $doc_info = DoctorInfoModel::find()->select('good_at,profile')->where(['doctor_id' => $doctor_id])->asArray()->one();
        return $doc_info;
    }


    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::find()
            ->select('*');
        if (!empty($params['doctor'])) {
            if (strlen(intval(trim($params['doctor']))) == strlen(trim($params['doctor']))) {
                $doctorQuery->andWhere(['doctor_id'=>intval(trim($params['doctor']))]);
            } else {
                $doctorQuery->andWhere(['like','realname',trim($params['doctor'])]);
            }
        }
        if (!empty($params['miao_doctor_id'])) {
            if (is_numeric($params['miao_doctor_id']) && $params['miao_doctor_id'] > 0) {
                $doctorQuery->andWhere(['miao_doctor_id'=>(int)$params['miao_doctor_id']]);
            } else {
                return [];
            }
        }
        if (!empty($params['status']) && $params['status']==1) {
            $doctorQuery->andWhere(['status'=>1]);
        }elseif(!empty($params['status']) && $params['status']==2){
            $doctorQuery->andWhere(['status'=>0]);
        }
        ##展示主医生
        if (isset($params['doc_primary'])) {
            if ($params['doc_primary'] == 1) {
                $doctorQuery->andWhere(['primary_id'=>0]);
            }elseif($params['doc_primary'] == 2){
                $doctorQuery->andWhere(['>','primary_id',0]);
            }
        }

        if (isset($params['primary_id']) && $params['primary_id'] != '') {
            if (is_numeric($params['primary_id']) && $params['primary_id'] > 0) {
                $doctorQuery->andWhere(['primary_id'=>(int)$params['primary_id']]);
            }
        }


        if (isset($params['tp_platform']) && $params['tp_platform'] !== '') {
            if (is_numeric($params['tp_platform']) && $params['tp_platform'] > 0) {
                $doctorQuery->andWhere(['tp_platform'=>(int)$params['tp_platform']]);
            }
        }

        //科室信息
        if (isset($params['fkid']) && $params['fkid'] != '') {
            if (is_numeric($params['fkid']) && $params['fkid'] > 0) {
                $doctorQuery->andWhere(['frist_department_id'=>(int)$params['fkid']]);
            }
        }
        if (isset($params['skid']) && $params['skid'] != '') {
            if (is_numeric($params['skid']) && $params['skid'] > 0) {
                $doctorQuery->andWhere(['second_department_id'=>(int)$params['skid']]);
            }
        }

        //医生医院等级
        if (isset($params['hospital_id']) and $params['hospital_id'] != '') {
            if (is_numeric($params['hospital_id']) && $params['hospital_id'] > 0) {
                $doctorQuery->andWhere(['hospital_id'=>(int)$params['hospital_id']]);
            }
        }
        //医生职称
        if (isset($params['title_id']) && $params['title_id'] != '') {
            if (is_numeric($params['title_id']) && $params['title_id'] > 0) {
                $doctorQuery->andWhere(['job_title_id'=>(int)$params['title_id']]);
            }
        }
        if (!empty($params['is_plus'])) {
            $doctorQuery->andWhere(['is_plus'=>intval($params['is_plus'])]);
        }
        if (!empty($params['is_nisiya'])) {
            if($params['is_nisiya']==1){
                $doctorQuery->andWhere(['<>','miao_doctor_id',0]);
            }
            if($params['is_nisiya']==2){
                $doctorQuery->andWhere(['miao_doctor_id'=>0]);
            }

        }

        if(!empty($params['fugao']))
        {
            if($params['fugao'] == 1)
            {
                $doctorQuery->andWhere(['<>','title'=>'主治医师']);
                $doctorQuery->andWhere(['<>','title'=>'主管药师']);
            }
            if($params['fugao'] == 3)
            {
                $title = ['副主任医师','主任医师','副主任药师','主任药师'];
                $doctorQuery->andWhere(['hospital_level'=>2,'title'=>$title]);
            }
        }

        //权限是否开通
        if(isset($params['power']) && $params['power'] !== ''){
            $doctorQuery->andWhere(['power'=>$params['power']]);
        }
        //医院名称
        if(isset($params['hospital']) && $params['hospital'] !== ''){
            $doctorQuery->andWhere(['hospital'=>$params['hospital']]);
        }
        //开通时间
        if(isset($params['power_create_time']) and $params['power_create_time'] != ''){
            $power_create_time_arr = explode(' - ', $params['power_create_time']);
            $doctorQuery->andWhere(['>=', 'create_time', strtotime(trim($power_create_time_arr[0]))]);
            $doctorQuery->andWhere(['<=', 'create_time', strtotime(trim($power_create_time_arr[1]) . ' 23:59:59')]);
        }

        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('doctor_id desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::find()->select('*');
        if (!empty($params['doctor'])) {
            if (strlen(intval(trim($params['doctor']))) == strlen(trim($params['doctor']))) {
                $doctorQuery->andWhere(['doctor_id'=>intval(trim($params['doctor']))]);
            } else {
                $doctorQuery->andWhere(['like','realname',trim($params['doctor'])]);
            }
        }
        if (!empty($params['miao_doctor_id'])) {
            if (is_numeric($params['miao_doctor_id']) && $params['miao_doctor_id'] > 0) {
                $doctorQuery->andWhere(['miao_doctor_id'=>(int)$params['miao_doctor_id']]);
            }
        }
        //科室信息
        if (isset($params['fkid']) && $params['fkid'] != '') {
            if (is_numeric($params['fkid']) && $params['fkid'] > 0) {
                $doctorQuery->andWhere(['frist_department_id'=>(int)$params['fkid']]);
            }
        }
        if (isset($params['skid']) && $params['skid'] != '') {
            if (is_numeric($params['skid']) && $params['skid'] > 0) {
                $doctorQuery->andWhere(['second_department_id'=>(int)$params['skid']]);
            }
        }
        if (!empty($params['status']) && $params['status']==1) {
            $doctorQuery->andWhere(['status'=>1]);
        }elseif(!empty($params['status']) && $params['status']==2){
            $doctorQuery->andWhere(['status'=>0]);
        }

        ##展示主医生
        if (isset($params['doc_primary'])) {
            if ($params['doc_primary'] == 1) {
                $doctorQuery->andWhere(['primary_id'=>0]);
            }elseif($params['doc_primary'] == 2){
                $doctorQuery->andWhere(['>','primary_id',0]);
            }
        }
        if (isset($params['primary_id']) && $params['primary_id'] != '') {
            if (is_numeric($params['primary_id']) && $params['primary_id'] > 0) {
                $doctorQuery->andWhere(['primary_id'=>(int)$params['primary_id']]);
            }
        }


        if (isset($params['tp_platform']) && $params['tp_platform'] !== '') {
            if (is_numeric($params['tp_platform']) && $params['tp_platform'] > 0) {
                $doctorQuery->andWhere(['tp_platform'=>(int)$params['tp_platform']]);
            }
        }
        //医生医院等级
        if (isset($params['hospital_id']) and $params['hospital_id'] != '') {
            if (is_numeric($params['hospital_id'])) {
                $doctorQuery->andWhere(['hospital_id'=>(int)$params['hospital_id']]);
            } else {
                $doctorQuery->andWhere(['hospital_id'=> -1]);
            }
        }
        //医生职称
        if (isset($params['title_id']) && $params['title_id'] != '') {
            if (is_numeric($params['title_id']) && $params['title_id'] > 0) {
                $doctorQuery->andWhere(['job_title_id'=>(int)$params['title_id']]);
            }
        }
        if (!empty($params['is_plus'])) {
            $doctorQuery->andWhere(['is_plus'=>intval($params['is_plus'])]);
        }
        if (!empty($params['is_nisiya'])) {
            if($params['is_nisiya']==1){
                $doctorQuery->andWhere(['<>','miao_doctor_id',0]);
            }
            if($params['is_nisiya']==2){
                $doctorQuery->andWhere(['miao_doctor_id'=>0]);
            }

        }
        //医院名称
        if(isset($params['hospital']) && $params['hospital'] !== ''){
            $doctorQuery->andWhere(['hospital'=>$params['hospital']]);
        }

        //开通时间
        if(isset($params['power_create_time']) and $params['power_create_time'] != ''){
            $power_create_time_arr = explode(' - ', $params['power_create_time']);
            $doctorQuery->andWhere(['>=', 'create_time', strtotime(trim($power_create_time_arr[0]))]);
            $doctorQuery->andWhere(['<=', 'create_time', strtotime(trim($power_create_time_arr[1]) . ' 23:59:59')]);
        }
        //修改时间
        if(isset($params['power_update_time']) and $params['power_update_time'] != ''){
            $power_create_time_arr = explode(' - ', $params['power_update_time']);
            $doctorQuery->andWhere(['>=', 'updated_time', strtotime(trim($power_create_time_arr[0]))]);
            $doctorQuery->andWhere(['<=', 'updated_time', strtotime(trim($power_create_time_arr[1]) . ' 23:59:59')]);
        }
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    /**
     * 更新医生is_plus状态
     * @param $doctor_id
     * @return bool
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/14
     */
    public static function updateIsPlus($doctor_id = 0)
    {
        if (!$doctor_id) {
            return false;
        }
        $model = self::findOne($doctor_id);
        if ($model) {
            //查关联表
            $relationCount = DoctorModel::find()->where(['primary_id' => $doctor_id])->andWhere(['>','tp_platform',0])->count();
            ##如果医生来源大于0或者有其他非来源为0的医生设置此医生为主医生，都是is_plus=1
            if ($model->tp_platform > 0 || ($relationCount > 0)) {
                $is_plus = 1;
            }else {
                $is_plus = 0;
            }
            DoctorModel::updateAll(['is_plus' => $is_plus],['doctor_id' => $doctor_id]);
            return true;
        }
        return false;
    }

    /**
     * 导入医生
     * @param $data
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/15
     */
    public static function autoImportDoctor($data)
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
        $validator = new ImportDoctorValidator();
        $validator->load($data, '');
        if (!$validator->validate()) {
            $error = array_values($validator->getErrors());
            $return['msg'] = json_encode($error, JSON_UNESCAPED_UNICODE);
            return $return;
        }

        //判断是否已经导入过该医生
        $relationInfo = DoctorModel::find()
            ->where(['tp_platform' => $data['tp_platform']])
            ->andWhere(['tp_hospital_code' => $data['tp_hospital_code']])
            ->andWhere(['tp_doctor_id' => $data['tp_doctor_id']])
            ->andWhere(['tp_department_id' => $data['tp_department_id']])
            ->one();
        if ($relationInfo) {
            $return['code'] = 400;
            $return['msg'] = '该医生已存在';
            return $return;
        }

        //检测是否有相同医生存在 兼容多点执业医生
        $primary_id = 0;
        if (isset($data['visit_type']) && $data['visit_type'] == 2) {
            $primary_id = isset($data['primary_id']) ? $data['primary_id'] : 0;
        } else {
            if (!empty(ArrayHelper::getValue($data, 'tp_primary_id'))) {
                $doctorInfo = DoctorModel::find()->where([
                    'tp_platform' => $data['tp_platform'],
                    'tp_hospital_code' => $data['tp_hospital_code'],
                    'tp_primary_id' => $data['tp_primary_id'],
                    'primary_id' => 0,
                ])->asArray()->one();
                if ($doctorInfo) {
                    $primary_id = $doctorInfo['doctor_id'];
                }
            } else {
                $doctorInfo = DoctorModel::find()->where([
                    'tp_platform' => $data['tp_platform'],
                    'tp_hospital_code' => $data['tp_hospital_code'],
                    'tp_doctor_id' => $data['tp_doctor_id'],
                    'primary_id' => 0,
                ])->asArray()->one();
                if ($doctorInfo) {
                    $primary_id = $doctorInfo['doctor_id'];
                }
            }
        }

        //字段处理
        $doctorTitles = array_flip(CommonFunc::getTitle());
        if (isset($data['job_title_id']) && $data['job_title_id'] && CommonFunc::getTitle($data['job_title_id'])) {
            $data['job_title'] = CommonFunc::getTitle($data['job_title_id']);
        } else {
            $data['job_title'] = isset($doctorTitles[$data['job_title']]) ? $data['job_title'] : '未知';
            $data['job_title_id'] = $doctorTitles[$data['job_title']] ?? 99;
        }

        //上传头像 先同步上传
        $data['avatar'] = CommonFunc::uploadDoctorAvatarByUrl($data['source_avatar'] ?? '');

        //导入医生
        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            $doctorModel = new DoctorModel();
            $doctorModel->primary_id = $primary_id;
            $doctorModel->realname = $data['realname'];
            $doctorModel->tp_platform = $data['tp_platform'];
            $doctorModel->avatar = $data['avatar'];
            $doctorModel->source_avatar = $data['source_avatar'] ?? '';
            $doctorModel->job_title_id = $data['job_title_id'];
            $doctorModel->job_title = $data['job_title'];
            $doctorModel->hospital_id = $data['hospital_id'];
            $doctorModel->hospital_name = $data['hospital_name'];
            $doctorModel->hospital_type = $data['hospital_type'];
            $doctorModel->frist_department_id = $data['frist_department_id'];
            $doctorModel->second_department_id = $data['second_department_id'];
            $doctorModel->frist_department_name = $data['frist_department_name'];
            $doctorModel->second_department_name = $data['second_department_name'];
            $doctorModel->miao_doctor_id = 0;
            $doctorModel->miao_frist_department_id = $data['miao_frist_department_id'];
            $doctorModel->miao_second_department_id = $data['miao_second_department_id'];
            $doctorModel->tp_hospital_code = $data['tp_hospital_code'];
            $doctorModel->tp_doctor_id = $data['tp_doctor_id'];
            $doctorModel->tp_primary_id = $data['tp_primary_id'] ?? "";
            $doctorModel->tp_frist_department_id = $data['tp_frist_department_id'] ?? "";
            $doctorModel->tp_department_id = $data['tp_department_id'];
            $doctorModel->create_time = time();
            $doctorModel->update_time = time();
            $doctorModel->is_plus = 1;
            $doctorModel->admin_id = $data['admin_id'] ?? 0;
            $doctorModel->admin_name = $data['admin_name'] ?? 'system';
            $res = $doctorModel->save();
            if ($res) {
                $doctor_id = $doctorModel->attributes['doctor_id'];
                //新增医生附属信息
                $doctorInfoModel = new DoctorInfoModel();
                $doctorInfoModel->doctor_id = $doctor_id;
                $doctorInfoModel->good_at = $data['good_at'] ?? '';
                $doctorInfoModel->profile = $data['profile'] ?? '';
                $doctorInfoModel->professional_title = $data['professional_title'] ?? '';
                $doctorInfoModel->related_disease = '';
                $doctorInfoModel->initial = '';
                $doctorInfoModel->create_time = time();
                $doctorInfoModel->update_time = time();
                $doctorInfoModel->admin_id = $data['admin_id'] ?? 0;
                $doctorInfoModel->admin_name = $data['admin_name'] ?? 'system';
                $doctorInfoModel->save();
            } else {
                throw new \Exception(json_encode($doctorModel->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $transition->commit();
            $return['code'] = 200;
            $return['msg'] = '导入成功';
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . $msg);
            $return['msg'] = $msg;
            return $return;
        }

        return $return;
    }

    /**
     * 更新医生信息
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-27
     */
    public static function updateDoctorInfo($doctor_id,  $data)
    {
        //上传头像 先同步上传
        $data['avatar'] = CommonFunc::uploadDoctorAvatarByUrl($data['source_avatar'] ?? '');
        $doctorModel = self::findOne($doctor_id);
        $doctorModel->primary_id = isset($data['primary_id']) ? $data['primary_id'] : 0;;
        $doctorModel->realname = $data['realname'];
        $doctorModel->tp_platform = $data['tp_platform'];
        $doctorModel->avatar = $data['avatar'];
        $doctorModel->source_avatar = $data['source_avatar'] ?? '';
        $doctorModel->job_title_id = $data['job_title_id'];
        $doctorModel->job_title = $data['job_title'];
        $doctorModel->hospital_id = $data['hospital_id'];
        $doctorModel->hospital_name = $data['hospital_name'];
        $doctorModel->hospital_type = $data['hospital_type'];
        $doctorModel->frist_department_id = $data['frist_department_id'];
        $doctorModel->second_department_id = $data['second_department_id'];
        $doctorModel->frist_department_name = $data['frist_department_name'];
        $doctorModel->second_department_name = $data['second_department_name'];
        $doctorModel->miao_frist_department_id = $data['miao_frist_department_id'];
        $doctorModel->miao_second_department_id = $data['miao_second_department_id'];
        $doctorModel->tp_hospital_code = $data['tp_hospital_code'];
        $doctorModel->tp_doctor_id = $data['tp_doctor_id'];
        $doctorModel->tp_primary_id = $data['tp_primary_id'] ?? "";
        $doctorModel->tp_frist_department_id = $data['tp_frist_department_id'] ?? "";
        $doctorModel->tp_department_id = $data['tp_department_id'];
        $doctorModel->update_time = time();
        $doctorModel->admin_id = $data['admin_id'] ?? 0;
        $doctorModel->admin_name = $data['admin_name'] ?? 'system';
        $res = $doctorModel->save();
        if ($res) {
            //新增医生附属信息
            $doctorInfoModel = DoctorInfoModel::findOne($doctor_id);
            $doctorInfoModel->good_at = $data['good_at'] ?? '';
            $doctorInfoModel->profile = $data['profile'] ?? '';
            $doctorInfoModel->professional_title = $data['professional_title'] ?? '';
            $doctorInfoModel->update_time = time();
            $doctorInfoModel->admin_id = $data['admin_id'] ?? 0;
            $doctorInfoModel->admin_name = $data['admin_name'] ?? 'system';
            $doctorInfoModel->save();
        }
        return true;
    }

    /**
     * 更新医生doctor_real_plus状态
     * @param int $doctor_id
     * @param int $doctor_real_plus
     * @return bool
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/7
     */
    public static function updateRealPlus($doctor_id = 0, $doctor_real_plus = 0)
    {
        if (!$doctor_id) {
            return false;
        }
        DoctorModel::updateAll(['doctor_real_plus' => $doctor_real_plus], ['doctor_id' => $doctor_id]);
        return true;
    }

    /**
     * 医生信息表关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-21
     * @return \yii\db\ActiveQuery
     */
    public function getDoctorInfo()
    {
        return $this->hasOne(\common\models\DoctorInfoModel::class, ['doctor_id' => 'doctor_id']);
    }

    /**
     * 民营医院关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-25
     * @return \yii\db\ActiveQuery
     */
    public function getMinDoctor()
    {
        return $this->hasOne(MinDoctorModel::class, ['min_doctor_id' => 'tp_doctor_id']);
    }
}
