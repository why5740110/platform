<?php
/**
 * 后台开通账号模型
 * Created by wangwencai.
 * @file: AgencyCreateForm.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-12
 */

namespace common\models\minying\account;


use common\models\minying\MinAccountModel;
use yii\helpers\ArrayHelper;

class CreateForm extends MinAccountModel
{
    // 所属单位二选一属性
    public $enterprise_agency;
    public $enterprise_hospital;

    public function attributes()
    {
        $parent = parent::attributes();
        $current = [
            'enterprise_agency',
            'enterprise_hospital',
        ];
        return ArrayHelper::merge($parent, $current);
    }

    public function rules()
    {
        $parent = parent::rules();
        unset($parent['enterprise']);
        $current = [
            ['enterprise_agency', 'required', 'when' => function ($model) {
                return empty($model->enterprise_hospital);
            }, 'whenClient' => "function (attribute, value) {
                return $('#createform-enterprise_hospital').value == '';
            }"],
            ['enterprise_hospital', 'required', 'when' => function ($model) {
                return empty($model->enterprise_agency);
            }, 'whenClient' => "function (attribute, value) {
                return $('#createform-enterprise_agency').value == '';
            }"]
        ];
        return ArrayHelper::merge($parent, $current);
    }

    public function attributeLabels()
    {
        $parent = parent::attributeLabels();
        $current = [
            'enterprise_agency' => '所属单位',
            'enterprise_hospital' => '所属医院',
        ];
        return ArrayHelper::merge($parent, $current);
    }
}