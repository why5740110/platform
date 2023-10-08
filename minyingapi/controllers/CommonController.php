<?php
/**
 * 基类控制器
 * @file CommonController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-08-31
 */

namespace minyingapi\controllers;

use common\models\GuahaoHospitalModel;
use minyingapi\behaviors\ApiCheckerBehavior;
use common\helpers\ApiResponseTrait;
use common\models\minying\MinAccountModel;
use yii\web\Controller;
use Yii;

class CommonController extends Controller
{
    use ApiResponseTrait;

    /**
     * 全局user信息
     * @var
     */
    public $user;

    public $errorMsg;

    public $limit = 10;

    const TP_PLATFORM = 13;

    public $admin_info;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->requestID = $this->getRequestID();
        header("X-REQUEST-ID:" . $this->requestID);
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
     * 身份认证及授权信息
     * @param $action
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-14
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $actions = parent::beforeAction($action);
        if ($actions == false) {
            return false;
        }
        //检查登录
        if ($this->checkLogin() == false) {
            $this->exitJson($this->errorMsg);
        }
        // 检查医院状态
        if (!$this->checkHospitalStatus()) {
            $this->exitJson($this->errorMsg);
        }
        return true;
    }

    /**
     * @date 2022-07-14
     * @return array|bool
     */
    public function checkLogin()
    {
        /** @var yii\redis\Connection $redis */
        $redis = Yii::$app->redis_codis;
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('Access-Token');
        if (empty($token)) {
            //上传文件没法添加header 放到post里面
            $token = Yii::$app->request->post('access_token');
            if (empty($token)) {
                $token = Yii::$app->request->get('access_token');
            }
        }
        if (empty($token)) {
            $this->errorMsg = '请登录';
            return false;
        }

        $token_cache_key = sprintf(MinAccountModel::REDIS_TOKEN_KEY_HOSPITAL, $token);
        $this->user = json_decode($redis->get($token_cache_key), true);
        if (empty($this->user)) {
            $this->errorMsg = '无效的登录凭证';
            return false;
        }

        if ($this->user['account_type'] != MinAccountModel::TYPE_HOSPITAL) {
            $this->errorMsg = '登录凭证不合法';
            return false;
        }

        $account = MinAccountModel::find()->where(['account_id' => $this->user['account_id']])->limit(1)->one();
        if (!$account) {
            $this->errorMsg = '账号信息错误';
            return false;
        }

        //被禁用用户不允许访问
        if ($account->status != MinAccountModel::STATUS_NORMAL) {
            $this->errorMsg = '账号已停用';
            return false;
        }

        $expire_time = strtotime('today') + 86400 - time();
        $redis->setex($token_cache_key, $expire_time, json_encode($this->user));//重置token

        $this->admin_info = [
            'admin_id' => $this->user['account_id'],
            'admin_name' => $this->user['username'],
        ];
        return true;
    }

    /**
     * 医院禁用后不可操作的资源
     * 原方案是指定资源禁止crud，现改为不可修改
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-01
     * @return bool
     */
    public function checkHospitalStatus()
    {
        /**
        $current_controller = Yii::$app->controller->id;
        $forbidden_controllers = ['hospital', 'department', 'doctor', 'schedule-plan'];
         */

        $miao_hospital_info = GuahaoHospitalModel::find()
            ->where([
                'tp_hospital_code' => $this->user['min_hospital_id'],
                'tp_platform' => 13,
            ])->one();

        if (!$miao_hospital_info) {
            $this->errorMsg = '医院信息有误，请联系管理员';
            return false;
        }
        // 未关联医院
        if ($miao_hospital_info->status == 2 && Yii::$app->request->isPost) {
            $this->errorMsg = '医院禁用中，请联系管理员';
            return false;
        }
        return true;
    }
}