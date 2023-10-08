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

/**
 * This is the model class for table "tb_guahao_platform".
 *
 * @property int $id 自增id
 * @property int $coo_platform 和咱们对接的平台（1：bd(百度)）
 * @property int $tp_platform 咱们对接的第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西)
 */
class GuahaoPlatformModel extends \yii\db\ActiveRecord
{
   // 这里要状态根据实际数据表
    public static $status_list = [
        0=>'未开放',
        1=>'已开放',
        2=>'停止开放',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_platform';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coo_platform', 'tp_platform','admin_id','update_time','create_time'], 'integer'],
            [['remarks'], 'string', 'max' => 50],
            [['admin_name'], 'string', 'max' => 50],
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
            'tp_platform' => 'Tp Platform',
            'status' => 'Status',
            'remarks' => 'Remarks',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * 对外合作方id 获取合作的tpid
     * @param $cooId    1:百度
     * @return array  [1,2]
     * @author xiujianying
     * @date 2021/6/22
     */
    public static function getTp($cooId)
    {
        $tp_platform_arr = GuahaoPlatformModel::find()->where(['coo_platform' => $cooId])->select(['tp_platform'])->asArray()->all();
        if ($tp_platform_arr) {
            $tp_platform_arr = ArrayHelper::getColumn($tp_platform_arr, 'tp_platform');
        }
        return $tp_platform_arr;
    }

    /**
     * 接入的第三方tpid 获取对外的合作方id
     * @param $tpId   1:河南，2:南京，3:好大夫,4:王氏,5:健康160,6:王氏医生加号,7:陕西
     * @return array  [1,2]
     * @author xiujianying
     * @date 2021/6/22
     */
    public static function getCoo($tpId)
    {
        $coo_platform_arr = GuahaoPlatformModel::find()->where(['tp_platform' => $tpId])->select(['coo_platform'])->asArray()->all();
        if ($coo_platform_arr) {
            $coo_platform_arr = ArrayHelper::getColumn($coo_platform_arr, 'coo_platform');
        }
        return $coo_platform_arr;
    }

    /**
     *  获取条数
     * @param $params
     * @return bool|int|string|null
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public static function getCount($params){
      $doctorQuery = self::find()->select('*');
      $posts = $doctorQuery->asArray()->count();
      return $posts;
    }

    /**
     *  根据合作平台id 获取开放的来源id lis
     * @param $cooId
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-04
     */
    public static function getTpPlatformIdListByCooId($cooId)
    {
        $idsListResult = self::find()->where(['coo_platform' => $cooId,'status'=>1])->select(['tp_platform'])->asArray()->all();
        $idList = [];
        if($idsListResult){
            foreach($idsListResult as $v){
                array_push($idList, $v['tp_platform']);
            }
        }
        return $idList;
    }
}