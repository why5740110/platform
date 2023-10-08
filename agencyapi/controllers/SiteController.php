<?php
/**
 * Created by wangwencai.
 * @file: LoginController.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-08
 */

namespace agencyapi\controllers;

use agencyapi\behaviors\ApiCheckerBehavior;
use common\helpers\ApiResponseTrait;
use common\models\minying\account\LoginForm;
use common\models\minying\MinAccountModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class SiteController extends Controller
{
    use ApiResponseTrait;

    public function init()
    {
        $this->requestID = $this->getRequestID();
        parent::init();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 接口参数验证
        $behaviors['apichecker'] = [
            'class' => ApiCheckerBehavior::class
        ];
        return $behaviors;
    }
    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-14
     * @return array|bool
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        if (!Yii::$app->getRequest()->isPost) {
            return $this->jsonError('请求方式不正确');
        }
        $model = new LoginForm();
        $model->login_type = LoginForm::AGENCY_LOGIN;
        $params = Yii::$app->getRequest()->getBodyParams();
        $model->load($params, '');
        if (!$model->validate()) {
            return $this->jsonError(array_values($model->getFirstErrors())[0]);
        }
        if (!$model->login()) {
            return $this->jsonError('登录失败，请重试');
        }

        // 将数据保存到redis中
        /** @var yii\redis\Connection $redis */
        $redis = Yii::$app->redis_codis;
        $access_token = Yii::$app->security->generateRandomString(64);
        $token_cache_key = sprintf(MinAccountModel::REDIS_TOKEN_KEY_AGENCY, $access_token);
        $expire_time = strtotime('today') + 86400 - time();
        // 返回用户基本信息
        $account = $model->getAccount();
        $user_info = [
            'account_id' => $account->account_id,
            'account_type' => $account->type,
            'account_number' => $account->account_number,
            'username' => $account->contact_name,
            'mobile' => $account->contact_mobile,
            'agency_id' => $account->enterprise_id,
            'agency_name' => $account->enterprise_name
        ];
        $redis->setex($token_cache_key, $expire_time, json_encode($user_info));
        return $this->jsonSuccess(['access_token' => $access_token, 'user_info' => $user_info]);
    }

    /**
     * 注销登录
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-18
     * @return array
     * @throws \Exception
     */
    public function actionLogout()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        if (!$access_token = ArrayHelper::getValue($headers, 'access-token')) {
            return $this->jsonError('缺少参数access-token');
        }
        /** @var yii\redis\Connection $redis */
        $redis = Yii::$app->redis_codis;
        $token_cache_key = sprintf(MinAccountModel::REDIS_TOKEN_KEY_AGENCY, $access_token);
        $redis->del($token_cache_key);

        return $this->jsonSuccess();

    }
}