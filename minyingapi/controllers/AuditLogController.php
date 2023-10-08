<?php
/**
 * 审核日志
 * @file AuditLogController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-15
 */
namespace minyingapi\controllers;

use common\models\AuditLogModel;
use Yii;

class AuditLogController extends CommonController
{
    /**
     * 审核记录列表
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-07-15
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;//页码
        $requestParams['limit'] = (isset($requestParams['limit']) && !empty($requestParams['limit'])) ? $requestParams['limit'] : $this->limit;//每页数
        $requestParams['operate_type'] = (isset($requestParams['operate_type']) && !empty($requestParams['operate_type'])) ? $requestParams['operate_type'] : 1;//类型 1 医院 2 科室 3 医生
        $requestParams['operate_id'] = (isset($requestParams['operate_id']) && (!empty($requestParams['operate_id']))) ? $requestParams['operate_id'] : 0;//对应类型  1 医院审核表id  2 科室审核表id  3 医生审核表id

        $list = AuditLogModel::getList($requestParams);
        foreach ($list as &$item) {
            $item['auditType'] = AuditLogModel::$auditType[$item['audit_type']];
            $item['auditStatus'] = AuditLogModel::$auditStatus[$item['audit_status']];
            $item['create_time'] = !empty($item['create_time']) ? date("Y-m-d H:i:s", $item['create_time']) : '';
        }

        $totalCount = AuditLogModel::getCount($requestParams);

        $result = [
            'currentpage' => $requestParams['page'],
            'pagesize' => $requestParams['limit'],
            'totalcount' => $totalCount,
            'totalpage' => ceil($totalCount / $requestParams['limit']),
            'list' => $list
        ];
        return $this->jsonSuccess($result);
    }

}