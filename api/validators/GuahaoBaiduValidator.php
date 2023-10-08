<?php
/**
 * Baidu挂号参数验证
 * @file GuahaoBaiduValidator.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2021-06-23
 */

namespace api\validators;

use yii\base\Model;

class GuahaoBaiduValidator extends Model
{
    public $doctor_id;
    public $scheduling_id;
    public $patient_name;
    public $gender;
    public $age;
    public $card;
    public $mobile;
    //public $province;
    //public $city;
    public $coo_patient_id;
    public $coo_order_id;

    public function rules()
    {
        return [
            ['doctor_id', 'required', 'message' => '医生ID不能为空'],
            ['scheduling_id', 'required', 'message' => '号源ID不能为空'],
            ['patient_name', 'required', 'message' => '患者姓名不能为空'],
            ['gender', 'required', 'message' => '患者性别不能为空'],
            ['age', 'required', 'message' => '患者年龄不能为空'],
            ['card', 'required', 'message' => '患者身份证号不能为空'],
            ['mobile', 'required', 'message' => '患者手机号不能为空'],
            //['province', 'required', 'message' => '省份不能为空'],
            //['city', 'required', 'message' => '城市不能为空'],
            ['coo_patient_id', 'required', 'message' => '患者ID不能为空'],
            ['coo_order_id', 'required', 'message' => '挂号订单ID不能为空'],
            ['card', 'validateIdCard'],
            ['mobile', 'integer', 'message' => '患者手机号校验失败'],
        ];
    }

    public function validateIdCard()
    {
        $idcard = $this->card;

        // 只能是18位
        if (strlen($idcard) != 18) {
            $this->addError('', '患者身份证号校验失败');
        } else {
            // 取出本体码
            $idcard_base = substr($idcard, 0, 17);
            // 取出校验码
            $verify_code = substr($idcard, 17, 1);
            // 加权因子
            $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            // 校验码对应值
            $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            // 根据前17位计算校验码
            $total = 0;
            for ($i = 0; $i < 17; $i++) {
                $total += substr($idcard_base, $i, 1) * $factor[$i];
            }
            // 取模
            $mod = $total % 11;
            // 比较校验码
            if ($verify_code != $verify_code_list[$mod]) {
                $this->addError('', '患者身份证号校验失败');
            }
        }
    }

}