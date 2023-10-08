<?php
/**
 * Created by wangwencai.
 * @file: OrderController.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-27
 */

namespace minyingapi\controllers;


use common\libs\CommonFunc;
use common\libs\Log;
use common\models\GuahaoOrderInfoModel;
use common\models\GuahaoOrderLog;
use common\models\GuahaoOrderModel;
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
        $pageSize = ArrayHelper::getValue($params, 'limit', 10);
        $page = ArrayHelper::getValue($params, 'page', 1);

        // 民营医院下的记录
        $query = GuahaoOrderModel::find()->where([
            'tp_platform' => 13,
            'tp_hospital_code' => $this->user['min_hospital_id']
        ]);

        // 状态
        $state = ArrayHelper::getValue($params, 'state', '');
        if (strlen($state) > 0) {
            $query->andWhere(['state' => $state]);
        }

        // 时间
        if ($beginTime = strtotime(ArrayHelper::getValue($params, 'begin_time', ''))) {
            $query->andWhere(['>=', 'create_time', $beginTime]);
        }
        if ($endTime = strtotime(ArrayHelper::getValue($params, 'end_time', ''))) {
            $query->andWhere(['<=', 'create_time', strtotime(date('Y-m-d 23:59:59', $endTime))]);
        }

        // 订单号
        if ($order_sn = trim(ArrayHelper::getValue($params, 'order_sn'))) {
            $query->andWhere(['order_sn' => $order_sn]);
        }

        // 患者姓名、手机号
        if ($keyword = trim(ArrayHelper::getValue($params, 'keyword'))) {
            $query->andWhere(['or', ['like', 'patient_name', $keyword], ['like', 'mobile', $keyword]]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->select('id,order_sn,patient_name,hospital_name,doctor_name,department_name,visit_cost,visit_time,create_time,state,pay_mode'),
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        $serialize = new Serializer();
        $list = $serialize->serialize($dataProvider->getModels());
        foreach ($list as &$item) {
            $item['visit_cost'] = $item['visit_cost'] / 100;
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['state'] = GuahaoOrderModel::$state[$item['state']] ?? '';
            $item['pay_mode'] = GuahaoOrderModel::$pay_mode[$item['pay_mode']] ?? '';
        }

        $return = [
            'page' => $page,
            'pagesize' => $pageSize,
            'totalcount' => $dataProvider->pagination->totalCount,
            'list' => $list,
        ];

        return $this->jsonSuccess($return);
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
     * 取消订单号
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-28
     * @return array
     */
    public function actionCancel()
    {
        if (!Yii::$app->request->isPost) {
            return $this->jsonError('请求方式不合法');
        }
        $order_id = Yii::$app->request->post('order_id');
        if (!$order_id) {
            return $this->jsonError('order_id不能为空');
        }
        if (!$order_model = $this->findModel($order_id)) {
            return $this->jsonError('未找到订单信息');
        }

        // 是否可以取消订单
        if (!$order_model->checkCancel()) {
            return $this->jsonError($order_model->cancelError);
        }

        $order_model->state = 1; //取消
        $order_model->update_time = time(); //取消

        $transition = Yii::$app->getDb()->beginTransaction();
        try {
            if (!$order_model->save()) {
                throw new \Exception('订单取消失败，请重试');
            }

            // 记录操作原因
            $order_info_model = GuahaoOrderInfoModel::find()->where(['order_id' => $order_model->id])->limit(1)->one();
            if (!$order_info_model) {
                throw new \Exception('获取订单信息错误，请重试');
            }
            // 产品要求写死
            $order_info_model->remark = '医院操作退号';
            $order_info_model->save(false);

            // 操作人信息
            $admin_info = [
                'admin_id' => $this->user['account_id'],
                'admin_name' => $this->user['username'],
            ];
            // 添加管理后台操作日志
            if (!TbLog::addLog("{$this->user['username']}取消订单【{$order_model->order_sn}】", '民营医院取消订单', $admin_info)) {
                throw new \Exception('订单取消失败，请重试[TLogErr]');
            }

            // 添加订单历史操作日志
            if (!GuahaoOrderLog::addLog($order_model->id, GuahaoOrderLog::OPT_TYPE_HOS_CANCEL, '医院操作退号', $admin_info)) {
                throw new \Exception('订单取消失败，请重试[GHLogErr]');
            }

            // 发送短信
            if (!CommonFunc::minCancelOrderSendSms('cancel', $order_model->order_sn)) {
                Log::sendGuaHaoErrorDingDingNotice("民营医院报警-取消订单【{$order_model->order_sn}】通知用户失败\r\n错误信息：". CommonFunc::$orderSendSmsErrorMsg);
            }
            $transition->commit();
        } catch (\Exception $exception) {
            $transition->rollBack();
            return $this->jsonError($exception->getMessage());
        }

        return $this->jsonSuccess(['order_id' => $order_id]);
    }

    /**
     * 操作历史（目前只有一条退号记录）
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-01
     * @return array
     * @throws \Exception
     */
    public function actionHistory()
    {
        $params = Yii::$app->request->getQueryParams();
        $pageSize = ArrayHelper::getValue($params, 'limit', 10);
        $page = ArrayHelper::getValue($params, 'page', 1);
        $order_id = ArrayHelper::getValue($params, 'order_id');

        if (!$order_id) {
            return $this->jsonError('order_id不能为空');
        }
        if (!$order_model = $this->findModel($order_id)) {
            return $this->jsonError('未找到订单信息');
        }
        $query = GuahaoOrderLog::find()
            ->where(['order_id' => $order_model->id])
            ->select('order_id,admin_name,opt_description,create_time');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        $serialize = new Serializer();
        $list = $serialize->serialize($dataProvider->getModels());

        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
        }

        $return = [
            'page' => $page,
            'pagesize' => $pageSize,
            'totalcount' => $dataProvider->pagination->totalCount,
            'list' => $list,
        ];

        return $this->jsonSuccess($return);
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
                'tp_platform' => 13,
                'tp_hospital_code' => $this->user['min_hospital_id']
            ])->one();
        if (!$model) {
            return null;
        }
        return $model;
    }
}