<?php
/**
 * Created by wangwencai.
 * @file: CreateForm.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-15
 */

namespace common\models\minying\doctor;

use common\models\minying\MinDoctorModel;
use yii\helpers\ArrayHelper;

class CreateForm extends MinDoctorModel
{
    // 代理商场景
    const SCENARIO_AGENCY = 'agency';
    // 医院场景
    const SCENARIO_HOSPITAL = 'hospital';

    // 图片上传最大张数
    const IMAGE_FILE_MAX_SIZE = 5;

    public function rules()
    {
        $parent = parent::rules();
        $current = [
            // 必填验证
            [
                [
                    'min_doctor_name', 'min_job_title_id', 'min_department_id', 'visit_type',
                    'id_card_begin', 'id_card_end', 'doctor_cert_begin', 'doctor_cert_end', 'practicing_cert_begin', 'practicing_cert_end', 'professional_cert_begin', 'professional_cert_end',
                    'id_card_file', 'doctor_cert_file', 'practicing_cert_file', 'professional_cert_file',
                ],
                'required',
            ],
            // 最大字数验证
            [['good_at', 'intro'], 'string', 'max' => 1000],
            // 图片最大长度验证
            [
                [
                    'id_card_file', 'doctor_cert_file', 'practicing_cert_file', 'professional_cert_file', 'multi_practicing_cert_file'
                ],
                'validateFile'
            ],
            // 时间验证
            [
                [
                    'id_card_begin', 'id_card_end', 'doctor_cert_begin', 'doctor_cert_end', 'practicing_cert_begin', 'practicing_cert_end', 'professional_cert_begin', 'professional_cert_end', 'multi_practicing_cert_begin', 'multi_practicing_cert_end'
                ],
                'validateDate'
            ],
            // 第一执业医院必填条件
            [['miao_hospital_id', 'multi_practicing_cert_file', 'multi_practicing_cert_begin', 'multi_practicing_cert_end'], 'required', 'when' => function ($model, $attribute) {
                return $model->visit_type == self::VISIT_TYPE_MULTI;
            }],
            // 保证传入的多点执业医院id是大于0的值
            ['miao_hospital_id', 'integer', 'integerOnly' => true, 'min' => 1, 'when' => function ($model, $attribute) {
                return $model->visit_type == self::VISIT_TYPE_MULTI;
            }],
            // 代理商添加医院时必填字段
            [['min_hospital_id'], 'required', 'on' => self::SCENARIO_AGENCY]
        ];
        return ArrayHelper::merge($parent, $current);
    }

