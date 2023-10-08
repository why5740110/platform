<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\DoctorModel;
use common\models\HospitalEsModel;
use common\models\HospitalDepartmentRelation;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\TbLog;
use common\models\TmpDepartmentThirdPartyModel;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;
use yii\web\Controller;
use yii\web\Response;

class KeshiController extends Controller
{
    public $enableCsrfValidation = false;
    public $platform;

    public $userInfo = ['id' => 0, 'username' => '', 'realname' => ''];

    public function init()
    {
        parent::init();
        $this->platform   = CommonFunc::getTpPlatformNameList(1);
        $userInfo = !empty($_COOKIE['MiaoBaseAdminUser']) ? $_COOKIE['MiaoBaseAdminUser'] : [];
        if ($userInfo) {
           $this->userInfo = json_decode($userInfo, true);
        }
    }

    /**
     * 获取王氏二级科室列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSecondDepartmentList()
    {
        $request     = Yii::$app->request;
        $fkeshi_id   = $request->get('fkeshi_id', '');
        $hospital_id = $request->get('hospital_id', '');
        $skeshi_list = HospitalDepartmentRelation::find()->select('id,second_department_id,second_department_name')->where([
            'frist_department_id' => $fkeshi_id,
            'hospital_id'         => $hospital_id,
        ])->asArray()->all();
        return $this->returnJson(1, '操作成功!', $skeshi_list);
    }

    public function actionMiaoSecondDepartmentList()
    {
        $request     = Yii::$app->request;
        $fkeshi_id   = $request->get('fkeshi_id', '');
        $skeshi_list = CommonFunc::getSkeshiInfos($fkeshi_id);
        return $this->returnJson(1, '操作成功!', $skeshi_list);
    }

    /**
     * 获取全部二级科室列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSkeshiList()
    {
        $request     = Yii::$app->request;
        $fkeshi_id   = $request->get('fkeshi_id', '');
        $skeshi_list = !empty($fkeshi_id) ? CommonFunc::get_all_skeshi_list($fkeshi_id) : [];
        return $this->returnJson(1, '操作成功!', $skeshi_list);

    }

    /**
     * 医院内编辑科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-13
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionRelation()
    {
        $request       = Yii::$app->request;
        $requestParams = Yii::$app->request->getQueryParams();
        $id            = $request->get('id', '');
        $data          = [];

        if (!$id) {
            return $this->_showMessage('id不存在！', Yii::$app->urlManager->createUrl('/hospital/index'));
        }
        $relationInfo               = HospitalDepartmentRelation::find()->where(['id' => $id])->asArray()->one();
        //$hospital                   = BaseDoctorHospitals::find()->where(['id' => $relationInfo['hospital_id']])->asArray()->one();
        $hospital = BaseDoctorHospitals::getHospitalDetail($relationInfo['hospital_id']);
        $hospital_keshi             = HospitalDepartmentRelation::find()->where(['hospital_id' => $id])->select('id,frist_department_id,frist_department_name,second_department_id,second_department_name,doctors_num,is_recommend')->asArray()->all();
        $hospital['hospital_keshi'] = $hospital_keshi;
        $data                       = [];
        $data['hospital']           = $hospital ?? [];
        $fkeshi_list                = Department::department_platform() ?? [];
        $data['fkeshi_list']        = $fkeshi_list;
        $data['relationInfo']       = $relationInfo;
        $data['miao_slist']         = [];
        if ($relationInfo['miao_frist_department_id']) {
            $data['miao_slist'] = CommonFunc::getSkeshiInfos($relationInfo['miao_frist_department_id']);
        }
        $data['id']               = $id;
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();
        $keshiRelation            = TbDepartmentThirdPartyRelationModel::find()->where(['hospital_department_id' => $id])->asArray()->all();
        foreach ($keshiRelation as &$item) {
            $item['tp_platform_name'] = $this->platform[$item['tp_platform']];
        }
        $data['keshi_relation'] = $keshiRelation;
        return $this->renderPartial('//hospital/relation', $data);
    }

    /**
     * 关联科室
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/12/1
     */
    public function actionThirdRelation()
    {
        $request     = Yii::$app->request;
        $hospital_id = (int)$request->get('hospital_id', 0);
        if ($hospital_id) {
            //$hospital       = BaseDoctorHospitals::find()->where(['id' => $hospital_id])->asArray()->one();
            $hospital       =  BaseDoctorHospitals::getHospitalDetail($hospital_id);
            if (!$hospital) {
                return $this->returnJson(2, '该医院:'.$hospital_id.'不存在!');
            }
            $hospital_keshi = HospitalDepartmentRelation::find()->distinct('frist_department_id')->where(['hospital_id' => $hospital_id])->select('frist_department_id,frist_department_name')->asArray()->all();

            $data['hospital_keshi'] = $hospital_keshi;
            $data['hospital']       = $hospital ?? [];
            $data['hospital_id']    = $hospital_id;

        }
        return $this->renderPartial('//hospital/third-relation', $data);
    }

