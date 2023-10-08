<?php
/**
 * 民营医院列表、审核
 * @file HospitalMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-15
 */
namespace minyingapi\controllers;

use common\models\minying\MinHospitalModel;
use Yii;
use common\libs\CommonFunc;

class HospitalMinyingCheckController extends CommonController
{
    /**
     * 详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-15
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id') ?? $this->user['min_hospital_id'];//民营医院id
        $info = MinHospitalModel::getDetail($id);
        $domain = \Yii::$app->params['min_hospital_img_oss_url_prefix'];
        $info['min_hospital_logo'] = $domain . $info['min_hospital_logo'];
        $info['create_time'] = date('Y-m-d H:i:s', $info['update_time']);
        $info['min_hospital_level'] = MinHospitalModel::$levellist[$info['min_hospital_level']];
        $info['min_hospital_nature'] = MinHospitalModel::$naturelist[$info['min_hospital_nature']];
        $info['check_status_desc'] = MinHospitalModel::$checklist[$info['check_status']];
        $info['min_hospital_tags'] = MinHospitalModel::getTagsInfo($info['min_hospital_tags']);
        $info['min_hospital_type'] = MinHospitalModel::$TypeList[$info['min_hospital_type']];
        $info['min_treatment_project'] = MinHospitalModel::getTreatmentProject($info['min_treatment_project']);//诊疗项目
        $info['min_business_license'] = CommonFunc::getDomainPic($domain, $info['min_business_license']);//营业执照
        $info['min_medical_license'] = CommonFunc::getDomainPic($domain, $info['min_medical_license']);//医疗许可证件
        $info['min_health_record'] = CommonFunc::getDomainPic($domain, $info['min_health_record']);//卫健委备案
        $info['min_medical_certificate'] = CommonFunc::getDomainPic($domain, $info['min_medical_certificate']);//医疗广告证
        return $this->jsonSuccess($info);
    }
}