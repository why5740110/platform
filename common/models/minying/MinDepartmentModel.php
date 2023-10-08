<?php

namespace common\models\minying;

use common\models\BaseModel;
use common\models\TbLog;
use yii\data\Pagination;

/**
 * 民营医院科室表
 * @file MinDepartmentModel.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-12
 */

/**
 * This is the model class for table "tb_min_department".
 *
 * @property int $id 自增id
 * @property int $min_hospital_id 民营医院id
 * @property string $min_hospital_name 医院名称
 * @property string $min_minying_fkname 民营一级标准科室名称
 * @property string $min_minying_skname 民营二级标准科室名称
 * @property int $check_status 审核状态(1:待审核,2:初审通过3:初审未通过4:二审通过5:二审未通过)
 * @property string $fail_reason 失败原因
 * @property int $miao_first_department_id 王氏一级科室id
 * @property int $miao_second_department_id 王氏二级科室id
 * @property int $first_check_uid 初审核人员id
 * @property string $first_check_name 初审核人员姓名
 * @property int $first_check_time 初审时间
 * @property int $second_check_uid 二审核人员id
 * @property string $second_check_name 二审核人员姓名
 * @property int $second_check_time 二审时间
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人
 * @property int second_check_passed_record 二审通过记录
 * @property int $agency_id 所属代理商
 * @property int $create_time 创建时间
 * @property int $update_time 修改时间
 */

class MinDepartmentModel extends BaseModel
{
    const ADMIN_ROLE_TYPE_HOSPITAL = 1; //民营医院
    const ADMIN_ROLE_TYPE_AGENCY = 2; //代理商医院

    const CHECK_STATUS_NORMAL = 1;
    const CHECK_STATUS_FST_PASS = 2;
    const CHECK_STATUS_FST_DENY = 3;
    const CHECK_STATUS_SND_PASS = 4;
    const CHECK_STATUS_SND_DENY = 5;

