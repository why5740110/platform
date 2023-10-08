<?php

namespace api\controllers;

use common\sdks\GuahaoSdk;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;

class HenanGuahaoController extends CommonController
{
    /**
     * 查询渠道开通医院列表
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/22
     */
    public function actionCityInfo()
    {
        $res = GuahaoSdk::getGuahaoInfo(100, ['citycode' => 410100]);
        return $this->asJson($res);
    }

    /**
     * 查询医院信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/22
     */
    public function actionHosInfo()
    {
        $res = GuahaoSdk::getGuahaoInfo(101, ['hosid' => 2020925117]);
        return $this->asJson($res);
    }

    /**
     * 查询科室信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/22
     */
    public function actionKeshiInfo()
    {
        $request      = \Yii::$app->request;
        $snisiyaSdk = new SnisiyaSdk();

        //  $params = [
        //     'tp_platform' => 2,
        //     'citycode'=>'411800'
        // ];
        // $res = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
        // echo "<pre>";print_r($res);die();
        // $res = $snisiyaSdk->getGuahaoDepartment(['tp_platform'=>1,'tp_hospital_code' => '2020925151']);
        // $res    = $snisiyaSdk->getGuahaoDepartment(['tp_platform' => 2, 'tp_hospital_code' => '32010100']);
        $params = [
            'tp_platform'      => 5,
            'tp_hospital_code' => '100000862',
            'hospital_name' => '广州市妇女儿童医疗中心(广州市儿童医院)',
            // 'tp_department_id' => '200215782',
        ];

        // $res = $snisiyaSdk->getGuahaoDoctor($params);
        $res = $snisiyaSdk->getGuahaoDepartment($params);
        foreach ($res['list'] as $key => &$value) {
            $value['tp_hospital_code'] = $params['tp_hospital_code'];
            $value['hospital_name'] = $params['hospital_name'];
        }

        // $hosid = $request->get('hosid', '2020925117');
        // $res = GuahaoSdk::getGuahaoInfo(102,['hosid' => $hosid]);
// echo "<pre>";print_r($res);die();
        return $this->asJson($res);
    }

    /**
     * 查询医生信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/22
     */
    public function actionDocInfo()
    {
        $request = \Yii::$app->request;
        $deptid  = $request->get('deptid', '19031311374600670');
        $res     = GuahaoSdk::getGuahaoInfo(103, ['deptid' => $deptid]);
        return $this->asJson($res);
    }

    /**
     * 查询排班信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/22
     */
    public function actionPreInfo()
    {
        $res = GuahaoSdk::getGuahaoInfo(104, [
            'deptid'    => 16090916185006050,
            'doctorid'  => '',
            'startdate' => '',
            'enddate'   => '',
            'nooncode'  => '',
            'state'     => '',
        ]);
        return $this->asJson($res);
    }

    public function actionSchInfo()
    {
        $res = GuahaoSdk::getGuahaoInfo(105, [
            //'doctorid' => 20051218021007540,
            //'schemaid' => 20092118005207520,

            'doctorid' => 18103015044904980,
            'schemaid' => 20091800023500060,
        ]);
        return $this->asJson($res);
    }

    public function actionTingInfo()
    {
        $res = GuahaoSdk::getGuahaoInfo(106, [
            'hosid'     => 2020915002,
            'startdate' => '',
            'enddate'   => '',
        ]);
        return $this->asJson($res);
    }
}
