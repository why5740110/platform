<?php
/**
 * @file ShaanxiController.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/3
 */


namespace api\controllers;


use api\behaviors\RecordLoging;
use common\libs\CommonFunc;
use common\models\DoctorModel;
use common\models\GuahaoOrderModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class ShaanxiController extends \yii\web\Controller
{

    const TP_PLATFORM = 7;

    public $username = '';
    public $aesSecretKey = '';
    public $aesIv = '';
    public $key = '';
    public $params = [];
    public $sign = '';
    public $funCode = '';

    public function init()
    {
        parent::init();

        $this->username = ArrayHelper::getValue(\Yii::$app->params, 'gh_shaanxi.username');
        $this->aesSecretKey = $this->key = ArrayHelper::getValue(\Yii::$app->params, 'gh_shaanxi.key');

        $this->validatorSign();
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
     * 验证
     * @return bool
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/3
     */
    private function validatorSign()
    {
        $data = [];
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
        }
        if (\Yii::$app->request->isGet) {
            $data = \Yii::$app->request->get();
        }

        //$this->postData = $data;
        //$data['reqEncrypted'] = 'bLOfMlW/hhkhUFZVZ7O9id4JhYRUz3mN3eTtPfTkNJJhKev1kUEDb+HC7j7dNsBCNDGiZy0oIWItjv7fVZhHjI4IRFDMjojRK1U7HxikjcyXbqfSr8mIQRxyPYFGopdVB1ECvwVafYy6Gbqh5TjrMKPqpkrzH5SzFnJ1bRhXfZfedmY347/StGGXS0AkBsLr671+z5awuUjHfdV4hH/rKg==';

        $this->sign = $sign = $this->makeSign($data);
        if ($data && ArrayHelper::getValue($data, 'sign') == $sign) {
            //验证通过 解密业务数据
            $params = $this->aesDecode(ArrayHelper::getValue($data, 'reqEncrypted'));
            $params = json_decode($params, true);
            $this->funCode = ArrayHelper::getValue($data, 'funCode');

            $this->params = $params;
        } else {
            exit(json_encode([
                'returnCode' => 'ILLEGAL_SIGN',
                'returnMsg' => 'token验证失败',
                'signType' => 'MD5',
                'sign' => $sign,
                'resEncrypted' => ''
            ]));
            return false;
        }
        return true;
    }

    /**
     * 加密
     * @param $data
     * @return string
     * @author xiujianying
     * @date 2021/6/3
     */
    protected function makeSign($data)
    {
        //去掉空值
        $data = array_filter($data);
        //去掉不参加签名参数
        unset($data['sign']);
        unset($data['signType']);
        unset($data['inputCharset']);
        unset($data['source']);
        unset($data['version']);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     * @param $data
     * @return string
     * @author xiujianying
     * @date 2021/6/4
     */
    protected function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($v !== "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    protected function aesDecode($secretData)
    {
        return openssl_decrypt(base64_decode($secretData), 'aes-128-ecb', $this->aesSecretKey, OPENSSL_RAW_DATA, $this->aesIv);
    }

    protected function aesEncode($data)
    {
        $data = base64_encode(openssl_encrypt($data, 'aes-128-ecb', $this->aesSecretKey, OPENSSL_RAW_DATA, $this->aesIv));
        return $data;
    }

    /**
     * 回调接口
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/4
     */
    public function actionIndex()
    {
        $params = $this->params;
        \Yii::$app->params['DataToHospitalRequest']['platform'] = '107';

        \Yii::$app->params['DataToHospitalRequest']['paramsData'] = $params;

        \Yii::$app->params['DataToHospitalRequest']['index'] = ArrayHelper::getValue($params, 'hosId', '');

        $errorMsg = '';
        $funCode = $this->funCode;
        switch ($funCode) {
            case '210102':  //取消通知接口
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderCancel';

                $tp_order_id = ArrayHelper::getValue($params, 'orderNo', '');
                $model = new GuahaoOrderModel();
                $order = $model->find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_order_id' => $tp_order_id])->one();
                if ($order) {
                    if ($order->state == 0) {
                        $order->state = 1;  //取消状态
                        $stop_type = '取消';
                        $cancelRemark = ArrayHelper::getValue($params, 'cancelRemark');
                        $cancelSceneId = ArrayHelper::getValue($params, 'cancelSceneId');
                        $scene = ['1' => '医院发起排班/订单停诊', '2' => '医院发起订单取消', '3' => '平台发起异常订单取消'];
                        $scene = ArrayHelper::getValue($scene, $cancelSceneId);
                        $order->state_desc = $order->state_desc . "||$stop_type;原因:$cancelRemark;场景:$scene";

                        $order->update_time = time();
                        $res = $order->save();
                        if ($res) {
                            //发短信
                            //CommonFunc::guahaoSendSms('guahao_cancel', $order['order_sn']);
                        }
                    } elseif ($order->state == 1) {
                        return $this->jsonError($tp_order_id . '已是取消状态');
                        //$errorMsg .= '|' . $tp_order_id . '已是取消状态';
                    }
                } else {
                    return $this->jsonError($tp_order_id . '订单不存在');
                    //$errorMsg .= '|' . $tp_order_id . '订单不存在';
                }

                break;
            case '210104':   //停诊通知接口
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderStop';
                //医生
                $docData = ArrayHelper::getValue($params, 'stopScheList');
                if ($docData && is_array($docData)) {
                    foreach ($docData as $doc) {
                        $docCode = ArrayHelper::getValue($doc, 'docCode');
                        $doctor_id = DoctorModel::find()->where(['tp_doctor_id' => $docCode, 'tp_platform' => self::TP_PLATFORM])->select(['doctor_id'])->scalar();
                        if ($doctor_id) {
                            //更新排班缓存
                            $snisiyaSdk = new SnisiyaSdk();
                            $snisiyaSdk->updateScheduleCache(['doctor_id' => $doctor_id, 'tp_platform' => self::TP_PLATFORM]);
                        } else {
                            return $this->jsonError($docCode . '医生不存在');
                            //$errorMsg .= '|' . $docCode . '医生不存在';
                        }
                    }
                }
                //订单
                $orderData = ArrayHelper::getValue($params, 'stopRegList');
                if ($orderData && is_array($orderData)) {
                    foreach ($orderData as $orderRow) {
                        $orderNo = ArrayHelper::getValue($orderRow, 'orderNo');
                        $order = GuahaoOrderModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_order_id' => $orderNo])->one();
                        if ($order) {
                            if ($order->state == 0) {
                                $order->state = 2;  //停诊状态
                                $stop_type = '停诊';
                                $stop_desc = '您的预约被停诊，给您带来的不便敬请理解。';
                                $stopRemark = ArrayHelper::getValue($params, 'stopRemark');
                                $order->state_desc = $order->state_desc . "||$stop_type:$stopRemark";
                                $order->update_time = time();
                                $res = $order->save();
                                if ($res) {
                                    //发短信
                                    CommonFunc::guahaoSendSms('guahao_stop', $order['order_sn'], $stop_type, $stop_desc);
                                }
                            } elseif ($order->state == 2) {
                                return $this->jsonError($orderNo . '已是停诊状态');
                                //$errorMsg .= '|' . $orderNo . '已是停诊状态';
                            }
                        } else {
                            return $this->jsonError($orderNo . '订单不存在');
                            //$errorMsg .= '|' . $orderNo . '订单不存在';
                        }
                    }
                }
                break;
            default:
                \Yii::$app->params['DataToHospitalRequest']['request_type'] = 'orderError';
                return $this->jsonError("funCode：{$funCode}未添加");
        }

        //\Yii::$app->params['DataToHospitalRequest']['errorMsg'] = $errorMsg;

        return $this->jsonSuccess();
    }

    /**
     * 返回成功的json数据
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function jsonSuccess($data = [], $msg = 'success')
    {
        $return['returnCode'] = $msg;
        $return['returnMsg'] = '成功';
        $return['signType'] = 'MD5';
        $return['sign'] = $this->sign;
        $return['resEncrypted'] = '';
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 200;
        return $this->jsonOutputCore($return);
    }

    /**
     * 返回失败的json数据
     * @param string $msg
     * @param int $code
     * @return array
     */
    protected function jsonError($msg = '', $code = 'FAIL')
    {
        $return['returnCode'] = $code;
        $return['returnMsg'] = $msg;
        $return['signType'] = 'MD5';
        $return['sign'] = $this->sign;
        $return['resEncrypted'] = '';
        \Yii::$app->params['DataToHospitalRequest']['cur_log']['log_code'] = 400;
        return $this->jsonOutputCore($return);
    }

    /**
     * json输出的核心
     * @param $data
     * @return array
     */
    protected function jsonOutputCore($data)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

}