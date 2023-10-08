<?php

namespace backend\controllers;

use common\components\Excel;
use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoOrderInfoModel;
use common\models\GuahaoOrderModel;
use common\models\DoctorModel;
use common\models\HdfMessageModel;
use common\models\TbLog;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use common\libs\HashUrl;

class GuahaoOrderController extends BaseController
{
    public $page_size = 10;
    public $max_down = 10000;
    public $pay_status;
    public $device_source;
    public $state;

    public function init()
    {
        $this->pay_status = GuahaoOrderModel::$pay_status;
        $this->state = GuahaoOrderModel::$state;
        $this->device_source = GuahaoOrderModel::$device_source;
        parent::init();
    }

    public function actionOrderList()
    {
        $requestParams        = Yii::$app->request->getQueryParams();
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['create_time'] = (isset($requestParams['create_time']) && (!empty($requestParams['create_time']))) ? $requestParams['create_time'] : '';
        $requestParams['visit_time'] = (isset($requestParams['visit_time']) && (!empty($requestParams['visit_time']))) ? $requestParams['visit_time'] : '';
        $requestParams['state']   = isset($requestParams['state']) ? intval($requestParams['state']) : '';
        $requestParams['tp_platform']   = isset($requestParams['tp_platform']) ? intval($requestParams['tp_platform']) : '';
        $requestParams['device_source']   = isset($requestParams['device_source']) ? intval($requestParams['device_source']) : '';
        $requestParams['tp_coo_platform']   = isset($requestParams['tp_coo_platform']) ? intval($requestParams['tp_coo_platform']) : '';
        $requestParams['famark_type']   = isset($requestParams['famark_type']) ? intval($requestParams['famark_type']) : '';
        $requestParams['gender']   = isset($requestParams['gender']) ? intval($requestParams['gender']) : '';

        //创建时间格式验证
        if (!empty($requestParams['create_time'])) {
            $pages = new Pagination(['totalCount' => 0, 'pageSize' => $requestParams['limit']]);
            $data =  ['dataProvider' => [], 'requestParams' => $requestParams,'totalCount' => 0, 'pages' => $pages];
            if (strripos($requestParams['create_time'], " - ") !== false) {
                list($stime, $etime) = explode(' - ', $requestParams['create_time']);
                if (!(CommonFunc::checkDate($stime) && CommonFunc::checkDate($etime))) {
                    return $this->render('list', $data);
                }
            } else {
                return $this->render('list', $data);
            }
        }
        //就诊时间格式验证
        if (!empty($requestParams['visit_time'])) {
            $pages = new Pagination(['totalCount' => 0, 'pageSize' => $requestParams['limit']]);
            $data =  ['dataProvider' => [], 'requestParams' => $requestParams,'totalCount' => 0, 'pages' => $pages];
            if (strripos($requestParams['visit_time'], " - ") !== false) {
                list($stime, $etime) = explode(' - ', $requestParams['visit_time']);
                if (!(CommonFunc::checkDate($stime) && CommonFunc::checkDate($etime))) {
                    return $this->render('list', $data);
                }
            } else {
                return $this->render('list', $data);
            }
        }

        $orderModel = new GuahaoOrderModel();

        $list       = $orderModel::getList($requestParams);
        foreach ($list as &$item) {
            //验证医生是否存在或者禁用
            $infos = DoctorModel::getInfo($item['doctor_id']);
            //获取主医生id
            $primary_id = HashUrl::getIdDecode(ArrayHelper::getValue($infos,'primary_id'));
            $item['doctorId'] = ($primary_id == 0) ? $item['doctor_id'] : $primary_id;
            $item['is_disable'] = 0;//医生存在或禁用  0 不存在或禁用  1 存在
            if (!empty($infos) && $infos['status'] == 1) $item['is_disable'] = 1;
            //$item['tp_platform'] = $this->platform[$item['tp_platform']] ?? '未知';
            $item['coo_platform'] = $this->coo_platform[$item['coo_platform']] ?? '';
            $item['state_desc']       = $this->state[$item['state']] ?? '未知';
            $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
            $item['visit_cost']  = ceil($item['visit_cost'] * 100 / 100) / 100;
            $item['pay_status']  = $this->pay_status[$item['pay_status']];
            $item['patient_name'] = CommonFunc::hiddenString($item['patient_name']);
            $orderInfo = GuahaoOrderInfoModel::find()->where(['=', 'order_id', $item['id']])->select(['invalid_type', 'invalid_reason', 'order_id'])->asArray()->one();
            $item['invalid_type'] = isset($orderInfo['invalid_type']) ? $orderInfo['invalid_type'] : "";
            $item['invalid_reason'] = isset($orderInfo['invalid_reason']) ? $orderInfo['invalid_reason'] : "";
            $item['invalid_status'] = (!empty($orderInfo['invalid_type']) || !empty($orderInfo['invalid_reason'])) ? 1 : 0;//无效原因状态 0 空白 1 无效类型或原因有一个不为空
        }
        $totalCount = $orderModel::getCount($requestParams);
        $pages      = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data       = ['dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('list', $data);
    }

    /**
     * actionInfo
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/28
     */
    public function actionInfo()
    {
        $request = Yii::$app->request;
        //获取医生信息
        $id   = $request->get('id');
        $info = GuahaoOrderModel::find()->select("*")->where(['id' => $id])->asArray()->one();
        if (!$info) {
            return $this->_showMessage('订单信息不存在', '/guahao-order/order-list');
        }
        //查询订单附表信息
        $res     = GuahaoOrderInfoModel::find()->where(['order_id' => $id])->asArray()->one();
        $message = [];
        if (!empty($info)) {
            //查询短信
            $message = HdfMessageModel::find()->where(['tp_order_id' => $info['tp_order_id']])->asArray()->all();
        }
        if (!empty($res)) {
            foreach ($res as $rk => $rv) {
                $info[$rk] = $rv;
            }
            if (!empty($res['visit_endtime'])) {
                $info['visit_time_long'] = $res['visit_starttime'] . "-" . $res['visit_endtime'];
            } else {
                $info['visit_time_long'] = $res['visit_starttime'];
            }
        } else {
            $info['visit_time_long'] = "";
        }

        //$info['tp_platform'] = $this->platform[$info['tp_platform']] ?? '未知';
        $info['state_desc']       = $this->state[$info['state']] ?? '未知';
        $info['patient_name']= CommonFunc::hiddenString($info['patient_name']);
        $info['mobile']      = CommonFunc::hiddenString($info['mobile'],0);
        $info['card']        = CommonFunc::hiddenString($info['card'],0);
        $info['create_time'] = date("Y-m-d H:i:s", $info['create_time']);
        $info['visit_cost']  = ceil($info['visit_cost'] * 100 / 100) / 100;
        $type                = [0 => '普通', 1 => '普通', 2 => '专家', 3 => '专科', 4 => '特需', 5 => '夜间', 6 => '会诊', 7 => '老院', 8 => '其他'];
        $info['visit_type']  = ArrayHelper::getValue($type, $info['visit_type'], '其他');
        $info['nooncode']  = GuahaoOrderModel::$visit_nooncode[$info['visit_nooncode']] ?? '';

        //获取医院挂号规则
        $hosRule = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $info['tp_hospital_code']])->select(['tp_allowed_cancel_day','tp_allowed_cancel_time','tp_open_day','tp_open_time'])->asArray()->one();
        $info['tp_allowed_cancel_day'] = $hosRule['tp_allowed_cancel_day'] ?? 1;
        $info['tp_allowed_cancel_time'] = $hosRule['tp_allowed_cancel_time'] ?? "";
        $info['tp_open_day'] = $hosRule['tp_open_day'] ?? 0;
        $info['tp_open_time'] = $hosRule['tp_open_time'] ?? "";

