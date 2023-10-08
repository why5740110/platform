<?php

namespace mobile\controllers;

use common\helpers\PatientLogin;
use common\sdks\ucenter\PihsSDK;

class PihsController extends CommonController
{
    /**
     * 预约挂号
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/8/18
     */
    public function actionPihs()
    {
        $uid = PatientLogin::isLogin() ?? 0;
        $link_type = $this->getUserAgent();

        if ($link_type == 'patient' || $link_type == 'doctor') {
            $type = 2;
        } else {
            $type = 1;
        }

        if (!$uid) {
            $url = \Yii::$app->params['domains']['ucenter'] . 'uc/login?goBack=' . \Yii::$app->request->referrer;
            $this->redirect($url);
        } else {
            $params = [
                'user_id' => $uid,
                'link_type' => 1,
            ];
            $res = PihsSDK::getInstance()->getRegist($params);
            if (is_array($res) && isset($res['fastOrderHospitalUrl']) && !empty($res['fastOrderHospitalUrl'])) {
                $fastOrderHospitalUrl = $res['fastOrderHospitalUrl'];
                $this->redirect($fastOrderHospitalUrl);
            } else {
                $this->redirect(\Yii::$app->params['hospitalUrl']);
            }
        }

    }
}