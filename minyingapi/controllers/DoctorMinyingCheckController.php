<?php
/**
 * 民营医院医生列表、审核
 * @file DoctorMinyingCheckController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-19
 */

namespace minyingapi\controllers;

use common\models\minying\MinDoctorModel;
use common\models\minying\MinDepartmentModel;
use Yii;
use common\libs\CommonFunc;

class DoctorMinyingCheckController extends CommonController
{

    public function actionList(){
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->limit;
        $requestParams['doctor_name'] = (isset($requestParams['doctor_name']) && (!empty($requestParams['doctor_name']))) ? $requestParams['doctor_name'] : '';
        $requestParams['check_status'] = (isset($requestParams['check_status']) && (!empty($requestParams['check_status']))) ? $requestParams['check_status'] : '';
        $requestParams['hospital_id'] = $this->user['min_hospital_id'];

        $field = ['min_doctor_id','min_doctor_name','min_hospital_id','min_hospital_name','check_status','first_check_uname','first_check_time','second_check_uname','second_check_time','create_time','update_time'];
        $list = MinDoctorModel::getList($requestParams, $field);
        foreach ($list as &$item) {
            $item['create_time'] = date("Y-m-d H:i:s", $item['update_time']);
            $item['first_check_time'] = ($item['first_check_time'] > 0) ? date("Y-m-d H:i:s", $item['first_check_time']) : '';
            $item['second_check_time'] = !empty($item['second_check_time']) ? date("Y-m-d H:i:s", $item['second_check_time']) : '';
            $item['check_status_desc'] = MinDoctorModel::$checklist[$item['check_status']];
        }

        $totalCount = MinDoctorModel::getCount($requestParams);
        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];
        return $this->jsonSuccess($result);
    }

    /**
     * 详情
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-18
     */
    public function actionInfo()
    {
        $id = Yii::$app->request->get('id');
        $info = MinDoctorModel::getDetail($id);
        $domain = \Yii::$app->params['min_doctor_img_oss_url_prefix'];
        $info['create_time'] = date("Y-m-d H:i:s", $info['update_time']);
        $info['mobile'] = (!empty($info['mobile'])) ? substr_replace($info['mobile'],'****', 3, 4) : "";
        //医生头像
        $info['avatar'] = !empty($info['avatar']) ? $domain . $info['avatar'] : '';
        $info['check_status_desc'] = MinDoctorModel::$checklist[$info['check_status']];
        $info['min_doctor_tags'] = MinDoctorModel::getTagsInfoById($info['min_doctor_tags']);

        $department = MinDepartmentModel::getDetail($info['min_department_id']);
        $info['department'] = $department['min_minying_fkname'] . '-' . $department['min_minying_skname'];
        $info['visitType'] = MinDoctorModel::$visitType[$info['visit_type']];
        //身份证有效期
        $info['id_card_begin'] = date("Y.m.d", $info['id_card_begin']);
        $info['id_card_end'] = date("Y.m.d", $info['id_card_end']);
        $info['id_card_file'] = CommonFunc::getDomainPic($domain, $info['id_card_file']);//身份证件图片
        //医师执业证有效期
        $info['practicing_cert_begin'] = date("Y.m.d", $info['practicing_cert_begin']);
        $info['practicing_cert_end'] = date("Y.m.d", $info['practicing_cert_end']);
        $info['practicing_cert_file'] = CommonFunc::getDomainPic($domain, $info['practicing_cert_file']);//医师执业证图片
        //医师资格证有效期
        $info['doctor_cert_begin'] = date("Y.m.d", $info['doctor_cert_begin']);
        $info['doctor_cert_end'] = date("Y.m.d", $info['doctor_cert_end']);
        $info['doctor_cert_file'] = CommonFunc::getDomainPic($domain, $info['doctor_cert_file']);//医师资格证图片
        //专业技术资格证有效期
        $info['professional_cert_begin'] = date("Y.m.d", $info['professional_cert_begin']);
        $info['professional_cert_end'] = date("Y.m.d", $info['professional_cert_end']);
        $info['professional_cert_file'] = CommonFunc::getDomainPic($domain, $info['professional_cert_file']);//专业技术资格证图片
        //多点执业证明有效期
        $info['multi_practicing_cert_begin'] = date("Y.m.d", $info['multi_practicing_cert_begin']);
        $info['multi_practicing_cert_end'] = date("Y.m.d", $info['multi_practicing_cert_end']);
        $info['multi_practicing_cert_file'] = CommonFunc::getDomainPic($domain, $info['multi_practicing_cert_file']);//多点执业证明图片

        return $this->jsonSuccess($info);
    }

    /**
     * 获取民营医院医生列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public function actionGetDoctorList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['min_hospital_id'] = $this->user['min_hospital_id'];
        $requestParams['min_doctor_name'] = (isset($requestParams['min_doctor_name']) && (!empty($requestParams['min_doctor_name']))) ? $requestParams['min_doctor_name'] : '';
        $docList = MinDoctorModel::getAuditList($requestParams);
        $result = [];
        if (!empty($docList)) $result = $docList;

        return $this->jsonSuccess($result);
    }

}