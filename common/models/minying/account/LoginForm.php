<?php
/**
 * Created by wangwencai.
 * @file: login.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-09
 */

namespace common\models\minying\account;

use common\models\minying\MinAccountModel;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    const AGENCY_LOGIN = 'agency_login';
    const HOSPITAL_LOGIN = 'hospital_login';

    public $account;
    public $access_token;

    public $mobile;
    public $password;

    // 登录类型[代理商、民营医院]
    public $login_type;

    public function rules()
    {
        return [
            ['mobile', 'match', 'pattern' => '/^[1][3456789][0-9]{9}$/', 'message' => '账号必须是手机号码格式'],
            [['password', 'mobile'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mobile' => '账户号码',
            'password' => '账户密码'
        ];
    }

    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $account = $this->getAccount();
            // 安全考虑不详细提示
            if (!$account || !$account->validatePassword($this->password . $account->salt)) {
                $this->addError($attribute, '账户号码或账户密码不正确！');
            }
            return true;
        }
        return false;
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-14
     * @return bool
     * @throws \yii\base\Exception
     */
    public function login()
    {
        return Yii::$app->user->login($this->getAccount());
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-14
     * @return AccountIdentity|null
     */
    public function getAccount()
    {
        $where['account_number'] = $this->mobile;
        if ($this->login_type == self::AGENCY_LOGIN) {
            $where['type'] = MinAccountModel::TYPE_AGENCY;
        } else {
            $where['type'] = MinAccountModel::TYPE_HOSPITAL;
        }
        if ($this->account === null) {
            $this->account = AccountIdentity::findOne($where);
        }
        return $this->account;
    }
}