<?php

namespace api\controllers;

use Yii;
use common\libs\DoctorUrl;
use common\models\DoctorModel;
use common\libs\CommonFunc;
use common\sdks\ucenter\PihsSDK;
use common\models\HospitalEsModel;
use common\models\DoctorEsModel;
use common\libs\HashUrl;
use api\controllers\HospitalController;
use common\models\HospitalDoctorIndexEsMap;

class DoctorController extends CommonController
{
    /**
     * 医生详情
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/24
     */
    public function actionInfo()
    {
        $request = Yii::$app->request;
        $doctor_id = $request->get('doctor_id', 0);
        $miao_doctor_id = $request->get('miao_doctor_id', 0);
        /*if(strval(intval($doctor_id)) != $doctor_id){
            $doctor_id = HashUrl::getIdDecode($doctor_id);
        }
        $update_cache = $request->get('update_cache', 0);
        $doctorInfo = DoctorModel::getInfo($doctor_id, $update_cache);
        $params = [
            'doctor_ids' => $miao_doctor_id,
        ];
        $jiahao_info = PihsSDK::getInstance()->plus_list($params);
        $doctorInfo['jiahao_info'] = $jiahao_info ?? [];*/
        $doctorInfo = ['aa' => 1];
        return $this->jsonSuccess($doctorInfo, '返回成功！');
    }

    public static function get_avatar($uid, $ext = 'jpg', $size = 'mid', $isdir = false)
    {
        $host = \Yii::$app->params['avatarUrl'];
        $size = in_array($size, array('big', 'mid', 'sma')) ? '_' . $size : '';
        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        if ($isdir) {
            return $dir1 . '/' . $dir2 . '/' . $dir3;
        }
        return $host . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . $uid . $size . "." . $ext;
    }

    public function actionList()
    {
        $page = \Yii::$app->request->get('page');
        $pagesize = \Yii::$app->request->get('pagesize', 20);

        $doctor_realname = \Yii::$app->request->get('realname', '');
        $doctor_id = \Yii::$app->request->get('doctor_id', 0);
        $tp_doctor_id = \Yii::$app->request->get('tp_doctor_id', 0);
        $doctor_frist_department_id = \Yii::$app->request->get('fkid', 0);
        $doctor_second_department_id = \Yii::$app->request->get('skid', 0);
        $hospital_id = \Yii::$app->request->get('hospital_id', 0);
        $doctor_title_id = \Yii::$app->request->get('doctor_title_id', 0);
        $doctor_disease_initial = \Yii::$app->request->get('initial', '');//疾病首字母
        $doctor_disease_id = \Yii::$app->request->get('disease_id', 0);//疾病id
        $province_id = \Yii::$app->request->get('province_id', 0);//省id
        $city_id = \Yii::$app->request->get('city_id', 0);//市id
        $district_id = \Yii::$app->request->get('district_id', 0);//区id

        $where = compact('tp_doctor_id','doctor_id', 'doctor_realname', 'doctor_frist_department_id', 'doctor_second_department_id', 'doctor_title_id', 'doctor_disease_initial', 'doctor_disease_id','hospital_id','province_id','city_id','district_id');
        $where = array_filter($where); //过滤值为null
        $order = ['doctor_title_id' => 'asc', 'doctor_id' => 'desc'];
        $docModel=new DoctorEsModel();

        $list = $docModel->selectEs($where,$page,$pagesize,$order);
        return $this->jsonSuccess($list);
    }

    /**
     * 推送合作方队列
     * @return array
     * @author xiujianying
     * @date 2021/6/23
     */
    public function actionGuahaoPush()
    {
        $id = \Yii::$app->request->get('id');
        $type = \Yii::$app->request->get('type'); //1：医生  2：订单  3：号源
        $action = \Yii::$app->request->get('action');
        $tp_platform = \Yii::$app->request->get('tp_platform',0);
        if ($id && $type) {
            if ($type != 3) {
                $job_id = CommonFunc::guahaoPushQueue($id, $type, $action, $tp_platform);
            }
            return $this->jsonSuccess();
        } else {
            return $this->jsonError('参数不全');
        }
    }

    /**
     * 王氏id对应的挂号医生id, 原miaoid_hospital_doctor_id
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-25
     */
    public function actionGetHospitalDoctorId()
    {
        $miao_doctor_id = (int)\Yii::$app->request->get('miao_doctor_id');
        if ($miao_doctor_id <= 0) {
            return $this->jsonError('miao_doctor_id参数不正确');
        }
        $redis = \Yii::$app->redis_codis;
        $docKeyHeader = Yii::$app->params['cache_key']['miaoid_hospital_doctor_id'];
        return $this->jsonSuccess(['doctor_id' => $redis->get(sprintf($docKeyHeader, $miao_doctor_id)) ?: 0], 'ok');
    }

    /**
     * 医生挂号服务数量
     * @return array
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-25
     */
    public function actionGetDoctorRegisterNum()
    {
        $miao_doctor_id = (int)\Yii::$app->request->get('miao_doctor_id');
        if ($miao_doctor_id <= 0) {
            return $this->jsonError('miao_doctor_id参数不正确');
        }
        return $this->jsonSuccess(['num' => CommonFunc::getDoctorRegisterNum($miao_doctor_id) ?: 0], 'ok');
    }

}