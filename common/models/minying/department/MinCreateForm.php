<?php
/**
 * 代理商字段验证
 * @file MinCreateForm.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-20
 */

namespace common\models\minying\department;


use common\models\minying\MinDepartmentModel;
use yii\helpers\ArrayHelper;

class MinCreateForm extends MinDepartmentModel
{
    public function rules()
    {
        $parent = parent::rules();
        $current = [
            [
                [
                    'min_minying_fkname','min_minying_skname','miao_first_department_id','miao_second_department_id'
                ], 'required'
            ],
            [
                [
                    'miao_first_department_id', 'miao_second_department_id', 'min_hospital_id'
                ],
                'filter', 'filter' => 'intval'
            ]
        ];
        return ArrayHelper::merge($parent, $current);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // 修改时没有修改的字段不执行保存
        if (!$insert && !$this->watchChange()) {
            $this->addError('id', '没有内容变更');
            return false;
        }
        return true;
    }

    /**
     * 自定义修改的字段
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-16
     * @return array
     */
    public function watchChange()
    {
        // 忽略的字段
        $ignore_attributes = [
            'agency_id', 'first_check_time', 'first_check_uid', 'first_check_name', 'second_check_uid', 'second_check_name',
            'second_check_time', 'admin_role_type', 'admin_id', 'admin_name', 'update_time', 'check_status'
        ];

        $attributes = $this->getDirtyAttributes();
        $change_attributes = [];
        foreach ($attributes as $attribute => $value) {
            if (!in_array($attribute, $ignore_attributes)) {
                $change_attributes[] = $attribute;
            }
        }

        return $change_attributes;
    }
}