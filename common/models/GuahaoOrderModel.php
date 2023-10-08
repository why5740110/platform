<?php

namespace common\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "tb_guahao_order".
 *
 * @property int $id
 * @property string $order_sn 王氏订单序列号
 * @property string $tp_order_id 第三方订单ID
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 * @property string $coo_order_id 来源方订单id
 * @property int $coo_platform 来源方平台类型(1:百度)
 * @property string $coo_patient_id 来源方患者ID
 * @property int $device_source 设备来源(1：H5，2：APP，3：小程序，4：PC)
 * @property string $tp_hospital_code 第三方医院id
 * @property string $patient_name 就诊人姓名
 * @property int $patient_id 王氏就诊人ID
 * @property int $uid 用户中心的UID
 * @property int $card_type 证件类型(1:身份证)
 * @property string $card 证件号码
 * @property string $mobile 就诊人手机号
 * @property int $famark_type 就诊类型(1:初诊 2:复诊)
 * @property int $gender 就诊人性别(1:男 2:女)
 * @property string $tp_doctor_id 第三方医生ID
 * @property int $primary_id 医生主ID
 * @property int $doctor_id 王氏医院医生ID
 * @property int $miao_doctor_id 王氏医生ID(默认是没有关联)
 * @property string $doctor_name 医生姓名
 * @property string $hospital_name 预约医院名称
 * @property string $department_name 预约科室名称
 * @property string $visit_time 就诊日期(年月日)
 * @property int $visit_nooncode 午别:1上午,2下午,3:晚上,4全天
 * @property int $visit_type 号源类型：1普通，2专家，3专科，4特需，5夜间，6会诊，7老院，8其他
 * @property int $visit_cost 挂号费,分单位制（如550实际为5.5元）
 * @property int $schedule_type 排班类型:1:挂号,2:加号
 * @property int $state 预约记录状态(0:下单成功 1:取消 2:停诊 3:已完成 4:爽约 5:待支付，6：无效，7：下单中,8:待审核)
 * @property string $state_desc 预约记录状态描述
 * @property int $pay_status 支付状态（0：无需付款，1：未支付，2：支付中，3：已支付，4：待退款，5：退款中，6：已退款）
 * @property int $pay_mode 支付方式(1在线支付，2线下支付，3无需支付)
 * @property int $create_time 预约时间
 * @property int $update_time 更新时间
 * @property int $complete_time 完成时间
 */
class GuahaoOrderModel extends \yii\db\ActiveRecord
{
    // 取消失败原因
    public $cancelError;

    ##设备
    public static $device_source = [1=>'H5',2=>'APP',3=>'小程序',4=>'PC'];
    ##预约记录状态
    public static $state = [0 => '下单成功', 1 => '取消', 2 => '停诊', 3 => '已完成', 4 => '爽约', 5 => '待支付', 6 => '无效', 7 => '下单中', 8 => '待审核'];

    ##支付状态
    public static $pay_status = [0 => '无需付款', 1 => '未支付', 2 => '支付中', 3 => '已支付', 4 => '待退款', 5 => '退款中', 6 => '已退款'];

    ##证件类型
    public static $card_type = [1 => '身份证'];

    ##号源类型
    public static $visit_type = [1 => '普通', 2 => '专家', 3 => '专科', 4 => '特需', 5 => '夜间', 6 => '会诊', 7 => '老院', 8 => '其他'];

    ##就诊时间段类型
    public static $visit_nooncode = [1 => '上午', 2 => '下午', 3 => '晚上', 4 => '全天'];

    ##就诊时间段类型
    public static $pay_mode = [1 => '在线支付', 2 => '线下支付', 3 => '无需支付'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_order';
    }

