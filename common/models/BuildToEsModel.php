<?php

namespace common\models;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\DiseaseDepartmentModel;
use common\models\DiseaseEsModel;
use common\models\DiseaseModel;
use common\models\DoctorModel;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\models\HospitalEsModel;
use common\models\GuahaoScheduleplaceRelation;
use common\models\GuahaoScheduleplace;
use common\models\minying\MinHospitalModel;
use common\models\minying\MinDoctorModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class BuildToEsModel extends ActiveRecord
{

    /**
     * 根据id更新或者创建医院索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   [type]     $id [description]
     * @return  [type]         [description]
     */
    public function db2esByIdHospital($id = 0, $disHosIds = [], $disHosNames = [])
    {

        try {
            //查询数据
            $infoData = $this->getHospitalInfo($id);
            if ($infoData) {
                $hospitalEsModel = new HospitalEsModel();
                $query           = $hospitalEsModel->findOne($id);

                if ((!empty($disHosIds) && in_array($id, $disHosIds)) ||
                    (!empty($disHosNames) && in_array($infoData['hospital_name'], $disHosNames))) {
                    if (!empty($query)) {
                        $hospitalEsModel->deleteDocument($id);
                    }
                    return ['code' => 1];
                }

                //es已经存在数据 则更新
                if (!empty($query)) {
                    //获取es 属性名
                    $fields = $hospitalEsModel->attributes();
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $query[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    $query['hospital_doctor'] = ['name' => 'hospital'];
                    $hospitalEsModel->saveDocument($id, $query);
                } else {
                    //获取es 属性名
                    $fields              = $hospitalEsModel->attributes();
                    $data                = [];
                    $data['hospital_id'] = ArrayHelper::getValue($infoData, 'hospital_id');
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $data[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    $data['hospital_doctor'] = ['name' => 'hospital'];
                    $hospitalEsModel->createDocument($data['hospital_id'], $data);

                }
                unset($hospitalEsModel);
                return ['code' => 1];

            } else {
                throw new \Exception('数据不存在');
            }

        } catch (\Exception $e) {

            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 组装医院数据es结构
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   integer    $id [description]
     * @return  [type]         [description]
     */
    public function getHospitalInfo($id = 0)
    {
        $infoData = [];
        $info = BaseDoctorHospitals::getHospitalDetail($id);
        if($info){
            $infoData['hospital_id'] = ArrayHelper::getValue($info,'id');
            $infoData['province_id'] = ArrayHelper::getValue($info,'province_id');
            $infoData['city_id'] = ArrayHelper::getValue($info,'city_id');
            $infoData['district_id'] = ArrayHelper::getValue($info,'district_id');
            $infoData['province_name'] = ArrayHelper::getValue($info,'province_name');
            $infoData['hospital_name'] = \yii\helpers\Html::encode(ArrayHelper::getValue($info,'name'));
            $infoData['hospital_nick_name'] = ArrayHelper::getValue($info,'nick_name');
            $infoData['hospital_address'] = ArrayHelper::getValue($info,'address');
            $infoData['hospital_level'] = ArrayHelper::getValue($info,'level');
            $infoData['hospital_level_num'] = ArrayHelper::getValue($info,'level_num');
            $infoData['hospital_type'] = ArrayHelper::getValue($info,'type');
            $infoData['hospital_kind'] = ArrayHelper::getValue($info,'kind');
            $infoData['hospital_photo'] = ArrayHelper::getValue($info,'photo');
            $infoData['hospital_phone'] = ArrayHelper::getValue($info,'phone');
            $infoData['hospital_status'] = ArrayHelper::getValue($info,'status');
            $infoData['hospital_score'] = ArrayHelper::getValue($info,'score');
            $infoData['is_hospital_project'] = ArrayHelper::getValue($info,'is_hospital_project');
            $infoData['hospital_fudan_order'] = ArrayHelper::getValue($info,'fudan_order');
            $infoData['hospital_fudan_honor_score'] = ArrayHelper::getValue($info,'fudan_honor_score');
            $infoData['hospital_fudan_scientific_score'] = ArrayHelper::getValue($info,'fudan_scientific_score');
            $infoData['longitude'] = ArrayHelper::getValue($info,'longitude');
            $infoData['latitude'] = ArrayHelper::getValue($info,'latitude');
            $infoData['hospital_fudan_score'] = ArrayHelper::getValue($info,'fudan_score');
            $infoData['hospital_logo'] = ArrayHelper::getValue($info,'logo');
        }

        ##删除不存在的医院es，医生es
        if (!$infoData || $infoData['hospital_status'] == 1 || $infoData['is_hospital_project'] != 1) {
            HospitalEsModel::deleteHospitalEsData($id);
            return [];
        }          
        // if ($infoData['hospital_kind'] != '公立') {
        //     return [];
        // }
        if (in_array($infoData['province_id'], ['1','2','3','4'])) {
            $infoData['city_id'] = isset($infoData['district_id'])?$infoData['district_id']:$infoData['city_id'];
        }
        ##增加默认图片
        $infoData['hospital_photo'] = isset($infoData['hospital_photo']) ? $infoData['hospital_photo'] : 'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';
        $infoData['hospital_phone'] = isset($infoData['hospital_phone']) ? $infoData['hospital_phone'] : '';

        $infoData['hospital_nick_name'] = isset($infoData['hospital_nick_name']) ? $infoData['hospital_nick_name'] : '';
        $infoData['district_id'] = isset($infoData['district_id']) ? $infoData['district_id'] : '';
        $hospital_disease_id    = '';
        $hospital_department_id = '';
        $hospital_department_name = '';
        $hospital_good_at       = '';
        $deplist                = HospitalDepartmentRelation::find()->where(['hospital_id' => $id])->select('frist_department_id,second_department_id,frist_department_name,second_department_name')->asArray()->all();
        if ($deplist) {
            $fkeshilist             = array_column($deplist, 'frist_department_id');
            $skeshilist             = array_column($deplist, 'second_department_id');
            $fkeshilist             = array_filter($fkeshilist);
            $skeshilist             = array_filter($skeshilist);
            $hospital_disease_list  = array_unique(array_merge($fkeshilist, $skeshilist));
            $hospital_department_id = implode(' ', $hospital_disease_list);

            $fkeshinamelist = array_column($deplist, 'frist_department_name');
            $skeshinamelist = array_column($deplist, 'second_department_name');
            $fkeshinamelist = array_filter($fkeshinamelist);
            $skeshinamelist = array_filter($skeshinamelist);
            $hospital_department_name = array_unique(array_merge($skeshinamelist, $fkeshinamelist));
            $hospital_department_name = implode('、', $hospital_department_name);
        }
        ##擅长疾病标签
        $docIds = DoctorModel::find()->where(['primary_id'=>0,'hospital_id'=>$id])->select('doctor_id')->asArray()->all();
        $docIdsArr = [];
        if($docIds){
            foreach($docIds as $v){
                $docIdsArr[] = $v['doctor_id'];
            }
        }
        if($docIdsArr){
            $doclist = DoctorInfoModel::find()->where(['doctor_id' => $docIdsArr])->andWhere(['!=', 'related_disease', ''])->select('related_disease')->limit(10)->asArray()->all();
            if ($doclist) {
                $disidlist = array_column($doclist, 'related_disease');
                $dis_ids = implode(',', $disidlist);
                $dis_arr = array_unique(explode(',', $dis_ids));
                shuffle($dis_arr);
                $randdis = array_slice($dis_arr, 0, 15);
                $taglist = DiseaseModel::find()->select('disease_name')->where(['status' => 1])->andWhere(['in', 'disease_id', $randdis])->limit(15)->asArray()->all();
                if ($taglist) {
                    $tags = array_column($taglist, 'disease_name');
                    $tags = array_unique($tags);
                    $hospital_good_at = implode(',', $tags);
                }
            }
        }

        $infoData['hospital_department_id'] = $hospital_department_id;
        $infoData['hospital_department_name'] = $hospital_department_name;
        $infoData['hospital_disease_id']    = $hospital_disease_id;
        $infoData['hospital_good_at']       = $hospital_good_at;
        //判断 医院名下医生是否有加号  用于医院按加号排序
        $infoData['hospital_name_keyword'] = $infoData['hospital_name'];
        $infoData['hospital_nick_name_keyword'] = $infoData['hospital_nick_name'];
        $infoData['tp_platform'] = 0;
        $infoData['tp_hospital_code'] = '';
        ##查询医院平台和第三方code
        $infoData['hospital_is_plus'] = 0;
        $incr_num = DoctorModel::find()->where(['hospital_id' => $id,'is_plus'=>1])->count();
        $guahaoInfo = GuahaoHospitalModel::find()->where(['hospital_id' => $id,'status'=>1])->one();
        if ($guahaoInfo || $incr_num > 0) {
            $infoData['hospital_is_plus'] = 1;
            if ($guahaoInfo) {
                //更新民营医院标签到es中
                if ($guahaoInfo->tp_platform == 13 && !empty($guahaoInfo->tp_hospital_code)) {
                    $minHospitalModel = MinHospitalModel::findOne($guahaoInfo->tp_hospital_code);
                    $infoData['hospital_tags'] = $minHospitalModel->min_hospital_tags ? MinHospitalModel::getTagsInfo($minHospitalModel->min_hospital_tags) : "";
                }
            }
        }
        ##start 如果没有号查询是否是执业地，如果是执业地也认为是有号的 by yangquanliang 2020-01-25
        if (!$infoData['hospital_is_plus']) {
            $has_scheduleplace = GuahaoScheduleplace::find()->where(['hospital_id' => $id])->one();
            if ($has_scheduleplace) {
                $infoData['hospital_is_plus'] = 1;
            }
        }
        ##end 如果没有号查询是否是执业地，如果是执业地也认为是有号的 by yangquanliang 2020-01-25
        $tmpInfo = GuahaoHospitalModel::find()->select('tp_platform,tp_hospital_code,tp_guahao_section,tp_guahao_verify,tp_allowed_cancel_day,tp_allowed_cancel_time,tp_guahao_description')->where(['hospital_id' => $id])->asArray()->all();

        $infoData['tb_third_party_relation']=$tmpInfo??[];
        //经纬度
        $infoData['hospital_location'] = ['lat' => $infoData['latitude'], 'lon' => $infoData['longitude']];
        //等级别名
        $infoData['hospital_level_alias'] = CommonFunc::getLevelAlias($infoData['hospital_level_num']);

        //根据排班是否有号
        $snisiyaSdk = new SnisiyaSdk();
        $hospital_real_plus = $snisiyaSdk->getRealPlus(['hospital_id' => $id]);
        $infoData['hospital_real_plus'] = $hospital_real_plus > 0 ? 1 : 0;

        //获取放号时间
        $hospitalTimeConfig = GuahaoHospitalModel::find()
            ->select('tp_open_day,tp_open_time')
            ->where(['hospital_id' => $id, 'status' => 1])
            ->andWhere(['>', 'tp_open_day', '0'])
            ->andWhere(['<>', 'tp_open_time', ''])
            ->orderBy('id desc')
            ->asArray()
            ->one();
        $infoData['hospital_open_day'] = ArrayHelper::getValue($hospitalTimeConfig, 'tp_open_day', 0);
        $infoData['hospital_open_time'] = ArrayHelper::getValue($hospitalTimeConfig, 'tp_open_time', '');

        $infoData['hospital_or_doctor'] = 1;
        return $infoData;
    }

    /**
     * 根据id更新或者创建医生索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   [type]     $id [description]
     * @param   int        $is_console 是否是脚本执行  0 不是  1 是
     * @return  [type]         [description]
     */
    public function db2esByIdDoctor($id = 0, $is_console = 0)
    {
        try {
            //查询数据
            $infoData = $this->getDoctorInfo($id, $is_console);
            if ($infoData) {
                $hospitalEsModel = new DoctorEsModel();

                $query           = $hospitalEsModel->findOne('doctor-' . $infoData['doctor_id']);
                //es已经存在数据 则更新
                if (!empty($query)) {
                    //获取es 属性名
                    $fields = $hospitalEsModel->attributes();
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $query[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    $query['hospital_doctor'] = [
                        'name'   => 'doctor',
                        'parent' => $query['hospital_id'],
                    ];
                    $hospitalEsModel->saveDocument('doctor-' . $query['doctor_id'], $query);
                } else {
                    //获取es 属性名
                    $fields = $hospitalEsModel->attributes();
                    $data   = [];
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $data[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    // $data['hospital_id'] = $data['doctor_id'];##测试
                    $data['hospital_doctor'] = [
                        'name'   => 'doctor',
                        'parent' => $data['hospital_id'],
                    ];
                    $hospitalEsModel->createDocument('doctor-' . $data['doctor_id'], $data);

                }
                unset($hospitalEsModel);
                return ['code' => 1];

            } else {
                throw new \Exception('数据不存在');
            }

        } catch (\Exception $e) {

            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 组装医生es数据
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   integer    $id [description]
     * @param   int        $is_console 是否是脚本执行  0 不是  1 是
     * @return  [type]         [description]
     */
    public function getDoctorInfo($id = 0, $is_console = 0)
    {
        //$infoData = DoctorModel::find()->where(['doctor_id' => $id])->select('hospital_id,doctor_id,realname doctor_realname,avatar doctor_avatar,job_title doctor_title,job_title_id doctor_title_id,good_at doctor_good_at,profile doctor_profile,professional_title doctor_professional_title,frist_department_id doctor_frist_department_id,second_department_id doctor_second_department_id,frist_department_name doctor_frist_department_name,second_department_name doctor_second_department_name,related_disease doctor_disease_id,initial doctor_disease_initial,status,is_plus doctor_is_plus,miao_doctor_id,weight doctor_weight,miao_frist_department_id,miao_second_department_id')->asArray()->one();
        $selectStr = 'hospital_id,
        hospital_name,
        tp_hospital_code,
        doctor_id,
        realname doctor_realname,
        avatar doctor_avatar,
        job_title doctor_title,
        job_title_id doctor_title_id,
        frist_department_id doctor_frist_department_id,
        second_department_id doctor_second_department_id,
        frist_department_name doctor_frist_department_name,
        second_department_name doctor_second_department_name,
        status,
        is_plus doctor_is_plus,
        doctor_real_plus,
        miao_doctor_id,
        weight doctor_weight,
        miao_frist_department_id,
        miao_second_department_id,
        primary_id,
        tp_platform,
        tp_doctor_id,
        tp_department_id
        ';
        $infoData = DoctorModel::find()->where(['doctor_id' => $id])->select($selectStr)->asArray()->one();
        $infoData['doctor_realname'] = Html::encode($infoData['doctor_realname']);

        //脚本执行过滤子医生数据
        /*if ($is_console == 1 && !empty($infoData) && $infoData['primary_id'] != 0) {
            return [];
        }*/

        ##状态不对删除es
        if (!$infoData || $infoData['status'] != 1) {
            DoctorEsModel::deleteDoctorEsData($id);
            return [];
        }

        if ($infoData['primary_id'] == 0) {
            $primary_id = $id;
        } else {
            $primary_id = $infoData['primary_id'];
            $infoData = DoctorModel::find()->where(['doctor_id' => $primary_id, 'status' => 1])->select($selectStr)->asArray()->one();
        }
        if (!$infoData) {
            return [];
        }
        $doctors = DoctorModel::find()->where(['primary_id' => $primary_id])->select('doctor_id,
        tp_platform,
        tp_doctor_id,
        tp_department_id,
        second_department_name,
        status,
        hospital_id,
        hospital_name,
        tp_hospital_code,
        frist_department_id,
        frist_department_name,
        second_department_id,
        second_department_name,
        ')->asArray()->all();
        $primaryInfo = [
            'doctor_id' => $infoData['doctor_id'],
            'tp_platform' => $infoData['tp_platform'],
            'tp_doctor_id' => $infoData['tp_doctor_id'],
            'tp_department_id' => $infoData['tp_department_id'],
            'frist_department_id' => $infoData['doctor_frist_department_id'],
            'frist_department_name' => $infoData['doctor_frist_department_name'],
            'second_department_id' => $infoData['doctor_second_department_id'],
            'second_department_name' => $infoData['doctor_second_department_name'],
            'hospital_id' =>  $infoData['hospital_id'],
            'hospital_name' => $infoData['hospital_name'],
            'tp_hospital_code' => $infoData['tp_hospital_code'],
        ];
        $relationInfo[] = $primaryInfo;
        $doctor_frist_depid_arr = [];
        $doctor_second_depid_arr = [];
        if ($doctors) {
            foreach ($doctors as $v) {
                //删除子医生es
                DoctorEsModel::deleteDoctorEsData($v['doctor_id']);
                //子医生组合
                if ($v['status'] == 1) {
                    unset($v['status']);
                    $relationInfo[] = $v;
                }
                $doctor_frist_depid_arr[] = $v['frist_department_id'];
                $doctor_second_depid_arr[] = $v['second_department_id'];
            }
        }

        //附表信息
        $doctorInfo = DoctorInfoModel::find()->where(['doctor_id' => $primary_id])->select('good_at doctor_good_at,profile doctor_profile,professional_title doctor_professional_title,related_disease doctor_disease_id,initial doctor_disease_initial')->asArray()->one();
        if ($doctorInfo) {
            $infoData = ArrayHelper::merge($infoData, $doctorInfo);
        }

        if (mb_strlen($infoData['doctor_profile']) > 1000) {
            $infoData['doctor_profile'] = mb_substr($infoData['doctor_profile'], 0,1000);
        }
        unset($infoData['status']);
        unset($infoData['primary_id']);
        //$hospitalData = BaseDoctorHospitals::find()->where(['id' => $infoData['hospital_id']])->select('kind,status,is_hospital_project,level hospital_level')->asArray()->one();

        $hospitalData = BaseDoctorHospitals::getHospitalDetail($infoData['hospital_id']);

        if (!$hospitalData) {
            return [];
        }
        $hospitalData['level'] = isset($hospitalData['level']) ? $hospitalData['level'] : "";
        if ($hospitalData['is_hospital_project'] != 1 || $hospitalData['status'] != 0) {
            return [];
        }
        $infoData['hospital_level'] = isset($hospitalData['hospital_level']) ? $hospitalData['hospital_level'] : "";
        // if ($hospitalData['kind'] != '公立') {
        //     return [];
        // }  
        //unset($hospitalData);
        $infoData['doctor_disease_name'] = '';
        if ($infoData['doctor_disease_id']) {
            $dis_ids = explode(',', $infoData['doctor_disease_id']);
            $dis_ids             = array_unique($dis_ids);
            $randdis = array_slice($dis_ids, 0, 15);
            $taglist = DiseaseModel::find()->select('disease_name')->where(['in', 'disease_id', $randdis])->limit(15)->asArray()->all();
            if ($taglist) {
                $tags             = array_column($taglist, 'disease_name');
                $infoData['doctor_disease_name'] = implode(',', $tags);
            }
            $infoData['doctor_disease_id'] = str_replace(',', ' ', $infoData['doctor_disease_id']);
        }
        if ($infoData['doctor_disease_initial']) {
            $infoData['doctor_disease_initial'] = str_replace(',', ' ', $infoData['doctor_disease_initial']);
        }
        //$doctor_hospital             = BaseDoctorHospitals::find()->where(['id' => $infoData['hospital_id']])->select('name')->asArray()->scalar();
        //$hosRow = BaseDoctorHospitals::getHospitalDetail($infoData['hospital_id']);
        //$doctor_hospital = ArrayHelper::getValue($hosRow,'name');
        $doctor_hospital = ArrayHelper::getValue($hospitalData,'name');
        $infoData['doctor_hospital'] = $doctor_hospital ?? '';
        $infoData['doctor_avatar'] = $infoData['doctor_avatar']=='' ?'https://u.nisiyacdn.com/avatar/default_2.jpg' : \Yii::$app->params['avatarUrl'] .$infoData['doctor_avatar'];
        
        $infoData['doctor_realname_keyword'] = $infoData['doctor_realname'];

        $infoData['tb_third_doctor_relation'] = $relationInfo;


        $infoData['doctor_frist_depid_arr'] = $doctor_frist_depid_arr;
        $infoData['doctor_second_depid_arr'] = $doctor_second_depid_arr;

        ##医生其他执业地
        $scheduleplace_list = GuahaoScheduleplaceRelation::find()->select('DISTINCT(scheduleplace_name) hospital_name')->where(['doctor_id' => $id])->asArray()->all();
        $doctor_third_scheduleplace = '';
        if ($scheduleplace_list) {
            $doctor_third_scheduleplace             = array_column($scheduleplace_list, 'hospital_name');
            if ($doctor_third_scheduleplace) {
                $doctor_third_scheduleplace = implode(',', $doctor_third_scheduleplace);
            }
        }
        //主次医生所属医院 && 是否是民营医院
        $doctor_is_min = 0;
        $relation_doctor_id = [$primary_id];//全部医生id
        if ($relationInfo) {
            foreach ($relationInfo as $v) {
                $relation_doctor_id[] = $v['doctor_id'];
                $doctor_third_scheduleplace .= $v['hospital_name'] . ',';
                if ($v['tp_platform'] == 13) {
                    $doctor_is_min = 1;
                }
            }
        }

        //更新民营医院标签到es中
        if ($infoData['tp_platform'] == 13) {
            $minDoctorModel = MinDoctorModel::findOne($infoData['tp_doctor_id']);
            $infoData['doctor_tags'] = $minDoctorModel->min_doctor_tags ? MinDoctorModel::getTagsInfoById($minDoctorModel->min_doctor_tags) : "";
            $infoData['doctor_visit_type'] = ($minDoctorModel->visit_type == 2) ? MinDoctorModel::$visitType[$minDoctorModel->visit_type] : "";
        } else {
            //获取所有民营子医生的标签
            $childDoctor = DoctorModel::find()->where(['status' => 1, 'primary_id' => $primary_id, 'tp_platform' => 13])->select('doctor_id,tp_doctor_id')->asArray()->all();
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
                    $infoData['doctor_tags'] = implode('、', array_unique(explode('、', trim($childTags, '、'))));
                }
                $infoData['doctor_visit_type'] = MinDoctorModel::$visitType[2];
            }
        }

        //更新订单量 0:下单成功 2:停诊 3:已完成 4:爽约
        $infoData['doctor_order_count'] = GuahaoOrderModel::find()->where(['doctor_id' => array_unique($relation_doctor_id), 'state' => ['0', '2', '3', '4']])->count();
        
        $infoData['tb_doctor_third_scheduleplace'] = rtrim($doctor_third_scheduleplace,',');
        //医院科室主键
        $relationId = HospitalDepartmentRelation::find()->where(['hospital_id'=>$infoData['hospital_id'],'second_department_id'=>$infoData['doctor_second_department_id']])->select('id')->scalar();
        $infoData['doctor_department_relation_id'] = $relationId?$relationId:0;

        //根据排班是否有号
        $snisiyaSdk = new SnisiyaSdk();
        $doctor_real_plus = $snisiyaSdk->getRealPlus(['doctor_id' => $primary_id]);
        if ($infoData['doctor_real_plus'] != $doctor_real_plus) {
            $infoData['doctor_real_plus'] = $doctor_real_plus;
            DoctorModel::updateRealPlus($id, $doctor_real_plus);
        }

        //是否有民营医院挂号
        if ($doctor_is_min == 1) {
            $doctor_real_plus = $snisiyaSdk->getRealPlus(['doctor_id' => $primary_id, 'tp_platform' => 13]);
            $infoData['doctor_min_plus'] = $doctor_real_plus > 0 ? 1 : 0;
        } else {
            $infoData['doctor_min_plus'] = 0;
        }

        $infoData['hospital_or_doctor'] = 2;
        return $infoData;
    }

    /**
     * 根据id更新或者创建疾病索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   [type]     $id [description]
     * @return  [type]         [description]
     */
    public function db2esByIdDisease($id = 0)
    {
        try {
            //查询数据
            $infoData = $this->getDiseaseDepartment($id);
            if ($infoData) {
                $diseaseEsModel = new DiseaseEsModel();
                $query          = $diseaseEsModel->findOne($id);
                //es已经存在数据 则更新
                if (!empty($query)) {
                    //获取es 属性名
                    $fields = $diseaseEsModel->attributes();
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $query[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    $diseaseEsModel->saveDocument($query['disease_id'], $query);
                } else {
                    //获取es 属性名
                    $fields = $diseaseEsModel->attributes();
                    $data   = [];
                    //根据属性名赋值
                    foreach ($fields as $k => $v) {
                        $data[$v] = ArrayHelper::getValue($infoData, $v, '');
                    }
                    $diseaseEsModel->createDocument($data['disease_id'], $data);

                }
                unset($diseaseEsModel);
                return ['code' => 1];

            } else {
                throw new \Exception('数据不存在');
            }

        } catch (\Exception $e) {

            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 获取疾病信息以及疾病下的科室id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-25
     * @version 1.0
     * @param   string     $disease_id [description]
     * @return  [type]                 [description]
     */
    public function getDiseaseDepartment($disease_id = '')
    {

        $keshi_list = DiseaseDepartmentModel::find()->select('disease_id,disease_name,initial,pinyin,status,frist_department_id,second_department_id')->where(['disease_id' => $disease_id, 'status' => 1])->asArray()->all();
        if (!$keshi_list) {
            return [];
        }
        $infoData                    = [];
        $infoData['disease_id']      = $disease_id;
        $infoData['disease_name']    = $keshi_list[0]['disease_name'] ?? '';
        $infoData['disease_keyword'] = $infoData['disease_name'] ?? '';
        $infoData['initial']         = $keshi_list[0]['initial'] ?? '';
        $infoData['pinyin']          = $keshi_list[0]['pinyin'] ?? '';
        $infoData['status']          = $keshi_list[0]['status'] ?? 1;
        $frist_department_id         = '';
        $second_department_id        = '';
        if ($keshi_list) {
            $fkeshilist           = array_column($keshi_list, 'frist_department_id');
            $skeshilist           = array_column($keshi_list, 'second_department_id');
            $fkeshilist           = array_filter($fkeshilist);
            $skeshilist           = array_filter($skeshilist);
            $frist_department_id  = implode(',', array_unique($fkeshilist));
            $second_department_id = implode(',', array_unique($skeshilist));
        }

        $infoData['frist_department_id']  = $frist_department_id;
        $infoData['second_department_id'] = $second_department_id;
        return $infoData;
    }

}
