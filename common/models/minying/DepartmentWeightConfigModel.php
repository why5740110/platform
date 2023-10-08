<?php
/**
 * 科室权重配置表
 * @file DepartmentWeightConfigModel.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-13
 */

namespace common\models\minying;


use common\models\BaseModel;
use yii\data\Pagination;

/**
 * This is the model class for table "tb_department_weight_config".
 *
 * @property int $id
 * @property int $first_department_id 一级科室id
 * @property string $first_department_name 一级科室名
 * @property int $second_department_id 二级科室id
 * @property string $second_department_name 二级科室名
 * @property int $status 审核状态(1:正在使用 2:删除)
 * @property int $weight 权重
 * @property int $admin_id 操作人id
 * @property string $admin_name 操作人
 * @property int $create_time 创建时间
 * @property int $update_time 修改时间
 */

class DepartmentWeightConfigModel extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_department_weight_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_department_id', 'second_department_id', 'status', 'weight', 'admin_id', 'create_time', 'update_time'], 'integer'],
            [['first_department_name', 'second_department_name'], 'string', 'max' => 30],
            [['admin_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'first_department_id' => '一级科室id',
            'first_department_name' => '一级科室名',
            'second_department_id' => '二级科室id',
            'second_department_name' => '二级科室名',
            'status' => '审核状态',
            'weight' => '权重',
            'admin_id' => '操作人id',
            'admin_name' => '操作人',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
        ];
    }

    /**
     * 获取科室配置列表
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;

        $departmentQuery = self::find()->select('*')->andWhere(['status' => 1]);
        if (!empty($params['department_name'])) {
            $departmentQuery->andWhere(['like','second_department_name',$params['department_name']]);
        }
        $totalCountQuery = clone $departmentQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);

        return $departmentQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('id desc')->asArray()->all();
    }

    /**
     * 获取科室配置总数
     * @param $params
     * @return bool|int|string|null
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getCount($params){
        $departmentQuery = self::find()->andWhere(['status' => 1]);
        if (!empty($params['hospital_name'])) {
            $departmentQuery->andWhere(['like','hospital_name',$params['hospital_name']]);
        }
        return $departmentQuery->asArray()->count();
    }

    /**
     * 获取全部科室
     * @return array|\yii\db\ActiveRecord[]
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-25
     */
    public static function getALl($field = '*'){
        return self::find()->select($field)->andWhere(['status' => 1])->orderBy('weight desc')->asArray()->all();
    }


}