    public function attributeLabels()
    {
        return [
            'patient_name' => '患者姓名',
            'mobile' => '患者手机号',
            'card' => '证件号码'
        ];
    }

    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::conditionWhere($params);
        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::conditionWhere($params);
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    public static function conditionWhere($params, $field = '*')
    {
        $doctorQuery = self::find()->select($field);
        if (!empty($params['patient_name'])) {
            $doctorQuery->andWhere(['patient_name'=>trim($params['patient_name'])]);
        }

        if (!empty($params['doctor_name'])) {
            $doctorQuery->andWhere(['doctor_name'=>trim($params['doctor_name'])]);
        }

        if (!empty($params['hospital'])) {
            $doctorQuery->andWhere(['hospital_name'=>trim($params['hospital'])]);
        }

        if (!empty($params['state']) && $params['state']==5) {
            $doctorQuery->andWhere(['state'=>0]);
        }elseif(!empty($params['state'])){
            $doctorQuery->andWhere(['state'=>$params['state']]);
        } else {
            $doctorQuery->andWhere(['<>', 'state', '6']);
        }
        if (isset($params['tp_platform']) && !empty($params['tp_platform'])) {
            $doctorQuery->andWhere(['tp_platform'=>$params['tp_platform']]);
        }

        if (isset($params['device_source']) && !empty($params['device_source'])) {
            $doctorQuery->andWhere(['device_source'=>$params['device_source']]);
        }

        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $doctorQuery->andWhere(['mobile'=>trim($params['mobile'])]);
        }

        if (isset($params['order_sn']) && !empty($params['order_sn'])) {
            $doctorQuery->andWhere(['order_sn'=>trim($params['order_sn'])]);
        }

        if (isset($params['tp_order_id']) && !empty($params['tp_order_id'])) {
            $doctorQuery->andWhere(['tp_order_id'=>trim($params['tp_order_id'])]);
        }

        if (isset($params['coo_order_id']) && !empty($params['coo_order_id'])) {
            $doctorQuery->andWhere(['coo_order_id'=>trim($params['coo_order_id'])]);
        }

        //开通时间
        if(isset($params['create_time']) and $params['create_time'] != ''){
            $create_time_arr = explode(' - ', $params['create_time']);
            $doctorQuery->andWhere(['>=', 'create_time', strtotime(trim($create_time_arr[0]))]);
            $doctorQuery->andWhere(['<=', 'create_time', strtotime(trim($create_time_arr[1]) . ' 23:59:59')]);
        }

        //就诊科室
        if (isset($params['department_name']) && !empty($params['department_name'])) {
            $doctorQuery->andWhere(['like', 'department_name', trim($params['department_name'])]);
        }
        //就诊时间
        if(isset($params['visit_time']) and $params['visit_time'] != ''){
            $create_time_arr = explode(' - ', $params['visit_time']);
            $doctorQuery->andWhere(['>=', 'visit_time', trim($create_time_arr[0])]);
            $doctorQuery->andWhere(['<=', 'visit_time', trim($create_time_arr[1]) . ' 23:59:59']);
        }

        //合作平台
        if (isset($params['tp_coo_platform']) && !empty($params['tp_coo_platform'])) {
            $doctorQuery->andWhere(['coo_platform' => $params['tp_coo_platform']]);
        }

        //是否复诊
        if (isset($params['famark_type']) && !empty($params['famark_type'])) {
            $doctorQuery->andWhere(['famark_type' => $params['famark_type']]);
        }

        //性别
        if (isset($params['gender']) && !empty($params['gender'])) {
            $doctorQuery->andWhere(['gender' => $params['gender']]);
        }

        //就诊疾病
        if (isset($params['symptom']) && !empty($params['symptom'])) {
            $orderInfo = GuahaoOrderInfoModel::find()->select(['order_id'])->where(['like', 'symptom', $params['symptom']])->asArray()->all();
            $orderIds = [];
            foreach ($orderInfo as $val) {
                $orderIds[] = $val['order_id'];
            }
            $doctorQuery->andWhere(['in', 'id', $orderIds]);
        }

