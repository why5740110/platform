<?php
/**
 * 第三方接口变动通知地址
 * @author yangquanliang <yangquanliang@yuanxin-inc.com>
 * @date    2021-03-22
 * @version 1.0
 * @return  [type]     [description]
 */

namespace api\controllers;

use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\ucenter\PihsSDK;
use common\models\TmpDoctorThirdPartyModel;
use Yii;
use yii\helpers\ArrayHelper;

class NoticeController extends CommonController
{

    const TP_PLATFORM = 6;
    public $data      = [];
    public $pagesize  = 100;

    public function init()
    {
        parent::init();
        $getData    = Yii::$app->request->get();
        $postData   = Yii::$app->request->post();
        $this->data = array_merge($getData, $postData);
    }

    public function actionConfig()
    {
        $tp_where = [
            'tp_platform'=>5,
            'tp_hospital_code'=>'200037466',
        ];
        $res = SnisiyaSdk::getInstance()->getHospitalConfig($tp_where);
        return $this->jsonSuccess($res);
    }

    public function actionDoctorList()
    {
        $tp_doctor_id = (int) ArrayHelper::getValue($this->data, 'tp_doctor_id', 0);
        $page         = (int) ArrayHelper::getValue($this->data, 'page', 1);
        $pagesize     = (int) ArrayHelper::getValue($this->data, 'pagesize', 20);
        if ($tp_doctor_id) {
            $scheduleplace_list = PihsSDK::getInstance()->getDoctorScheduleplace(['tp_doctor_id' => $tp_doctor_id]);
        } else {
            $scheduleplace_list = PihsSDK::getInstance()->getDoctorScheduleplace(['page' => $page, 'pagesize' => $pagesize]);
        }
        $list = ArrayHelper::getValue($scheduleplace_list, 'list', []);
        return $this->jsonSuccess($scheduleplace_list);
    }

    /**
     * 获取s.医生列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-26
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDoctorLists()
    {
        $tp_doctor_id = (int) ArrayHelper::getValue($this->data, 'tp_doctor_id', 0);
        $page         = (int) ArrayHelper::getValue($this->data, 'page', 1);
        $pagesize     = (int) ArrayHelper::getValue($this->data, 'pagesize', $this->pagesize);
        $tp_where     = ['tp_platform' => self::TP_PLATFORM, 'page' => $page, 'pagesize' => $pagesize];
        if ($tp_doctor_id) {
            $tp_where = ['tp_doctor_id' => $tp_doctor_id,'tp_platform' => self::TP_PLATFORM];
        }
        $scheduleplace_list = SnisiyaSdk::getInstance()->getGuahaoDoctor($tp_where);
        // $list               = ArrayHelper::getValue($scheduleplace_list, 'list.0.scheduleplace_list', []);

        // $list = ArrayHelper::getValue($scheduleplace_list, 'list', []);
        return $this->jsonSuccess($scheduleplace_list);
    }

}
