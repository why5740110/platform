<?php
/**
 * 民营医院
 * @file MinHospitalModel.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-12
 */

namespace common\models\minying;

use common\libs\CommonFunc;
use common\models\TbLog;
use Yii;
use common\models\BaseModel;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_min_hospital".
 *
 * @property int $min_hospital_id
 * @property string $min_hospital_name 医院名称
 * @property string $min_hospital_logo 医院logo
 * @property int $min_hospital_type 医院类型
 * @property int $min_hospital_level 医院等级(2:三级甲等 3:三级乙等 4:三级丙等 5:二级甲等 6:二级乙等 7:二级丙等 8:一级甲等 9:一级乙等 10:一级丙等)
 * @property int $min_hospital_nature 医院性质(1,公立 2,民营)
 * @property int $check_status 审核状态(1:待审核,2:一审通过 3:一审拒绝 4:二审通过 5:二审未通过)
 * @property string $fail_reason 失败原因
 * @property string $min_hospital_tags 医生标签多个以','分割
 * @property int $min_hospital_province_id 医院所在省id
 * @property string $min_hospital_province_name 医院所在省名
 * @property int $min_hospital_city_id 医院所在市id
 * @property string $min_hospital_city_name 医院所在市名
 * @property int $min_hospital_county_id 医院所在县(区)id
 * @property string $min_hospital_county_name 医院所在县(区)名
 * @property string $min_hospital_address 医院具体地址
 * @property string $min_bus_line 乘车路线
 * @property string $min_hospital_phone 医院联系电话
 * @property string $min_hospital_introduce 医院简介
 * @property string $min_business_license 营业执照图片
 * @property string $min_medical_license 医疗许可证件图片
 * @property string $min_health_record 卫健委备案图片
 * @property string $min_medical_certificate 医疗广告证图片
 * @property string $min_treatment_project 诊疗项目
 * @property string $min_guahao_rule 挂号规则
 * @property string $min_hospital_contact 医院联系人
 * @property string $min_hospital_contact_phone 联系人电话
 * @property int $first_check_uid 初审核人员id
 * @property string $first_check_name 初审核人员姓名
 * @property int $first_check_time 初审时间
 * @property int $second_check_uid 二审核人员id
 * @property string $second_check_name 二审核人员姓名
 * @property int $second_check_time 二审时间
 * @property int $admin_role_type 添加行为角色类型
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人
 * @property int second_check_passed_record 二审通过记录
 * @property int $agency_id 所属代理商
 * @property int $create_time 创建时间
 * @property int $update_time 修改时间
 * @property int $min_company_name 单位名称
 */

class MinHospitalModel extends BaseModel
{
    const ADMIN_ROLE_TYPE_HOSPITAL = 1;
    const ADMIN_ROLE_TYPE_AGENCY = 2;

    const CHECK_STATUS_NORMAL = 1;
    const CHECK_STATUS_FST_PASS = 2;
    const CHECK_STATUS_FST_DENY = 3;
    const CHECK_STATUS_SND_PASS = 4;
    const CHECK_STATUS_SND_DENY = 5;

    //民营医院等级
    public static $levellist = [
        ''=>'全部',
        2=>'三级甲等',
        3=>'三级乙等',
        4=>'三级丙等',
        5=>'二级甲等',
        6=>'二级乙等',
        7=>'二级丙等',
        8=>'一级甲等',
        9=>'一级乙等',
        10 =>'一级丙等',
        11 =>'暂未评级'
    ];

    //医院审核状态
    public static $checklist = [
        1=>'待一审',
        2=>'一审通过',
        3=>'一审拒绝',
        4=>'二审通过',
        5=>'二审未通过'
    ];

    //医院性质(1,公立 2,民营)
    public static $naturelist = [
        1 => '公立',
        2 => '社会办医'
    ];

    //医院类型
    public static $TypeList = [
        1 => '综合',
        2 => '专科',
        3 => '中医院',
        4 => '门诊',
        5 => '整型美容院',
        6 => '未知'
    ];

