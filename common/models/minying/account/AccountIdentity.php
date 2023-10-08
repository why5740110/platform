<?php
/**
 * Created by wangwencai.
 * @file: AccountIdentityModel.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-09
 */

namespace common\models\minying\account;

use common\models\minying\MinAccountModel;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * 代理商及民营医院账号登录身份验证类
 * Class AccountIdentityModel
 * @package common\models\minying
 */
class AccountIdentity extends MinAccountModel implements IdentityInterface
{

    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() met
        //hod.
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var yii\redis\Connection $redis */
        $redis = Yii::$app->redis_codis;
        if (!$account = $redis->get($token)) {
            return null;
        }
        return unserialize($account);
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
}