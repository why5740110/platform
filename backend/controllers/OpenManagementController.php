<?php
/**
 * Created by PhpStorm.
 * @file OpenManagementController.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-12-04
 */


namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\GuahaoPlatformModel;
use common\models\GuahaoCooListModel;
use common\models\GuahaoPlatformListModel;
use common\models\TbLog;
use Yii;

use yii\data\Pagination;


class OpenManagementController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size = 10;


    /**
     *  列表
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public function actionOpenList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $list = GuahaoPlatformListModel::getList($requestParams);

        $cooPlatform = (isset($requestParams['coo_platform']) && !empty($requestParams['coo_platform'])) ? $requestParams['coo_platform'] : 2;

        $cooList = GuahaoCooListModel::getCooPlatformList();
        $reaList = GuahaoPlatformListModel::getReaList(['coo_platform'=>$cooPlatform]);
        $reaListArr = array_combine(array_column($reaList, 'tp_platform'), array_column($reaList, 'remarks'));
        $reaStatusArr = array_combine(array_column($reaList, 'tp_platform'), array_column($reaList, 'status'));

        foreach ($list as &$item) {
            $item['coo_platform_name'] =  $cooList[$cooPlatform] ?? '';
            $item['coo_platform'] =  $cooPlatform;
            $item['remarks'] =  isset($reaListArr[$cooPlatform]) ? $reaListArr[$cooPlatform] : "";
            $item['status'] =  isset($reaStatusArr[$item['tp_platform']]) ? $reaStatusArr[$item['tp_platform']] : 0;
            $item['guahao_platform_id'] = $item['tp_platform'];
        }

        $totalCount = GuahaoPlatformListModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data = ['dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('open-list', $data);
    }

    /**
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public function actionUpdateStatus()
    {
        $request = \Yii::$app->request;
        $t_id = (int)$request->post('plat_form_id', '');
        $coo_id = (int)$request->post('coo_id', '');
        $dis_type = (int)$request->post('dis_type', 0);
        $remarks = trim($request->post('remarks', '停止开放'));
        if($t_id){
            if (mb_strlen($remarks) > 100) {
                return $this->returnJson(2, '不能超过100个字！');
            }
            $info = GuahaoPlatformModel::find()->where(['tp_platform' => $t_id,'coo_platform'=>$coo_id])->one();

            if (!$info) {
                $info = new GuahaoPlatformModel();
                $info->remarks = "";

                $info->status = 1;
                $info->coo_platform = $coo_id;
                $info->tp_platform = $t_id;
                $dis_text = '开放';
                $remarks_txt = "";
                $info->admin_id = $this->userInfo['id'];
                $info->admin_name = $this->userInfo['realname'];
                $info->remarks = $remarks;
                $info->update_time = time();
            } else {
                if ($dis_type == 0 && $info->status == 1) {
                    return $this->returnJson(2, '已经开放了， 无需操作！');
                }
                if ($dis_type == 1 && $info->status == 2) {
                    return $this->returnJson(2, '已经停止开放了， 无需操作！');
                }
                $dis_text     = '开放';
                $remarks_txt    = '';
                $info->status = 1;
                $oldRemark = $info->remarks;
                if ($dis_type == 1) {
                    $info->status = 2;
                    $dis_text = "停止开放";
                    $remarks_txt = '原因:由【'.$oldRemark."】改为".$remarks;
                }

                $info->admin_id = $this->userInfo['id'];
                $info->admin_name = $this->userInfo['realname'];
                $info->remarks = $remarks;
                $info->update_time = time();
            }
            if ($info->save()) {
                $tpPlatformList = GuahaoPlatformListModel::getTpPlatformList();
                $cooList = GuahaoCooListModel::getCooPlatformList();
                $editContent = $this->userInfo['realname'] . $dis_text . '了王氏对接来源:【' .
                    $tpPlatformList[$t_id] . "】给对接王氏来源：【" .
                    $cooList[$coo_id]."】".$remarks_txt;
                TbLog::addLog($editContent, '开放管理');
                return $this->returnJson(1, '操作成功');
            }
            return $this->returnJson(2, '操作失败');
        }else{
            $platFormId = trim($request->post('plat_form_id'));
            $cooId = trim($request->post('coo_id'));
            if(empty($platFormId) || empty($cooId)){
                return $this->returnJson(2, '数据有误！');
            }
            $cooRealModel = new GuahaoPlatformModel();
            $cooRealModel->coo_platform = $cooId;
            $cooRealModel->tp_platform = $platFormId;
            $cooRealModel->status = 1;
            if($cooRealModel->save()){
                $tpPlatformList = GuahaoPlatformListModel::getTpPlatformList();
                $cooList = GuahaoCooListModel::getCooPlatformList();
                $addContent = $this->userInfo['realname'] . '开放了王氏对接来源:【' .
                    $tpPlatformList[$platFormId] . "】给对接王氏来源：【" .
                    $cooList[$cooId];
                TbLog::addLog($addContent, '开放管理');
                return $this->returnJson(1, '操作成功');
            }else{
                return $this->returnJson(2, '操作失败');
            }

        }

    }


}