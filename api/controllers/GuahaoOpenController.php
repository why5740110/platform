<?php
/**
 * @file GuahaoOpenController.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/15
 */


namespace api\controllers;


use api\behaviors\RecordLoging;
use common\models\GuahaoPlatformModel;
use common\models\DoctorModel;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use common\models\GuahaoCooListModel;

class GuahaoOpenController extends \yii\web\Controller
{
    /**
     * 第三方请求参数
     * @var array
     */
    public $params = [];

    /**
     * sign key (不同的第三方 自己定义)
     * @var string
     */
    public $key = '';

    /**
     * encrypt key
     * @var string
     */
    public $encryptKey = '';

    /**
     * 合作方映射第三方关系
     * @var int[]
     */
    public $platformArr = ['bd' => 1,'ali'=>2];

    /**
     * 公共查询条件
     * @var array
     */
    public $where = [];

    /**
     * 公共查询条件
     * @var array
     */
    public $joinWhere = [];

    /**
     * 公共查询条件
     * @var array
     */
    public $relationWhere = [];

    /**
     * 错误信息 记录日志
     * @var string
     */
    public $logErrorMsg = '';

    /**
     * 返回json 状态码键名
     * @var string
     */
    public $code = 'errno';

    /**
     * 返回json 错误信息键名
     * @var string
     */
    public $errorMsg = 'errmsg';

    /**
     * 返回json 数据体键名
     * @var string
     */
    public $content = 'data';

    /**
     * 合作的来源
     * @var array
     */
    public $tp_platform_arr = [];

    public function init()
    {
        parent::init();


    }

    public function behaviors()
    {
        return [
            // 记录请求日志
            [
                'class' => RecordLoging::className()
            ]
        ];
    }

    /**
     * 预处理条件  根据来源判断所提供的第三方数据 生成where
     * @return array
     * @author xiujianying
     * @date 2021/6/16
     */
    public function preWhere()
    {

        $from = ArrayHelper::getValue($this->params, 'from');

        $coo_platform_id = ArrayHelper::getValue($this->platformArr, $from);
        //判断来源是否可用
        if ($coo_platform_id && GuahaoCooListModel::checkCooPlatform($coo_platform_id)) {
            $this->tp_platform_arr = $tp_platform_arr = GuahaoPlatformModel::getTp($coo_platform_id);
            if ($tp_platform_arr) {
                //医院/医生where
                $this->where = ['hospital_type' => 1, 'status' => 1];
                $this->relationWhere = ['coo_platform' => $coo_platform_id, 'status' => 1];
                $this->joinWhere = [
                    'd.doctor_real_plus' => 2,
                    'd.hospital_type' => 1,
                    'd.status' => 1,
                    'tpr.coo_platform' => $coo_platform_id,
                    'tpr.status' => 1,
                ];
            } else {
                $this->logErrorMsg = $from . '来源没有配置第三方';
            }
        } else {
            $this->logErrorMsg = $from . '来源不存在';
        }
        //记录日志
        if ($this->logErrorMsg) {
            \Yii::$app->params['DataToHospitalRequest']['error_msg'] = $this->logErrorMsg;
        }

    }

    /**
     * 验证签名 是此校验规则的就复用，否则重写
     * @return bool
     * @author xiujianying
     * @date 2021/6/15
     */
    public function validatorSign()
    {
        $data = [];
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
        }
        if (\Yii::$app->request->isGet) {
            $data = \Yii::$app->request->get();
        }
        $sign = ArrayHelper::getValue($data, 'sign');
        unset($data['sign']);
        $this->params = $data;

        //$cipherid = ArrayHelper::getValue($data,'cipherid');
        //$this->key = ArrayHelper::getValue($this->cipherKv,$cipherid);

        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = http_build_query($data);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);

        if ($sign && $sign == $string) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * 自定义 第三方返回格式
     * @param int $code
     * @param string $msg
     * @author xiujianying
     * @date 2021/6/16
     */
    public function returnError($code = 101, $msg = 'token验证失败')
    {
        exit(json_encode([
            $this->code => $code,
            $this->errorMsg => $msg
        ]));
    }

    /**
     * 返回成功的json数据
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function jsonSuccess($data = [], $msg = 'success')
    {
        $return[$this->code] = 0;
        $return[$this->content] = $data;
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @param int $code
     * @return array
     */
    public function jsonError($msg = '', $code = 102)
    {
        $return[$this->code] = $code;
        $return[$this->errorMsg] = $msg;
        //记录日志
        \Yii::$app->params['DataToHospitalRequest']['errorMsg'] = $msg;
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;

        return $this->jsonOutputCore($return);
    }

    /**
     * json输出的核心
     * @param $data
     * @return array
     */
    public function jsonOutputCore($data)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }
}