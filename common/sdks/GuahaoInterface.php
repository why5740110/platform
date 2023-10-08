<?php
namespace common\sdks;

interface GuahaoInterface{
    /**
     * 获取医院
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    public function actionGetTpHospital();

    /**
     * 获取医生
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    public function actionGetTpDoctor();


}