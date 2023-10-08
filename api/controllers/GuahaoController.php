<?php
/**
 * 挂号相关接口
 * @file GuahaoController.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2020-10-12
 */

namespace api\controllers;

use common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\models\DoctorModel;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoOrderInfoModel;
use common\sdks\CenterSDK;
use common\sdks\snisiya\SnisiyaSdk;
use common\models\HospitalDepartmentRelation;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class GuahaoController extends CommonController
{
    /**
     * 取消预约
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/10/12
     */
    public function actionCancel()
    {
        $params = Yii::$app->request->get();
        unset($params['appid']);
        unset($params['os']);
        unset($params['version']);
        unset($params['time']);
        unset($params['noncestr']);
        unset($params['sign']);
        $snisiyaSdk = new SnisiyaSdk();
        $result = $snisiyaSdk->guahaoCancel($params);
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return $this->jsonSuccess(ArrayHelper::getValue($result, 'data'));
        } else {
            return $this->jsonError(ArrayHelper::getValue($result, 'msg', '请求失败！'));
        }
    }

    public function actionHospital()
    {
        $request = Yii::$app->request->get();
        $params = [
            'tp_platform' => 2,
            'tp_hospital_code' => '32010100',
        ];
        $snisiyaSdk = new SnisiyaSdk();
        $result = $snisiyaSdk->getGuahaoHospital($params);
        return $this->jsonSuccess($result);
    }

    public function actionDepartment()
    {
        $request = Yii::$app->request->get();
        $params = [
            'tp_platform' => 2,
            // 'hosCode'=>'32010100',
            'tp_hospital_code' => '2020925001',
            'tp_hospital_code' => '32010100',
        ];
        $snisiyaSdk = new SnisiyaSdk();
        $result = $snisiyaSdk->getGuahaoDepartment($params);
        return $this->jsonSuccess($result);
    }

    /**
     * 获取订单列表
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/10/12
     */
    public function actionGetOrderList()
    {
        $params = Yii::$app->request->get();
        $result = [
            'list' => [],
            'page' => 0,
            'pagesize' => 0,
            'totalCount' => 0
        ];

        $page = isset($params['page']) ? $params['page'] : 1;
        $pagesize = isset($params['pagesize']) ? $params['pagesize'] : 10;
        $uid = intval($params['uid'] ?? 0);
        if (empty($uid)) {
            return $this->jsonError('uid不能为空！');
        }

        $order = "create_time DESC";
        $query = GuahaoOrderModel::find()
            ->where(['uid' => $uid])
            ->orderBy($order);

        $tp_order_id = $params['tp_order_id'] ?? '';
        if (!empty($tp_order_id)) {
            $query->andWhere(['tp_order_id' => $tp_order_id]);
        }

        $query->andWhere(['!=', 'state', 6]);
        $tp_platform = $params['tp_platform'] ?? 0;
        if (!empty($tp_platform)) {
            $query->andWhere(['tp_platform' => $tp_platform]);
        }

        $visit_start_time = $params['visit_start_time'] ?? '';
        if (!empty($visit_start_time)) {
            $query->andWhere(['>=', 'visit_time', $visit_start_time]);
        }

        $visit_end_time = $params['visit_end_time'] ?? '';
        if (!empty($visit_end_time)) {
            $query->andWhere(['<=', 'visit_time', $visit_start_time]);
        }

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => $pagesize
        ]);
        $pageObj->setPage($page - 1);
        $query->offset($pageObj->offset)->limit($pageObj->limit);

        $list = GuahaoOrderModel::find()->select(['a.*'])->from(['a' => GuahaoOrderModel::tableName(), 'b' => $query])->where('a.id=b.id')->asArray()->all();
        if (empty($list)) {
            return $this->jsonSuccess($result);
        }

        //组合数据
        foreach ($list as $k => &$v) {
            $v['id'] = $v['order_sn'];
            $doctor_info = DoctorModel::getInfo($v['doctor_id']);
            $v['doctor_title'] = ArrayHelper::getValue($doctor_info, 'doctor_title', '');
            $v['doctor_avatar'] = ArrayHelper::getValue($doctor_info, 'doctor_avatar', '');
            $v['primary_id'] = HashUrl::getIdDecode(ArrayHelper::getValue($doctor_info, 'primary_id')) ?: $v['doctor_id'];
            $v['state_show'] = CommonFunc::formatGuahaoState($v['state'], $v['pay_status']);
            $v['detail_url'] =  rtrim(ArrayHelper::getValue(\Yii::$app->params,'domains.mobile'),'/').Url::to(['/hospital/register/register-detail.html','id'=>$v['id'],'source_from'=>'mycenter']);
            unset($v['tp_json']);
        }

        $result = [
            'list' => $list,
            'page' => $page,
            'pagesize' => $pagesize,
            'totalCount' => $totalCount
        ];

        return $this->jsonSuccess($result);
    }

    /**
     * 获取科大讯飞订单列表（最大范围支持查询7天）
     * @return array
     * @author wanghongying <wanghongying@yuanxin-inc.com>
     * @date 2023/04/24
     */
    public function actionKedaOrderList()
    {
        $params = Yii::$app->request->get();
        $page = (int)$params['page'] > 0 ? (int)$params['page'] :  1;
        $pagesize = (int)$params['pagesize'] > 0 ? (int)$params['pagesize']  : 20;
        $order = "create_time DESC";
        $time = time();
        $start_time = $time - 7 * 86400;
        $query = GuahaoOrderModel::find()
            ->where(['srefer' => 'kedaxunfei'])
            ->andWhere(['>=', 'create_time', $start_time])
            ->andWhere(['<=', 'create_time', $time])
            ->orderBy($order);

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        if ($totalCount <= 0) return $this->jsonSuccess([
            'total' => $totalCount,
            'list' => []
        ]);
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => $pagesize
        ]);
        $pageObj->setPage($page - 1);
        $query->offset($pageObj->offset)->limit($pageObj->limit);
        $field = ['order_sn','uid','hospital_name','department_name','visit_time','visit_nooncode','state','create_time'];
        $list = $query->select($field)->asArray()->all();
        $uids =array_unique(array_column($list, 'uid'));
        $users = CenterSDK::getInstance()->memberGetUsers(['uids' => json_encode($uids)]);
        $users = array_column($users, 'mobile', 'uid');
        foreach ($list as &$val) {
            //获取用户信息
            $val['mobile'] = isset($users[$val['uid']]) ? $users[$val['uid']] : '';
        }
        $result = [
            'total' => $totalCount,
            'list' => $list
        ];
        return $this->jsonSuccess($result);
    }

    /**
     * 查询订单详情接口
     * @return array
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/3
     */
    public function actionGetOrder()
    {
        $params = Yii::$app->request->get();

        $id = intval($params['id'] ?? 0);
        if (empty($id)) {
            return $this->jsonError('订单id不能为空！');
        }

        $orderInfo = GuahaoOrderModel::find()
            ->where(['order_sn' => $id])
            ->one()->toArray();

        if (!empty($orderInfo)) {
            //组合附表信息
            $orderChildInfo = GuahaoOrderInfoModel::find()
                ->where(['order_id' => $orderInfo['id']])
                ->one()->toArray();
            if (!empty($orderChildInfo)) {
                $orderInfo = array_merge($orderInfo, $orderChildInfo);
                unset($orderInfo['order_id']);
            }

            $paiAddress = $depAddress = "";
            //获取号源中的地址
            $guahao_paiban = SnisiyaSdk::getInstance()->guahao_paiban_info(
                [
                    'doctor_id'=>$orderInfo['doctor_id'],
                    "tp_scheduling_id" => strval($orderInfo['tp_scheduling_id']),
                    "tp_platform" =>$orderInfo['tp_platform']
                ]
            );
            $paibanInfo = ArrayHelper::getValue($guahao_paiban, 'schedule_info');
            if (!empty($paibanInfo)) {
                $paiAddress = (isset($paibanInfo['visit_address']) && !empty($paibanInfo['visit_address'])) ? $paibanInfo['visit_address'] : "";
                $orderInfo['hospital_address'] = $paiAddress;
            }

            //获取科室中的地址
            if (empty($paiAddress)) {
                $docModel = DoctorModel::findOne($orderInfo['doctor_id']);
                $hosDepInfo = HospitalDepartmentRelation::find()->where(['hospital_id' => $docModel->hospital_id, 'second_department_id' => $docModel->second_department_id])->asArray()->one();
                $depAddress = (isset($hosDepInfo['address']) && !empty($hosDepInfo['address'])) ? $hosDepInfo['address'] : "";
                $orderInfo['hospital_address'] = $depAddress;
            }

            //获取医院所在医院
            $doctor_info = DoctorModel::find()->select('hospital_id')->where(['doctor_id'=>$orderInfo['doctor_id']])->one()->toArray();
            if (!empty($doctor_info)) {
                $hospitalCache = \common\models\BaseDoctorHospitals::HospitalDetail($doctor_info['hospital_id']);
                if (empty($paiAddress) && empty($depAddress)) {
                    $orderInfo['hospital_address'] = $hospitalCache['address'];
                }

                $hospitalInfo = GuahaoHospitalModel::find()
                    ->select('tp_guahao_description')
                    ->where(['hospital_id' => $doctor_info['hospital_id'], 'status' => 1])
                    ->orderBy('id desc')
                    ->asArray()
                    ->one();
                $orderInfo['tp_guahao_description'] = ArrayHelper::getValue($hospitalInfo, 'tp_guahao_description', '');
            }
            //取消时间
            $orderInfo['id'] = $orderInfo['order_sn'];
            $cancelTime = CommonFunc::getCancelTime($orderInfo['doctor_id'], $orderInfo['tp_platform'], $orderInfo['visit_time']);
            $orderInfo = array_merge($orderInfo, $cancelTime);
            return $this->jsonSuccess($orderInfo);
        } else {
            return $this->jsonError('订单不存在');
        }
    }

    /**
     * 发送短信
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/20
     */
    public function actionGuahaoSms()
    {
        $type = \Yii::$app->request->get('type');
        $order_sn = \Yii::$app->request->get('order_sn');
        if (!$order_sn) {
            return $this->jsonError('订单号不能为空');
        }
        CommonFunc::guahaoSendSms($type, $order_sn);
        return $this->jsonSuccess();
    }
}

?>