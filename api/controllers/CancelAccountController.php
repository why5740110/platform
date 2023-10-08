<?php
/**
 * 账号注销
 * @file CancelAccountController.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-01-21
 */

namespace api\controllers;


use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use common\models\GuahaoOrderModel;
use yii\web\Response;
use Exception;

class CancelAccountController extends CommonController
{

    /**
     *  根据用户的uid 查询15 日内有效订单
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-01-21
     */

    public function actionGetGuahaoCountByUser()
    {
        try {
            $data['guahao_order_count'] = 0;
            $data['order_type'] = 0;
            if ($this->request->getIsGet() == false) {
                throw new Exception('请求方式不正确');
            }
            $uid = \Yii::$app->request->get('uid', '');
            if (empty($uid)) {
                throw new Exception('参数有误');
            }
            // 15天以内 无订单
            $effectiveNumberIn = GuahaoOrderModel::find()
                ->select('id')
                ->where(['uid' => $uid])
                ->andWhere(['>=', 'create_time', strtotime("-15 day")])
                ->count();

            // 未完结订单
            $effectiveNumberOut = GuahaoOrderModel::find()
                ->select('id')
                ->where(['uid' => $uid])
                ->andWhere(['in', 'state', [0,5,7,8]]) //0:下单成功 1:取消 2:停诊 3:已完成 4:爽约 5:待支付，6：无效，7：下单中,8:待审核)
                ->count();
            $msg = '请求成功';
            if ($effectiveNumberIn) {
                $data['guahao_order_count'] = $effectiveNumberIn;
                $data['order_type'] = 1; // 15天内有订单
                $msg = "您的账号存在近15天内的订单，不符合注销条件";
            }

            if ($effectiveNumberOut) {
                $data['guahao_order_count'] = $effectiveNumberOut;
                $data['order_type'] = 2; // 未完结订单
                $msg = "您的账号存在未完结的服务，不符合注销条件";
            }

            return $this->jsonSuccess($data, $msg);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }
}

