<?php
/**
 * @file GuahaoPlatformModel.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/16
 */

namespace common\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;


class GuahaoPlatformRelationHospitalModel extends \yii\db\ActiveRecord
{

    public static $status_list = [
        0 => '停止开放',
        1 => '已开放',
        2 => '未开放',
    ];
    public static $view_status_list = [

        1 => '已开放',
        2 => '停止开放',
        3 => '未开放',
    ];

    public static $view_get_db_status = [
        1 => 1,
        2 => 0,
        3 => 2
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_platform_relation_hospital';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_platform', 'status', 'coo_platform', 'create_time', 'update_time', 'admin_id',], 'integer'],
            [['remarks'], 'string', 'max' => 50],
            [['admin_name'], 'string', 'max' => 50],
            [['tp_hospital_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coo_platform' => 'Coo Platform',
            'status' => 'Status',
            'tp_platform' => 'TP Platform',
            'tp_hospital_code' => 'Tp Hospital Code',
            'remarks' => 'Remarks',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     *  统计来源开放的医院数量
     * @param $coo_id
     * @return bool|int|string|null
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public static function getCountByCooPlatformId($coo_id)
    {
        if (empty($coo_id)) {
            return 0;
        }
        $tpPlatform = GuahaoPlatformListModel::getOpenCooTpPlatformIdListByCooId($coo_id);
        $hospQuery = self::find()
            ->where(['coo_platform' => intval($coo_id)])
            ->andWhere(['status'=>1])
            ->andWhere(['in','tp_platform',[1,2,8,9]])
            ->select('id')->asArray();
        $posts = $hospQuery->asArray()->count();
        return $posts;

    }

    /**
     *  开始/ 停止 开放操作
     * @param $ids
     * @param $openType
     * @param $coo_id
     * @param $remarks
     * @return false|string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public static function updateAllStartStop($ids, $openType, $cooId, $remarks, $adminInfo)
    {
        $idsArr = explode(',', $ids);

        if (count($idsArr) == 0) {
            return false;
        }

        // 备注日志
        $upMsgList = [];
        $updateStatus = self::$view_get_db_status[intval($openType)];
        $okCount = 0;
        $noCount = 0;
        foreach ($idsArr as $k => $v) {
            $vArr = explode('||||', $v);
            $params['tp_hospital_code'] = $vArr[0];
            $params['tp_platform'] = $vArr[1];
            $params['coo_platform'] = $cooId;
            $info = GuahaoPlatformRelationHospitalModel::find()->where($params)->one();
            if ($info) {
                $oldRemark = $info->remarks;
                $info->admin_id = $adminInfo['admin_id'];
                $info->admin_name = $adminInfo['admin_name'];
                $info->status = $updateStatus;
                $info->remarks = $remarks;
                $info->update_time = time();
                $res = $info->save();
                $hospitalName = GuahaoHospitalModel::getHospitalNameByTpCodeAndTpPlatform($vArr[1], $vArr[0]);
                $upMsg = $hospitalName . "的备注：【" . $oldRemark . '】改成了:' . $remarks;
                array_push($upMsgList, $upMsg);
            } else {
                $realModel = new GuahaoPlatformRelationHospitalModel();
                $realModel->tp_hospital_code = $vArr[0];
                $realModel->tp_platform = $vArr[1];
                $realModel->coo_platform = $cooId;
                $realModel->status = $updateStatus;
                $realModel->remarks = $remarks;
                $realModel->admin_id = $adminInfo['admin_id'];
                $realModel->admin_name = $adminInfo['admin_name'];
                $realModel->create_time = time();
                $realModel->update_time = time();
                $res = $realModel->save();
            }
            if ($res) {
                $okCount++;
            } else {
                $noCount++;
            }
        }
        $msg = '<br><br>5s 后自动关闭提示：<br>本次操作总条数【' . count($idsArr) . '】<br>成功条数:【' . $okCount . '】<br>失败条数 【' . $noCount . '】';
        $data['msg'] = $msg;
        $data['log'] = $upMsgList;

        return $data;
    }

    /**
     *  修改备注
     * @param $ids
     * @param $cooId
     * @param $remarks
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-07
     */
    public static function updateRemarks($ids, $relHospStatus, $cooId, $remarks, $adminInfo)
    {

        $vArr = explode('||||', $ids);
        $params['tp_hospital_code'] = $vArr[0];
        $params['tp_platform'] = $vArr[1];
        $params['coo_platform'] = $cooId;
        $returnMsg = '';
        $oldRemark = '';
        $info = GuahaoPlatformRelationHospitalModel::find()->where($params)->one();
        if ($info) {
            $oldRemark = $info->remarks;
            $info->admin_id = $adminInfo['admin_id'];
            $info->admin_name = $adminInfo['admin_name'];
            $info->remarks = $remarks;
            $info->update_time = time();
            $res = $info->save();
        }else{
            $realModel = new GuahaoPlatformRelationHospitalModel();
            $realModel->tp_hospital_code = $vArr[0];
            $realModel->tp_platform = $vArr[1];
            $realModel->coo_platform = $cooId;
            $realModel->status = self::$view_get_db_status[$relHospStatus] ?? 2;
            $realModel->remarks = $remarks;
            $realModel->admin_id = $adminInfo['admin_id'];
            $realModel->admin_name = $adminInfo['admin_name'];
            $realModel->create_time = time();
            $realModel->update_time = time();
            $res = $realModel->save();
        }
        if ($res) {
            $returnMsg = "的备注：【" . $oldRemark . '】改成了:【' . $remarks . '】';
        }
        return $returnMsg;
    }

    /**
     *  修改医院的全部开放来源为未开放
     * @param $hospitalCode
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public static function updateHospitalNoOpen($hospitalCode, $tpPlatform)
    {
        if(!empty($hospitalCode)){
            $params['tp_hospital_code'] = $hospitalCode;
            $params['tp_platform'] = $tpPlatform;
            $info = GuahaoPlatformRelationHospitalModel::find()->where($params)->one();
            $res = false;
            if($info){
                $res = GuahaoPlatformRelationHospitalModel::updateAll(
                    ['status' => 2,'remarks'=>'','update_time'=>time()],
                    ['tp_hospital_code' => $hospitalCode, 'tp_platform' => $tpPlatform]
                );
            }
            return $res;

        }
    }
    /**
     * 获取合作第三方id
     * @param $where
     * @return array|GuahaoPlatformRelationHospitalModel[]|\yii\db\ActiveRecord[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/7
     */
    public static function getCooList($where)
    {
        $coo_platform_arr = self::find()->where($where)->select('coo_platform')->asArray()->all();
        if ($coo_platform_arr) {
            $coo_platform_arr = ArrayHelper::getColumn($coo_platform_arr, 'coo_platform');
        }
        return $coo_platform_arr;
    }

    /**
     *  获取有效count
     * @return bool|int|string|null
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public static function getCount()
    {
        return self::find()->where(['status'=>1])->select('*')->count();
    }
}