<?php
/**
 * Created by PhpStorm.
 * @file CooperationPlatform.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-12-04
 */

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\GuahaoCooListModel;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoPlatformListModel;
use common\models\GuahaoPlatformModel;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\models\TbLog;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use \yii\helpers\ArrayHelper;


class CooperationPlatformController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size = 10;


    /**
     *  列表
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public function actionCooperationList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $cooPlatform = $requestParams['coo_platform'] ?? '';
        $requestParams['status'] = 1;

        $list = GuahaoCooListModel::getList($cooPlatform);
        foreach ($list as &$item) {
            $requestParams['status'] = 1;
            $requestParams['coo_id'] = $item['coo_platform'];
            $tpPlatformlistSearch = GuahaoPlatformListModel::getOpenCooTpPlatformIdListByCooId($item['coo_platform']);
            $item['relation_count'] = GuahaoHospitalModel::getHosptailCooCount($tpPlatformlistSearch, $requestParams);
        }

        $totalCount = GuahaoCooListModel::getCount();
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data = ['dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('cooperation-list', $data);
    }

    /**
     * 合作平台详情列表
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public function actionCooperationDetail()
    {
        $request = Yii::$app->request;

        $id = $request->get('coo_id');
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['coo_platform'] = (isset($requestParams['coo_platform']) && !empty($requestParams['coo_platform'])) ? $requestParams['coo_platform'] : '';
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['tp_platform']   = isset($requestParams['tp_platform']) ? intval($requestParams['tp_platform']) : '';
        $requestParams['status']   = isset($requestParams['status']) ? intval($requestParams['status']) : '';

        $cooName = GuahaoCooListModel::getCooNameByCooPlatform($id);
        $tpPlatformlistSearch = GuahaoPlatformListModel::getOpenCooTpPlatformIdListByCooId($id);
        if ($id && count($tpPlatformlistSearch) > 0) {
            $guahaoModel = new GuahaoHospitalModel();
            // 获取开放医院的列表数据
            $list = $guahaoModel::getHosptailListAssociateCoo($tpPlatformlistSearch, $requestParams);
            $returnList = [];
            foreach ($list as $k=>$v) {
                $item = $v;
                // 判断该合作平台的 该医院开放状态是否存在
                $relationHospitalFind = GuahaoPlatformRelationHospitalModel::find()
                    ->where(
                        ['tp_platform'=>$v['tp_platform'],'coo_platform'=>$id,'tp_hospital_code'=>$v['tp_hospital_code']])
                    ->asArray()->one();

                if ($relationHospitalFind) {
                    $item['coo_platform'] = $relationHospitalFind['coo_platform'];
                    // 判断状态
                    if(intval($relationHospitalFind['status']) == 2){
                        $item['rel_hosp_status'] = 2;
                    }elseif(intval($relationHospitalFind['status']) == 1){
                        $item['rel_hosp_status'] = 1;
                    }elseif($relationHospitalFind['status'] == '0'){
                        $item['rel_hosp_status'] = 2;
                    }
                    $item['status_title'] = GuahaoPlatformRelationHospitalModel::$view_status_list[$item['rel_hosp_status']] ?? "未开放";
                    $item['rel_hosp_remarks'] = $relationHospitalFind['remarks'] ?? "";
                } else {
                    $item['rel_hosp_status'] = 3;
                    $item['rel_hosp_remarks'] = "";
                }
                $item['status_title'] = GuahaoPlatformRelationHospitalModel::$view_status_list[$item['rel_hosp_status']] ?? "未开放";
                $item['tp_platform_name'] = GuahaoPlatformListModel::getTpPlatformList()[intval($v['tp_platform'])] ?? "数据有误";
                array_push($returnList,$item);
            }
            $totalCount = $guahaoModel::getHosptailCooCount($tpPlatformlistSearch, $requestParams);
            $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
            $data = [
                'dataProvider' => $returnList,
                'coo_id' => $id,
                'requestParams' => $requestParams,
                'totalCount' => $totalCount,
                'pages' => $pages,
            ];
        }else{
            $pages = new Pagination(['totalCount' => 1, 'pageSize' => 1]);

            $data = [
                'dataProvider' => [],
                'coo_id' => $id,
                'requestParams' => $requestParams,
                'totalCount' => 0,
                'pages' => $pages,
            ];
        }
        $this->getView()->title = $cooName;
        return $this->render('detail', $data);
    }


    /**
     *  开放 开始和停止
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public function actionCooperationStartStop()
    {
        $request = Yii::$app->request;
        $ids = $request->post('ids');
        $openType = $request->post('open_type');
        $tpPlatform = $request->post('tp_platform');
        $remarks = trim($request->post('remarks', ''));
        $cooId = $request->post('coo_id');

        $tpPlatformList = GuahaoPlatformModel::getTpPlatformIdListByCooId($cooId);
        $adminInfo['admin_id'] = $this->userInfo['id'];
        $adminInfo['admin_name'] = $this->userInfo['realname'];

        if (intval($tpPlatform) !== 0 && !in_array($tpPlatform, $tpPlatformList)) {
            return $this->returnJson(2, '请您给该平台开放来源');
        }
        if ($openType == 2 && empty($remarks)) {
            return $this->returnJson(2, '请填写备注');
        }

        if ($ids) {
            $result = GuahaoPlatformRelationHospitalModel::updateAllStartStop($ids, $openType, $cooId, $remarks, $adminInfo);
            if ($openType == 2) {
                $logMsg = '停止开放';
            } elseif ($openType == 1) {
                $logMsg = '开始开放';
            }

            if (count($result['log']) > 0) {
                foreach ($result['log'] as $logk => $logv) {
                    $openStartStopRemarkLOgContent = $this->userInfo['realname'] . '把 ' . $logv;
                    TbLog::addLog($openStartStopRemarkLOgContent, '医院开放操作备注');
                }
            }

            $hospitalNameList = GuahaoHospitalModel::getHospitalNameListByIdList($ids);
            foreach ($hospitalNameList as $k => $v) {
                $openStartStopContent = $this->userInfo['realname'] . '操作了 ' . $v . '的' . $logMsg;
                TbLog::addLog($openStartStopContent, '医院开放操作');
            }
            return $this->returnJson(1, $result['msg']);
        }
        return $this->returnJson(2, '操作失败');
    }

    /**
     *  备注
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-07
     */
    public function actionCooperationUpdateRemarks()
    {
        $request = Yii::$app->request;
        $ids = $request->post('ids');
        $remarks = $request->post('remarks', '');
        $hospNameTxt = $request->post('hosp_name_txt', '');
        $relHospStatus = $request->post('rel_hosp_status', '');
        if (empty($hospNameTxt) || empty($ids)) {
            return $this->returnJson(2, '信息有误');
        }
        $adminInfo['admin_id'] = $this->userInfo['id'];
        $adminInfo['admin_name'] = $this->userInfo['realname'];
        $cooId = $request->post('coo_id');
        $resultMsg = GuahaoPlatformRelationHospitalModel::updateRemarks($ids,$relHospStatus, $cooId, $remarks, $adminInfo);
        if (!empty($resultMsg)) {
            $openStartStopContent = $this->userInfo['realname'] . '操作了' . $hospNameTxt . $resultMsg;
            TbLog::addLog($openStartStopContent, '医院开放操作备注');
            return $this->returnJson(1, '修改成功');
        }
        return $this->returnJson(2, '操作失败');
    }
}