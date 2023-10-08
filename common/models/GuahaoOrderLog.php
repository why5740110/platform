<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "tb_guahao_order_log".
 *
 * @property int $id 自增ID
 * @property int $order_id 王氏订单序id
 * @property int $opt_type 操作类型1:医院取消订单2:用户取消订单
 * @property string $opt_description 操作描述
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人姓名
 * @property int $create_time 创建时间
 */
class GuahaoOrderLog extends ActiveRecord
{
    // 医院取消
    const OPT_TYPE_HOS_CANCEL = 1;
    // 用户取消
    const OPT_TYPE_USER_CANCEL = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "{{%tb_guahao_order_log}}";
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'create_time', 'order_id', 'admin_id', 'opt_type'], 'integer'],
            [['opt_description'], 'string', 'max' => 100],
            [['admin_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '王氏订单序id',
            'opt_type' => '操作类型',
            'opt_description' => '操作描述',
            'admin_id' => '操作人id',
            'admin_name' => '操作人姓名',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 增加记录
     * @param $order_id
     * @param $opt_type
     * @param $desc
     * @param $user
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-01
     * @return bool
     */
    public static function addLog($order_id, $opt_type, $desc, $user)
    {
        $order_log_model = new self;
        $order_log_model->order_id = $order_id;
        $order_log_model->opt_type = $opt_type;
        $order_log_model->opt_description = $desc;
        $order_log_model->admin_id = $user['admin_id'] ?? '';
        $order_log_model->admin_name = $user['admin_name'] ?? '';
        $order_log_model->create_time = time();
        return $order_log_model->save(false);
    }
}
