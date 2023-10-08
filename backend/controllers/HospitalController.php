<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\DoctorModel;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\models\GuahaoScheduleplaceRelation;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\TbLog;
use common\libs\HashUrl;
use Yii;
use yii\data\ActiveDataProvider;

//小部件数据源类

class HospitalController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size            = 20;

    public function actionIndex()
    {
        return '暂停使用';
        /*$requestParams              = Yii::$app->request->getQueryParams();
        $requestParams['page']      = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit']     = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['name']      = isset($requestParams['name']) ? trim($requestParams['name']) : '';
        $requestParams['level_num'] = isset($requestParams['level_num']) ? trim($requestParams['level_num']) : '';
        $requestParams['type']      = isset($requestParams['type']) ? trim($requestParams['type']) : '';
        $requestParams['kind']      = isset($requestParams['kind']) ? trim($requestParams['kind']) : '';

        // //拼装条件
        $field                        = '*';
        $where                        = [];
        $where['is_hospital_project'] = 1;

        $query = BaseDoctorHospitals::find()->where($where);

        if (!empty(trim($requestParams['level_num']))) {
            $query->andWhere(['level_num' => trim($requestParams['level_num'])]);
        }

        if (!empty(trim($requestParams['type']))) {
            $typeName = BaseDoctorHospitals::$Typelist[$requestParams['type']] ?? '';
            $query->andWhere(['type' => $typeName]);
        }

        if (!empty(trim($requestParams['kind']))) {
            if ($requestParams['kind'] == 4) {
                $query->andWhere(['not in', 'kind', ['公立', '私立', '其他']]);
            } else {
                $kindName = BaseDoctorHospitals::$Kindlist[$requestParams['kind']] ?? '';
                $query->andWhere(['kind' => $kindName]);
            }
        }

        if (!empty(trim($requestParams['name']))) {
            if (is_numeric($requestParams['name'])) {
                $query->andWhere(['id'=>trim($requestParams['name'])]);
            }else{
                $hash_to_id = HashUrl::getIdDecode(trim($requestParams['name']));
                if ($hash_to_id > 0 ) {
                    $query->andWhere(['id'=>$hash_to_id]);
                }else{
                    $query->andWhere(['like', 'name', trim($requestParams['name'])]);
                }
                
            }
            
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $requestParams['limit'],
            ],
            'sort'       => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        $data = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        return $this->render('index', $data);*/
    }

    /**
     * 医院编辑页面
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionEdit()
    {
        $request                    = Yii::$app->request;
        $requestParams              = Yii::$app->request->getQueryParams();
        $requestParams['fkid']      = isset($requestParams['miao_frist_department_id']) ? trim($requestParams['miao_frist_department_id']) : '';
        $requestParams['skid']      = isset($requestParams['miao_second_department_id']) ? trim($requestParams['miao_second_department_id']) : '';
        $requestParams['fkeshi_id'] = isset($requestParams['frist_department_id']) ? trim($requestParams['frist_department_id']) : '';
        $requestParams['skeshi_id'] = isset($requestParams['second_department_id']) ? trim($requestParams['second_department_id']) : '';
        $id                         = $request->get('id', '');
        $data                       = [];
        if (!$id) {
            return $this->_showMessage('id不存在！', Yii::$app->urlManager->createUrl('/hospital/index'));
        }
        //$hospital = BaseDoctorHospitals::find()->where(['id' => $id])->asArray()->one();
        $hospital = BaseDoctorHospitals::getHospitalDetail($id);
        /*$hospital_keshi             = HospitalDepartmentRelation::find()->where(['hospital_id' => $id])->select('id,frist_department_id,frist_department_name,second_department_id,second_department_name,doctors_num,is_recommend')->asArray()->all();
        $hospital['hospital_keshi'] = $hospital_keshi;*/

        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;

        // //拼装条件
        $field = '*';
        $where = [
            'hospital_id' => $id,
        ];

        $query = HospitalDepartmentRelation::find()->where($where);
        if (!empty(trim($requestParams['fkid']))) {
            $query->andWhere(['miao_frist_department_id' => trim($requestParams['fkid'])]);
        }
        if (!empty(trim($requestParams['skid']))) {
            $query->andWhere(['miao_second_department_id' => trim($requestParams['skid'])]);
        }
        if (!empty(trim($requestParams['fkeshi_id']))) {
            $query->andWhere(['frist_department_id' => trim($requestParams['fkeshi_id'])]);
        }
        if (!empty(trim($requestParams['skeshi_id']))) {
            $query->andWhere(['second_department_id' => trim($requestParams['skeshi_id'])]);
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

        $data                     = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        $data['hospital']         = $hospital ?? [];
        $fkeshi_list              = Department::department_platform() ?? [];
        $data['fkeshi_list']      = $fkeshi_list;
        $data['skeshi_list']      = !empty($requestParams['fkeshi_id']) ? CommonFunc::get_all_skeshi_list($requestParams['fkeshi_id']) : [];
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();
        $data['miao_skeshi_list'] = CommonFunc::getSkeshiInfos();
        return $this->render('edit', $data);
    }

    public function actionAdd()
    {
        $request       = Yii::$app->request;
        $requestParams = Yii::$app->request->getQueryParams();
        $id            = $request->get('hospital_id', '');
        $data          = [];
        /*if (!$id) {
        return $this->_showMessage('id不存在！', Yii::$app->urlManager->createUrl('/hospital/index'));
        }*/
        //$hospital = BaseDoctorHospitals::find()->where(['id' => $id])->asArray()->one();
        $hospital = BaseDoctorHospitals::getHospitalDetail($id);

        $hospital_keshi             = HospitalDepartmentRelation::find()->where(['hospital_id' => $id])->select('id,frist_department_id,frist_department_name,second_department_id,second_department_name,doctors_num,is_recommend')->asArray()->all();
        $hospital['hospital_keshi'] = $hospital_keshi;

        $data                     = ['params' => ['requestParams' => $requestParams], 'requestParams' => $requestParams];
        $data['hospital']         = $hospital ?? [];
        $fkeshi_list              = Department::department_platform() ?? [];
        $data['fkeshi_list']      = $fkeshi_list;
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();
        return $this->render('add', $data);
    }
    
    /**
     * 保存医院科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSave()
    {
        $request  = Yii::$app->request;
        $postData = $request->post();

        $hospital_id = $postData['hospital_id'] ?? 0;
        $data        = [];
        if (!$hospital_id) {
            return $this->returnJson(0, 'id不存在！!');
        }
        $hasData = HospitalDepartmentRelation::find()->where(['hospital_id' => $hospital_id, 'frist_department_id' => $postData['frist_department_id'], 'second_department_id' => $postData['second_department_id']])->one();
        if ($hasData) {
            return $this->returnJson(0, '该医院下当前科室已存在！');
        }
        $status      = false;
        $msg         = '';
        $tp_platform = 0;
        $guahaoInfo  = GuahaoHospitalModel::find()->select('hospital_id,tp_platform')->where(['hospital_id' => $hospital_id])->one();
        if ($guahaoInfo) {
            $tp_platform = $guahaoInfo->tp_platform;
        }

        try {
            $address = isset($postData['address']) ? $postData['address'] : "";
            $hospitalDepartmentModel              = new HospitalDepartmentRelation();
            $hospitalDepartmentModel->hospital_id = $hospital_id;
            //$hospitalDepartmentModel->tp_platform               = $tp_platform ?? 0;
            $hospitalDepartmentModel->frist_department_id       = (int) $postData['frist_department_id'];
            $hospitalDepartmentModel->second_department_id      = (int) $postData['second_department_id'];
            $hospitalDepartmentModel->frist_department_name     = trim($postData['frist_department_name']);
            $hospitalDepartmentModel->second_department_name    = trim($postData['second_department_name']);
            $hospitalDepartmentModel->miao_frist_department_id  = (int) $postData['miao_frist_department_id'];
            $hospitalDepartmentModel->miao_second_department_id = (int) $postData['miao_second_department_id'];
            $hospitalDepartmentModel->address                   = $address;
            $hospitalDepartmentModel->doctors_num               = 0;
            $hospitalDepartmentModel->related_disease           = '1';
            $hospitalDepartmentModel->is_recommend              = 0;
            $hospitalDepartmentModel->status                    = 1;
            $hospitalDepartmentModel->create_time               = time();
            $hospitalDepartmentModel->admin_id                  = $this->userInfo['id'];
            $hospitalDepartmentModel->admin_name                = $this->userInfo['realname'];
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
            $editContent  = $this->userInfo['realname'] . '添加了医院:' . $hospitalInfo['name'] . ' 一级科室为:' . $postData['frist_department_name'] . ' 二级科室为:' . $postData['second_department_name'] . '的科室' . ' 科室地址为:' . $address;
            TbLog::addLog($editContent, '医院科室添加');
            return $this->returnJson(1, '操作成功!');
        } else {
            return $this->returnJson(0, !empty($msg) ? $msg : '保存失败!');
        }

    }

    /**
     * 删除医院科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDelKeshi()
    {
        $request  = Yii::$app->request;
        $postData = $request->post();
        $id       = $postData['id'] ?? 0;
        $data     = [];
        if (!$id) {
            return $this->returnJson(0, 'id不存在！!');
        }
        $status        = false;
        $hasData       = HospitalDepartmentRelation::find()->where(['id' => $id])->one();
        $keshiRelation = TbDepartmentThirdPartyRelationModel::find()->where(['hospital_department_id' => $id])->asArray()->all();
        if (!$hasData) {
            return $this->returnJson(0, '科室不存在或者已经删除！');
        }

        // if ($hasData->doctors_num > 0) {
        //     return $this->returnJson(0, '科室下存在关联医生暂不能删除！');
        // }
        if ($keshiRelation) {
            return $this->returnJson(0, '科室下存在关联科室暂不能删除！');
        }
        $scheduleplaceRelation = GuahaoScheduleplaceRelation::find()->select('id')->where(['hospital_department_id'=>$id])->one();
        if ($scheduleplaceRelation) {
            return $this->returnJson(0, '科室下存在关联出诊机构暂不能删除！');
        }
        ##查看科室下的医生数
        $doclist = DoctorModel::find()->where(['hospital_id'=>$hasData['hospital_id']])
            ->andWhere(['frist_department_id'=>$hasData['frist_department_id']])
            ->andWhere(['second_department_id'=>$hasData['second_department_id']])
            ->andWhere(['<=', 'status', 10])->count();
        if ($doclist > 0) {
            return $this->returnJson(0, "科室下存在{$doclist}个医生不能删除！");
        }
        
        //$hospitalInfo = BaseDoctorHospitals::find()->select('name')->where(['id' => $hasData->hospital_id])->asArray()->one();
        $hospitalInfo = BaseDoctorHospitals::getHospitalDetail($hasData->hospital_id);

        $editContent  = $this->userInfo['realname'] . '删除了医院:' . $hospitalInfo['name'] . ' 一级科室为:' . $hasData->frist_department_name . ' 二级科室为:' . $hasData->second_department_name . '的科室';
        $status       = $hasData->delete();
        
        TbLog::addLog($editContent, '医院科室删除');
        if ($status) {
            return $this->returnJson(1, '操作成功!');
        } else {
            return $this->returnJson(0, '操作失败!');
        }
    }

}
