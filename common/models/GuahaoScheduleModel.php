<?php

namespace common\models;

use common\libs\HashUrl;
use common\models\TbLog;
use common\sdks\snisiya\SnisiyaSdk;
use common\models\HospitalDepartmentRelation;
use common\libs\CommonFunc;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_guahao_schedule".
 *
 * @property int $scheduling_id 排班ID
 * @property string $tp_scheduling_id 第三方排班ID
 * @property string $tp_section_id 第三方时段ID
 * @property int $tp_platform 第三方平台类型(1:河南，2:南京，3:好大夫 4:王氏)
 * @property int $primary_id 王氏医院医生主ID
 * @property int $doctor_id 王氏医院医生ID
 * @property string $realname 医生姓名
 * @property int $scheduleplace_id 出诊地ID
 * @property string $scheduleplace_name 出诊医院(工作室)
 * @property string $tp_department_id 第三方科室ID
 * @property string $department_name 出诊科室
 * @property string $visit_time 就诊日期(年月日)
 * @property int $visit_nooncode 午别 1:上午 2：下午
 * @property string $visit_starttime 就诊开始时间
 * @property string $visit_endtime 就诊结束时间
 * @property int $visit_valid_time 可预约截止时间戳
 * @property int $visit_type 号源类型：1普通，2专家，3专科，4特需，5夜间，6会诊，7老院，8其他
 * @property string $visit_address 就诊地址
 * @property int $visit_cost 挂号费,分单位制
 * @property int $referral_visit_cost 复诊挂号费,分单位制
 * @property int $visit_cost_original 挂号费原价,分单位制
 * @property int $referral_visit_cost_original 复诊挂号费原价,分单位制
 * @property int $schedule_available_count 剩余号源数量，-1表示无限制
 * @property int $schedule_type 排班类型:1:挂号,2:加号
 * @property int $pay_mode 支付方式(1在线支付，2线下支付，3无需支付)
 * @property int $status 出诊状态(0约满 1可约 2停诊 3已过期 4其他)
 * @property int $first_practice 是否是第一执业0否1是
 * @property string $extended 扩展字段，兼容不同来源号源
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class GuahaoScheduleModel extends \yii\db\ActiveRecord
{
    public static $visit_nooncode = [0 => '', 1 => '上午', 2 => '下午', 3 => '晚上', 4 => '全天'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_platform', 'primary_id', 'doctor_id', 'scheduleplace_id', 'visit_nooncode', 'visit_valid_time', 'visit_type', 'visit_cost', 'referral_visit_cost', 'visit_cost_original', 'referral_visit_cost_original', 'schedule_available_count', 'schedule_type', 'pay_mode', 'first_practice', 'create_time', 'update_time'], 'integer'],
            [['visit_time'], 'required'],
            [['visit_time', 'tp_doctor_id', 'hospital_id', 'frist_department_id', 'second_department_id', 'tp_scheduleplace_id', 'tp_frist_department_id', 'tp_frist_department_name', 'status', 'admin_id', 'admin_name'], 'safe'],
            [['tp_scheduling_id', 'tp_section_id', 'tp_department_id'], 'string', 'max' => 32],
            [['realname'], 'string', 'max' => 30],
            [['scheduleplace_name', 'department_name'], 'string', 'max' => 100],
            [['visit_starttime', 'visit_endtime'], 'string', 'max' => 20],
            [['visit_address', 'extended'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scheduling_id'                => 'Scheduling ID',
            'tp_scheduling_id'             => 'Tp Scheduling ID',
            'tp_section_id'                => 'Tp Section ID',
            'tp_platform'                  => 'Tp Platform',
            'primary_id'                   => 'Primary ID',
            'doctor_id'                    => 'Doctor ID',
            'realname'                     => 'Realname',
            'scheduleplace_id'             => 'Scheduleplace ID',
            'scheduleplace_name'           => 'Scheduleplace Name',
            'tp_frist_department_id'       => 'tp_frist_department_id',
            'tp_frist_department_name'     => 'tp_frist_department_name',
            'tp_department_id'             => 'Tp Department ID',
            'department_name'              => 'Department Name',
            'visit_time'                   => 'Visit Time',
            'visit_nooncode'               => 'Visit Nooncode',
            'visit_starttime'              => 'Visit Starttime',
            'visit_endtime'                => 'Visit Endtime',
            'visit_valid_time'             => 'Visit Valid Time',
            'visit_type'                   => 'Visit Type',
            'visit_address'                => 'Visit Address',
            'visit_cost'                   => 'Visit Cost',
            'referral_visit_cost'          => 'Referral Visit Cost',
            'visit_cost_original'          => 'Visit Cost Original',
            'referral_visit_cost_original' => 'Referral Visit Cost Original',
            'schedule_available_count'     => 'Schedule Available Count',
            'schedule_type'                => 'Schedule Type',
            'pay_mode'                     => 'Pay Mode',
            'status'                       => 'Status',
            'first_practice'               => 'first_practice',
            'extended'                     => 'extended',
            'create_time'                  => 'Create Time',
            'update_time'                  => 'Update Time',
            'admin_id'                     => 'Admin ID',
            'admin_name'                   => 'Admin Name',
        ];
    }

    /**
     * 如果来源为王氏，保存后更新tp_scheduling_id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @param   [type]     $insert            [description]
     * @param   [type]     $changedAttributes [description]
     * @return  [type]                        [description]
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->tp_platform == 4) {
                $model                   = self::findOne($this->scheduling_id);
                $model->tp_scheduling_id = HashUrl::getGuahaoTpIdEncode($this->scheduling_id);
                $model->save();
            }
        }
    }

    /**
     * 更新排班缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-01
     * @version 1.0
     * @param   string     $scheduling_id [多条用逗号分割]
     * @return  [type]                    [description]
     */
    public static function updateScheduleCache($scheduling_id = '', $tp_platform = 0)
    {
        $snisiyaSdk = new SnisiyaSdk();
        if (is_array($scheduling_id)) {
            $scheduling_id = array_unique($scheduling_id);
            $scheduling_id = implode(',', $scheduling_id);
        }
        $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id, 'tp_platform' => $tp_platform]);
    }

    /**
     * 更新医院，医院科室，医生缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-05
     * @version 1.0
     * @param   string     $hospital_id [description]
     * @param   integer    $doctor_id   [description]
     */
    public static function Up_Hospital_doctor_department_Cache($hospital_id='',$doctor_id = 0)
    {
        if ($hospital_id) {
            CommonFunc::UpHospitalCache($hospital_id);
            HospitalDepartmentRelation::hospitalDepartment($hospital_id,true);
        }
       
        if ($doctor_id) {
           CommonFunc::UpdateInfo($doctor_id);
        }
    }

    /**
     * 根据医生取消排班
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-05
     * @version 1.0
     * @param   string     $doctor_id [description]
     * @return  [type]                [description]
     */
    public static function cancelPaibanByDoctorID($doctor_id='')
    {
        $week_list = array_keys(CommonFunc::get_week(time()));
        $start_time = $week_list[0];
        $paiban_list = self::find()->where(['doctor_id'=>$doctor_id,'status'=>1])->andWhere(['>=', 'visit_time', $start_time])->asArray()->all();
        if ($paiban_list) {
            $scheduling_ids = array_column($paiban_list, 'scheduling_id');
            $tp_scheduleplace_id = array_column($paiban_list, 'tp_scheduleplace_id');
            self::updateAll(['status' => -1],['scheduling_id' => $scheduling_ids]);
            self::updateScheduleCache($scheduling_ids);
            $tp_scheduleplace_id = array_unique($tp_scheduleplace_id);
            foreach ($tp_scheduleplace_id as $hospital_id) {
                self::Up_Hospital_doctor_department_Cache($hospital_id);
            }
            CommonFunc::UpdateInfo($doctor_id);
        }
    }

    /**
     * 批量增加挂号信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-08
     * @version 1.0
     * @param   array      $data [description]
     */
    public static function addMultipleData($data = [])
    {
        $sub_type       = ArrayHelper::getValue($data, 'sub_type', 1);
        $nodes          = $data['nodes'] ?? [];
        $res            = ['code' => 0];
        $scheduling_ids = [];
        $tp_scheduleplace_id = 0;
        $doctor_id = ArrayHelper::getValue($data, 'doctor_id',0);
        $transition = self::getDb()->beginTransaction();
        try {
            ##先全部更新为无号
            foreach ($nodes as $no_date => $no_val) {
                $node_where = [
                    'tp_platform'          => ArrayHelper::getValue($data, 'tp_platform', 4),
                    'doctor_id'            => ArrayHelper::getValue($data, 'doctor_id'),
                    'tp_doctor_id'         => ArrayHelper::getValue($data, 'doctor_id'),
                    'visit_time'           => $no_date,
                    'hospital_id'          => ArrayHelper::getValue($data, 'hospital_id', ''),
                    'frist_department_id'  => ArrayHelper::getValue($data, 'frist_department_id', ''),
                    'second_department_id' => ArrayHelper::getValue($data, 'second_department_id', ''),
                    'scheduleplace_id'     => ArrayHelper::getValue($data, 'scheduleplace_id', 0),
                ];
                $node_model_list = self::find()->where($node_where)->all();
                if ($node_model_list) {
                    foreach ($node_model_list as $node_model) {
                        $node_model->status = -1;
                        $node_model->save();
                        $scheduling_ids[] = $node_model->scheduling_id;
                        $tp_scheduleplace_id  = $node_model->tp_scheduleplace_id;
                    }
                }
            }
            $guahao_model = new self();
            foreach ($nodes as $k_date => $item) {
                if (!isset($item['visit_nooncode'])) {
                    continue;
                }
                foreach ($item['visit_nooncode'] as $node_item) {
                    $where = [
                        'tp_platform'          => ArrayHelper::getValue($data, 'tp_platform', 4),
                        'doctor_id'            => ArrayHelper::getValue($data, 'doctor_id'),
                        'tp_doctor_id'         => ArrayHelper::getValue($data, 'doctor_id'),
                        'visit_time'           => $k_date,
                        'visit_nooncode'       => $node_item,
                        'hospital_id'          => ArrayHelper::getValue($data, 'hospital_id', ''),
                        'frist_department_id'  => ArrayHelper::getValue($data, 'frist_department_id', ''),
                        'second_department_id' => ArrayHelper::getValue($data, 'second_department_id', ''),
                        'scheduleplace_id'     => ArrayHelper::getValue($data, 'scheduleplace_id', 0),
                    ];
                    $_model = self::find()->where($where)->one();
                    if ($_model) {
                        $logInfo    = [];
                        $attributes = [
                            'visit_nooncode'           => $node_item,
                            'visit_cost'               => ArrayHelper::getValue($data, 'visit_cost') ? (int) (ArrayHelper::getValue($data, 'visit_cost', 0) * 100) : 0,
                            'status'                   => 1,
                            'update_time'              => time(),
                            'tp_frist_department_id'   => ArrayHelper::getValue($data, 'tp_frist_department_id', ''),
                            'tp_frist_department_name' => ArrayHelper::getValue($data, 'tp_frist_department_name', ''),
                            'tp_department_id'         => ArrayHelper::getValue($data, 'tp_department_id', ''),
                            'department_name'          => ArrayHelper::getValue($data, 'department_name', ''),
                        ];
                        if ($sub_type == 0) {
                            $attributes['status']         = -1;
                            $attributes['visit_nooncode'] = $node_item;
                            $editContent                  = $data['admin_name'] . "取消了医生id:{$data['doctor_id']};排班时间:{$k_date};的排班";
                            TbLog::addLog($editContent, '取消医生排班');
                        }
                        foreach ($attributes as $key => $value) {
                            $_model->$key = $value;
                        }
                        
                        /*$old_department_name = $_model->getOldAttribute('department_name');
                        if ($old_department_name != $_model->getAttribute('department_name')) {
                            $logInfo[] = ["科室", $old_department_name, $_model->getAttribute('department_name')];
                        }*/
                        $old_visit_cost = $_model->getOldAttribute('visit_cost');
                        if ($old_visit_cost != $_model->getAttribute('visit_cost')) {
                            $logInfo[] = ["挂号费", $old_visit_cost ? ceil($old_visit_cost / 100) : 0, $_model->getAttribute('visit_cost') ? ceil($_model->getAttribute('visit_cost') / 100) : 0];
                        }
                        $old_visit_nooncode = $_model->getOldAttribute('visit_nooncode');
                        if ($old_visit_nooncode != $_model->getAttribute('visit_nooncode')) {
                            $logInfo[] = ["挂号午别", GuahaoScheduleModel::$visit_nooncode[$old_visit_nooncode] ?? '', GuahaoScheduleModel::$visit_nooncode[$_model->getAttribute('visit_nooncode')] ?? ''];
                        }
                        if ($logInfo) {
                            $editContent = $data['admin_name'] . "修改了医生id:{$data['doctor_id']};排班时间:{$k_date};的排班";
                            $editContent .= TbLog::formatLog($logInfo);
                            TbLog::addLog($editContent, '修改医生排班');
                        }
                        $res = $_model->save();
                        if (!$res) {
                            throw new \Exception(json_encode($_model->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                        $scheduling_ids[] = $_model->scheduling_id;
                        $tp_scheduleplace_id  = $_model->tp_scheduleplace_id;

                    } else {
                        if ($sub_type == 0) {
                            continue;
                        }
                        $attributes = [
                            'tp_scheduling_id'         => (string) md5(time() . mt_rand(1, 99999)),
                            'tp_section_id'            => '',
                            'tp_platform'              => ArrayHelper::getValue($data, 'tp_platform', 4),
                            'doctor_id'                => ArrayHelper::getValue($data, 'doctor_id', 0),
                            'tp_doctor_id'             => ArrayHelper::getValue($data, 'doctor_id', 0),
                            'realname'                 => ArrayHelper::getValue($data, 'realname', ''),
                            'hospital_id'              => ArrayHelper::getValue($data, 'hospital_id', ''),
                            'frist_department_id'      => ArrayHelper::getValue($data, 'frist_department_id', ''),
                            'second_department_id'     => ArrayHelper::getValue($data, 'second_department_id', ''),
                            'scheduleplace_id'         => ArrayHelper::getValue($data, 'scheduleplace_id', 0),
                            'scheduleplace_name'       => ArrayHelper::getValue($data, 'scheduleplace_name', ''),
                            'tp_scheduleplace_id'      => ArrayHelper::getValue($data, 'tp_scheduleplace_id', ''),
                            'tp_frist_department_id'   => ArrayHelper::getValue($data, 'tp_frist_department_id', ''),
                            'tp_frist_department_name' => ArrayHelper::getValue($data, 'tp_frist_department_name', ''),
                            'tp_department_id'         => ArrayHelper::getValue($data, 'tp_department_id', ''),
                            'department_name'          => ArrayHelper::getValue($data, 'department_name', ''),
                            'visit_time'               => $k_date,
                            'visit_nooncode'           => $node_item,
                            'visit_type'               => ArrayHelper::getValue($data, 'visit_type', 1),
                            'visit_address'            => ArrayHelper::getValue($data, 'visit_address', ''),
                            'visit_cost'               => ArrayHelper::getValue($data, 'visit_cost') ? (int) (ArrayHelper::getValue($data, 'visit_cost', 0) * 100) : 0,
                            'schedule_available_count' => -1,
                            'schedule_type'            => 2,//类型为加号
                            'pay_mode'                 => 1,
                            'status'                   => 1,
                            'first_practice'           => ArrayHelper::getValue($data, 'first_practice', 0),
                            'create_time'              => time(),
                            'update_time'              => time(),
                            'admin_id'                 => ArrayHelper::getValue($data, 'admin_id', 0),
                            'admin_name'               => ArrayHelper::getValue($data, 'admin_name', ''),
                        ];
                        $_model = clone $guahao_model;
                        $_model->setAttributes($attributes);
                        $res = $_model->save();
                        if (!$res) {
                            throw new \Exception(json_encode($_model->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                        $scheduling_ids[] = $_model->attributes['scheduling_id'];
                        $tp_scheduleplace_id  = $_model->attributes['tp_scheduleplace_id'];
                        $editContent      = $data['admin_name'] . "添加了医生id:{$data['doctor_id']};排班时间:{$k_date};的排班";
                        TbLog::addLog($editContent, '设置医生排班');
                    }
                }

            }
            $transition->commit();
            $res = ['code' => 0];
        } catch (\Exception $e) {
            $transition->rollBack();
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . ' error');
            $res = ['code' => 1, 'msg' => $e->getMessage()];
        }
        if (isset($res['code']) && $res['code'] == 0) {
            self::updateScheduleCache($scheduling_ids);
            self::Up_Hospital_doctor_department_Cache($tp_scheduleplace_id,$doctor_id);
            
        }
        return $res;

    }

    /**
     * 获取过期列表
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function getExpiredList($params)
    {
        $page     = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['pageSize']) ? intval($params['pageSize']) : 10;
        $query    = self::find()
            ->select('scheduling_id')
            ->where(['status' => [0, 1]])
            ->andWhere(['<', 'visit_valid_time', time()]);

        $totalCountQuery = clone $query;
        $totalCount      = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => $pageSize,
        ]);
        $pageObj->setPage($page - 1);
        $posts = $query->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time asc')->asArray()->all();
        return $posts;
    }

    /**
     * 更新过期排班状态
     * @param $id
     * @return false|int|mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public static function updateExpiredSchedule($id)
    {
        $Schedule = 0;
        $model    = self::find()
            ->where(['scheduling_id' => $id])
            ->one();

        if (!empty($model)) {
            if (in_array($model->status, [0, 1]) && $model->visit_valid_time < time()) {
                $model->status = 3;
                $model->save();
                $Schedule = $model->scheduling_id;
            }
        } else {
            return false;
        }
        return $Schedule;
    }

    /**
     * 根据医生ID和来源删除排班
     * @param $tp_doctor_id
     * @param $doctor_id
     * @param $tp_platform
     * @return bool
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/9
     */
    public static function deleteByDoctorId($tp_doctor_id, $doctor_id, $tp_platform)
    {
        if (empty($tp_doctor_id) || empty($doctor_id) || empty($tp_platform)) {
            return false;
        }
        self::updateAll(
            ['status' => 4],
            [
                'tp_doctor_id' => $tp_doctor_id,
                'doctor_id'    => $doctor_id,
                'tp_platform'  => $tp_platform,
            ]
        );

        $scheduling_id = self::find()->select('scheduling_id')->where([
            'tp_doctor_id' => $tp_doctor_id,
            'doctor_id'    => $doctor_id,
            'tp_platform'  => $tp_platform,
        ])->asArray()->column();
        if (!empty($scheduling_id)) {
            $scheduling_id = implode(',', $scheduling_id);
            //更新排班缓存
            $snisiyaSdk = new SnisiyaSdk();
            $snisiyaSdk->updateScheduleCache(['scheduling_id' => $scheduling_id]);
        }

        return true;
    }

    /**
     * 根据第三方医生ID和来源和就诊时间段获取排班ID
     * @param $tp_platform
     * @param $tp_doctor_id
     * @param $tp_scheduleplace_id
     * @param $visit_time
     * @param $visit_nooncode
     * @param $status
     * @return bool
     * @author wanghongying <wanghongying@yuanxin-inc.com>
     * @date 2022/07/20
     */
    public static function getScheduleByTpDoctorId($param=[])
    {
        $query = self::find()->select(['scheduling_id','status', 'doctor_id'])->where(['tp_platform' => $param['tp_platform']]);

        if (isset($param['tp_scheduleplace_id']) && !empty($param['tp_scheduleplace_id'])) {
            $query->andWhere(['tp_scheduleplace_id' => $param['tp_scheduleplace_id']]);
        }

        if (isset($param['tp_department_id']) && !empty($param['tp_department_id'])) {
            $query->andWhere(['tp_department_id' => $param['tp_department_id']]);
        }

        if (isset($param['tp_doctor_id']) && !empty($param['tp_doctor_id'])) {
            $query->andWhere(['tp_doctor_id' => $param['tp_doctor_id']]);
        }

        if (isset($param['visit_time']) && !empty($param['visit_time'])) {
            $query->andWhere(['visit_time' => $param['visit_time']]);
        }

        if (isset($param['visit_nooncode']) && !empty($param['visit_nooncode'])) {
            $query->andWhere(['visit_nooncode' => $param['visit_nooncode']]);
        }

        if (isset($param['status']) && !empty($param['status'])) {
            if (is_array($param['status'])) {
                $query->andWhere(['IN', 'status', $param['status']]);
            } else {
                $query->andWhere(['status' => $param['status']]);
            }
        }
        $scheduling = $query->asArray()->all();
        return !empty($scheduling) ? $scheduling : [];
    }

    /**
     *  获取extended 扩展字段
     * @param $tp_doctor_id
     * @param $tp_platform
     * @param $tp_scheduling_id
     * @return false|string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-10-14
     */
    public static function getExtended($tp_doctor_id, $tp_platform, $tp_scheduling_id)
    {
        $extended = self::findOne([
            'tp_doctor_id' => $tp_doctor_id,
            'tp_platform'  => $tp_platform,
            'tp_scheduling_id'  => $tp_scheduling_id,
        ]);
        if($extended){
            return $extended->extended;
        }else{
            return false;
        }
    }

    /**
     * 添加排班
     * @return false|string
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-08-01
     */
    public static function addSchedule($data)
    {
        $_model = new self();
        $attributes = [
            'tp_scheduling_id'         => (string) md5(time() . mt_rand(1, 99999)),
            'tp_platform'              => ArrayHelper::getValue($data, 'tp_platform', 13),//默认13 民营医院
            'doctor_id'                => ArrayHelper::getValue($data, 'doctor_id', 0),
            'primary_id'               => ArrayHelper::getValue($data, 'primary_id', 0),
            'tp_doctor_id'             => ArrayHelper::getValue($data, 'tp_doctor_id', 0),
            'realname'                 => ArrayHelper::getValue($data, 'realname', ''),
            'hospital_id'              => ArrayHelper::getValue($data, 'hospital_id', ''),
            'frist_department_id'      => ArrayHelper::getValue($data, 'frist_department_id', ''),
            'second_department_id'     => ArrayHelper::getValue($data, 'second_department_id', ''),
            'scheduleplace_id'         => ArrayHelper::getValue($data, 'scheduleplace_id', 0),
            'scheduleplace_name'       => ArrayHelper::getValue($data, 'scheduleplace_name', ''),
            'tp_scheduleplace_id'      => ArrayHelper::getValue($data, 'tp_scheduleplace_id', ''),
            'tp_frist_department_id'   => ArrayHelper::getValue($data, 'tp_frist_department_id', ''),
            'tp_frist_department_name' => ArrayHelper::getValue($data, 'tp_frist_department_name', ''),
            'tp_department_id'         => ArrayHelper::getValue($data, 'tp_department_id', ''),
            'department_name'          => ArrayHelper::getValue($data, 'department_name', ''),
            'visit_time'               => ArrayHelper::getValue($data, 'visit_time', ''),
            'visit_nooncode'           => ArrayHelper::getValue($data, 'visit_nooncode', ''),
            'tp_section_id'            => ArrayHelper::getValue($data, 'tp_section_id', ''),
            'visit_starttime'          => ArrayHelper::getValue($data, 'visit_starttime', ''),
            'visit_endtime'            => ArrayHelper::getValue($data, 'visit_endtime', ''),
            'visit_type'               => ArrayHelper::getValue($data, 'visit_type', 1),
            'visit_address'            => ArrayHelper::getValue($data, 'visit_address', ''),
            'visit_cost'               => ArrayHelper::getValue($data, 'visit_cost') ? (int) (ArrayHelper::getValue($data, 'visit_cost', 0)) : 0,
            'schedule_available_count' => ArrayHelper::getValue($data, 'schedule_available_count', -1),
            'schedule_type'            => ArrayHelper::getValue($data, 'schedule_type', 1),//类型 1 挂号  2 加号
            'pay_mode'                 => ArrayHelper::getValue($data, 'pay_mode', 2),//支付方式(1在线支付，2线下支付，3无需支付)
            'status'                   => ArrayHelper::getValue($data, 'status', 1),
            'first_practice'           => ArrayHelper::getValue($data, 'first_practice', 0),
            'create_time'              => time(),
            'update_time'              => time(),
            'admin_id'                 => ArrayHelper::getValue($data, 'admin_id', 0),
            'admin_name'               => ArrayHelper::getValue($data, 'admin_name', ''),
        ];
        $_model->setAttributes($attributes);
        $res = $_model->save();
        if ($res) {
            $scheduling_id = $_model->attributes['scheduling_id'];
            return $scheduling_id;
        }
        return false;
    }
}
