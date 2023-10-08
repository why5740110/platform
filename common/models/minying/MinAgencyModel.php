<?php

namespace common\models\minying;

use common\models\TbLog;
use Yii;

/**
 * This is the model class for table "tb_min_agency".
 *
 * @property int $agency_id
 * @property string $agency_name 代理商名称
 * @property string $contact_mobile 联系人电话
 * @property string $contact_name 联系人姓名
 * @property int $created_time 创建时间
 * @property int $update_time 最后修改时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 */
class MinAgencyModel extends \yii\db\ActiveRecord
{
    /**
     * 代理商列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-13
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function agencies()
    {
        $list = self::find()->select('agency_id,agency_name')->asArray()->all();
        return array_column($list, 'agency_name', 'agency_id');
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_agency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agency_name', 'contact_name', 'contact_mobile'], 'required'],
            [['created_time', 'update_time', 'admin_id'], 'integer'],
            [['agency_name', 'admin_name'], 'string', 'max' => 50],
            [['contact_mobile'], 'string', 'max' => 11],
            [['contact_name'], 'string', 'max' => 100],
            [['agency_name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'agency_id' => 'ID',
            'agency_name' => '公司名称',
            'contact_mobile' => '联系方式',
            'contact_name' => '联系人',
            'created_time' => '创建时间',
            'update_time' => '修改时间',
            'admin_id' => '操作人id',
            'admin_name' => '操作人',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            $this->created_time = time();
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
            $info = "{$this->admin_name}添加了代理商id：{$this->agency_id}；代理商名：{$this->agency_name}；联系人：{$this->contact_name}；联系人手机：{$this->contact_mobile}";
            TbLog::addLog($info, '民营医院代理商添加', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }

        // 修改时记录修改日志
        if (!$insert) {
            unset($changedAttributes['update_time']);
            if (!$changedAttributes) {
                return true;
            }
            $info = "{$this->admin_name}修改了账户id：{$this->agency_id}；";
            foreach ($changedAttributes as $attribute => $value) {
                $info .= "{$this->getAttributeLabel($attribute)} 由【{$value}】修改成【{$this->$attribute}】；";
            }
            TbLog::addLog($info, '代理商账号更新', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
    }
}
