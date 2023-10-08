<?php
namespace common\models;

use yii\data\Pagination;
/**
 * This is the model class for table "tb_min_audit_log".
 * @property int $id  主键id
 * @property int $operate_type 类型 1 医院 2 科室 3 医生
 * @property int $operate_id 对应类型id  1 医院审核表id  2 科室审核表id  3 医生审核表id
 * @property int $audit_uid  审核用户id (审核类型为1 代理商id  类型2  王氏管理员id)
 * @property string $audit_name  审核用户名称 (审核类型为1 代理商名称  类型2  王氏管理员名称)
 * @property int $audit_status 审核状态 1 通过 2 拒绝
 * @property string $audit_remark 审核备注
 * @property int $audit_type 审核类型 1 一审  2 二审
 * @property int $create_time 创建时间
 */
class AuditLogModel extends \yii\db\ActiveRecord
{
    ##审核类型
    public static $auditType = [1 => '初审', 2 => '二审'];
    ##审核状态
    public static $auditStatus = [1 => '通过', 2 => '拒绝'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_min_audit_log';
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
        //类型 1 医院 2 科室 3 医生
        if (isset($params['operate_type']) && !empty($params['operate_type'])) {
            $doctorQuery->andWhere(['operate_type' => $params['operate_type']]);
        }

        //对应类型id  1 医院审核表id  2 科室审核表id  3 医生审核表id
        if (isset($params['operate_id']) && !empty($params['operate_id'])) {
            $doctorQuery->andWhere(['operate_id' => $params['operate_id']]);
        }
        return $doctorQuery;
    }

    //保存审核记录
    public static function addLog($params)
    {
        $model = new self();
        $model->operate_type  = isset($params['operate_type']) ? $params['operate_type'] : 0;
        $model->operate_id  = isset($params['operate_id']) ? $params['operate_id'] : 0;
        $model->audit_uid  = isset($params['audit_uid']) ? $params['audit_uid'] : 0;
        $model->audit_name  = isset($params['audit_name']) ? $params['audit_name'] : '';
        $model->audit_status  = isset($params['audit_status']) ? $params['audit_status'] : 0;
        $model->audit_type  = isset($params['audit_type']) ? $params['audit_type'] : 0;
        $model->audit_remark  = isset($params['audit_remark']) ? $params['audit_remark'] : '';
        $model->create_time  = time();
        $res = $model->save();
        if ($res) {
            return $model->attributes['id'];
        } else {
            return 0;
        }
    }
}