        return $doctorQuery;
    }

    /**
     * 获取取消超时未支付订单
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function getTimeoutList($params)
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['pageSize']) ? intval($params['pageSize']) : 10;
        $query = self::find()
            ->select('id')
            ->where(['state' => 5])
            ->andWhere(['pay_status' => [1, 2]])
            ->andWhere(['<', 'create_time', strtotime('-16 minute')]);

        $totalCountQuery = clone $query;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => $pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $query->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time asc')->asArray()->all();
        return $posts;
    }

    /**
     * 定时取消未支付订单
     * @param $id
     * @return false|int
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function orderPayTimeout($id)
    {
        $order_id = 0;
        $model = self::findOne($id);
        if (!empty($model)) {
            if ($model->state == 5 && in_array($model->pay_status, [1, 2]) && $model->create_time < strtotime('-16 minute')) {
                $model->state = 1;
                $model->save();
                $order_id = $model->id;
            }
        } else {
            return false;
        }

        //更新附表
        if (!empty($order_id)) {
            $infoModel = GuahaoOrderInfoModel::find()
                ->where(['order_id' => $order_id])
                ->one();

            if (!empty($infoModel)) {
                $infoModel->cancel_time = time();
                $infoModel->save();
            }
        }
        return $order_id;
    }

    /**
     * 获取过期订单
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function getExpiredOrder($params)
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['pageSize']) ? intval($params['pageSize']) : 10;
        $query = self::find()
            ->select('id')
            ->where(['state' => 0])
            ->andWhere(['pay_status' => [0, 3]])
            ->andWhere(['<', 'visit_time', date('Y-m-d')]);

        $totalCountQuery = clone $query;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => $pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $query->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time asc')->asArray()->all();
        return $posts;
    }

    /**
     * 更新过期订单状态
     * @param $id
     * @return false|int
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function updateExpiredOrder($id)
    {
        $order_id = 0;
        $model = self::findOne($id);
        if (!empty($model)) {
            if ($model->state == 0 && in_array($model->pay_status, [0, 3]) && $model->visit_time < date('Y-m-d')) {
                if (empty($model->complete_time)) {
                    $model->complete_time = time();
                }
                $model->state = 3;
                $model->save();
                $order_id = $model->id;
            }
        } else {
            return false;
        }
        return $order_id;
    }

    /**
     * 订单时间轴
     * @param $state
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-03
     * @return array
     */
    public static function timeline($state)
    {
        //
        $timeline['steps'][] = ['title' => '创建订单'];
        // 订单创建成功
        if ($state == 0) {
            $timeline['active'] = 2;
            $timeline['steps'][] = ['title' => '下单成功',];
            $timeline['steps'][] = ['title' => '订单完成',];
            return $timeline;
        }
        // 订单完成的情况
        if ($state == 3) {
            $timeline['active'] = 3;
            $timeline['steps'][] = ['title' => '下单成功',];
            $timeline['steps'][] = ['title' => '订单完成',];
            return $timeline;
        }
        // 订单终止情况
        if (in_array($state, [1, 2, 4, 6])) {
            $timeline['active'] = 2;
            $timeline['steps'][] = ['title' => self::$state[$state] ?? '订单无效'];
            return $timeline;
        } else {
            $timeline['active'] = 2;
            $timeline['steps'][] = ['title' => self::$state[$state] ?? '订单无效',];
            $timeline['steps'][] = ['title' => '订单完成', 'active' => 0];
            return $timeline;
        }
    }

    /**
     * 取消规则：下单成功且未过就诊时间的订单
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-06
     * @return boolean
     */
    public function checkCancel()
    {
        // 状态判断
        if ($this->state != 0) {
            $this->cancelError = '订单已经是取消状态';
            return false;
        }

        // 附表没有信息的异常订单
        $order_info = GuahaoOrderInfoModel::findOne(['order_id' => $this->id]);
        if (!$order_info) {
            $this->cancelError = '订单附表信息异常';
            return false;
        }

        // 防止结束时间为空，默认上午时间12点截止，下午18点截止, 全天 24点之前
        if ($this->visit_nooncode == 1) {
            $visit_end_time = $order_info->visit_endtime ?: '12:00:00';
        } else if ($this->visit_nooncode == 2) {
            $visit_end_time = $order_info->visit_endtime ?: '18:00:00';
        } else {
            $visit_end_time = $order_info->visit_endtime ?: '23:59:59';
        }

        if (time() > strtotime($this->visit_time . ' ' . $visit_end_time)) {
            $this->cancelError = '订单已经超出取消时间';
            return false;
        }

        return true;
    }
}