    //科室审核状态
    public static $checklist = [
        1=>'待一审',
        2=>'一审通过',
        3=>'一审拒绝',
        4=>'二审通过',
        5=>'二审未通过'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_min_department}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['min_hospital_id', 'check_status', 'miao_first_department_id', 'miao_second_department_id', 'first_check_uid', 'first_check_time', 'second_check_uid', 'second_check_time', 'admin_id', 'agency_id', 'create_time', 'update_time'], 'integer'],
            [['min_hospital_name', 'fail_reason'], 'string', 'max' => 100],
            [['first_check_name', 'second_check_name', 'admin_name'], 'string', 'max' => 50],
            [['min_minying_fkname', 'min_minying_skname'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '科室id',
            'min_hospital_id' => '民营医院id',
            'min_hospital_name' => '医院名称',
            'min_minying_fkname' => '民营一级标准科室名称',
            'min_minying_skname' => '民营二级标准科室名称',
            'check_status' => '审核状态',
            'fail_reason' => '失败原因',
            'miao_first_department_id' => '王氏一级科室id',
            'miao_second_department_id' => '王氏二级科室id',
            'first_check_uid' => '初审核人员id',
            'first_check_name' => '初审核人员姓名',
            'first_check_time' => '初审时间',
            'second_check_uid' => '二审核人员id',
            'second_check_name' => '二审核人员姓名',
            'second_check_time' => '二审时间',
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

        $departmentQuery = self::conditionWhere($params);
        $totalCountQuery = clone $departmentQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        return $departmentQuery->select($field)->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('update_time desc')->asArray()->all();
    }

    public static function getCount($params){
        $departmentQuery = self::conditionWhere($params);
        return $departmentQuery->asArray()->count();
    }

    /**
     * 查询条件共用
     * @param $params
     * @param string $field
     * @return \yii\db\ActiveQuery
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function conditionWhere($params, $field = '*'){
        $departmentQuery = self::find()->select($field);
        //民营医院id
        if (isset($params['min_hospital_id']) && !empty($params['min_hospital_id'])) {
            $departmentQuery->andWhere(['min_hospital_id' => $params['min_hospital_id']]);
        }

        // 审核状态(0:待审核,1:一审通过 2:一审拒绝 3:二审通过 4:二审未通过)
        if (isset($params['admin_type']) && $params['admin_type'] == 1) {
            $departmentQuery->andWhere(['in','check_status', [1, 2, 3, 4, 5]]);
        } else if (isset($params['admin_type']) && $params['admin_type'] == 2) {
            $departmentQuery->andWhere(['in','check_status', [2, 4, 5]]);
        }
        //代理商下的科室
        if (isset($params['agency_id']) && !empty($params['agency_id'])){
            $departmentQuery->andWhere(['agency_id' => trim($params['agency_id'])]);
        }
        //二审通过后，再次修改且在审核中也能查到
        if (isset($params['second_check_passed_record']) && !empty($params['second_check_passed_record'])){
            $departmentQuery->andWhere(['second_check_passed_record' => $params['second_check_passed_record']]);
        }
        //民营医院下的科室
        if (isset($params['hospital_id']) && !empty($params['hospital_id'])){
            $departmentQuery->andWhere(['min_hospital_id' => trim($params['hospital_id'])]);
        }
        if (isset($params['keshi_name']) && !empty($params['keshi_name'])){
            $departmentQuery->andWhere(['min_minying_skname' => trim($params['keshi_name'])]);
        }
        if (isset($params['department_name']) && !empty($params['department_name'])){
            $departmentQuery->andWhere(['like','min_minying_skname',$params['department_name']]);
        }
        if (isset($params['department_id']) && !empty($params['department_id'])){
            $departmentQuery->andWhere(['id' => $params['department_id']]);
        }
        if (isset($params['hospital_name']) && !empty($params['hospital_name'])) {
            $departmentQuery->andWhere(['like','min_hospital_name',$params['hospital_name']]);
        }
        if (isset($params['check_status']) && $params['check_status'] !== '' && $params['check_status'] != 0) {
            $departmentQuery->andWhere(['check_status' => trim($params['check_status'])]);
        }
        //开通时间
        if(isset($params['create_time']) && $params['create_time'] != ''){
            $create_time_arr = explode(' - ', $params['create_time']);
            $departmentQuery->andWhere(['>=', 'update_time', strtotime(trim($create_time_arr[0]))]);
            $departmentQuery->andWhere(['<=', 'update_time', strtotime(trim($create_time_arr[1]) . ' 23:59:59')]);
        }
        return $departmentQuery;
    }

    /**
     * 科室详情
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getDetail($id, $field = '*')
    {
        $info = self::find()->select($field)->where(['id' => $id])->asArray()->one();
        return $info;
    }

    /**
     * 获取民营医院二审通过的科室列表
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-07-20
     */
    public static function getAuditList($param=[])
    {
        $query = self::find()->where(['check_status' => self::CHECK_STATUS_SND_PASS]);

        if (isset($param['agentcy_id']) && !empty($param['agentcy_id'])) {
            $query->andWhere(['agentcy_id' => $param['agentcy_id']]);
        }
        if (isset($param['min_hospital_id']) && !empty($param['min_hospital_id'])) {
            $query->andWhere(['min_hospital_id' => $param['min_hospital_id']]);
        }

        if (isset($param['min_department_name']) && !empty($param['min_department_name'])) {
            $keyword = trim($param['min_department_name']);
            $search_like = ['or', ['like', 'min_minying_fkname', $keyword], ['like', 'min_minying_skname', $keyword]];
            $query->andWhere($search_like);
        }

        $depList = $query->asArray()->all();
        if (empty($depList)) return [];
        $res = [];
        foreach ($depList as $val) {
            $arr = [
                'min_department_id' => $val['id'],
                'min_department_name' => $val['min_minying_fkname'] . '-' . $val['min_minying_skname']
            ];
            $res[] = $arr;
        }
        return $res;
    }

    /**
     * 获取所有科室
     * @author wanghongying<wanghongying@yuanyinjituan.com>
     * @date 2022-08-15
     */
    public static function getAllList()
    {
        $depList = self::find()->asArray()->all();
        if (empty($depList)) return [];
        $res = [];
        foreach ($depList as $val) {
            $arr = [
                'min_department_id' => $val['id'],
                'min_department_name' => $val['min_minying_fkname'] . '-' . $val['min_minying_skname'],
                'min_hospital_name' => $val['min_hospital_name']
            ];
            $res[] = $arr;
        }
        return $res;
    }

    /**
     * 医院关联模型
     * @return \yii\db\ActiveQuery
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-20
     */
    public function getHospitalModel()
    {
        return $this->hasOne(MinHospitalModel::class, ['min_hospital_id' => 'min_hospital_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 新增时记录日志
        if ($insert) {
            $info = "{$this->admin_name}添加了科室id：{$this->id}；一级科室名称：{$this->min_minying_fkname}；二级科室名称：{$this->min_minying_skname}";
            TbLog::addLog($info, '民营医院科室添加', ['admin_id' => $this->admin_id, 'admin_name' => $this->admin_name]);
        }
    }

}