<?php

namespace common\models;

use Yii;
use common\libs\CommonFunc;

/**
 * This is the model class for table "tb_guahao_coo_interrogation".
 *
 * @property int $id
 * @property string $card 证件号码
 * @property string $mobile 就诊人手机号
 * @property int $patient_id 王氏就诊人ID
 * @property int $uid 用户中心的UID
 * @property int $coo_platform 和咱们对接的平台
 * @property string $coo_patient_id 第三方问诊人id
 * @property string $coo_user_id 第三方用户id
 * @property int $create_time 创建时间
 * @property int $update_time 修改时间
 */
class GuahaoCooInterrogationModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_coo_interrogation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['patient_id', 'uid', 'create_time', 'update_time', 'coo_platform'], 'integer'],
            [['card'], 'string', 'max' => 30],
            [['mobile'], 'string', 'max' => 11],
            [['coo_patient_id', 'coo_user_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card' => 'Card',
            'mobile' => 'Mobile',
            'patient_id' => 'Patient ID',
            'uid' => 'Uid',
            'coo_platform' => 'Coo Platform',
            'coo_patient_id' => 'Coo Patient ID',
            'coo_user_id' => 'Coo User ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * 获取信息
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/23
     */
    public static function getInfo($params)
    {
        return self::find()
            ->select('uid,patient_id')
            ->where(['coo_platform' => $params['coo_platform']])
            ->andWhere(['coo_patient_id' => $params['coo_patient_id']])
            ->asArray()->one();
    }

    /**
     * 获取信息
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/3/8
     */
    public static function getInfoByCard($params)
    {
        return self::find()
            ->select('uid,patient_id')
            ->where(['coo_platform' => $params['coo_platform']])
            ->andWhere(['uid' => $params['uid']])
            ->andWhere(['card' => $params['card']])
            ->andWhere(['coo_user_id' => $params['coo_user_id']])
            ->asArray()->one();
    }

    /**
     * 获取信息by 用户id和就诊人id
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/03/07
     */
    public static function getInfoByUserIdParentId($params)
    {
        return self::find()
            ->select('*')
            ->where(['uid' => $params['uid']])
            ->andWhere(['patient_id' => $params['patient_id']])
            ->andWhere(['coo_platform' => $params['coo_platform']])
            ->asArray()->one();
    }

    /**
     * 存储信息
     * @param $params
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/6/24
     */
    public static function addInfo($params)
    {
        $transaction = self::getDb()->beginTransaction();
        if (isset($params['coo_patient_id']) && !empty($params['coo_patient_id'])) {
            $GuahaoCooInterrogationData = self::getInfo($params);
        } else {
            //用身份证查询
            $GuahaoCooInterrogationData = self::getInfoByCard($params);
        }

        if (empty($GuahaoCooInterrogationData)) {
            try {
                $params['update_time'] = time();
                $params['create_time'] = time();
                $model = new self();
                $model->setAttributes($params);
                $res = $model->save();
                if (!$res) {
                    throw new \Exception(json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE));
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            }
        }
    }

    /**
     * 阿里问诊人信息处理
     * @param $params
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/03/03
     */
    public static function formatAliInterrogation($param)
    {
        $cooInterrogatData = [
            'coo_platform' => 2,
            'card' => $param['member_idcard'],
            'uid' => $param['user_id'],
            'coo_user_id' => $param['coo_user_id'],
        ];
        $GuahaoCooInterrogationData = self::getInfoByCard($cooInterrogatData);

        if (empty($GuahaoCooInterrogationData)) {
            $GuahaoCooInterrogationData['uid'] = $param['user_id'];
            $interrogatDataData = [
                'patient_name' => $param['member_name'],
                'card' => $param['member_idcard'],
                'mobile' => $param['member_mobile'],
                'coo_platform' => $cooInterrogatData['coo_platform'],
                'coo_patient_id' => isset($cooInterrogatData['coo_patient_id']) ? $cooInterrogatData['coo_patient_id'] : '',
                'coo_user_id' => $param['coo_user_id']
            ];
            $GuahaoCooInterrogationData = CommonFunc::addInterrogationData($GuahaoCooInterrogationData, $interrogatDataData);
        }
        return $GuahaoCooInterrogationData;
    }
}
