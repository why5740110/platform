<?php
/**
 * Created by wangwencai.
 * @file: DeadlineModel.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-22
 */

namespace backend\models;

use yii\base\Model;

class DeadlineSearchModel extends Model
{
    public $agency_id;
    public $hospital_id;

    public $doctor_cert_id;
    public $doctor_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agency_id', 'hospital_id', 'doctor_cert_id'], 'integer'],
            [[ 'doctor_id'], 'safe'],
        ];
    }
}