<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\DoctorModel;
use common\models\GuahaoScheduleModel;
use common\models\HospitalDepartmentRelation;
use common\models\TbLog;
use common\sdks\snisiya\SnisiyaSdk;
use common\models\TmpDoctorThirdPartyModel;
use Yii;
use yii\data\ActiveDataProvider;
use \yii\helpers\ArrayHelper;

class DoctorRelationController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size = 10;

    public function actionList()
    {
        $requestParams                  = Yii::$app->request->getQueryParams();

        $requestParams['page']          = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit']         = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['tp_platform']   = isset($requestParams['tp_platform']) ? (int) ($requestParams['tp_platform']) : '';
        $requestParams['is_relation']   = isset($requestParams['is_relation']) ? trim($requestParams['is_relation']) : '';
        $requestParams['doctor']        = isset($requestParams['doctor']) ? trim($requestParams['doctor']) : '';
        $requestParams['tp_doctor_id']        = isset($requestParams['tp_doctor_id']) ? trim($requestParams['tp_doctor_id']) : '';
        $requestParams['doctor_id']        = isset($requestParams['doctor_id']) ? trim($requestParams['doctor_id']) : '';
        $requestParams['hospital_name'] = isset($requestParams['hospital_name']) ? trim($requestParams['hospital_name']) : '';
        $requestParams['keshi']         = isset($requestParams['keshi']) ? trim($requestParams['keshi']) : '';
        // $requestParams['admin_name']    = isset($requestParams['admin_name']) ? trim($requestParams['admin_name']) : '';

        $field = '*';
        $where = [
            'status'=>1
        ];
        $query = TmpDoctorThirdPartyModel::find()->select($field)->where($where);
        $query->andWhere(['in','is_relation',[0,1]]);##增加只展示0和1
        if (isset($requestParams['is_relation']) && $requestParams['is_relation'] !== '') {
            $query->andWhere(['is_relation' => trim($requestParams['is_relation'])]);
        }
        if (!empty(trim($requestParams['tp_platform']))) {
            $query->andWhere(['tp_platform' => trim($requestParams['tp_platform'])]);
        }

        if (!empty(trim($requestParams['doctor']))) {
            $query->andWhere(['like', 'realname', trim($requestParams['doctor'])]);
        }        

        if (!empty(trim($requestParams['tp_doctor_id']))) {
            $query->andWhere(['tp_doctor_id'=>trim($requestParams['tp_doctor_id'])]);
        }
        if (!empty(trim($requestParams['doctor_id']))) {
            $query->andWhere(['doctor_id'=>trim($requestParams['doctor_id'])]);
        }

        if (!empty(trim($requestParams['hospital_name']))) {
            $query->andWhere(['like', 'hospital_name', trim($requestParams['hospital_name'])]);
        }

        if (!empty($requestParams['keshi'])) {
            $query->andWhere(['or', ['frist_department_name' => $requestParams['keshi']], ['second_department_name' => $requestParams['keshi']]]);
        }
        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $requestParams['limit'],
            ],
            'sort'       => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);


        $data = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        return $this->render('list', $data);
    }

    public function actionRelation()
    {
        $request                = \Yii::$app->request;
        $tp_doctor_id           = $request->get('tp_doctor_id', '');
        $tmp_id           = $request->get('tmp_id', '');
        $relation           = $request->get('relation', '');
        $data['tp_doctor_info'] = TmpDoctorThirdPartyModel::find()->where(['id' => $tmp_id])->one();
        return $this->renderPartial('relation', $data);
    }

    public function actionAjaxGetInfo()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            $doctor_id = $request->get('doctor_id', '');
            $is_primary = $request->get('is_primary', 0);
            $query = DoctorModel::find()->select('realname,hospital_id,hospital_name hospital,doctor_id,frist_department_name fkname,second_department_name skname')->where(['doctor_id' => $doctor_id]);
            if ($is_primary == 1) {
                $query->andWhere(['=','primary_id',0]);
            }
            $info = $query->asArray()->one();
            if ($info) {
                return $this->returnJson(1, '', $info);
            }
        }
    }

    /**
     * 关联医生
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/26
     */
    public function actionUpdateRelation()
    {
        $request      = \Yii::$app->request;
        $post         = $request->post();
        $tp_doctor_id = trim($request->post('tp_doctor_id', ''));
        $doctor_id    = (int)$request->post('doctor_id', '');
        $tmp_id    = (int)$request->post('tmp_id', '');
        $new_doc_hospital_id    = (int)$request->post('new_doc_hospital_id', 0);
        $new_doc_search_fkid    = (int)$request->post('new_doc_search_fkid', 0);
        $new_doc_search_skid    = (int)$request->post('new_doc_search_skid', 0);
        if (!$doctor_id) {
            return $this->returnJson(2, '医生id不存在');
        }
        $docInfo = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();
        if (!$docInfo) {
            return $this->returnJson(2, '医生信息不存在');
        }
        $info            = TmpDoctorThirdPartyModel::find()->where(['id' => $tmp_id])->one();
        if ($info->doctor_id > 0) {
            return $this->returnJson(2, '该医生已关联过！');
        }
        if (!$new_doc_hospital_id || !$new_doc_search_fkid || !$new_doc_search_skid) {
            return $this->returnJson(2, '请选择医生医院科室信息！');
        }
        if ($info->tp_platform == 6) {
            $tmpRelation = DoctorModel::find()->where(['doctor_id' => $doctor_id, 'tp_platform' => $info->tp_platform])->one();
            if ($tmpRelation) {
                return $this->returnJson(2, "该医生{$doctor_id}已和该平台医生id:{$tmpRelation->tp_doctor_id}关联过！");
            }
        }
        ##查询该来源同一个tb_doctor_id 医生
        $tp_doc_info = TmpDoctorThirdPartyModel::find()->where(['tp_doctor_id' => $info->tp_doctor_id,'tp_platform'=>$info->tp_platform,'doctor_id'=>$doctor_id])->one();
        if ($tp_doc_info) {
            return $this->returnJson(2, '该医生已和id为'.$doctor_id.'的医生关联过,请查看科室建立新的医生关联！');
        }
        ##查询科室信息
        $keshi_filter = [
            'hospital_id'=>$new_doc_hospital_id,
            'frist_department_id'=>$new_doc_search_fkid,
            'second_department_id'=>$new_doc_search_skid,
        ];
        $keshi_relation = HospitalDepartmentRelation::find()->where($keshi_filter)->one();
        if (!$keshi_relation) {
            return $this->returnJson(2, '该医院科室信息不存在了');
        }
        $new_doc_hospital_info = BaseDoctorHospitals::getInfo($new_doc_hospital_id);
        if (!$new_doc_hospital_info) {
            return $this->returnJson(2, '该医院不存在了');
        }
        $new_doc_data = [
            'primary_id'=>$doctor_id,
            'realname'=>$info->realname,
            'tp_platform'=>$info->tp_platform,
            'avatar'=>$docInfo->avatar,
            'source_avatar'=>$info->source_avatar,
            'job_title_id'=>$docInfo->job_title_id,
            'job_title'=>$docInfo->job_title,
            'hospital_id'=>$new_doc_hospital_id,
            'hospital_name'=>$new_doc_hospital_info['name'],
            'frist_department_id'=>$keshi_relation->frist_department_id,
            'frist_department_name'=>$keshi_relation->frist_department_name,
            'second_department_id'=>$keshi_relation->second_department_id,
            'second_department_name'=>$keshi_relation->second_department_name,
            'miao_frist_department_id'=>$keshi_relation->miao_frist_department_id,
            'miao_second_department_id'=>$keshi_relation->miao_second_department_id,
            'tp_hospital_code'=>$info->tp_hospital_code,
            'tp_doctor_id'=>$info->tp_doctor_id,
            'tp_frist_department_id'=>$info->tp_frist_department_id,
            'tp_department_id'=>$info->tp_department_id,
            'status'=>1,
            'is_plus'=>1,
            'weight'=>$docInfo->weight,
            'good_at'=>$info->good_at,
            'profile'=>$info->profile,
        ];
        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            $info->is_relation           = 1;
            if ($info->tp_platform == 6) {
                ##如果有其他设置王氏id的设置为0，并更新此次关联的王氏id
                $miaores = DoctorModel::find()->where(['miao_doctor_id' => $info->tp_doctor_id])->one();
                if ($miaores && $miaores->doctor_id != $docInfo->doctor_id) {
                    $miaores->miao_doctor_id = 0;
                    $miaores->save();
                }
                $docInfo->miao_doctor_id = $info->tp_doctor_id;
                $docInfo->save();
            }
            $new_status = DoctorModel::saveDoctor($new_doc_data);
            if (!$new_status['doctor_id']) {
                throw new Exception('医生关联失败！'.$new_status['msg']);
            }
            $info->doctor_id = $new_status['doctor_id'];
            $info->save();
            $transition->commit();
            CommonFunc::UpdateInfo($doctor_id);
            CommonFunc::updateScheduleCacheByDoctor(['doctor_id'=>$doctor_id]);
            $editContent = $this->userInfo['realname'] . '第三方医生:' . $tp_doctor_id . $info->realname . ' 关联王氏医生 ' . $doctor_id . $docInfo['realname'];
            TbLog::addLog($editContent, '医生关联添加');
            return $this->returnJson(1, '关联成功');
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error 关联失败3');
            return $this->returnJson(2, $msg);
        }
    }

    /**
     * 取消关联关系
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/26
     */
    public function actionCancleRelation()
    {
        $request      = \Yii::$app->request;
        $doctor_id  = $request->get('relation_id', 0);
        try {
            $docInfo = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();
            $primary_id = $docInfo->primary_id;
            $docInfo->primary_id = 0;
            $res = $docInfo->save();
           
            if ($res) {
                //删除挂号相关信息
                // GuahaoScheduleModel::deleteByDoctorId($tp_doctor_id, $relationInfo->doctor_id, $relationInfo->tp_platform);
                CommonFunc::UpdateInfo($doctor_id);
                DoctorModel::updateIsPlus($primary_id);##更新is_plus
                $rp_doc_realname = $docInfo->realname;
                CommonFunc::updateScheduleCacheByDoctor(['doctor_id' => $doctor_id, 'tp_platform' => $docInfo->tp_platform]);
                $editContent = $this->userInfo['realname'] . ' 第三方医生:' . $docInfo->tp_doctor_id . $rp_doc_realname . '取消关联王氏主医生id ' . $primary_id;
                TbLog::addLog($editContent, '医生关联取消');
                return $this->returnJson(1, '取消关联成功');
            }
         } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error 取消关联失败');
            return $this->returnJson(2, '取消关联失败');
        }
        return $this->returnJson(2, '取消关联失败');
    }

}
