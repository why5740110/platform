<?php

namespace common\models;

use common\models\GuahaoScheduleplaceRelation;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_guahao_scheduleplace".
 *
 * @property int $scheduleplace_id 出诊地ID
 * @property string $scheduleplace_name 出诊医院(工作室)
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class GuahaoScheduleplace extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_scheduleplace';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospital_id', 'create_time', 'update_time'], 'integer'],
            [['hospital_name'], 'safe'],
            [['scheduleplace_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scheduleplace_id'   => 'Scheduleplace ID',
            'hospital_id'        => 'hospital_id',
            'hospital_name'      => 'hospital_name',
            'scheduleplace_name' => 'Scheduleplace Name',
            'create_time'        => 'Create Time',
            'update_time'        => 'Update Time',
        ];
    }

    /**
     * 批量多点执业地
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @param   array      $data [description]
     */
    public static function addScheduleplace($data = [])
    {
        $res        = ['code' => 0];
        $transition = self::getDb()->beginTransaction();
        try {
            $place_data= [
                'hospital_id'        => ArrayHelper::getValue($data, 'hospital_id'),
                'scheduleplace_name' => ArrayHelper::getValue($data, 'scheduleplace_name'),
                'hospital_name'      => ArrayHelper::getValue($data, 'hospital_name'),
                'tp_platform'        => ArrayHelper::getValue($data, 'tp_platform'),
                'tp_doctor_id'        => ArrayHelper::getValue($data, 'tp_doctor_id'),
            ];
            $_model = GuahaoScheduleplace::find()->select('scheduleplace_id')->where(['hospital_id' => $place_data['hospital_id']])->one();
            if (!$_model) {
                $_model              = new self();
                $place_data['update_time'] = time();
                $place_data['create_time'] = time();
                $_model->setAttributes($place_data);
                $res = $_model->save();
                if (!$res) {
                    throw new \Exception(json_encode($_model->getErrors(), JSON_UNESCAPED_UNICODE));
                }
            }
            $scheduleplace_id                    = $_model->attributes['scheduleplace_id'];
            $relation_model                      = new GuahaoScheduleplaceRelation();
            $relation_model->scheduleplace_id    = $scheduleplace_id;
            $relation_model->tp_scheduleplace_id = ArrayHelper::getValue($data, 'hospital_id');
            $relation_model->scheduleplace_name  = ArrayHelper::getValue($data, 'scheduleplace_name');
            $relation_model->tp_platform         = ArrayHelper::getValue($data, 'tp_platform', 4);
            $relation_model->tp_doctor_id         = ArrayHelper::getValue($data, 'tp_doctor_id', '');
            // $relation_model->doctor_id           = ArrayHelper::getValue($data, 'doctor_id');
            $relation_model->realname            = ArrayHelper::getValue($data, 'realname');
            $relation_model->admin_id            = ArrayHelper::getValue($data, 'admin_id',0);
            $relation_model->admin_name          = ArrayHelper::getValue($data, 'admin_name','');
            $relation_model->status              = ArrayHelper::getValue($data, 'status', 0);
            $relation_model->create_time         = time();
            $relation_model->update_time         = time();
            $relation_res                        = $relation_model->save();
            if (!$relation_res) {
                throw new \Exception(json_encode($relation_model->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $transition->commit();
            $res = ['code' => 0];
        } catch (\Exception $e) {
            $transition->rollBack();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            $res = ['code' => 1, 'msg' => $e->getMessage()];
        }
        return $res;

    }

}