    public function actionSaveThirdRelation()
    {
        $request  = Yii::$app->request;
        $postData = $request->post();
        $hosRes = HospitalDepartmentRelation::find()->where(['id'=>$postData['dep_id']])->asArray()->one();
        if (!$hosRes) {
            return $this->returnJson(2, '该关联科室不存在!');
        }
        $res      = TbDepartmentThirdPartyRelationModel::find()->where([
            'hospital_department_id' => $postData['dep_id'],
            'tp_department_id'       => $postData['tp_department_id'],
            'tp_platform'            => $postData['tp_platform'],
        ])->one();
        if ($res) {
            return $this->returnJson(0, '已存在!');
        } else {
            $tbModel                         = new TbDepartmentThirdPartyRelationModel();
            $tbModel->hospital_department_id = $postData['dep_id'];
            $tbModel->tp_platform            = $postData['tp_platform'];
            $tbModel->tp_department_id       = $postData['tp_department_id'];
            $tbModel->create_time            = time();

            $tmpRes = TmpDepartmentThirdPartyModel::find()->where([
                'tp_department_id' => $postData['tp_department_id'],
                'tp_platform'      => $postData['tp_platform'],
            ])->one();
            if ($tmpRes) {
                $tmpRes->is_relation            = 1;
                $tmpRes->hospital_department_id = $postData['dep_id'];
            }
            try {
                if (!$tbModel->save()){
                    throw new \Exception(json_encode($tbModel->getErrors(), JSON_UNESCAPED_UNICODE));
                }
                if (!$tmpRes->save()) {
                    throw new \Exception(json_encode($tmpRes->getErrors(), JSON_UNESCAPED_UNICODE));
                }
                $editContent  = $this->userInfo['realname'] . '关联了科室:' . $tmpRes['third_fkname'].'-'.$tmpRes['third_skname'] . ' ->' . $hosRes['frist_department_name'].'-'.$hosRes['second_department_name'];
                    TbLog::addLog($editContent, '科室关联添加');
                return $this->returnJson(1, '操作成功!');
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
                return $this->returnJson(2, $msg);
            }

        }
        return $this->returnJson(0, '操作失败!');
    }

    /**
     * 医院保存科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-13
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSaveRelation()
    {
        $request    = Yii::$app->request;
        $cookie     = \Yii::$app->request->cookies;
        $admin_id   = $cookie->getValue('uid', '');
        $admin_name = $cookie->getValue('name', '');
        $postData   = $request->post();
        $id         = $postData['id'];
        if (!$id) {
            return $this->_showMessage('id不存在！', Yii::$app->urlManager->createUrl('/hospital/index'));
        }

        $hospitalDepartmentModel = HospitalDepartmentRelation::find()->where(['id' => $id])->one();
        if (!$hospitalDepartmentModel) {
            return $this->_showMessage('内存不存在！', Yii::$app->urlManager->createUrl('/hospital/index'));
        }
        $hospital_id = $hospitalDepartmentModel->hospital_id;
        try {
            $hospitalDepartmentModel->miao_frist_department_id  = (int) $postData['miao_frist_department_id'];
            $hospitalDepartmentModel->miao_second_department_id = (int) $postData['miao_second_department_id'];
            $hospitalDepartmentModel->address = $postData['address'];
            $hospitalDepartmentModel->status                    = 1;
            $status                                             = $hospitalDepartmentModel->save();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
        }
        if ($status) {
            //$hospitalInfo = BaseDoctorHospitals::find()->select('name')->where(['id' => $hospital_id])->asArray()->one();
            $hospitalInfo = BaseDoctorHospitals::getHospitalDetail($hospital_id);
            //更新当前医院科室缓存
            HospitalDepartmentRelation::hospitalDepartment($hospital_id, true);
            $editContent  = $admin_name . '修改了医院:' . $hospitalInfo['name'] . ' 一级科室为:' . $hospitalDepartmentModel->frist_department_name . ' 二级科室为:' . $hospitalDepartmentModel->second_department_name . '的科室' . ' 科室地址为:' . $hospitalDepartmentModel->address;
            TbLog::addLog($editContent, '医院科室编辑');
            return $this->returnJson(1, '操作成功!');
        } else {
            return $this->returnJson(0, !empty($msg) ? $msg : '保存失败!');
        }

    }

    public function returnJson($status = 1, $msg = '操作成功', $data = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data   = [
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,

        ];
        Yii::$app->response->send();
        exit();
    }

    public function _showMessage($message = '', $redirect = '', $timeout = 2, $type = 'failed')
    {
        exit($this->render('//tips/message', array('content' => $message, 'redirect' => $redirect, 'timeout' => $timeout, 'type' => $type)));
    }

    public function actionCancleRelation()
    {
        $request          = \Yii::$app->request;
        $tp_department_id = $request->get('tp_department_id', '');
        $tp_platform      = $request->get('tp_platform', '');
        $tbModel          = TbDepartmentThirdPartyRelationModel::find()->where([
            'tp_department_id' => $tp_department_id,
            'tp_platform'      => $tp_platform,
        ])->one();
        $tmpRes = TmpDepartmentThirdPartyModel::find()->where([
            'tp_department_id' => $tp_department_id,
            'tp_platform'      => $tp_platform,
        ])->one();
        if ($tmpRes) {
            $tmpRes->is_relation            = 0;
            $tmpRes->hospital_department_id = 0;
        }
        if ($tmpRes->save() && $tbModel->delete()) {
            return $this->returnJson(1, '取消关联成功');
        }
        return $this->returnJson(2, '取消关联失败');
    }

    /**
     * 获取医院
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-11
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionAjaxHos()
    {
        $request = \Yii::$app->request;
        $name                        = trim($request->get('name'));

        /*$query   = BaseDoctorHospitals::find()->select('id,name')->where(['status'=>0,'is_hospital_project'=>1]);
        if (is_numeric($name)) {
            $query->andWhere(['id' => $name]);
        } else {
            $query->andWhere(['like', 'name', $name]);
        }
        $result = $query->asArray()->all();*/

