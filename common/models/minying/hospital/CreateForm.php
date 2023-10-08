<?php
/**
 * Created by PhpStorm.
 * @file AgencyCreateForm.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-19
 */

namespace common\models\minying\hospital;

use \common\models\minying\MinHospitalModel;
use yii\helpers\ArrayHelper;

class CreateForm extends MinHospitalModel
{
    // 图片上传最大张数
    const IMAGE_FILE_MAX_SIZE = 3;

    public function rules()
    {
        $parent = parent::rules();
        $current = [
            [
                [
                    'min_hospital_logo', 'min_hospital_name','min_hospital_type', 'min_hospital_level','min_hospital_nature','min_hospital_province_id','min_hospital_city_id',/*'min_hospital_county_id',*/
                    'min_hospital_address','min_hospital_introduce','min_company_name','min_business_license','min_medical_license','min_health_record','min_medical_certificate','min_treatment_project',
                    'min_guahao_rule','min_hospital_contact','min_hospital_contact_phone'
                ], 'required'
            ],
            [
                [
                    'min_hospital_county_id', 'min_hospital_type', 'min_hospital_level', 'min_hospital_nature', 'min_hospital_province_id', 'min_hospital_city_id', 'agency_id'
                ],
                'filter', 'filter' => 'intval'
            ],
            // 图片最大长度验证
            [
                [
                    'min_business_license', 'min_medical_license', 'min_health_record', 'min_medical_certificate'
                ],
                'validateFile'
            ],
        ];
        return ArrayHelper::merge($parent, $current);
    }

    /**
     * 图片数量验证
     * @param $attribute
     * @return bool
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-27
     */
    public function validateFile($attribute){
        // 去掉cdn地址再判断
        $trim_oss_path = $this->replaceOssUrl($this->$attribute);
        if (mb_strlen($trim_oss_path) > 1000) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . '图片路径太长');
            return false;
        }
        $max_size = self::IMAGE_FILE_MAX_SIZE;
        $count = count(array_filter(explode(',', $trim_oss_path)));
        // 最多5张照片
        if ($count > $max_size) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . "最多可传{$max_size}张");
            return false;
        }
    }

    /**
     * @param bool $insert
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-16
     * @return bool
     */
    public function beforeSave($insert){
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // 替换掉oss路径 支持数组或拼接串
        if (is_array($this->min_business_license)) {
            $this->min_business_license = join(',', array_filter($this->min_business_license));
        }
        if (is_array($this->min_medical_license)) {
            $this->min_medical_license = join(',', array_filter($this->min_medical_license));
        }
        if (is_array($this->min_health_record)) {
            $this->min_health_record = join(',', array_filter($this->min_health_record));
        }
        if (is_array($this->min_medical_certificate)) {
            $this->min_medical_certificate = join(',', array_filter($this->min_medical_certificate));
        }
        if (is_array($this->min_hospital_logo)) {
            $this->min_hospital_logo = join(',', array_filter($this->min_hospital_logo));
        }

        $this->min_business_license = $this->replaceOssUrl($this->min_business_license);
        $this->min_medical_license = $this->replaceOssUrl($this->min_medical_license);
        $this->min_health_record = $this->replaceOssUrl($this->min_health_record);
        $this->min_medical_certificate = $this->replaceOssUrl($this->min_medical_certificate);
        if ($this->min_hospital_logo){
            $this->min_hospital_logo = $this->replaceOssUrl($this->min_hospital_logo);
        }

        // 修改时没有修改的字段不执行保存
        if (!$insert && !$this->watchChange()) {
            $this->addError('min_hospital_id', '没有内容变更');
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

     /**
     * 图片oss路径替换
     * @param $url
     * @return string|string[]
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-27
     */
    protected function replaceOssUrl($url)
    {
        return str_replace(\Yii::$app->params['min_hospital_img_oss_url_prefix'], '', $url);
    }

}