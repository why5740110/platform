<?php

namespace common\models\minying;

use common\models\TbLog;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_min_doctor".
 *
 * @property int $min_doctor_id 自增ID
 * @property string $min_doctor_name 医生姓名
 * @property string $mobile 医生手机号
 * @property string $avatar 头像图片url
 * @property int $min_job_title_id 职称ID
 * @property string $min_job_title 职称
 * @property string $min_doctor_tags 医生标签多个以','分割
 * @property int $min_hospital_id 医院ID
 * @property string $min_hospital_name 医院名称
 * @property int $min_department_id 二级科室ID
 * @property int $visit_type 出诊类型 1本院医生 2多点执业
 * @property string $good_at 医生擅长
 * @property string $intro 医生简介
 * @property string $miao_hospital_name 多点执业第一执业医院名称
 * @property int $miao_hospital_id 多点执业第一执业医院(来自基础数据)
 * @property string $id_card_file 身份证件图片
 * @property int $id_card_begin 身份证件开始时间
 * @property int $id_card_end 身份证件结束时间
 * @property string $doctor_cert_file 医师资格证图片
 * @property int $doctor_cert_begin 医师资格证开始时间
 * @property int $doctor_cert_end 医师资格证结束时间
 * @property string $practicing_cert_file 执业证图片
 * @property int $practicing_cert_begin 执业证开始时间
 * @property int $practicing_cert_end 执业证结束时间
 * @property string $professional_cert_file 专业证图片
 * @property int $professional_cert_begin 专业证开始时间
 * @property int $professional_cert_end 专业证结束时间
 * @property string $multi_practicing_cert_file 多点执业证图片
 * @property int $multi_practicing_cert_begin 多点执业证开始时间
 * @property int $multi_practicing_cert_end 多点执业证结束时间
 * @property int $check_status 审核结果(1:待审核,2:初审通过3:初审未通过4:二审通过5:二审未通过)
 * @property int $cert_status 证书过期情况(任一证书过期,则都为过期:1正常2过期)
 * @property int $admin_role_type 添加行为角色类型1:民营医院2:代理商
 * @property int $admin_id 添加行为角色人员id
 * @property string $admin_name 添加行为角色人员姓名
 * @property int $first_check_uid 初审核人员id
 * @property string $first_check_uname 初审核人员姓名
 * @property int $second_check_uid 二审核人员id
 * @property string $second_check_uname 二审核人员姓名
 * @property int $first_check_time 初审时间
 * @property int second_check_passed_record 二审通过记录
 * @property int $second_check_time 二审时间
 * @property int $agency_id 所属代理商
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class MinDoctorModel extends \yii\db\ActiveRecord
{
    // 本医院执业
    const VISIT_TYPE_INTERNAL = 1;
    // 多点执业
    const VISIT_TYPE_MULTI = 2;

    const ADMIN_ROLE_TYPE_HOSPITAL = 1;
    const ADMIN_ROLE_TYPE_AGENCY = 2;

    // 审核状态：默认
    const CHECK_STATUS_NORMAL = 1;
    // 审核状态：一审通过
    const CHECK_STATUS_FST_PASS = 2;
    // 审核状态：一审拒绝
    const CHECK_STATUS_FST_DENY = 3;
    // 审核状态：二审通过
    const CHECK_STATUS_SND_PASS = 4;
    // 审核状态：二审拒绝
    const CHECK_STATUS_SND_DENY = 5;

    // 证书状态：正常
    const CERT_STATUS_NORMAL = 1;
    // 证书状态：失效
    const CERT_STATUS_EXPIRED = 2;

        //医生审核状态
    public static $checklist = [
        1 => '待一审',
        2 => '一审通过',
        3 => '一审拒绝',
        4 => '二审通过',
        5 => '二审未通过'
    ];

    //医生出诊类型
    public static $visitType = [
        1 => '本院医生',
        2 => '多点执业'
    ];

    public static $certStatus = [
        1 => '正常',
        2 => '过期'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_doctor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['min_job_title_id', 'min_hospital_id', 'min_department_id', 'visit_type', 'miao_hospital_id', 'check_status', 'admin_role_type', 'admin_id', 'first_check_uid', 'second_check_uid', 'first_check_time', 'second_check_time', 'agency_id', 'create_time', 'update_time'], 'integer'],
            [['min_job_title_id', 'min_hospital_id', 'min_department_id', 'visit_type', 'miao_hospital_id', 'agency_id'], 'filter', 'filter' => 'intval'],
            [['good_at', 'intro'], 'required'],
            [['min_doctor_name', 'miao_hospital_name', 'admin_name', 'first_check_uname', 'second_check_uname'], 'string', 'max' => 50],
            [['mobile'],  'match', 'pattern' => '/^[1][3456789][0-9]{9}$/', 'message' => '联系方式必须是手机号码格式'],
            [['avatar'], 'safe'],
            [['id_card_file', 'doctor_cert_file', 'practicing_cert_file', 'professional_cert_file', 'multi_practicing_cert_file'], 'safe'],
            [['min_job_title'], 'string', 'max' => 20],
            [['min_doctor_tags', 'min_hospital_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'min_doctor_id' => '医生id',
            'min_doctor_name' => '医生姓名',
            'mobile' => '医生手机号',
            'avatar' => '医生头像',
            'min_job_title_id' => '职称ID',
            'min_job_title' => '职称',
            'min_doctor_tags' => '医生标签',
            'min_hospital_id' => '医院ID',
            'min_hospital_name' => '医院名称',
            'min_department_id' => '科室ID',
            'visit_type' => '出诊类型',
            'good_at' => '医生擅长',
            'intro' => '医生简介',
            'miao_hospital_name' => '第一执业医院名称',
            'miao_hospital_id' => '第一执业医院ID',
            'id_card_file' => '身份证件图片',
            'id_card_begin' => '身份证件开始时间',
            'id_card_end' => '身份证件结束时间',
            'doctor_cert_file' => '医师资格证图片',
            'doctor_cert_begin' => '医师资格证开始时间',
            'doctor_cert_end' => '医师资格证结束时间',
            'practicing_cert_file' => '执业证图片',
            'practicing_cert_begin' => '执业证开始时间',
            'practicing_cert_end' => '执业证结束时间',
            'professional_cert_file' => '专业证图片',
            'professional_cert_begin' => '专业证开始时间',
            'professional_cert_end' => '专业证结束时间',
            'multi_practicing_cert_file' => '多点执业证图片',
            'multi_practicing_cert_begin' => '多点执业证开始时间',
            'multi_practicing_cert_end' => '多点执业证结束时间',
            'cert_status' => '证书过期状态',
            'check_status' => '审核结果',
            'admin_role_type' => '管理员角色类型',
            'admin_id' => '管理员id',
            'admin_name' => '管理员名',
            'second_check_passed_record' => '二审通过记录',
            'first_check_uid' => '一审操作人id',
            'first_check_uname' => '一审操作人名',
            'second_check_uid' => '二审操作人id',
            'second_check_uname' => '二审操作人名',
            'first_check_time' => '一审时间',
            'second_check_time' => '二审时间',
            'agency_id' => '所属代理商',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }

    /**
     * 医生标签 status：0禁用,1使用
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-16
     * @return array
     */
    public static function DoctorTagsMap()
    {
        return [
            ['id' => 1, 'status' => 1, 'name' => '知名专家'],
            ['id' => 2, 'status' => 1, 'name' => '传统学术传承人']
        ];
    }

    /**
     * 产品指定写死的职称
     * status：0禁用,1使用
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-18
     * @return array
     */
    public static function DoctorJobTitleMap()
    {
        return [
            ['id' => 1, 'status' => 1, 'name' => '主任医师'],
            ['id' => 2, 'status' => 1, 'name' => '副主任医师'],
            ['id' => 3, 'status' => 1, 'name' => '主治医师'],
            ['id' => 4, 'status' => 1, 'name' => '住院医师'],
            ['id' => 5, 'status' => 1, 'name' => '主诊医师'],
        ];
    }

    /**
     * @param $params
     * @param string $field
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public static function getList($params, $field = '*')
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;

        $query = self::conditionWhere($params);
        $totalCountQuery = clone $query;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => $pageSize
        ]);
        $pageObj->setPage($page - 1);

        return $query->select($field)->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('update_time desc')->asArray()->all();
    }

    /**
     * @param $params
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return int|string
     * @throws \Exception
     */
    public static function getCount($params)
    {
        $query = self::conditionWhere($params);
        return $query->asArray()->count();
    }

    /**
     * @param $params
     * @param string $field
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return ActiveQuery
     * @throws \Exception
     */
    public static function conditionWhere($params, $field = '*')
    {
        $query = self::find()->select($field);

        //搜索关键词 医生名称或医生ID
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $search_like = ['or', ['like', 'min_doctor_id', $keyword], ['like', 'min_doctor_name', $keyword]];
            $query->andWhere($search_like);
        }

        // 医院id
        if ($hospital_id = ArrayHelper::getValue($params, 'hospital_id', '')) {
            $query->andWhere(['min_hospital_id' => $hospital_id]);
        }

        // 科室id
        if ($department_id = ArrayHelper::getValue($params, 'department_id', '')) {
            $query->andWhere(['min_department_id' => $department_id]);
        }

        // 代理商id
        if ($agency_id = ArrayHelper::getValue($params, 'agency_id', '')) {
            $query->andWhere(['agency_id' => $agency_id]);
        }

        // 出诊类型
        if ($visit_type = ArrayHelper::getValue($params, 'visit_type', '')) {
            $query->andWhere(['visit_type' => $visit_type]);
        }

        // 医生职称id
        if ($job_title_id = ArrayHelper::getValue($params, 'job_title_id', '')) {
            $query->andWhere(['min_job_title_id' => $job_title_id]);
        }

        // 审核状态(0:待审核,1:一审通过 2:一审拒绝 3:二审通过 4:二审未通过)
        if (isset($params['admin_type']) && $params['admin_type'] == 1) {
            $query->andWhere(['in', 'check_status', [1, 2, 3, 4, 5]]);
        } else if (isset($params['admin_type']) && $params['admin_type'] == 2) {
            $query->andWhere(['in', 'check_status', [2, 4, 5]]);
        }

        if (isset($params['doctor_name']) && !empty($params['doctor_name'])) {
            $query->andWhere(['like', 'min_doctor_name', $params['doctor_name']]);
        }

        if (isset($params['hospital_name']) && !empty($params['hospital_name'])) {
            $query->andWhere(['like', 'min_hospital_name', $params['hospital_name']]);
        }

        if (isset($params['check_status']) && $params['check_status'] !== '' && $params['check_status'] != 0) {
            $query->andWhere(['check_status' => trim($params['check_status'])]);
        }
        //开通时间
        if (isset($params['create_time']) and $params['create_time'] != '') {
            $create_time_arr = explode(' - ', $params['create_time']);
            $query->andWhere(['>=', 'update_time', strtotime(trim($create_time_arr[0]))]);
            $query->andWhere(['<=', 'update_time', strtotime(trim($create_time_arr[1]) . ' 23:59:59')]);
        }

        return $query;
    }

    public static function getDetail($id)
    {
        $info = self::find()->where(['min_doctor_id' => $id])->asArray()->one();
        return $info;
    }

    /**
     * 获取民营医院二审通过的医生列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public static function getAuditList($param = [])
    {
        $query = self::find()->where(['check_status' => self::CHECK_STATUS_SND_PASS]);
        $query->andWhere(['cert_status' => 1]);
        if (isset($param['agentcy_id']) && !empty($param['agentcy_id'])) {
            $query->andWhere(['agentcy_id' => $param['agentcy_id']]);
        }
        if (isset($param['min_hospital_id']) && !empty($param['min_hospital_id'])) {
            $query->andWhere(['min_hospital_id' => $param['min_hospital_id']]);
        }

        if (isset($param['min_doctor_name']) && !empty($param['min_doctor_name'])) {
            $query->andWhere(['like', 'min_doctor_name', $param['min_doctor_name']]);
        }

        $docList = $query->asArray()->all();
        if (empty($docList)) return [];
        $res = [];
        foreach ($docList as $val) {
            $depInfo = MinDepartmentModel::findOne($val['min_department_id']);
            $arr = [
                'min_doctor_id' => $val['min_doctor_id'],
                'min_doctor_name' => $val['min_doctor_name'],
                'min_department_id' => $val['min_department_id'],
                'department_name' => $depInfo->min_minying_fkname . '-' . $depInfo->min_minying_skname,
            ];
            $res[] = $arr;
        }
        return $res;
    }

    /**
     * 返回人性化字段
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     * @return $this
     */
    public function getHumanFormat()
    {
        // 需要处理图片路径字段
        $image_fields = ['id_card_file', 'doctor_cert_file', 'practicing_cert_file', 'professional_cert_file', 'multi_practicing_cert_file'];
        // 需要处理时间格式的字段
        $time_fields = [
            'id_card_begin', 'id_card_end', 'doctor_cert_begin', 'doctor_cert_end', 'practicing_cert_begin', 'practicing_cert_end',
            'professional_cert_begin', 'professional_cert_end', 'multi_practicing_cert_begin', 'multi_practicing_cert_end',
        ];

        foreach ($this->getAttributes() as $attribute => &$value) {
            // 处理图片，avatar以字符串形式返回
            if (in_array($attribute, $image_fields)) {
                // 没有图片强制转换成数组
                $img_arr = [];
                if (!empty($value)) {
                    $img_arr = array_values(array_filter(explode(',', $value)));
                    array_walk($img_arr, function (&$v) {
                        $v = Yii::$app->params['min_doctor_img_oss_url_prefix'] . $v;
                    });
                }
                $this->setAttribute($attribute, $img_arr);
            }
            if (in_array($attribute, ['avatar'])) {
                !empty($value) && $this->setAttribute($attribute, Yii::$app->params['min_doctor_img_oss_url_prefix'] . $value);
            }
            // 处理日期
            if (in_array($attribute, $time_fields)) {
                $date_format = empty($value) ? '' : date('Y-m-d', $value);
                $this->setAttribute($attribute, $date_format);
            }
        }

        return $this;
    }


    /**
     * 科室关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return \yii\db\ActiveQuery
     */
    public function getDepartmentModel()
    {
        return $this->hasOne(MinDepartmentModel::class, ['id' => 'min_department_id']);
    }

    /**
     * 通过标签id获取
     * @param $tags
     * @param $split
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-26
     * @return string
     */
    public static function getTagsInfoById($tags, $split = '、')
    {
        $doctor_tags_info = array_filter(self::DoctorTagsMap(), function ($v) use ($tags) {
            $tags = explode(',', $tags);
            return in_array($v['id'], $tags);
        });
        return join($split, array_column($doctor_tags_info, 'name'));
    }

    /**
     * 医院关联模型
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return \yii\db\ActiveQuery
     */
    public function getHospitalModel()
    {
        return $this->hasOne(MinHospitalModel::class, ['min_hospital_id' => 'min_hospital_id']);
    }

    /**
     * 获取医生职称信息：返回单条记录
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @throws \Exception
     */
    public function getJobTitleInfo()
    {
        $job_title_info = array_filter(self::DoctorJobTitleMap(), function ($v) {
            return $v['id'] == $this->min_job_title_id;
        });
        return ArrayHelper::getValue(array_values($job_title_info), '0', '');
    }

    /**
     * 获取医生标签
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-20
     * @return array
     */
    public function getTagsInfo()
    {
        $doctor_tags_info = array_filter(self::DoctorTagsMap(), function ($v) {
            $tags = explode(',', $this->min_doctor_tags);
            return in_array($v['id'], $tags);
        });
        return array_values($doctor_tags_info);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 新增时记录日志
        if ($insert) {
            $info = "{$this->admin_name}添加了医生id：{$this->min_doctor_id}；医院名称：{$this->min_hospital_name}；医生姓名：{$this->min_doctor_name}";
            TbLog::addLog($info, '民营医院医生添加', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }

        // 修改时记录修改日志
        if (!$insert) {
            // 仅修改时间发生变化不记录日志
            unset($changedAttributes['update_time']);
            if (!$changedAttributes) {
                return true;
            }
            $info = "{$this->admin_name}修改了医生id：{$this->min_doctor_id}；";
            foreach ($changedAttributes as $attribute => $value) {
                $info .= "{$this->getAttributeLabel($attribute)} 由【{$value}】修改成【{$this->$attribute}】；";
            }
            TbLog::addLog($info, '民营医院医生更新', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
    }
}
