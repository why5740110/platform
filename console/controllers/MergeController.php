<?php
/**
 * 合并医生数据
 * @author yangquanliang <yangquanliang@yuanxinjituan.com>
 * @version 1.0
 * date 2021-06-09
 */

namespace console\controllers;

use common\models\BaseDoctorHospitals;
use common\models\DoctorInfoModel;
use common\models\TmpDoctorThirdPartyModel;
use common\models\DoctorModel;
use yii\web\Cookie;
use common\models\TbLog;
use yii\helpers\ArrayHelper;
use common\models\DoctorPrimaryModel;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoScheduleplaceRelation;
use common\models\TbDoctorThirdPartyRelationModel;
use Yii;

class MergeController extends CommonController
{
    /**
     * @合并关联表医生数据到tb_doctor表
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-09
     * @param integer $start
     * @param integer $end
     * @return void
     */
    public function actionRun($start_id = 0, $end_id = 0)
    {
        $query       = TbDoctorThirdPartyRelationModel::find()->where(['status' => 1]);
        $pageSize    = 1000;
        $execute_num = 0;
        $order_field = [1, 2, 5, 3, 6, 4];
        $error_num   = 0;
        $page        = 1;
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'id', (int) ($start_id)]);
            $query->andWhere(['<=', 'id', (int) ($end_id)]);
        }
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        do {
            if ($page > $maxPage) {
                break;
            }
            $offset = max(0, ($page - 1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy(["FIELD(tp_platform, " . join(',', $order_field) . ")" => true])->asArray()->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => &$value) {
                $execute_num++;
                $doctor_id = $value['doctor_id'];
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $docker_info = DoctorModel::find()->where([
                    'doctor_id'              => $value['doctor_id'],
                    'tp_platform'            => $value['tp_platform'],
                    'is_plus'                => 1,
                    'tp_doctor_id'           => $value['tp_doctor_id'],
                    'tp_hospital_code'       => $value['tp_hospital_code'],
                    'tp_frist_department_id' => $value['tp_frist_department_id'],
                    'tp_department_id'       => $value['tp_department_id'],
                ])->one();
                if ($docker_info) {
                    ##如果存在来源id完全一直医生跳过
                    continue;
                }
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $doctor_model = DoctorModel::find()->where(['doctor_id' => $value['doctor_id']])->one();
                    if ($doctor_model->is_plus == 0) {
                        $doctor_model->tp_platform            = $value['tp_platform'] ?? 0;
                        $doctor_model->tp_hospital_code       = $value['tp_hospital_code'];
                        $doctor_model->tp_doctor_id           = $value['tp_doctor_id'];
                        $doctor_model->tp_frist_department_id = $value['tp_frist_department_id'];
                        $doctor_model->tp_department_id       = $value['tp_department_id'];
                        $doctor_model->is_plus                = 1;
                        $doctor_model->admin_id               = $value['admin_id'];
                        $doctor_model->admin_name             = $value['admin_name'];
                        $doc_res                              = $doctor_model->save();
                        if (!$doc_res) {
                            throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                    } else {
                        unset($value['doctor_id']);
                        $value['primary_id']                = $doctor_id;
                        $value['realname']                  = $doctor_model['realname'];
                        $value['avatar']                    = $doctor_model['avatar'];
                        $value['job_title_id']              = $doctor_model['job_title_id'];
                        $value['job_title']                 = $doctor_model['job_title'];
                        $value['hospital_id']               = $doctor_model['hospital_id'];
                        $value['hospital_name']             = $doctor_model['hospital_name'];
                        $value['frist_department_id']       = $doctor_model['frist_department_id'];
                        $value['frist_department_name']     = $doctor_model['frist_department_name'];
                        $value['second_department_id']      = $doctor_model['second_department_id'];
                        $value['second_department_name']    = $doctor_model['second_department_name'];
                        $value['miao_frist_department_id']  = $doctor_model['miao_frist_department_id'];
                        $value['miao_second_department_id'] = $doctor_model['miao_second_department_id'];
                        $value['is_plus']                   = 1;
                        $value['weight']                    = $doctor_model['weight'];
                        $docinfo_item                       = DoctorInfoModel::find()->where(['doctor_id' => $doctor_id])->one();
                        if (!$docinfo_item) {
                            throw new \Exception('医生附属信息不存在！');
                        }
                        $value['good_at']            = $docinfo_item['good_at'] ?? '';
                        $value['profile']            = $docinfo_item['profile'] ?? '';
                        $value['professional_title'] = $docinfo_item['professional_title'] ?? '';
                        $value['related_disease']    = $docinfo_item['related_disease'] ?? '';
                        $value['initial']            = $docinfo_item['initial'] ?? '';

                        $doctor_model = new DoctorModel();
                        $doctor_item  = array_keys($doctor_model->attributeLabels());
                        $info_model   = new DoctorInfoModel();
                        $info_item    = $info_model->attributeLabels();
                        $info_item    = array_keys($info_model->attributeLabels());
                        foreach ($value as $v_key => $v_item) {
                            if (in_array($v_key, $doctor_item)) {
                                $doctor_model->$v_key = $v_item;
                            } elseif (in_array($v_key, $info_item)) {
                                $info_model->$v_key = $v_item;
                            }
                        }
                        $doctor_model->admin_name = 'system';
                        $info_model->admin_name = 'system';
                        $doc_res = $doctor_model->save();
                        if (!$doc_res) {
                            throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                        $info_doctor_id        = $doctor_model->attributes['doctor_id'];
                        $info_model->doctor_id = $info_doctor_id;
                        $info_res              = $info_model->save();
                        if (!$info_res) {
                            throw new \Exception(json_encode($info_model->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                    }

                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "id:{$value['id']} 医生id:{$doctor_id}" . " 医生保存失败:{$msg}！\n";
                    // break;
                }
            }
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * @补充docker_info信息拆分医生不常用信息到info表
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @version 1.0
     * date 2021-06-10
     * @return void
     */
    public function actionInfo($start_id = 0, $end_id = 0)
    {
        $query       = DoctorModel::find()->where([]);
        $pageSize    = 1000;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', (int) ($start_id)]);
            $query->andWhere(['<', 'doctor_id', (int) ($end_id)]);
        }
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        do {
            if ($page > $maxPage) {
                break;
            }
            $offset = max(0, ($page - 1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->asArray()->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => &$value) {
                $execute_num++;
                $hospital_info          = BaseDoctorHospitals::getInfo($value['hospital_id']);
                $hospital_type = ArrayHelper::getValue($hospital_info,'kind') == '公立' ? 1 : 2;
                $value['hospital_name'] = $hospital_info['name'] ?? '';
                $value['hospital_type'] = $hospital_type;
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $doc_info = DoctorPrimaryModel::find()->select('doctor_id')->where(['doctor_id' => $value['doctor_id']])->one();
                if ($doc_info) {
                    continue;
                }
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $doctor_model          = new DoctorPrimaryModel();
                    $doctor_item           = array_keys($doctor_model->attributeLabels());
                    $info_model            = new DoctorInfoModel();
                    $info_item             = $info_model->attributeLabels();
                    $info_item             = array_keys($info_model->attributeLabels());
                    $info_model->doctor_id = $value['doctor_id'];
                    foreach ($value as $v_key => $v_item) {
                        if (in_array($v_key, $doctor_item)) {
                            $doctor_model->$v_key = $v_item;
                        } elseif (in_array($v_key, $info_item)) {
                            $info_model->$v_key = $v_item;
                        }
                    }
                    $doc_res  = $doctor_model->save();
                    $info_res = $info_model->save();
                    if (!$doc_res) {
                        throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                    if (!$info_res) {
                        throw new \Exception(json_encode($info_model->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . $value['doctor_id'] . " 医生保存失败:{$msg}！\n";
                }
            }
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 合并tb_doctor关联主键医生
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-11
     * @version v1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @return  [type]               [description]
     */
    public function actionDoctor($start_id = 0, $end_id = 0)
    {
        $query       = DoctorModel::find()->select('min(doctor_id) doctor_id,tp_hospital_code,tp_platform,tp_doctor_id,count(1) cnum')->where([]);
        $pageSize    = 1000;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        ##处理健康160同第三方医生id的合并
        $query->andWhere(['=', 'tp_platform', 5]);
        $query->andWhere(['=', 'primary_id', 0]);
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', (int) ($start_id)]);
            $query->andWhere(['<=', 'doctor_id', (int) ($end_id)]);
        }
        $query->groupBy('tp_doctor_id');
        $query->having(['>', 'cnum', 1]);
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        $temp_maxPage = $maxPage;
        do {
            if ($temp_maxPage < 1) {
                break;
            }
            // $offset = max(0, ($page - 1)) * $pageSize;
            $tpage      = $temp_maxPage-1;
            $offset     = max(0, $tpage) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('doctor_id asc')->asArray()->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doctor_model) {
                $execute_num++;
                $doctor_id = $doctor_model['doctor_id'];
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $doc_res = DoctorModel::updateAll(['primary_id' => $doctor_id],
                        ['and', 
                            ['tp_platform' => $doctor_model['tp_platform']],
                            ['tp_hospital_code' => $doctor_model['tp_hospital_code']],
                            ['tp_doctor_id' => $doctor_model['tp_doctor_id']],
                            ['primary_id' => 0],
                            ['<>', 'doctor_id', $doctor_id],
                        ]
                    );
                    if (!$doc_res) {
                        throw new \Exception("医生id:{$doctor_id} 更新失败！");
                    }

                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "医生id:{$doctor_id}" . " 医生保存失败:{$msg}！\n";
                }
            }
            $page++;
            $temp_maxPage--;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 补充医生表医院信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-01
     * @version v1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @return  [type]               [description]
     */
    public function actionHospital($start_id = 0, $end_id = 0)
    {
        $query       = DoctorModel::find()->where([]);
        $pageSize    = 1000;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;

        $query->andWhere(['>', 'hospital_id', 0]);
        $query->andWhere(['=', 'hospital_name', '']);
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', (int) ($start_id)]);
            $query->andWhere(['<=', 'doctor_id', (int) ($end_id)]);
        }
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        $temp_maxPage = $maxPage;
        do {
            if ($temp_maxPage < 1) {
                break;
            }
            // $offset = max(0, ($page - 1)) * $pageSize;
            $tpage      = $temp_maxPage-1;
            $offset     = max(0, $tpage) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('doctor_id asc')->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doctor_model) {
                $execute_num++;
                $doctor_id = $doctor_model['doctor_id'];
                $hospital_id = $doctor_model['hospital_id'];
                echo "最大分页{$maxPage} 当前第{$temp_maxPage}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    //$hospital_info = BaseDoctorHospitals::find()->select('name')->where(['id'=>$doctor_model->hospital_id])->one();
                    $hospital_info = BaseDoctorHospitals::getHospitalDetail($doctor_model->hospital_id);
                    $doctor_model->hospital_name = ArrayHelper::getValue($hospital_info,'name');
                    $doc_res                  = $doctor_model->save();
                    if (!$doc_res) {
                        throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
                    }

                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "医生id:{$doctor_id}" . " 医生保存失败:{$msg}！\n";
                    break;
                }
            }
            $temp_maxPage--;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 修改临时表医生id
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-29
     * @version v1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @return  [type]               [description]
     */
    public function actionTmpDoctor($start_id = 0, $end_id = 0)
    {
        $query       = TmpDoctorThirdPartyModel::find()->where([]);
        $pageSize    = 1000;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;

        $query->andWhere(['>', 'doctor_id', 0]);
        $query->andWhere(['=', 'status', 1]);
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'id', (int) ($start_id)]);
            $query->andWhere(['<=', 'id', (int) ($end_id)]);
        }
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        $temp_maxPage = $maxPage;
        do {
            if ($temp_maxPage < 1) {
                break;
            }
            // $offset = max(0, ($page - 1)) * $pageSize;
            $tpage      = $temp_maxPage-1;
            $offset     = max(0, $tpage) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('id asc')->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doctor_model) {
                $execute_num++;
                $id = $doctor_model['id'];
                echo "最大分页{$maxPage} 当前第{$temp_maxPage}页 共{$total}条数据 id {$id} 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                try {
                    $doctor_info = DoctorModel::find()->select('doctor_id')->where([
                        'tp_platform'=>$doctor_model->tp_platform,
                        'tp_doctor_id'=>$doctor_model->tp_doctor_id,
                        'tp_hospital_code'=>$doctor_model->tp_hospital_code,
                        'tp_department_id'=>$doctor_model->tp_department_id,
                    ])->one();
                    $doctor_model->doctor_id = $doctor_info->doctor_id ?? 0;
                    $doc_res                  = $doctor_model->save();
                    if (!$doc_res) {
                        throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                } catch (\Exception $e) {
                    $error_num++;
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "id:{$id}" . " 医生保存失败:{$msg}！\n";
                    break;
                }
            }
            $temp_maxPage--;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 更新出诊机构第三方医生ID
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-01
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionUpTpDoctor()
    {
        $query = GuahaoScheduleplaceRelation::find()->where(['tp_platform'=>6]);
        $query->andWhere(['=', 'tp_doctor_id', '']);
        $pageSize    = 200;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        $total   = $query->count();
        do {
            $offset     = max(0, ($page-1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('id asc')->all();
            if (!$list) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $place_info) {
                try {
                    $doctor_item = DoctorModel::find()->where([
                        'doctor_id'=>$place_info['doctor_id'],
                        'tp_platform'=>6,
                    ])->asArray()->one();
                    if (!$doctor_item) {
                        $doctor_item = DoctorModel::find()->where([
                            'primary_id'=>$place_info['doctor_id'],
                            'tp_platform'=>6,
                        ])->asArray()->one();
                    }
                    if (!$doctor_item) {
                        $doctor_item = TmpDoctorThirdPartyModel::find()->where([
                            'doctor_id'=>$place_info['doctor_id'],
                            'tp_platform'=>6,
                        ])->asArray()->one();
                    }
                    $place_info->tp_doctor_id = $doctor_item['tp_doctor_id'] ?? '';
                    $tmp_res = $place_info->save();
                    if (!$tmp_res) {
                        throw new \Exception(json_encode($place_info->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                    echo "{$place_info['doctor_id']}:更新成功！" . "\n";

                } catch (\Exception $e) {
                    $error_num++;
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . $place_info['doctor_id'].'-'.$place_info['tp_scheduleplace_id'] . " 医生保存失败:{$msg}！\n";
                }
            }
            
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);

         echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 更新出诊地管理表第三方医生ID
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-02
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionSchDoctor()
    {
        $query = GuahaoScheduleplaceRelation::find()->where(['tp_platform'=>6]);
        $pageSize    = 200;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        $total   = $query->count();
        do {
            $offset     = max(0, ($page-1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('id asc')->all();
            if (!$list) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $schModel) {
                $execute_num++;
                $doctor_item = TbDoctorThirdPartyRelationModel::find()->select('tp_doctor_id')->where([
                    'doctor_id' => $schModel['doctor_id'],
                    'tp_platform' => 6,
                ])->asArray()->one();
                if (!$doctor_item) {
                    echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 不存在关联医生数据跳过！" . "\n";
                    continue;
                }
                if ($schModel->tp_doctor_id != '') {
                    echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 数据已更新跳过！" . "\n";
                    continue;
                }
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $schModel->tp_doctor_id = $doctor_item['tp_doctor_id'];
                    $tmp_res = $schModel->save();
                    if (!$tmp_res) {
                        throw new \Exception(json_encode($schModel->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                    $transition->commit();

                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo $schModel['doctor_id'].'-'.$schModel['tp_scheduleplace_id'] . " 医生保存失败:{$msg}！\n";
                    continue;
                }
                echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 保存成功！" . "\n";
            }
            unset($transition);
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 合并王氏加号医生多点执业到医生主表
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-01
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionScheduleplace()
    {
        $query = GuahaoScheduleplaceRelation::find()->where(['tp_platform'=>6]);
        $query->andWhere(['>', 'hospital_department_id', 0]);
        $query->andWhere(['!=', 'tp_doctor_id', '']);
        $pageSize    = 500;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        $total   = $query->count();
        do {
            $offset     = max(0, ($page-1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('id asc')->all();
            if (!$list) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $schModel) {
                $execute_num++;
                 ##查询相同来源
                $doctor_item = DoctorModel::find()->where([
                    'tp_doctor_id'=>$schModel['tp_doctor_id'],
                    'tp_hospital_code'=>$schModel['tp_scheduleplace_id'],
                    'tp_platform'=>6,
                ])->asArray()->one();
                if ($doctor_item) {
                    $schModel->doctor_id = $doctor_item['doctor_id'];
                    $tmp_res = $schModel->save();
                    echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 已存在跳过！" . "\n";
                    continue;
                }
                $doctor_info = DoctorModel::find()->where([
                    'tp_doctor_id'=>$schModel['tp_doctor_id'],
                    'tp_platform'=>6,
                ])->asArray()->one();
                if (!$doctor_info) {
                    echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 医生没有关联关系跳过！" . "\n";
                    continue;
                }

                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $tmp_doctor = [
                        'primary_id'=>$doctor_info['primary_id'] > 0 ? $doctor_info['primary_id'] : $doctor_info['doctor_id'],
                        'realname'=>$doctor_info['realname'],
                        'tp_platform'=>6,
                        'avatar'=>$doctor_info['avatar'],
                        'source_avatar'=>$doctor_info['source_avatar'],
                        'job_title_id'=>$doctor_info['job_title_id'],
                        'job_title'=>$doctor_info['job_title'],
                        'good_at'=>$doctor_info['good_at'] ?? '',
                        'profile'=>$doctor_info['profile'] ?? '',
                        'related_disease'=>$doctor_info['related_disease'] ?? '',
                        'tp_doctor_id'=>$schModel['tp_doctor_id'],

                    ];
                    $depment_info = HospitalDepartmentRelation::find()->where(['id'=>$schModel['hospital_department_id']])->asArray()->one();
                    if (!$depment_info) {
                        echo "{$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 出诊地科室不存在跳过！" . "\n";
                        continue;
                    }
                    if ($depment_info) {
                        $tmp_doctor['hospital_id'] = $schModel['tp_scheduleplace_id'];
                        $tmp_doctor['tp_hospital_code'] = $schModel['tp_scheduleplace_id'];
                        $tmp_doctor['hospital_name'] = $schModel['scheduleplace_name'];
                        $tmp_doctor['frist_department_id'] = $depment_info['frist_department_id'];
                        $tmp_doctor['frist_department_name']=$depment_info['frist_department_name'];
                        $tmp_doctor['second_department_id']=$depment_info['second_department_id'];
                        $tmp_doctor['second_department_name']=$depment_info['second_department_name'];
                        $tmp_doctor['miao_frist_department_id']=$depment_info['miao_frist_department_id'];
                        $tmp_doctor['miao_second_department_id']=$depment_info['miao_second_department_id'];
                    }
                    $attr_doctor = DoctorModel::saveDoctor($tmp_doctor,false);
                    if ($attr_doctor['doctor_id'] > 0) {
                        $schModel->doctor_id = $attr_doctor['doctor_id'];
                        $tmp_res = $schModel->save();
                        if (!$tmp_res) {
                            throw new \Exception(json_encode($schModel->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                    }else{
                        throw new \Exception($attr_doctor['msg']);
                    }
                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo $schModel['doctor_id'].'-'.$schModel['tp_scheduleplace_id'] . " 医生保存失败:{$msg}！\n";
                    continue;
                }
                echo "共{$total}条数据 当前第{$execute_num}条 {$schModel['doctor_id']}:{$schModel['tp_scheduleplace_id']} 保存成功！" . "\n";
            }
            unset($transition);
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 按照一级科室合并重名医生
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-07-12
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionKeshi($tp_platform = 1)
    {
        $query       = DoctorModel::find()->select('min(doctor_id) doctor_id,realname,tp_platform,hospital_name,frist_department_name,job_title,count(1) cnum')->where([]);
        $pageSize    = 500;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        ##处理健康160同第三方医生id的合并
        $query->andWhere(['=', 'tp_platform', $tp_platform]);
        $query->groupBy('realname,hospital_name,frist_department_name,job_title');
        $query->having(['>', 'cnum', 1]);
        $total   = $query->count();
        // $getRawSql =  $query->createCommand()->getRawSql();
        $maxPage = ceil($total / $pageSize);
        do {
            $offset = max(0, ($page - 1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('doctor_id asc')->asArray()->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doctor_model) {
                $execute_num++;
                $doctor_id = $doctor_model['doctor_id'];
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条 医生id:{$doctor_id}" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                ##查询已经设置过主医生
                $have_primary = DoctorModel::find()->where(['tp_platform'=>$tp_platform,'primary_id'=>$doctor_id])->count();
                if ($have_primary) {
                    echo "医生id：{$doctor_id} 已经存在子医生跳过！" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                    continue;
                }
                ##查询此医生是否为其他子医生
                $tdoctor_info = DoctorModel::find()->select('doctor_id,primary_id')->where(['doctor_id'=>$doctor_id])->asArray()->one();
                if ($tdoctor_info['primary_id'] > 0) {
                    echo "医生id：{$doctor_id} 为{$tdoctor_info['primary_id']}的子医生跳过！" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                    continue;
                }

                $p_num = DoctorModel::find()->select('doctor_id')->where([
                    'tp_platform'=>$tp_platform,
                    'realname'=>$doctor_model['realname'],
                    'hospital_name'=>$doctor_model['hospital_name'],
                    'frist_department_name'=>$doctor_model['frist_department_name'],
                    'job_title'=>$doctor_model['job_title'],
                ])->andWhere(['>', 'primary_id', 0])->count();
                if ($p_num > 0) {
                    echo "医生id：{$doctor_id} 已经存在子医生关系跳过！" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                    continue;
                }

                $suc_ids = DoctorModel::find()->select('doctor_id')->where([
                    'tp_platform'=>$tp_platform,
                    'realname'=>$doctor_model['realname'],
                    'hospital_name'=>$doctor_model['hospital_name'],
                    'frist_department_name'=>$doctor_model['frist_department_name'],
                    'job_title'=>$doctor_model['job_title'],
                    'primary_id'=>0,
                ])->andWhere(['<>', 'doctor_id', $doctor_id])->column();
                ##查询是否有子医生为其他医生主医生
                // $has_child_doctor = DoctorModel::find()->select('primary_id')->where([])->andWhere(['in', 'primary_id', $suc_ids])->count();
                // if ($has_child_doctor) {
                //     echo "医生id：{$doctor_id} 已经存在子医生关系跳过！" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                //     continue;
                // }
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    $doc_res = DoctorModel::updateAll(['primary_id' => $doctor_id],
                        ['and',
                            ['tp_platform' => $doctor_model['tp_platform']],
                            ['realname' => $doctor_model['realname']],
                            ['hospital_name' => $doctor_model['hospital_name']],
                            ['frist_department_name' => $doctor_model['frist_department_name']],
                            ['job_title' => $doctor_model['job_title']],
                            ['primary_id' => 0],
                            ['<>', 'doctor_id', $doctor_id],
                        ]
                    );
                    if (!$doc_res) {
                        throw new \Exception("医生id:{$doctor_id} 更新失败！");
                    }
                    $editContent = "system 关联了医生id{" . implode(',', $suc_ids) . "}的主键医生:{$doctor_id}";
                    TbLog::addLog($editContent, '医生关联主键',['admin_name'=>'system']);

                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "医生id:{$doctor_id}" . " 医生保存失败:{$msg}！\n";
                    continue;
                }
            }
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

}