    //医院标签
    public static $hospitalTags = [
        1 => '知名医院',
        2 => '医保报销',
        3 => '复旦排名医院',
        4 => '中外合资'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_min_hospital}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['min_hospital_type', 'min_hospital_level', 'min_hospital_nature', 'check_status', 'min_hospital_province_id', 'min_hospital_city_id', 'min_hospital_county_id', 'first_check_uid', 'first_check_time', 'second_check_uid', 'second_check_time', 'admin_role_type', 'admin_id', 'agency_id', 'create_time', 'update_time'], 'integer'],
            [['min_hospital_introduce', 'min_guahao_rule'], 'required'],
            [['min_hospital_introduce'], 'string', 'max' => 5000],
            [['min_guahao_rule'], 'string', 'max' => 1000],
            [['first_check_name', 'second_check_name', 'admin_name'], 'string', 'max' => 50],
            [['min_hospital_name', 'min_hospital_address','min_company_name', 'min_hospital_contact'], 'string', 'max' => 30],
            [['min_hospital_logo', 'min_treatment_project'], 'string', 'max' => 255],
            [['min_business_license', 'min_medical_license', 'min_health_record', 'min_medical_certificate'], 'string', 'max' => 1000],
            [['fail_reason', 'min_hospital_tags', 'min_hospital_province_name', 'min_hospital_city_name', 'min_hospital_county_name'], 'string', 'max' => 100],
            [['min_bus_line'], 'string', 'max' => 1000],
            [['min_hospital_contact_phone'],  'match', 'pattern' => '/^[1][3456789][0-9]{9}$/', 'message' => '联系人电话必须是手机号码格式'],
            [['min_hospital_phone'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'min_hospital_id' => '医院id',
            'min_hospital_name' => '医院名称',
            'min_hospital_logo' => '医院logo',
            'min_hospital_type' => '医院类型',
            'min_hospital_level' => '医院等级',
            'min_hospital_nature' => '医院性质',
            'check_status' => '审核状态',
            'fail_reason' => '失败原因',
            'min_hospital_tags' => '医院标签',
            'min_hospital_province_id' => '医院所在省id',
            'min_hospital_province_name' => '医院所在省名',
            'min_hospital_city_id' => '医院所在市id',
            'min_hospital_city_name' => '医院所在市名',
            'min_hospital_county_id' => '医院所在县(区)id',
            'min_hospital_county_name' => '医院所在县(区)名',
            'min_hospital_address' => '医院具体地址',
            'min_bus_line' => '乘车路线',
            'min_hospital_phone' => '医院联系电话',
            'min_hospital_introduce' => '医院简介',
            'min_business_license' => '营业执照图片',
            'min_medical_license' => '医疗许可证件图片',
            'min_health_record' => '卫健委备案图片',
            'min_medical_certificate' => '医疗广告证图片',
            'min_treatment_project' => '诊疗项目',
            'min_guahao_rule' => '挂号规则',
            'min_hospital_contact' => '医院联系人',
            'min_hospital_contact_phone' => '联系人电话',
            'min_company_name' => '单位名称',
            'first_check_uid' => '初审核人员id',
            'first_check_name' => '初审核人员姓名',
            'first_check_time' => '初审时间',
            'second_check_uid' => '二审核人员id',
            'second_check_name' => '二审核人员姓名',
            'second_check_time' => '二审时间',
            'admin_role_type' => '添加行为角色类型',
            'admin_id' => '操作人id',
            'admin_name' => '操作人',
            'second_check_passed_record' => '二审通过记录',
            'agency_id' => '所属代理商',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
        ];
    }

    /**
     * @param $params
     * @param string $field
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getList($params, $field = '*'){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;

        $hospitalQuery = self::conditionWhere($params);
        $totalCountQuery = clone $hospitalQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        return $hospitalQuery->select($field)->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('update_time desc')->asArray()->all();
    }

    public static function getCount($params){
        $hospitalQuery = self::conditionWhere($params);
        return $hospitalQuery->asArray()->count();
    }

    /**
     * 条件共用
     * @param $params
     * @param string $field
     * @return \yii\db\ActiveQuery
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function conditionWhere($params, $field = '*'){
        $hospitalQuery = self::find()->select($field);
        //代理商登录id
        if (isset($params['agency_id']) && !empty($params['agency_id'])) {
            $hospitalQuery->andWhere(['agency_id' => $params['agency_id']]);
        }

        // 审核状态(0:待审核,1:一审通过 2:一审拒绝 3:二审通过 4:二审未通过)
        if (isset($params['admin_type']) && $params['admin_type'] == 1) {
            $hospitalQuery->andWhere(['in','check_status', [1, 2, 3, 4, 5]]);
        } else if (isset($params['admin_type']) && $params['admin_type'] == 2) {
            $hospitalQuery->andWhere(['in','check_status', [2, 4, 5]]);
        }

        if (isset($params['hospital_name']) && !empty($params['hospital_name']) && is_numeric($params['hospital_name'])) {
            $hospitalQuery->andWhere(['min_hospital_id' => (int)$params['hospital_name']]);
        } elseif (isset($params['hospital_name']) && !empty($params['hospital_name'])) {
            $hospitalQuery->andWhere(['like','min_hospital_name',$params['hospital_name']]);
        }

        if (isset($params['hospital_type']) && $params['hospital_type']) {
            $hospitalQuery->andWhere(['min_hospital_type' => trim($params['hospital_type'])]);
        }

        if (isset($params['hospital_level']) && $params['hospital_level'] !== '') {
            $hospitalQuery->andWhere(['min_hospital_level' => trim($params['hospital_level'])]);
        }

        if (isset($params['check_status']) && $params['check_status'] !== '' && $params['check_status'] != 0) {
            $hospitalQuery->andWhere(['check_status' => trim($params['check_status'])]);
        }
        //开通时间
        if(isset($params['create_time']) && $params['create_time'] != ''){
            $create_time_arr = explode(' - ', $params['create_time']);
            $hospitalQuery->andWhere(['>=', 'update_time', strtotime(trim($create_time_arr[0]))]);
            $hospitalQuery->andWhere(['<=', 'update_time', strtotime(trim($create_time_arr[1]) . ' 23:59:59')]);
        }
        return $hospitalQuery;
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getDetail($id)
    {
        $info = self::find()->where(['min_hospital_id' => $id])->asArray()->one();
        return $info;
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-19
     */
    public static function hospitals()
    {
        $min_hospitals = self::find()
            ->where(['check_status' => 4])
            ->select('min_hospital_id,min_hospital_name')
            ->asArray()
            ->all();
        return array_column($min_hospitals, 'min_hospital_name', 'min_hospital_id');
    }

    /**
     * 返回人性化字段
     * @return $this
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function getHumanFormat(){
        // 需要处理图片路径字段
        $image_fields = ['min_business_license', 'min_medical_license', 'min_health_record', 'min_medical_certificate'];
        // 需要处理时间格式的字段
        $time_fields = [
            'create_time'
        ];
        foreach ($this as $attribute => &$value) {
            if (in_array($attribute, $image_fields)) {
                // 没有图片强制转换成数组
                $img_arr = [];
                if (!empty($value)) {
                    $img_arr = array_values(array_filter(explode(',', $value)));
                    array_walk($img_arr, function (&$v) {
                        $v = Yii::$app->params['min_hospital_img_oss_url_prefix'] . $v;
                    });
                }
                $this->setAttribute($attribute, $img_arr);
            }
            if (in_array($attribute, $time_fields)) {
                $this->setAttribute($attribute, date('Y-m-d H:i:s', $value));
            }
        }

        return $this;
    }

    /**
     * 获取医院标签
     * @return array
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public static function getTagsInfo($tags){
//        $hospital_tags_info = array_filter(self::HospitalTagsMap(), function ($v) {
//            $tags = explode(',', $this->min_hospital_tags);
//            return in_array($v['id'], $tags);
//        });
//        return $hospital_tags_info;
        $tags_info = [];
        $tags = explode(',', $tags);
        foreach ($tags as $val){
            if (isset(self::$hospitalTags[$val]) && !empty(self::$hospitalTags[$val])) {
                $tags_info[] = self::$hospitalTags[$val];
            }
        }
        return implode('、',$tags_info);
    }

    /**
     * 获取诊疗项目文案
     * @param $projects
     * @return string
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getTreatmentProject($projects){
        $projects_info = [];
        $tags = explode(',', $projects);
        foreach ($tags as $val){
            $projects_info[] = CommonFunc::getKeshiName($val);
        }
        return implode('/',$projects_info);
    }

    /**
     * 修改医院禁用状态
     * @param $hospitalCode
     * @param $status
     * @return bool
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-26
     */
    public static function updateHospitalStatus($hospitalCode,$status){
        if(!empty($hospitalCode)){
            $params['min_hospital_id'] = $hospitalCode;
            $info = self::find()->where($params)->one();
            if ($info){
                $status = $status == 1 ? 1 : 2;
                $info->update_time = time();
                $info->status = $status;
                return $info->save();
            }
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 增加一条添加记录
        // 新增时记录日志
        if ($insert) {
            $info = "{$this->admin_name}添加了医院id：{$this->min_hospital_id}；医院名称：{$this->min_hospital_name}";
            TbLog::addLog($info, '民营医院添加', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
    }
}