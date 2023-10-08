<?php

namespace backend\controllers;

use backend\controllers\UploadController;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use common\models\BuildToEsModel;
use common\models\Department;
use common\models\DoctorInfoModel;
use common\models\DoctorModel;
use common\models\minying\MinDoctorModel;
use common\models\minying\MinHospitalModel;
use queues\upDoctorScheduleJob;
use common\models\GuahaoScheduleplace;
use common\models\GuahaoScheduleplaceRelation;
use common\models\HospitalDepartmentRelation;
use common\models\TbLog;
use common\models\TmpDoctorThirdPartyModel;
use common\sdks\CenterSDK;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\Response;
use \yii\helpers\ArrayHelper;

class DoctorController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size            = 10;

    public function actionDocList()
    {
        $requestParams                = Yii::$app->request->getQueryParams();
        $requestParams['tp_platform'] = isset($requestParams['tp_platform']) ? intval($requestParams['tp_platform']) : '';
        $requestParams['miao_doctor_id'] = isset($requestParams['miao_doctor_id']) ? intval($requestParams['miao_doctor_id']) : '';
        $requestParams['fkid'] = isset($requestParams['fkid']) ? intval($requestParams['fkid']) : '';
        $requestParams['skid'] = isset($requestParams['skid']) ? intval($requestParams['skid']) : '';
        $requestParams['title_id'] = isset($requestParams['title_id']) ? intval($requestParams['title_id']) : '';
        $requestParams['is_nisiya'] = isset($requestParams['is_nisiya']) ? intval($requestParams['is_nisiya']) : '';
        $requestParams['is_plus'] = isset($requestParams['is_plus']) ? intval($requestParams['is_plus']) : '';
        $requestParams['doc_primary'] = isset($requestParams['doc_primary']) ? intval($requestParams['doc_primary']) : '';
        $requestParams['primary_id'] = isset($requestParams['primary_id']) ? intval($requestParams['primary_id']) : '';

        if (isset($requestParams['doc_type']) && $requestParams['doc_type'] == 'doctor_hash_id') {
            $requestParams['doctor'] = HashUrl::getIdDecode(trim($requestParams['doctor']));
        }
        //获取一级科室信息
        $fkeshiInfo = Department::department_platform() ?? [];
        //获取全部二级科室信息
        if (!empty($requestParams['fkid'])) {
            // $skeshiInfo = CommonFunc::getKeshiInfo($requestParams['fkid']);
            $skeshiInfo = CommonFunc::get_all_skeshi_list($requestParams['fkid']);
        } else {
            $skeshiInfo = [];
        }

        $doctorTitles = CommonFunc::getTitle();
        if (isset($requestParams['hospital_id'])) {
            $hos = BaseDoctorHospitals::getInfo($requestParams['hospital_id']);
        }
        //分页
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['power_create_time'] = (isset($requestParams['power_create_time']) && (!empty($requestParams['power_create_time']))) ? $requestParams['power_create_time'] : '';

        //时间格式验证
        if (!empty($requestParams['power_create_time'])) {
            $pages = new Pagination(['totalCount' => 0, 'pageSize' => $requestParams['limit']]);
            $data       = [
                'hos' => $hos ?? [],
                'fkeshiInfo' => $fkeshiInfo,
                'skeshiInfo' => $skeshiInfo,
                'dataProvider' => [],
                'doctor_titles' => $doctorTitles,
                'requestParams' => $requestParams,
                'totalCount' => 0,
                'pages' => $pages,
                'minDoctorList' => []
            ];
            if (strripos($requestParams['power_create_time'], " - ") !== false) {
                list($stime, $etime) = explode(' - ', $requestParams['power_create_time']);
                if (!(CommonFunc::checkDate($stime) && CommonFunc::checkDate($etime))) {
                    return $this->render('list', $data);
                }
            } else {
                return $this->render('list', $data);
            }
        }
        $list = DoctorModel::getList($requestParams);
        // 民营医院id
        $minDoctorIds = [];
        foreach ($list as &$item) {
            $item['realname'] = str_replace(' ', '&nbsp;', $item['realname']);
            if ($item['tp_platform'] == 13) {
                $minDoctorIds[] = $item['tp_doctor_id'];
            }
        }
        // 民营医院列表信息
        $minDoctorList = MinDoctorModel::find()
            ->where(['min_doctor_id' => array_unique($minDoctorIds)])
            ->select('min_doctor_id,visit_type')
            ->indexBy('min_doctor_id')
            ->asArray()
            ->all();

        $totalCount = DoctorModel::getCount($requestParams);
        $pages      = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data       = [
            'hos' => $hos ?? [],
            'fkeshiInfo' => $fkeshiInfo,
            'skeshiInfo' => $skeshiInfo,
            'dataProvider' => $list,
            'doctor_titles' => $doctorTitles,
            'requestParams' => $requestParams,
            'totalCount' => $totalCount,
            'pages' => $pages,
            'minDoctorList' => $minDoctorList
        ];
        return $this->render('list', $data);
    }

    /**
     * 合并关联操作医生
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-15
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionMergeDoctor()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            $post              = $request->post();
            $doc_ids           = $post['doc_ids'] ?? [];
            $primary_doctor_id = $post['primary_doctor_id'] ?? 0;
            if (!$primary_doctor_id && !$doc_ids) {
                return $this->returnJson(2, '没有要操作的医生或者没有选择主医生！');
            }

            $primary_doctor_model  = DoctorModel::find()->where(['doctor_id' => $primary_doctor_id])->one();
            if ($primary_doctor_model->primary_id > 0) {
                return $this->returnJson(2, '只能关联到主医生信息！此医生存在主键医生:'.$primary_doctor_model->primary_id);
            }
            if ($primary_doctor_model->status != 1) {
                return $this->returnJson(2, '医生被禁用了不能关联！:'.$primary_doctor_model->primary_id);
            }

            $msg     = '';
            $suc_ids = [];
            foreach ($doc_ids as $key => $doctor_id) {
                if ($doctor_id == $primary_doctor_id) {
                    // $msg .= "doctor_id:{$doctor_id}不能设置自己为主医生,设置主键失败！";
                    continue;
                }
                try {
                    $doctor_model  = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();
                    $doc_child_num = DoctorModel::find()->where(['primary_id' => $doctor_id])->count();
                    if ($doc_child_num > 0) {
                        $msg .= "doctor_id:{$doctor_id}包含{$doc_child_num}个子医生,设置主键失败！";
                        continue;
                    }
                    if ($doctor_model->primary_id > 0) {
                        $msg .= "doctor_id:{$doctor_id}已关联{$doctor_model->primary_id}医生,设置主键失败！";
                        continue;
                    }
                    if ($doctor_model->status != 1) {
                        $msg .= "doctor_id:{$doctor_id}被禁用,设置主键失败！";
                        continue;
                    }
                    if ($doctor_model->miao_doctor_id > 0) {
                        $msg .= "doctor_id:{$doctor_id}已关联王氏id{$doctor_model->miao_doctor_id}医生,设置主键失败！";
                        continue;
                    }

                    if ($doctor_model && ($doctor_id != $primary_doctor_id)) {
                        $doctor_model->primary_id = $primary_doctor_id;
                        $res                      = $doctor_model->save();
                        if ($res) {
                            $suc_ids[] = $doctor_id;
                        } else {
                            throw new \Exception(json_encode($doctor_model->getErrors(), JSON_UNESCAPED_UNICODE));

                        }
                    } else {
                        $msg .= "doctor_id:{$doctor_id}设置主键医生{$primary_doctor_id}失败！";
                    }
                } catch (\Exception $e) {
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 设置主键医生失败');
                    $msg .= "doctor_id:{$doctor_id}设置主键医生{$primary_doctor_id}失败！".$msg;
                }
            }
            if ($suc_ids) {
                DoctorModel::updateIsPlus($primary_doctor_id);##更新is_plus
                $editContent = $this->userInfo['realname'] . "关联了医生id{" . implode(',', $suc_ids) . "}的主键医生:{$primary_doctor_id}";
                TbLog::addLog($editContent, '医生关联主键');
                CommonFunc::updateScheduleCacheByDoctor(['doctor_id'=>$primary_doctor_id]);
            }

            if ($msg) {
                return $this->returnJson(2, $msg);
            } else {
                return $this->returnJson(1, '操作成功');
            }

        }
    }

    public function actionAjaxKeshi()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $hosid                       = (int) $request->get('hosid');
            $result                      = HospitalDepartmentRelation::hospitalDepartment($hosid);
            return ['code' => 200, 'keshi' => $result];
        }
    }

    /**
     * 异步获取二级科室信息
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2018-08-20
     */
    public function actionAjaxSkeshi()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $pid                         = $request->get('pid');
            $hosid                       = $request->get('hosid');
            $result                      = HospitalDepartmentRelation::hospitalDepartment($hosid)[$pid] ?? [];
            return ['code' => 200, 'skeshi' => $result];
        }
    }

    public function actionAjaxSkeshiList()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $pid                         = $request->get('pid');
            $result                      = CommonFunc::getKeshiInfo($pid);
            return ['code' => 200, 'skeshi' => $result];
        }
    }

    /**
     * 异步获取医院信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/4
     */
    public function actionAjaxHos()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $name                        = trim($request->get('name'));
            $result                      = BaseDoctorHospitals::getListByName($name);
            return ['code' => 200, 'hos' => $result];
        }
    }

    /**
     * 编辑或添加医生信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/4
     */
    public function actionAdd()
    {
        $request = Yii::$app->request;
        //获取医生信息
        $id           = $request->get('doctor_id');
        $tp_doctor_id = $request->get('tp_doctor_id');
        $tp_platform  = $request->get('tp_platform', 0);
        $tmp_id       = $request->get('tmp_id', 0);

        $fkeshi       = [];
        $skeshi       = [];
        $doctorTitles = CommonFunc::getTitle();
        if ($id) {
            $info = DoctorModel::find()->where(['doctor_id' => $id])->asArray()->one();
            if (!$info) {
                return $this->returnJson(2, '没有医生信息！');
            }
            $doc_info = DoctorModel::getDcotorInfoItem($id);
            $info     = array_merge($info, $doc_info);
            $relation_fields = 'realname,doctor_id,tp_doctor_id,tp_platform,hospital_name,frist_department_name,second_department_name,create_time,tp_department_id,tp_hospital_code';
            if ($info['primary_id'] == 0) {
                $relationInfo = DoctorModel::find()->select($relation_fields)->where(['primary_id' => $id])->asArray()->all();
            } else {
                $relationInfo = DoctorModel::find()->select($relation_fields)->where(['doctor_id' => $info['primary_id']])->asArray()->all();
            }

            if ($relationInfo) {
                foreach ($relationInfo as $k => &$v) {
                    $v['tp_platform_name'] = $this->platform[$v['tp_platform']] ?? '';
                }
            }

            $info['good_at'] = CommonFunc::filterContent($info['good_at']);
            $info['profile'] = CommonFunc::filterContent($info['profile']);
            if ($info['hospital_id']) {
                $fkeshi = HospitalDepartmentRelation::hospitalDepartment($info['hospital_id']);
                $skeshi = HospitalDepartmentRelation::hospitalDepartment($info['hospital_id'])[$info['frist_department_id']] ?? [];
            }
            $view_name = 'info';
            if ($info['primary_id'] > 0) {
                // $view_name = 'docker_item_info';
            }
            $info['tp_platform_name'] = $this->platform[$info['tp_platform']] ?? '';
            return $this->render($view_name, [
                'info'          => $info,
                'id'            => $id,
                'model'         => $info,
                'fkeshiInfo'    => $fkeshi,
                'skeshiInfo'    => $skeshi,
                'imagePath'     => isset(\Yii::$app->params['avatarUrl']) ? \Yii::$app->params['avatarUrl'] : "",
                'hospital'      => BaseDoctorHospitals::getList($info['hospital_id']),
                'doctor_titles' => $doctorTitles,
                'relationInfo' => $relationInfo,
            ]);
        } else {
            $relationInfo = [];

            return $this->render('info', [
                'info'          => [],
                'relationInfo'  => $relationInfo ?? [],
                'id'            => 0,
                'pages'         => '',
                'count'         => '',
                'model'         => '',
                'fkeshiInfo'    => '',
                'skeshiInfo'    => '',
                'imagePath'     => isset(\Yii::$app->params['avatarUrl']) ? \Yii::$app->params['avatarUrl'] : "",
                'hospital'      => '',
                'doctor_titles' => $doctorTitles,
            ]);
        }
    }

    public function actionEditWeight()
    {
        $id     = intval($_GET['doctor_id']);
        $weight = intval($_GET['weight']);
        $res    = DoctorModel::find()->where(['doctor_id' => $id])->one();
        if ($res) {
            $res->weight = $weight;
            $res->save();
            return $this->returnJson(1, '保存成功');
        }

    }

    /**
     * TODO 当前控制器没有在用
     * actionEditMid
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/20
     *
     */
    public function actionEditMid()
    {
        $id             = intval($_GET['doctor_id']);
        $miao_doctor_id = intval($_GET['miao_doctor_id']);
        $redis          = Yii::$app->redis_codis;
        $key            = 'vapi:doctor_info:' . $miao_doctor_id;
        $docInfo        = $redis->get($key);
        if (!$docInfo && $miao_doctor_id > 0) {
            return $this->returnJson('2', '要关联的王氏id不存在！');
        }
        if ($docInfo) {
            $res = DoctorModel::find()->where(['miao_doctor_id' => $miao_doctor_id])->one();
            if ($res) {
                return $this->returnJson('2', '已存在');
            } else {
                $res = DoctorModel::find()->where(['doctor_id' => $id])->one();
                if ($res->primary_id > 0) {
                    return $this->returnJson('2', '只能允许主医生关联王氏ID');
                }
                ##查询是否有关联王氏加号
                $old_miaoid = $res->miao_doctor_id;
                if ($old_miaoid > 0) {
                    $reDocInfo = DoctorModel::find()->where(['tp_doctor_id' => $old_miaoid, 'doctor_id' => $id, 'tp_platform' => 6])->one();
                    if ($reDocInfo) {
                        return $this->returnJson('2', '请取消关联王氏加号医生在修改！');
                    }
                }
                if ($docInfo) {
                    $res->miao_doctor_id = $miao_doctor_id;
                }
                $save_status = $res->save();
                $err         = $res->getErrors();
                if ($err && $err['job_title']) {
                    return $this->returnJson('2', '请补充职称');
                }
                if ($err) {
                    return $this->returnJson('2', array_shift($res->getFirstErrors()));
                }
                if ($save_status) {
                    $logInfo            = [];
                    $old_miao_doctor_id = $res->getOldAttribute('miao_doctor_id') ?? 0;
                    if ($old_miao_doctor_id != $miao_doctor_id) {
                        $logInfo[] = ["王氏id", $old_miao_doctor_id, $miao_doctor_id];
                    }
                    if ($logInfo) {
                        $editContent = $this->userInfo['realname'] . '修改了id为' . $id . '的医生:';
                        $editContent .= $this->formatLog($logInfo);
                        TbLog::addLog($editContent, '医生修改');
                    }

                }
            }
            $is_hospital_project = ($miao_doctor_id == 0) ? 0 : 1;
            $ress                = CenterSDK::getInstance()->updateuser(['doctor_id' => $miao_doctor_id, 'params' => json_encode(['is_hospital_project' => $is_hospital_project])]);
            return $this->returnJson(1, '保存成功');
        } else {
            $res                 = DoctorModel::find()->where(['doctor_id' => $id])->one();
            $is_hospital_project = 0;
            $ress                = CenterSDK::getInstance()->updateuser(['doctor_id' => $res['miao_doctor_id'], 'params' => json_encode(['is_hospital_project' => $is_hospital_project])]);
            if ($res) {
                $res->miao_doctor_id = 0;
                $res->is_plus        = 0;
                $res->save();
            }
            return $this->returnJson(2, '没有医生信息！');
        }

    }

    /**
     * actionSave
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/4
     */
    public function actionSave()
    {
        $request       = Yii::$app->request;
        $requestParams = $request->post();

        $miao_doctor_id                  = isset($requestParams['miao_doctor_id']) ? (int) $requestParams['miao_doctor_id'] : 0;
        $tp_doctor_id                    = trim($request->get('tp_doctor_id', 0));
        $force_add                       = (int)($request->post('force_add', 0));
        $tmp_id                          = (int) $request->get('tmp_id', 0);
        $requestParams['miao_doctor_id'] = $miao_doctor_id;
        $requestParams['tp_doctor_id']   = $tp_doctor_id;
        $requestParams['tmp_id']         = $tmp_id;
        if (!$requestParams['frist_department_id'] || !$requestParams['second_department_id']) {
            return $this->returnJson(0, '科室不能为空！');
        }
        if (!$requestParams['hospital_id']) {
            return $this->returnJson(0, '医院不能为空！');
        }

        if (
            (!empty($requestParams['realname']) && CommonFunc::checkXss($requestParams['realname'])) ||
            (!empty($requestParams['good_at']) && CommonFunc::checkXss($requestParams['good_at'])) ||
            (!empty($requestParams['profile']) && CommonFunc::checkXss($requestParams['profile']))
        ) {
            return $this->returnJson(0, '输入框中不能含有非法脚本！');
        }

        $department_name = HospitalDepartmentRelation::getKeshi($requestParams['hospital_id'], $requestParams['frist_department_id'], $requestParams['second_department_id']);
        if ($department_name) {
            $requestParams['frist_department_name']     = current($department_name)['frist_department_name'];
            $requestParams['second_department_name']    = current($department_name)['second_department_name'];
            $requestParams['miao_frist_department_id']  = current($department_name)['miao_frist_department_id'];
            $requestParams['miao_second_department_id'] = current($department_name)['miao_second_department_id'];
        } else {
            $requestParams['frist_department_name']  = CommonFunc::getKeshiInfo($requestParams['frist_department_id'])['department_name'] ?? '';
            $requestParams['second_department_name'] = CommonFunc::getKeshiInfo($requestParams['second_department_id'])['department_name'] ?? '';
        }
        $hospitalData = HospitalDepartmentRelation::find()->where([
            'hospital_id'          => $requestParams['hospital_id'],
            'frist_department_id'  => $requestParams['frist_department_id'],
            'second_department_id' => $requestParams['second_department_id'],
        ])->one();
        if (!$hospitalData) {
            return $this->returnJson(0, '科室已被删除');
        }
        $doctorTitles               = CommonFunc::getTitle();
        $requestParams['job_title'] = $doctorTitles[$requestParams['job_title_id']] ?? '';
        //第三方医生头像
        $requestParams['source_avatar'] = CommonFunc::filterSourceAvatar($requestParams['source_avatar']);
        if (empty($requestParams['avatar']) && !empty($requestParams['source_avatar'])) {
            //上传到oss lyw 2021-09-07
            $img = CommonFunc::uploadImageOssByUrl($requestParams['source_avatar']);
            //存储头像路径
            if (!empty($img['img_path'])) {
                $requestParams['avatar'] = $img['img_path'];
            }else{
                $requestParams['avatar'] = '';
            }
        }
        $requestParams['avatar']= trim($requestParams['avatar'],"/");
        unset($requestParams['_csrf-backend']);
        unset($requestParams['file']);
        try {

            if (!$request->get('doctor_id')) {
                $docInfo = DoctorModel::find()->where([
                    'realname'             => $requestParams['realname'],
                    // 'job_title_id'         => $requestParams['job_title_id'],
                    'hospital_id'          => $requestParams['hospital_id'],
                    'frist_department_id'  => $requestParams['frist_department_id'],
                    'second_department_id' => $requestParams['second_department_id'],
                ])->one();
                if ($docInfo && ($force_add != 1)) {
                    return $this->returnJson(202, '该医生已存在！要继续添加重名医生吗！');
                }

                if ($miao_doctor_id) {
                    $miaores = DoctorModel::find()->where(['miao_doctor_id' => $miao_doctor_id])->one();
                    if ($miaores) {
                        return $this->returnJson(0, '该王氏医生已关联医生' . $miaores->doctor_id);
                    }
                }

                $inc_docid = 0;
                $doc_item_temp = DoctorModel::saveDoctor($requestParams);
                if (!$doc_item_temp['doctor_id']) {
                    throw new Exception('医生创建失败！' . $doc_item_temp['msg']);
                }
                if ($doc_item_temp['doctor_id']) {
                    $inc_docid = $doc_item_temp['doctor_id'];
                }
                $is_hospital_project = ($miao_doctor_id == 0) ? 0 : 1;
                $ress                = CenterSDK::getInstance()->updateuser(['doctor_id' => $miao_doctor_id, 'params' => json_encode(['is_hospital_project' => $is_hospital_project])]);

            } else {
                //编辑
                $doc = DoctorModel::find()->select('doctor_id,primary_id')->where(['doctor_id' => $request->get('doctor_id', 0)])->one();
                if ($doc) {
                    $requestParams['doctor_id'] = $doc->doctor_id;
                    unset($requestParams['tp_doctor_id'], $requestParams['miao_doctor_id']);
                    if ($doc->primary_id > 0 ) {
                        // return $this->saveChildDoctor($requestParams);
                    }
                    $doc_item_temp = DoctorModel::saveDoctor($requestParams);
                    if (!$doc_item_temp['doctor_id']) {
                        throw new Exception('医生信息保存失败！' . $doc_item_temp['msg']);
                    }
                }
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            return $this->returnJson('2', $msg);
        }

        return $this->returnJson(1, '操作成功！');
    }

    /**
     * 保存子医生信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-22
     * @version v1.0
     * @param   [type]     $requestParams [description]
     * @return  [type]                    [description]
     */
    public function saveChildDoctor($requestParams)
    {
        $params = [
            'hospital_id'=>$requestParams['hospital_id'],
            'frist_department_id'=>$requestParams['frist_department_id'],
            'second_department_id'=>$requestParams['second_department_id'],
            'frist_department_name'=>$requestParams['frist_department_name'],
            'second_department_name'=>$requestParams['second_department_name'],
            'miao_frist_department_id'=>$requestParams['miao_frist_department_id'],
            'miao_second_department_id'=>$requestParams['miao_second_department_id'],
            'doctor_id'=>$requestParams['doctor_id'],
        ];
        $doc_item_temp = DoctorModel::saveDoctor($params);
        if (!$doc_item_temp['doctor_id']) {
            throw new Exception('医生信息保存失败！' . $doc_item_temp['msg']);
        }
        return $this->returnJson(1, '操作成功！');

    }

    public function formatLog($log_data = [])
    {
        $result     = '';
        $log_format = "修改 {%s} 由 {%s} 修改为 {%s}，";
        if (!empty($log_data) && is_array($log_data)) {
            foreach ($log_data as $item) {
                $result .= sprintf($log_format, $item[0], $item[1], $item[2]);
            }
            $result = rtrim($result, '，');
        }

        return $result;
    }

    /**
     * TODO 当前控制器没有在用
     * 获取王氏医生信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/17
     */
    public function actionInfo()
    {
        $request = Yii::$app->request;
        //获取医生信息
        $miaoid = (int) $request->get('miaoid', 0);
        if (!$miaoid) {
            return $this->returnJson('2', '医生id不能为空！');
        }
        if ($miaoid) {
            $miaores = DoctorModel::find()->where(['miao_doctor_id' => $miaoid])->one();
            if ($miaores) {
                return $this->returnJson('2', $miaoid . '医生已关联，不允许关联');
            }
        }
        $docid   = $request->get('doc_id', 0);
        $redis   = Yii::$app->redis_codis;
        $key     = 'vapi:doctor_info:' . $miaoid;
        $docInfo = $redis->get($key);
        //37,38,39,63,64,65]['主任医师','副主任医师','主治医师','主任药师','副主任药师','主管药师
        $titleArr = [37 => 1, 38 => 6, 39 => 3, 63 => 16, 64 => 18, 65 => 17];
        if ($docInfo) {
            $avatar              = json_decode($docInfo, true)['avatar'] ?? '';
            $img                 = CommonFunc::uploadImageOssByUrl($avatar); // 修改 oss 上传图片 lyw 2021-08-31
            $img['img_url']      = $img['img_url'] . '?v=' . time();
            $docInfo             = json_decode($docInfo, true);
            $docInfo['title_id'] = $titleArr[$docInfo['title_id']] ?? '99';
            $jsonInfo            = array_merge($docInfo, $img);
            return $this->returnJson(1, '成功！', $jsonInfo);
        }
        return $this->returnJson('2', '该医生不存在');
    }

    public function actionChangeStatus()
    {
        $doctor_id = intval($_GET['id']);
        $status    = $_GET['status'];
        $res       = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();

        if ($res) {
            $res->status = $status;
            $res->save();
            if ($res) {
                //增加日志
                if ($status == 1) {
                    $logInfo[] = ["医生状态", '禁用', '启用'];
                    //更新医生缓存
                    DoctorModel::getInfo($doctor_id, 1);
                    $model = new BuildToEsModel();
                    $model->db2esByIdDoctor($doctor_id);
                } else {
                    $logInfo[] = ["医生状态", '启用', '禁用'];
                }
                if ($logInfo) {
                    $editContent = $this->userInfo['realname'] . '修改了id为' . $doctor_id . '的医生:';
                    $editContent .= $this->formatLog($logInfo);
                    TbLog::addLog($editContent, '医生修改');
                }
                return $this->returnJson('1', '成功');
            }
        } else {
            return $this->returnJson('2', '医生信息不存在！');
        }
    }

    /**
     * 医生多点执业列表
     * @return string
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/9
     */
    public function actionDocScheduleplace()
    {
        $requestParams = Yii::$app->request->getQueryParams();

        //分页
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;

        $requestParams['doctor_id']          = (isset($requestParams['doctor_id']) && !empty($requestParams['doctor_id'])) ? $requestParams['doctor_id'] : '';
        $requestParams['realname']           = (isset($requestParams['realname']) && !empty($requestParams['realname'])) ? $requestParams['realname'] : '';
        $requestParams['scheduleplace_name'] = (isset($requestParams['scheduleplace_name']) && !empty($requestParams['scheduleplace_name'])) ? $requestParams['scheduleplace_name'] : '';
        $requestParams['status']             = (isset($requestParams['status'])) ? $requestParams['status'] : '';

        // //拼装条件
        $field = ['a.*', 'b.hospital_id', 'b.hospital_name', 'c.frist_department_name', 'c.second_department_name','d.primary_id'];
        $where = [];
        if (isset($requestParams['tp_platform']) && $requestParams['tp_platform'] > 0) {
            $where['a.tp_platform'] = $requestParams['tp_platform'];
        }
        $query = GuahaoScheduleplaceRelation::find()->alias('a')->select($field)->leftJoin(GuahaoScheduleplace::tableName() . ' b', '`a`.`scheduleplace_id` = `b`.`scheduleplace_id`')->leftJoin(HospitalDepartmentRelation::tableName() . ' c', '`a`.`hospital_department_id` = `c`.`id`')->leftJoin(DoctorModel::tableName() . ' d', '`a`.`doctor_id` = `d`.`doctor_id`')->where($where);

        // $query = GuahaoScheduleplace::find();
        if (!empty($requestParams['doctor_id'])) {
            $query->andWhere(['a.doctor_id' => trim($requestParams['doctor_id'])]);
        }

        if (!empty($requestParams['realname'])) {
            $query->andWhere(['like', 'a.realname', trim($requestParams['realname'])]);
        }
        if ($requestParams['status'] !== '') {
            $query->andWhere(['a.status' => trim($requestParams['status'])]);
        }

        if (!empty($requestParams['scheduleplace_name'])) {
            $query->andWhere(['like', 'a.scheduleplace_name', trim($requestParams['scheduleplace_name'])]);
        }

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj    = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => $requestParams['limit'],
        ]);
        $pageObj->setPage($requestParams['page'] - 1, false);
        $list = $query->orderBy(['create_time' => SORT_DESC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
        $data = ['dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pageObj];
        return $this->render('scheduleplace', $data);
    }

    /**
     * 医生多点执业列表
     * @return string
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/9
     */
    public function actionDocScheduleplaceRelation()
    {
        $requestParams = Yii::$app->request->getQueryParams();

        //分页
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;

        $requestParams['scheduleplace_id'] = (isset($requestParams['scheduleplace_id']) && !empty($requestParams['scheduleplace_id'])) ? $requestParams['scheduleplace_id'] : '';

        $query = GuahaoScheduleplaceRelation::find()->where(['scheduleplace_id' => trim($requestParams['scheduleplace_id'])]);

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj    = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => $requestParams['limit'],
        ]);
        $pageObj->setPage($requestParams['page'] - 1, false);
        $list = $query->orderBy(['create_time' => SORT_DESC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();

        $data = ['dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pageObj, 'platform' => $this->platform];
        return $this->renderPartial('scheduleplace_relation', $data);
    }

    /**
     * 增加医生多点执业
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-11
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionAddScheduleplace()
    {
        $request = Yii::$app->request;
        $data    = [];
        return $this->render('add_scheduleplace', $data);
    }

    /**
     * 编辑出诊机构
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-18
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionEditScheduleplace()
    {
        $request  = Yii::$app->request;
        $id       = (int) $request->get('id', 0);
        $back_url = Url::to(['doctor/doc-scheduleplace']);
        if (!$id) {
            die('该出诊地不存在！');
        }
        // //拼装条件
        $field = ['a.*', 'b.hospital_id', 'b.hospital_name'];
        $where = ['a.id' => $id];
        $data  = GuahaoScheduleplaceRelation::find()->alias('a')->select($field)->leftJoin(GuahaoScheduleplace::tableName() . ' b', '`a`.`scheduleplace_id` = `b`.`scheduleplace_id`')->where($where)->asArray()->one();
        if (!$data || $data['status'] != 1) {
            die('该出诊地审核状态错误！');
        }
        $data['fkeshi_list'] = HospitalDepartmentRelation::hospitalDepartment($data['hospital_id']) ?? [];
        $data['skeshi_list'] = [];
        // echo "<pre>";print_r($data);die();
        return $this->renderPartial('edit_scheduleplace', $data);
    }

    /**
     * 更新编辑出诊地
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-18
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionUpScheduleplace()
    {
        $request                = Yii::$app->request;
        $requestPost            = $request->post();
        $id                     = (int) ArrayHelper::getValue($requestPost, 'id', 0);
        $hospital_department_id = (int) ArrayHelper::getValue($requestPost, 'hospital_department_id', 0);
        if (!$id || !$hospital_department_id) {
            return $this->returnJson('2', '科室或者关联ID不能为空！');
        }
        $hospitalData = HospitalDepartmentRelation::find()->where(['id' => $hospital_department_id])->one();
        if (!$hospitalData) {
            return $this->returnJson(0, '科室已被删除');
        }
        $relationModel = GuahaoScheduleplaceRelation::find()->where(['id' => $id])->one();
        if (!$relationModel || $relationModel['status'] != 1) {
            return $this->returnJson('2', '该出诊地审核状态错误！');
        }
        if ($relationModel['hospital_department_id'] > 0) {
            return $this->returnJson('2', '该出诊地已经关联过科室！');
        }
        ##如果存在次出诊地医生，直接更新设置医生ID
        $has_doc_info = DoctorModel::find()->where(['tp_doctor_id'=>$relationModel['tp_doctor_id'],'tp_platform'=>$relationModel['tp_platform'],'hospital_id'=>$relationModel['tp_scheduleplace_id']])->asArray()->one();
        if ($has_doc_info) {
            $relationModel->hospital_department_id = $hospital_department_id;
            $relationModel->doctor_id = $has_doc_info['doctor_id'];
            $relationModel->admin_id               = $this->userInfo['id'];
            $relationModel->admin_name             = $this->userInfo['realname'];
            $res                                   = $relationModel->save();
            if (!$res) {
                return $this->returnJson('2','更新出诊机构医生操作失败！');
            }
            $editContent = $this->userInfo['realname'] . '更新了医生id为' . $relationModel->doctor_id . ' 执业地科室为:' . $hospitalData['frist_department_name'] . '-' . $hospitalData['second_department_name'];
            TbLog::addLog($editContent, '更新出诊机构');
            return $this->returnJson('1', '成功');
        }

        ##更新出诊机构的时候设置医生id
        $transition = Yii::$app->getDb()->beginTransaction();
        try{
            $doc_info = DoctorModel::find()->where(['tp_doctor_id'=>$relationModel['tp_doctor_id'],'tp_platform'=>$relationModel['tp_platform']])->asArray()->one();
            if (!$doc_info) {
                throw new Exception('此第三方医生信息不存在！');
            }
            $tmp_doc_info  = DoctorModel::getDcotorInfoItem($doc_info['doctor_id']);
            $params = array_merge($doc_info, $tmp_doc_info);
            unset($params['doctor_id'],$params['primary_id'],$params['admin_id'],$params['admin_name']);
            unset($params['hospital_name'],$params['miao_doctor_id']);
            $params['hospital_id'] = $relationModel['tp_scheduleplace_id'];
            $params['primary_id'] = $doc_info['primary_id'] >0 ? $doc_info['primary_id'] : $doc_info['doctor_id'];
            $params['tp_hospital_code'] = $relationModel['tp_scheduleplace_id'];
            $params['frist_department_id'] = $hospitalData['frist_department_id'];
            $params['second_department_id'] = $hospitalData['second_department_id'];
            $params['frist_department_name'] = $hospitalData['frist_department_name'];
            $params['second_department_name'] = $hospitalData['second_department_name'];
            $params['miao_frist_department_id'] = $hospitalData['miao_frist_department_id'];
            $params['miao_second_department_id'] = $hospitalData['miao_second_department_id'];
            $params['is_plus'] = 1;
            $params['status'] = 1;
            $params['create_time'] = time();
            $doc_item_temp = DoctorModel::saveDoctor($params);
            if (!$doc_item_temp['doctor_id']) {
                throw new Exception('医生信息保存失败！' . $doc_item_temp['msg']);
            }
            $relationModel->hospital_department_id = $hospital_department_id;
            $relationModel->doctor_id = $doc_item_temp['doctor_id'];
            $relationModel->admin_id               = $this->userInfo['id'];
            $relationModel->admin_name             = $this->userInfo['realname'];
            $res                                   = $relationModel->save();
            if (!$res) {
                throw new \Exception(json_encode($relationModel->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $transition->commit();
            CommonFunc::updateScheduleCacheByDoctor(['doctor_id'=>$relationModel->doctor_id]);
            $editContent = $this->userInfo['realname'] . '更新了医生id为' . $relationModel->doctor_id . ' 执业地科室为:' . $hospitalData['frist_department_name'] . '-' . $hospitalData['second_department_name'];
            TbLog::addLog($editContent, '更新出诊机构');
            return $this->returnJson('1', '成功');
        } catch (\Exception $e) {
            $transition->rollBack();
            $msg = $e->getMessage();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            return $this->returnJson('2', $msg);
        }

        return $this->returnJson('2','操作失败！');
    }

    /**
     * 保存新增多点执业
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-11
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSaveScheduleplace()
    {
        $request     = Yii::$app->request;
        $requestPost = $request->post();
        if (!ArrayHelper::getValue($requestPost, 'doctor_id') || !ArrayHelper::getValue($requestPost, 'hospital_id')) {
            return $this->returnJson('2', '医生或者医院id不能为空！');
        }
        $params = [
            'doctor_id'          => ArrayHelper::getValue($requestPost, 'doctor_id'),
            'hospital_id'        => ArrayHelper::getValue($requestPost, 'hospital_id'),
            'realname'           => ArrayHelper::getValue($requestPost, 'realname'),
            'scheduleplace_name' => ArrayHelper::getValue($requestPost, 'hos_name'),
            'hospital_name'      => ArrayHelper::getValue($requestPost, 'hos_name'),
            'tp_platform'        => 4,
            'admin_id'           => $this->userInfo['id'],
            'admin_name'         => $this->userInfo['realname'],
        ];
        ##检查医院科室
        $keshi_num = HospitalDepartmentRelation::find()->where(['hospital_id' => $requestPost['hospital_id']])->count();
        if (!$keshi_num) {
            return $this->returnJson('2', '请先添加' . $requestPost['hos_name'] . '医院下科室！');
        }
        $hasData = GuahaoScheduleplaceRelation::find()->select('id')->where(['doctor_id' => $params['doctor_id'], 'tp_scheduleplace_id' => $params['hospital_id'], 'tp_platform' => 4])->one();
        if ($hasData) {
            return $this->returnJson('2', '该医生出诊地已存在！');
        }
        $res = GuahaoScheduleplace::addScheduleplace($params);
        if (isset($res['code']) && $res['code'] == 0) {
            $editContent = $this->userInfo['realname'] . '添加了医生id为' . $params['doctor_id'] . ' 执业地为:' . $params['scheduleplace_name'];
            TbLog::addLog($editContent, '添加多点执业地');
            CommonFunc::UpdateInfo($params['doctor_id'], $params['hospital_id']);
            return $this->returnJson('1', '成功');
        } else {
            return $this->returnJson('2', $res['msg'] ?? '操作失败！');
        }
    }

    /**
     * 医生详情页
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-26
     * @return string
     * @throws \Exception
     */
    public function actionDetail()
    {
        if (!$doctor_id = Yii::$app->request->get('doctor_id', '')) {
            $this->_showMessage('doctor_id不能为空', Url::to('doc-list'));
        }
        $doctor_model = DoctorModel::findOne(['doctor_id' => $doctor_id]);
        if (!$doctor_model) {
            $this->_showMessage('医生信息不存在!', Url::to('doc-list'));
        }

        $info_data = [
            'mobile' => '',
            'doctor_tags' => '',
            'visit_type' => '',
            'multi_hospital_name' => '',
            'id_card_file' => [],
            'id_card_expire' => '',
            'doctor_cert_file' => [],
            'doctor_cert_expire' => '',
            'practicing_cert_file' => [],
            'practicing_cert_expire' => '',
            'professional_cert_file' => [],
            'professional_cert_expire' => '',
            'multi_practicing_cert_file' => [],
            'multi_practicing_cert_expire' => '',
        ];
        // 医院扩展信息
        if ($doctor_model->tp_platform == 13) {
            // 处理图片、日期等格式
            $min_doctor_model = $doctor_model->minDoctor->getHumanFormat();
            $info_data['mobile'] = substr_replace($min_doctor_model->mobile, '****', 3, 4);
            $info_data['doctor_tags'] = join('、', array_column(ArrayHelper::getValue($min_doctor_model, "tagsInfo", []), 'name'));
            $info_data['visit_type'] = $min_doctor_model->visit_type == MinDoctorModel::VISIT_TYPE_MULTI ? '多点执业' : '本医院';
            $info_data['multi_hospital_name'] = $min_doctor_model->miao_hospital_name;
            $info_data['id_card_file'] = $min_doctor_model->id_card_file;
            $info_data['id_card_expire'] = $min_doctor_model->id_card_begin . ' - ' . $min_doctor_model->id_card_end;
            $info_data['doctor_cert_file'] = $min_doctor_model->doctor_cert_file;
            $info_data['doctor_cert_expire'] = $min_doctor_model->doctor_cert_begin . ' - ' . $min_doctor_model->doctor_cert_end;
            $info_data['practicing_cert_file'] = $min_doctor_model->practicing_cert_file;
            $info_data['practicing_cert_expire'] = $min_doctor_model->practicing_cert_begin . ' - ' . $min_doctor_model->practicing_cert_end;
            $info_data['professional_cert_file'] = $min_doctor_model->professional_cert_file;
            $info_data['professional_cert_expire'] = $min_doctor_model->professional_cert_begin . ' - ' . $min_doctor_model->professional_cert_end;
            $info_data['multi_practicing_cert_file'] = $min_doctor_model->multi_practicing_cert_file;
            $info_data['multi_practicing_cert_expire'] = $min_doctor_model->multi_practicing_cert_begin . ' - ' . $min_doctor_model->multi_practicing_cert_end;
        }

        if (!empty($doctor_model->avatar)) {
            $imgUrl = isset(\Yii::$app->params['avatarUrl']) ? \Yii::$app->params['avatarUrl'] : "";
            $doctor_model->avatar = $imgUrl . $doctor_model->avatar;
        }

        $doctor_model->doctorInfo->good_at = CommonFunc::filterContent($doctor_model->doctorInfo->good_at);
        $doctor_model->doctorInfo->profile = CommonFunc::filterContent($doctor_model->doctorInfo->profile);
        return $this->render('detail', [
            'base_model' => $doctor_model,
            'info_data' => $info_data
        ]);
    }
}