        $where = ['status'=>0,'is_hospital_project'=>1];
        if (is_numeric($name)) {
            $where['id'] = $name;
        } else {
            $where['name'] = $name;
        }
        $result = BaseDoctorHospitals::getHospitalSearch($where);

        foreach ($result as &$item) {
            $item['name'] = $item['id'].'-'.$item['name'];
        }
        return $this->returnJson(1, '操作成功!', $result); 
    }

    /**
     * 获取医生
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-11
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionAjaxDoctor()
    {
        $request = \Yii::$app->request;
        $name    = trim($request->get('name', ''));
        $is_primary    = (int)($request->get('is_primary', 0));
        $query   = DoctorModel::find()->select('doctor_id,realname')->where(['status' => 1]);
        if ($is_primary) {
            $query->andWhere(['primary_id' => 0]);
        }
        if (is_numeric($name)) {
            $query->andWhere(['doctor_id' => $name]);
        } else {
            $query->andWhere(['like', 'realname', $name]);
        }
        $result = $query->asArray()->all();
        return $this->returnJson(1, '操作成功!', $result);
    }

    /**
     * 获取科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-11
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionAjaxKeshi()
    {
        $request = \Yii::$app->request;
        $hosid  = $request->get('hosid');
        $result = HospitalDepartmentRelation::hospitalDepartment($hosid);
        return $this->returnJson(1, '操作成功!', $result);
    }

    /**
     * 异步获取二级科室信息
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2018-08-20
     */
    public function actionAjaxSkeshi()
    {
        $request = \Yii::$app->request;
        $pid     = $request->get('pid');
        $hosid   = $request->get('hosid');
        $result  = HospitalDepartmentRelation::hospitalDepartment($hosid)[$pid] ?? [];
        return $this->returnJson(1, '操作成功!', $result);
    }

    /**
     * 更新医院医生es
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionAjaxUpHospital()
    {
        $request = \Yii::$app->request;
        $hospital_id     = (int)$request->get('hospital_id',0);
        if (!$hospital_id) {
            return $this->returnJson(2, '医院id不存在！');
        }
        //$infoData = BaseDoctorHospitals::find()->where(['id' => $hospital_id])->asArray()->one();
        $infoData = BaseDoctorHospitals::getHospitalDetail($hospital_id);
        if (!$infoData) {
            return $this->returnJson(2, '医院信息不存在！');
        }
        // if ($infoData['status'] == 0) {
        //    return $this->returnJson(2, '医院非禁用不能操作！');
        // }    
        // if ($infoData['is_hospital_project'] == 1) {
        //    return $this->returnJson(2, '医院在医院线展示不能操作！');
        // }          
        ##入队列更新医院以及医生信息
        CommonFunc::upHospitalDoctorEsData($hospital_id,1);
        return $this->returnJson(1, '操作成功！');
    }



}
