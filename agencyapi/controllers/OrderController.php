<?php
/**
 * Created by wangwencai.
 * @file: OrderController.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-27
 */

namespace agencyapi\controllers;


use common\libs\CommonFunc;
use common\models\GuahaoOrderInfoModel;
use common\models\GuahaoOrderModel;
use common\models\minying\MinHospitalModel;
use common\models\TbLog;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Serializer;

class OrderController extends CommonController
{
    /**
     * 订单列表
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-27
     * @return array
     * @throws \Exception
     */
    public function actionList()
    {
        $params = Yii::$app->request->getQueryParams();
        $page_size = ArrayHelper::getValue($params, 'limit', 10);
        $page = ArrayHelper::getValue($params, 'page', 1);

        // 代理商属下医院id
        $hospital_ids = MinHospitalModel::find()
            ->where(['agency_id' => $this->user['agency_id']])
            ->select('min_hospital_id')
            ->column();
        // 所属民营医院下的记录
        $query = GuahaoOrderModel::find()
            ->where([
                'tp_platform' => 13,
                'tp_hospital_code' => $hospital_ids
            ]);

        // 状态
        $state = ArrayHelper::getValue($params, 'state', '');
        if (strlen($state) > 0) {
            $query->andWhere(['state' => $state]);
        }

        // 时间
        if ($begin_time = strtotime(ArrayHelper::getValue($params, 'begin_time', ''))) {
            $query->andWhere(['>=', 'create_time', $begin_time]);
        }
        if ($end_time = strtotime(ArrayHelper::getValue($params, 'end_time', ''))) {
            $query->andWhere(['<=', 'create_time', strtotime(date('Y-m-d 23:59:59', $end_time))]);
        }

        // 订单号
        if ($order_sn = trim(ArrayHelper::getValue($params, 'order_sn'))) {
            $query->andWhere(['order_sn' => $order_sn]);
        }

        // 患者姓名、手机号
        if ($keyword = trim(ArrayHelper::getValue($params, 'keyword'))) {
            $query->andWhere(['or', ['like', 'patient_name', $keyword], ['like', 'mobile', $keyword]]);
        }

        // 医院名称
        if ($hospital_name = trim(ArrayHelper::getValue($params, 'hospital_name'))) {
            $query->andWhere(['hospital_name' => $hospital_name]);
        }

        $data_provider = new ActiveDataProvider([
            'query' => $query->select('id,order_sn,patient_name,hospital_name,doctor_name,department_name,visit_cost,visit_time,create_time,state,pay_mode'),
            'pagination' => [
                'pageSize' => $page_size,
                'page' => $page - 1,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        $serialize = new Serializer();
        $list = $serialize->serialize($data_provider->getModels());
        foreach ($list as &$item) {
            $item['visit_cost'] = $item['visit_cost'] / 100;
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['state'] = GuahaoOrderModel::$state[$item['state']] ?? '';
            $item['pay_mode'] = GuahaoOrderModel::$pay_mode[$item['pay_mode']] ?? '';
        }

        $return = [
            'page' => $page,
            'pagesize' => $page_size,
            'totalcount' => $data_provider->pagination->totalCount,
            'list' => $list,
        ];

        return $this->jsonSuccess($return);
    }

    /**
     * 订单详情
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-27
     * @return array
     */
    public function actionDetail()
    {
        $order_id = Yii::$app->request->get('order_id');
        if (!$order_id) {
            return $this->jsonError('order_id不能为空');
        }
        if (!$order_model = $this->findModel($order_id)) {
            return $this->jsonError('未找到订单信息');
        }

        $order_info_model = GuahaoOrderInfoModel::find()
            ->where(['order_id' => $order_model->id])
            ->limit(1)
            ->one();

        $detail = [
            'id' => $order_model->id,
            'order_sn' => $order_model->order_sn,
            'doctor_name' => $order_model->doctor_name,
            'visit_cost' => $order_model->visit_cost / 100,
            'patient_name' => $order_model->patient_name,
            'gender' => $order_model->gender == 1 ? '男' : '女',
            'age' => $order_model->card_type == 1 ? (CommonFunc::getAgeByIdCard($order_model->card)) : '',
            'mobile' => substr_replace($order_model->mobile, '****', 3, 4),
            'card_type' => GuahaoOrderModel::$card_type[$order_model->card_type] ?? '',
            'card' => substr_replace($order_model->card, '**********', 4, 10),
            'visit_time' => $order_model->visit_time,
            'state' => GuahaoOrderModel::$state[$order_model->state] ?? '',
            'hospital_name' => $order_model->hospital_name,
            'visit_type' => GuahaoOrderModel::$visit_type[$order_model->visit_type] ?? '',
            'department_name' => $order_model->department_name,
            'create_time' => date('Y-m-d H:i:s', $order_model->create_time),
            'symptom' => $order_info_model->symptom,
            'taketime_desc' => $order_info_model->taketime_desc,
            'visit_starttime' => $order_info_model->visit_starttime,
            'visit_endtime' => $order_info_model->visit_endtime,
            'visit_number' => $order_info_model->visit_number,
            'allow_cancel' => $order_model->checkCancel() ? 1 : 0,
            'remark' => $order_info_model->remark,
            'timeline' => GuahaoOrderModel::timeline($order_model->state)
        ];

        return $this->jsonSuccess($detail);
    }

    /**
     * 订单状态map
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-03
     * @return array
     */
    public function actionStateMap()
    {
        $map = [];
        foreach (GuahaoOrderModel::$state as $value => $item) {
            $map[] = [
                'value' => $value,
                'text' => $item
            ];
        }
        return $this->jsonSuccess($map);
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-17
     * @return array
     * @throws \Exception
     */
    public function actionSensitive()
    {
        $valid_fields = ['patient_name', 'card', 'mobile'];
        $params = Yii::$app->getRequest()->getQueryParams();
        if (!$id = ArrayHelper::getValue($params, 'order_id')) {
            return $this->jsonError('缺少参数order_id');
        }
        if (!$field = ArrayHelper::getValue($params, 'field')) {
            return $this->jsonError('缺少参数field');
        }

        if (!in_array($field, $valid_fields)) {
            return $this->jsonError('field参数不合法');
        }
        if (!$model = $this->findModel($id)) {
            return $this->jsonError('订单信息未找到');
        }

        $info = "{$this->user['username']}(account_id:{$this->user['account_id']}) 查看了订单信息；字段为：" . $model->getAttributeLabel($field);
        TbLog::addLog($info, '民营医院隐秘信息查看', ['admin_id' => $this->user['account_id'], 'admin_name' => $this->user['username']]);

        $data['info'] = ArrayHelper::getValue($model, $field, '');
        return $this->jsonSuccess($data);
    }

    /**
     * @param $order_id
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-27
     * @return array|null| GuahaoOrderModel
     */
    protected function findModel($order_id)
    {
        $model = GuahaoOrderModel::find()
            ->where([
                'id' => $order_id,
                'tp_platform' => 13
            ])->one();
        if (!$model) {
            return null;
        }
        // 判断是否为当前代理商
        if (!MinHospitalModel::find()->where(['agency_id' => $this->user['agency_id'], 'min_hospital_id' => $model->tp_hospital_code])->limit(1)->one()) {
            return null;
        }
        return $model;
    }
}