    /**
     * 图片数量验证
     * @param $attribute
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return bool
     */
    public function validateFile($attribute)
    {
        $max_size = self::IMAGE_FILE_MAX_SIZE;
        // 图片先转换成字符串后统一处理，传参时兼容数组或字符串
        if (is_array($this->$attribute)) {
            $this->$attribute = join(',', $this->$attribute);
        }
        // 去掉cdn地址再判断
        $trim_oss_path = $this->replaceOssUrl($this->$attribute);
        if (mb_strlen($trim_oss_path) > 1000) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . '图片路径太长');
        }
        $count = count(array_filter(explode(',', $trim_oss_path)));
        // 最多5张照片
        if ($count > $max_size) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . "最多可传{$max_size}张");
        }
    }

    /**
     * 验证日期
     * @param $attribute
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return bool
     */
    public function validateDate($attribute)
    {
        if (!strtotime($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . '格式不正确');
        }
        if (strtotime($this->id_card_begin) > strtotime($this->id_card_end)) {
            $this->addError('id_card_begin', $this->getAttributeLabel('id_card_begin') . '不能大于结束时间');
        }
        if (strtotime($this->doctor_cert_begin) > strtotime($this->doctor_cert_end)) {
            $this->addError('doctor_cert_begin', $this->getAttributeLabel('doctor_cert_begin') . '不能大于结束时间');
        }
        if (strtotime($this->practicing_cert_begin) > strtotime($this->practicing_cert_end)) {
            $this->addError('practicing_cert_begin', $this->getAttributeLabel('practicing_cert_begin') . '不能大于结束时间');
        }
        if (strtotime($this->professional_cert_begin) > strtotime($this->professional_cert_end)) {
            $this->addError('professional_cert_begin', $this->getAttributeLabel('professional_cert_begin') . '不能大于结束时间');
        }
        if (strtotime($this->multi_practicing_cert_begin) > strtotime($this->multi_practicing_cert_end)) {
            $this->addError('multi_practicing_cert_begin', $this->getAttributeLabel('multi_practicing_cert_begin') . '不能大于结束时间');
        }
        // 所有证书结束时间不能小于当前时间
        if (strtotime($this->id_card_end) < time()) {
            $this->addError('id_card_end', $this->getAttributeLabel('id_card_end') . '不能小于当前时间');
        }
        if (strtotime($this->doctor_cert_end) < time()) {
            $this->addError('doctor_cert_end', $this->getAttributeLabel('doctor_cert_end') . '不能小于当前时间');
        }
        if (strtotime($this->practicing_cert_end) < time()) {
            $this->addError('practicing_cert_end', $this->getAttributeLabel('practicing_cert_end') . '不能小于当前时间');
        }
        if (strtotime($this->professional_cert_end) < time()) {
            $this->addError('professional_cert_end', $this->getAttributeLabel('professional_cert_end') . '不能小于当前时间');
        }
        if (strtotime($this->multi_practicing_cert_end) && strtotime($this->multi_practicing_cert_end) < time()) {
            $this->addError('multi_practicing_cert_end', $this->getAttributeLabel('multi_practicing_cert_end') . '不能小于当前时间');
        }
    }

    /**
     * 字段转换入库时间戳、图片
     * @param bool $insert
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (strtotime($this->id_card_begin)) {
            $this->id_card_begin = $this->startTimeOfDate($this->id_card_begin);
        }
        if (strtotime($this->id_card_end)) {
            $this->id_card_end = $this->endTimeOfDate($this->id_card_end);
        }
        if (strtotime($this->doctor_cert_begin)) {
            $this->doctor_cert_begin = $this->startTimeOfDate($this->doctor_cert_begin);
        }
        if (strtotime($this->doctor_cert_end)) {
            $this->doctor_cert_end = $this->endTimeOfDate($this->doctor_cert_end);
        }
        if (strtotime($this->practicing_cert_begin)) {
            $this->practicing_cert_begin = $this->startTimeOfDate($this->practicing_cert_begin);
        }
        if (strtotime($this->practicing_cert_end)) {
            $this->practicing_cert_end = $this->endTimeOfDate($this->practicing_cert_end);
        }
        if (strtotime($this->professional_cert_begin)) {
            $this->professional_cert_begin = $this->startTimeOfDate($this->professional_cert_begin);
        }
        if (strtotime($this->professional_cert_end)) {
            $this->professional_cert_end = $this->endTimeOfDate($this->professional_cert_end);
        }
        if (strtotime($this->multi_practicing_cert_begin)) {
            $this->multi_practicing_cert_begin = $this->startTimeOfDate($this->multi_practicing_cert_begin);
        } else {
            $this->multi_practicing_cert_begin = 0; // 为空时保存会报null错误
        }
        if (strtotime($this->multi_practicing_cert_end)) {
            $this->multi_practicing_cert_end = $this->endTimeOfDate($this->multi_practicing_cert_end);
        } else {
            $this->multi_practicing_cert_end = 0; // 为空时保存会报null错误
        }

        // 替换掉oss路径 支持数组或拼接串
        if (is_array($this->avatar)) {
            $this->avatar = join(',', array_filter($this->avatar));
        }
        if (is_array($this->id_card_file)) {
            $this->id_card_file = join(',', array_filter($this->id_card_file));
        }
        if (is_array($this->doctor_cert_file)) {
            $this->doctor_cert_file = join(',', array_filter($this->doctor_cert_file));
        }
        if (is_array($this->practicing_cert_file)) {
            $this->practicing_cert_file = join(',', array_filter($this->practicing_cert_file));
        }
        if (is_array($this->professional_cert_file)) {
            $this->professional_cert_file = join(',', array_filter($this->professional_cert_file));
        }
        if (is_array($this->multi_practicing_cert_file)) {
            $this->multi_practicing_cert_file = join(',', array_filter($this->multi_practicing_cert_file));
        }
        $this->avatar = $this->replaceOssUrl($this->avatar);
        $this->id_card_file = $this->replaceOssUrl($this->id_card_file);
        $this->doctor_cert_file = $this->replaceOssUrl($this->doctor_cert_file);
        $this->practicing_cert_file = $this->replaceOssUrl($this->practicing_cert_file);
        $this->professional_cert_file = $this->replaceOssUrl($this->professional_cert_file);
        $this->multi_practicing_cert_file = $this->replaceOssUrl($this->multi_practicing_cert_file);

        // 修改时没有修改的字段不执行保存
        if (!$insert && !$this->watchChange()) {
            $this->addError('min_doctor_id', '没有内容变更');
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
            'agency_id', 'first_check_time', 'first_check_uid', 'first_check_uname', 'second_check_uid', 'second_check_uname',
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

    /**
     * 获取某个时间的当天第一秒时间戳
     * eg: 2022-07-19 17:23 则返回 2022-07-19 00:00:00 对应的时间戳
     * @param $date
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return false|int
     */
    protected function startTimeOfDate($date)
    {
        return strtotime(date('Y-m-d', strtotime($date)));
    }

    /**
     * 获取某个时间的当天最后一秒时间戳
     * eg: 2022-07-19 17:23 则返回 2022-07-19 23:59:59 对应的时间戳
     * @param $date
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return false|int
     */
    protected function endTimeOfDate($date)
    {
        return strtotime(date('Y-m-d 23:59:59', strtotime($date)));
    }

    protected function replaceOssUrl($url)
    {
        return str_replace(\Yii::$app->params['min_doctor_img_oss_url_prefix'], '', $url);
    }
}