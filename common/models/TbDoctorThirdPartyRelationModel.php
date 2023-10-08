<?php

namespace common\models;

use common\libs\CommonFunc;
use common\models\GuahaoScheduleplace;
use common\models\GuahaoScheduleplaceRelation;
use common\models\TmpDoctorThirdPartyModel;
use common\models\DoctorModel;
use common\sdks\ucenter\PihsSDK;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class TbDoctorThirdPartyRelationModel extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%tb_doctor_third_party_relation}}';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        DoctorModel::updateIsPlus($this->doctor_id);
        if ($this->doctor_id && $this->tp_doctor_id && $this->tp_platform == 6 && $this->status == 1) {
            ##异步拉取第三方出诊机构
            CommonFunc::getDoctorVisitPlace($this->doctor_id, $this->tp_doctor_id);
        }

    }

    public function afterDelete()
    {
        parent::afterDelete();
        //更新tb_doctor表is_plus状态
        DoctorModel::updateIsPlus($this->doctor_id);
    }

    /**
     * 增加关联关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-07
     * @version 1.0
     * @param   array      $params [description]
     * @param   string     &$msg   [description]
     */
    public static function addRelation($params = [])
    {
        $res = [
            'code' => 0,
        ];
        try {
            $model = TbDoctorThirdPartyRelationModel::find()->where(['tp_doctor_id' => $params['tp_doctor_id'], 'tp_platform' => $params['tp_platform'], 'doctor_id' => $params['doctor_id']])->one();
            if (!$model) {
                $model               = new self();
                $model->doctor_id    = $params['doctor_id'];
                $model->tp_platform  = $params['tp_platform'] ?? 4;
                $model->tp_doctor_id = $params['tp_doctor_id'] ?? 0;
                $model->create_time  = time();
            }
            $model->realname               = $params['realname'] ?? '';
            $model->tp_hospital_code       = $params['tp_hospital_code'] ?? 0;
            $model->hospital_name          = $params['hospital_name'] ?? 0;
            $model->tp_frist_department_id = $params['tp_frist_department_id'] ?? 0;
            $model->frist_department_name  = $params['frist_department_name'] ?? 0;
            $model->tp_department_id       = $params['tp_department_id'] ?? 0;
            $model->second_department_name = $params['second_department_name'] ?? 0;
            $model->status                 = $params['status'] ?? 1;
            $model->admin_id               = $params['admin_id'] ?? 0;
            $model->admin_name             = $params['admin_name'] ?? '';
            $sts                           = $model->save();
            if (!$sts) {
                throw new \Exception(json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $res['code'] = 0;
        } catch (\Exception $e) {
            $msg         = $e->getMessage();
            $res['code'] = 1;
            $res['msg']  = $msg;
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
        }
        return $res;
    }

    /**
     * 异步拉取第三方出诊机构
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @param   integer    $doctor_id    [description]
     * @param   integer    $tp_doctor_id [description]
     * @return  [type]                   [description]
     */
    public static function pullVisitPlace($doctor_id = 0, $tp_doctor_id = 0,$tp_platform = 6)
    {
        try {
            if (!$doctor_id || !$tp_doctor_id) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '医生或者第三方医生不存在！' . PHP_EOL;die();
            }
            $tmpDoc      = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();
            if (!$tmpDoc) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '医生未关联！' . PHP_EOL;die();
            }

            // $scheduleplace_list = PihsSDK::getInstance()->getDoctorScheduleplace(['tp_doctor_id' => $tp_doctor_id]);
            $scheduleplace_list = SnisiyaSdk::getInstance()->getGuahaoDoctor(['tp_doctor_id' => $tp_doctor_id,'tp_platform' =>$tp_platform]);
            $list               = ArrayHelper::getValue($scheduleplace_list, 'list.0.scheduleplace_list', []);
            if (!$list) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '该医生暂无出诊机构！' . PHP_EOL;die();
            }
            foreach ($list as $key => $scheduleplace_item) {
                self::doAddRelation($scheduleplace_item, $tmpDoc, $tp_platform);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 异步拉取医生出诊机构失败');
            echo "[" . date('Y-m-d H:i:s') . "] " . ' 医生id:' . $doctor_id . ' 第三方医生id:' . $tp_doctor_id . " 拉取失败：{$msg}！\n";
        }

        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    }

    /**
     * 更新出诊地通知
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @param   array      $postData [description]
     * @return  [type]               [description]
     */
    public static function uPVisitPlace($postData = [])
    {
        try {
            $tp_doctor_id                = trim(ArrayHelper::getValue($postData, 'tp_doctor_id', 0));
            $tp_hospital_code            = trim(ArrayHelper::getValue($postData, 'tp_hospital_code', 0));
            $scheduleplace_hospital_id   = trim(ArrayHelper::getValue($postData, 'scheduleplace_hospital_id', 0));
            $scheduleplace_hospital_name = trim(ArrayHelper::getValue($postData, 'scheduleplace_hospital_name', ''));
            if (!$tp_doctor_id || !$scheduleplace_hospital_id) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '医生信息或者出诊机构信息不能为空！' . PHP_EOL;die();
            }
            $tp_platform = 6;
            $docItem     = [
                'tp_doctor_id'           => ArrayHelper::getValue($postData, 'tp_doctor_id', ''),
                'tp_platform'            => $tp_platform,
                'tp_primary_id'          => ArrayHelper::getValue($postData, 'tp_doctor_id', ''),
                'realname'               => ArrayHelper::getValue($postData, 'realname', ''),
                'source_avatar'          => ArrayHelper::getValue($postData, 'source_avatar', ''),
                'good_at'                => ArrayHelper::getValue($postData, 'good_at', ''),
                'profile'                => ArrayHelper::getValue($postData, 'profile', ''),
                'job_title'              => ArrayHelper::getValue($postData, 'job_title', ''),
                'hospital_name'          => ArrayHelper::getValue($postData, 'hospital_name', ''),
                'tp_frist_department_id' => ArrayHelper::getValue($postData, 'tp_first_department_id', ''),
                'frist_department_name'  => ArrayHelper::getValue($postData, 'first_department_name', ''),
                'tp_department_id'       => ArrayHelper::getValue($postData, 'tp_department_id', ''),
                'second_department_name' => ArrayHelper::getValue($postData, 'department_name', ''),
                'tp_hospital_code'       => ArrayHelper::getValue($postData, 'tp_hospital_code', ''),
            ];
            ##查询是否有此医生，没有新增
            $docModel = TmpDoctorThirdPartyModel::find()->where(['tp_doctor_id' => $tp_doctor_id, 'tp_platform' => $tp_platform])->one();
            if ($docModel) {
                $docItem['id'] = $docModel->id;                
            }
            TmpDoctorThirdPartyModel::saveDoctor($docItem);
            if (!$docModel) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '医生未添加！' . PHP_EOL;die();
            }
            ##王氏加号
            $tmpDoc = [
                'realname'               => ArrayHelper::getValue($postData, 'realname', ''),
                'tp_doctor_id'           => ArrayHelper::getValue($postData, 'tp_doctor_id', ''),
            ];
            $scheduleplace_item = [
                'visit_status'  => ArrayHelper::getValue($postData, 'visit_status', 0),
                'status'        => ArrayHelper::getValue($postData, 'status', 0),
                'hospital_id'   => $scheduleplace_hospital_id,
                'hospital_name' => $scheduleplace_hospital_name,
            ];
            self::doAddRelation($scheduleplace_item, $tmpDoc, $tp_platform);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 异步响应医生出诊机构变更失败');
            echo "[" . date('Y-m-d H:i:s') . "] " . ' 第三方医生id:' . $tp_doctor_id . " 拉取失败：{$msg}！\n";
        }

        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    }

    /**
     * 增加更新出诊地
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @param   [type]     $scheduleplace_item [description]
     * @param   [type]     $tmpDoc             [description]
     * @param   [type]     $tp_platform        [description]
     * @return  [type]                         [description]
     */
    public static function doAddRelation($scheduleplace_item, $tmpDoc, $tp_platform = 6)
    {
        $status = 0;
        if ($scheduleplace_item['visit_status'] == 1) {
            #0为审核中；1为审核通过且已开通；2为审核失败；3为已关闭；99停诊关闭
            if ($scheduleplace_item['status'] == 1) {
                $status = 1;
            } elseif ($scheduleplace_item['status'] == 0) {
                $status = 0;
            } else {
                $status = -1;
            }
        }else{
            $status = 2;
        }
        $params = [
            // 'doctor_id'          => ArrayHelper::getValue($tmpDoc, 'doctor_id'),
            'hospital_id'        => ArrayHelper::getValue($scheduleplace_item, 'hospital_id'),
            'realname'           => ArrayHelper::getValue($tmpDoc, 'realname'),
            'scheduleplace_name' => ArrayHelper::getValue($scheduleplace_item, 'hospital_name'),
            'hospital_name'      => ArrayHelper::getValue($scheduleplace_item, 'hospital_name'),
            'tp_platform'        => $tp_platform ?? 6,
            'tp_doctor_id'        =>ArrayHelper::getValue($tmpDoc, 'tp_doctor_id'),
            'status'             => $status,
        ];

        $hasData = GuahaoScheduleplaceRelation::find()->where(['tp_scheduleplace_id' => $params['hospital_id'], 'tp_platform' => $tp_platform,'tp_doctor_id'=>$params['tp_doctor_id']])->one();
        if ($hasData) {
            $editContent     = '更新了第三方医生id为' . $params['tp_doctor_id'] . ' 执业地为:' . $params['scheduleplace_name'];
            $hasData->status = $status;
            $hres            = $hasData->save();
            if ($hres) {
                echo (date('Y-m-d H:i:s', time())) . $editContent . ' 操作成功！' . PHP_EOL;
            }
        } else {
            $editContent = '添加了医生id为' . $params['tp_doctor_id'] . ' 执业地为:' . $params['scheduleplace_name'];
            $res         = GuahaoScheduleplace::addScheduleplace($params);
            if (isset($res['code']) && $res['code'] == 0) {
                echo (date('Y-m-d H:i:s', time())) . $editContent . ' 操作成功！' . PHP_EOL;
            } else {
                echo (date('Y-m-d H:i:s', time())) . $editContent . ' 操作失败！--' . $res['msg'] . PHP_EOL;
            }
        }
        ##拉取排班
        $snisiyaSdk = new SnisiyaSdk();
        $snisiyaSdk->updateScheduleCache(['doctor_id' => $params['doctor_id'], 'tp_platform' =>$tp_platform]);
    }
}
