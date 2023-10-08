<?php

namespace common\models\minying;

use common\models\TbLog;
use Yii;

/**
 * This is the model class for table "tb_min_account".
 *
 * @property int $account_id
 * @property int $type 账号类型 1:代理商;2:民营医院
 * @property string $account_number 账号(默认同手机号)
 * @property string $enterprise_name 所属单位名称
 * @property int $enterprise_id 所属单位id
 * @property string $contact_name 联系人名称
 * @property string $contact_mobile 手机号
 * @property string $password 密码
 * @property string $salt 盐值
 * @property int $status 账号状态 1:启用;2:禁用
 * @property int $created_time 创建时间
 * @property int $update_time 最后修改时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class MinAccountModel extends \yii\db\ActiveRecord
{
    // 缓存Token键名
    const REDIS_TOKEN_KEY_AGENCY = 'agencyapi_%s';
    const REDIS_TOKEN_KEY_HOSPITAL = 'minyingapi_%s';

    // 状态：正常
    const STATUS_NORMAL = 1;
    // 状态：禁用
    const STATUS_FORBIDDEN = 2;

    // 账号类型：代理商
    const TYPE_AGENCY = 1;
    // 账号类型：医院
    const TYPE_HOSPITAL = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'contact_name', 'contact_mobile', 'account_number'], 'required'],
            [['type', 'enterprise_id', 'status', 'created_time', 'update_time', 'admin_id'], 'integer'],
            [['enterprise_id', 'type'],  'filter', 'filter' => 'intval'],
            [['account_number'], 'string', 'max' => 20],
            [['enterprise_name'], 'string', 'max' => 100],
            [['contact_name', 'password'], 'string', 'max' => 60],
            ['contact_mobile', 'match', 'pattern' => '/^[1][3456789][0-9]{9}$/', 'message' => '账号必须是手机号码格式'],
            [['salt'], 'string', 'max' => 6],
            [['admin_name'], 'string', 'max' => 50],
            [['contact_mobile', 'account_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'account_id' => 'id',
            'account_number' => '账号',
            'type' => '账号类型',
            'enterprise_name' => '所属单位',
            'enterprise_id' => '所属单位id',
            'contact_name' => '账号联系人',
            'contact_mobile' => '联系方式',
            'password' => '账号密码',
            'status' => '账号状态',
            'created_time' => '创建时间',
            'update_time' => '修改时间',
            'admin_id' => '操作人id',
            'admin_name' => '操作人',
        ];
    }

    public static function typeMaps()
    {
        return [
            self::TYPE_AGENCY => '代理商',
            self::TYPE_HOSPITAL => '民营医院'
        ];
    }

    public static function generatePassword()
    {
        if (YII_ENV == 'dev') {
            return '123456';
        }
        return rand(100000, 999999) . '@' . date('Ymd');
    }

    /**
     * Validates password
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function getAgency()
    {
        return $this->hasOne(MinAgencyModel::class, ['agency_id' => 'enterprise_id']);
    }

    /**
     * @param bool $insert
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-13
     * @return bool
     * @throws \yii\base\Exception
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            $this->salt = Yii::$app->security->generateRandomString(6);
            $this->created_time = time();
            $this->password = Yii::$app->security->generatePasswordHash($this->password . $this->salt);
        } else {
            $this->update_time = time();
        }
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 新增时记录日志
        if ($insert) {
            $info = "{$this->admin_name}添加了账户id：{$this->account_id}；联系人：{$this->contact_name}；联系人手机：{$this->contact_mobile}；默认账号：{$this->account_number}; 账号类型：" . self::typeMaps()[$this->type] ?? '';
            TbLog::addLog($info, '民营医院账号添加', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
        // 修改时记录修改日志
        if (!$insert) {
            unset($changedAttributes['update_time']);
            if (!$changedAttributes) {
                return true;
            }
            $info = "{$this->admin_name}修改了账户id：{$this->account_id}；";
            foreach ($changedAttributes as $attribute => $value) {
                $info .= "{$this->getAttributeLabel($attribute)} 由【{$value}】修改成【{$this->$attribute}】；";
            }
            TbLog::addLog($info, '民营医院账号更新', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
    }

    public static function findIdentityToken()
    {
        return true;
    }

    /**
     * @param $enterprise_id
     * @param $type
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-28
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function accountLimit($enterprise_id, $type)
    {
        return self::find()->where(['enterprise_id' => $enterprise_id, 'type' => $type])->limit(1)->one();
    }

    /**
     * 医院关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-21
     * @return \yii\db\ActiveQuery
     */
    public function getHospitalModel()
    {
        return $this->hasOne(MinHospitalModel::class, ['min_hospital_id' => 'enterprise_id']);
    }

    /**
     * 代理商关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-21
     * @return \yii\db\ActiveQuery
     */
    public function getAgencyModel()
    {
        return $this->hasOne(MinAgencyModel::class, ['agency_id' => 'enterprise_id']);
    }
}
