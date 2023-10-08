<?php
/**
 * Created by PhpStorm.
 * @file GuahaoPlatformController.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-12-11
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

class GuahaoPlatformController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size = 10;

    /**
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-11
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['status'] = 1;
        $platformModel = new GuahaoPlatformListModel();

        $list = $platformModel::getList($requestParams);
        foreach($list as &$item){
            $item['status_title'] = ArrayHelper::getValue($platformModel::$status_list, $item['status']);
            $item['schedule_type_title'] = ArrayHelper::getValue($platformModel::$schedule_type, $item['schedule_type']);
        }

        $totalCount = $platformModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data =  ['dataProvider' => $list, 'requestParams' => $requestParams,'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('list', $data);
    }

    /**
     *  新增
     * @return string|void
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-13
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $data['id']                 = $request->post('id');
            $data['tp_platform']        = $request->post('tp_platform');
            $data['platform_name']      = $request->post('platform_name');
            $data['tp_type']            = $request->post('tp_type');
            $data['sdk']             = $request->post('sdk');
            $data['get_paiban_type']   = $request->post('get_paiban_type');
            $data['schedule_type']   = $request->post('schedule_type');
            $data['status']             = $request->post('status');

            if(empty($data['tp_platform']) || empty($data['platform_name']) || empty($data['tp_type']) || empty($data['sdk'])){
                return $this->returnJson(2, '参数必填');
            }
            $data['open_time']          = $request->post('open_time');
            if(empty($data['open_time'])) {
                $data['open_time'] = date('Y-m-d',time());
            }
            if($data['id']){
                $title = '修改';
                $findCount = GuahaoPlatformListModel::find()
                    ->where(['tp_platform'=> $data['tp_platform']])
                    ->andWhere(['<>','id',$data['id']])
                    ->count();
                if($findCount){
                    return $this->returnJson(2, '平台类型Type已存在， 请检查');
                }
                $findCount2 = GuahaoPlatformListModel::find()
                    ->where(['tp_type'=> $data['tp_type']])
                    ->andWhere(['<>','id',$data['id']])
                    ->count();
                if($findCount2){
                    return $this->returnJson(2, '平台类型已存在， 请检查');
                }
            }else{
                $title = '新增';
                $findCount = GuahaoPlatformListModel::find()
                    ->where(['tp_platform'=> $data['tp_platform']])
                    ->count();
                if($findCount){
                    return $this->returnJson(2, '平台类型Type已存在， 请检查');
                }
                $findCount2 = GuahaoPlatformListModel::find()
                    ->where(['tp_type'=> $data['tp_type']])
                    ->count();
                if($findCount2){
                    return $this->returnJson(2, '平台类型已存在， 请检查');
                }
            }
            $data['admin_name'] = $this->userInfo['realname'];
            $data['admin_id'] = $this->userInfo['id'];
            $infoSave = GuahaoPlatformListModel::dataSave($data);
            if($infoSave){
                $editContent = $this->userInfo['realname'] .$title. '了王氏对接来源【'.$data['platform_name']."】";
                TbLog::addLog($editContent, '第三方来源');
                return $this->returnJson(1, '操作成功');
            }else{
                return $this->returnJson(2, '操作失败，请稍后再试');
            }
        } else {
            $id        = $request->get('id');
            if($id){
                $info = GuahaoPlatformListModel::find()->where(['id'=>$id])->one();
                return $this->render('add', ['info' => $info]);
            }
            return $this->render('add', ['info' => []]);
        }
    }

    /**
     *  修改状态
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-13
     */
    public function actionUpdateStatus()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $id                 = $request->post('id');
            $status                 = $request->post('status');
            $info = GuahaoPlatformListModel::find()->where(['id'=>$id])->one();
            if($info){
                $info->status=$status;
                try {
                    $msg = GuahaoPlatformListModel::$status_list[$status];
                }catch (\Exception $e){
                    $msg = $e->getMessage();
                }
                if($info->save()){
                    $deleteContent = $this->userInfo['realname'] . '更改王氏对接来源状态为'.$msg;
                    TbLog::addLog($deleteContent, '第三方来源状态');
                    return $this->returnJson(1, '操作成功');
                }else{
                    return $this->returnJson(2, '操作失败，请稍后再试');
                }
            }else{
                return $this->returnJson(2, '操作失败，请稍后再试');
            }
        }
    }
}