        $data = ['dataProvider' => $info, 'message' => $message];
        return $this->render('info', $data);
    }

    /**
     * 挂号无效订单导出
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-25
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDown()
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        $requestParams        = Yii::$app->request->getQueryParams();
        $field                = 'id,order_sn,doctor_name,department_name,hospital_name,visit_cost,famark_type,visit_time,state,pay_status,create_time,tp_platform';
        $requestParams['state'] = 6;//只导出无效订单
        $query = GuahaoOrderModel::conditionWhere($requestParams, $field);
        $totalCount = $query->count();
        $totalCount = intval($totalCount);
        if ($totalCount == 0) {
            $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('guahao-order/order-list');
            return $this->_showMessage("暂无数据", $url);
        }
        if ($totalCount > $this->max_down) {
            $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('guahao-order/order-list');
            return $this->_showMessage("最大导出不能超过{$this->max_down},请筛选后重试！", $url);
        }
        $list = $query->orderBy('create_time desc')->asArray()->all();
        $orderInfoArr = [];
        $newIdsArr = array_chunk(array_column($list, 'id'), 2000);
        foreach ($newIdsArr as $k => $v) {
            $orderInfoList = GuahaoOrderInfoModel::find()->where(['in', 'order_id', $v])->select(['tp_json', 'invalid_type', 'invalid_reason', 'order_id'])->asArray()->all();
            $orderInfoArr = $orderInfoArr + array_column($orderInfoList, NULL, 'order_id');
        }

        foreach ($list as &$item) {
            $item['tp_platform'] = $this->platform[$item['tp_platform']] ?? '未知';
            $item['state']       = $this->state[$item['state']] ?? '未知';
            $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
            $item['visit_cost']  = ceil($item['visit_cost'] * 100 / 100) / 100;
            $item['pay_status']  = $this->pay_status[$item['pay_status']];
            $item['famark_type'] = $item['famark_type'] == 1 ? '初诊' : '复诊';
            $item['tp_json'] = isset($orderInfoArr[$item['id']]['tp_json']) ? $orderInfoArr[$item['id']]['tp_json'] : "";
            $item['invalid_type'] = isset($orderInfoArr[$item['id']]['invalid_type']) ? $orderInfoArr[$item['id']]['invalid_type'] : "";
            $item['invalid_reason'] = isset($orderInfoArr[$item['id']]['invalid_reason']) ? $orderInfoArr[$item['id']]['invalid_reason'] : "";
        }

        if (count($list) > $this->max_down) {
            $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('guahao-order/order-list');
            return $this->_showMessage("最大导出不能超过{$this->max_down},请筛选后重试！", $url);
        }


        $excel  = new Excel();
        $header = [
            '序号'    => 'id',
            //'就诊人'   => 'patient_name',
            '订单流水号' => 'order_sn',
            '医生'    => 'doctor_name',
            '科室'    => 'department_name',
            '医院'    => 'hospital_name',
            '医事服务费' => 'visit_cost',
            '初诊/复诊' => 'famark_type',
            '就诊时间'  => 'visit_time',
            '订单状态'  => 'state',
            '支付状态'  => 'pay_status',
            '创建时间'  => 'create_time',
            '来源'    => 'tp_platform',
        ];
        if (isset($requestParams['state']) && $requestParams['state'] == 6) {
            $header['无效类型'] = 'invalid_type';
            $header['无效原因'] = 'invalid_reason';
            $header['备注'] = 'tp_json';
        }
        // CommonFunc::down_xls($list, array_keys($header));
        $fileName = '挂号订单_' . date('Y-m-d H:i:s') . '.xlsx';
        $excel->export($list, $header)->downFile($fileName,'Excel5');
        exit;
    }

    /**
     * 订单用户uid导出  guahao-order/down-uid
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-11-10
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionDownUid()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $field         = 'uid';
        $query = GuahaoOrderModel::conditionWhere($requestParams, $field);
        $totalCount = $query->groupBy('uid')->count();
        $totalCount = intval($totalCount);
        if ($totalCount > $this->max_down) {
            $url = !empty(Yii::$app->request->urlReferrer) ? Yii::$app->request->urlReferrer : Yii::$app->urlManager->createUrl('guahao-order/order-list');
            return $this->_showMessage("最大导出不能超过{$this->max_down},请筛选后重试！", $url);
        }
        $list = $query->orderBy('uid asc')->groupBy('uid')->asArray()->all();
        $excel  = new Excel();
        $header = [
            '用户中心的UID' => 'uid',
        ];
        $fileName = '挂号订单用户中心UID_' . date('Y-m-d H:i:s') . '.xlsx';
        $excel->export($list, $header)->downFile($fileName);
        exit;

    }

    /**
     *  隐秘信息查看
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-11-10
     */
    public function actionSecretShow()
    {
        $request = Yii::$app->request;
        $secretTypeArr = [
          '1'=>"patient_name",
          '2'=>"mobile",
          '3'=>"card",
        ];
        $secretNameArr = [
            '1'=>"姓名",
            '2'=>"手机号",
            '3'=>"身份证号",
        ];
        //获取订单ID
        $orderSn        = $request->get('order_sn');
        $secretType     = $request->get('secret_type');
        $showHide       = $request->get('show_hide');

        if(empty($orderSn)){
            return $this->returnJson(0, '订单号有误!');
        }

        $select = $secretTypeArr[strval($secretType)];
        if(!$select){
            return $this->returnJson(0, '隐秘参数类型有误!');
        }

        $selectInfo = $select.","."order_sn";
        $info       = GuahaoOrderModel::find()->select($selectInfo)->where(['order_sn' => $orderSn])->asArray()->one();
        $infoData   = $info[strval($select)];
        $editContent    = $this->userInfo['realname'] . '查看了订单流水号:' . $info['order_sn'] . '就诊人' . $secretNameArr[strval($secretType)];
        TbLog::addLog($editContent, '隐秘信息查看');
        if(intval($showHide) == 2){
            if(strval($select)=="patient_name"){
                $infoData   = CommonFunc::hiddenString(strval($info['patient_name']));
            }elseif(strval($select)=="mobile"){
                $infoData   = CommonFunc::hiddenString(strval($info['mobile']),0);
            }else{
                $infoData   = CommonFunc::hiddenString(strval($info['card']),0);
            }
        }
        if ($info) {
            return $this->returnJson(1, '操作成功!',['info'=>$infoData ?? ""]);
        } else {
            return $this->returnJson(0, '操作失败!');
        }
    }

}
