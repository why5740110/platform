<?php
/**
 *  合作平台表
 * @file GuahaoPlatformModel.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/16
 */

namespace common\models;

use common\libs\CommonFunc;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;


class GuahaoCooListModel extends \yii\db\ActiveRecord
{

    public static $status_list = [
        0 => '禁用',
        1 => '正常',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_coo_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'coo_platform', 'create_time', 'update_time', 'admin_id',], 'integer'],
            [['open_time'], 'date'],
            [['coo_name'], 'string', 'max' => 100],
            [['admin_name'], 'string', 'max' => 50]
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
            'coo_name' => 'Coo Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'open_time' => 'Open Time',
        ];
    }

    /**
     * 获取合作第三方id 名称
     * @param $where
     * @return array|GuahaoCooListModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public static function getCooPlatformList()
    {
        $list = self::find()->select('coo_platform, coo_name')->asArray()->all();
        $arr = [];
        if($list){
            foreach ($list as $k=>$v){
                $arr[$v['coo_platform']] = $v['coo_name'];
            }
        }
        return $arr;
    }

    /**
     *  获取列表
     * @return array|GuahaoCooListModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public static function getList($cooId = "")
    {

        $query = self::find()
            ->where(['status'=>1])
            ->select('id, coo_platform, coo_name,open_time,docking');
        if($cooId){
            $query->andWhere(['coo_platform'=>$cooId]);
        }

        return $query->asArray()->all();
    }

    /**
     *  根据coo_platform 获取名称
     * @param $cooPlatform
     * @return array|GuahaoCooListModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-09
     */
    public static function getCooNameByCooPlatform($cooPlatform)
    {
        $findName =self::find()->where(['status'=>1,'coo_platform'=>strval($cooPlatform)])->select('coo_name')->one();
        $cooName = '';
        if($findName){
            $cooName = $findName->coo_name;
        }
        return $cooName;
    }

    /**
     * 获取合作平台缓存
     * @param false $update_cache
     * @return array|GuahaoCooListModel[]|mixed|\yii\db\ActiveRecord[]
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/9
     */
    public static function getCooPlatformListCache($update_cache = false)
    {
        $snisiyaSdk = new SnisiyaSdk();
        $key = Yii::$app->params['cache_key']['coo_list'];
        $data = [];
        if (!$update_cache) {
            $data = $snisiyaSdk->getCooList();
        }
        if (empty($data) || $update_cache) {
            $data = self::getCooPlatformList();
            if ($data) {
                CommonFunc::setCodisCache($key, $data);
            } else {
                CommonFunc::setCodisCache($key, []);
                return [];
            }
        }
        if ($data) {
            return $data;
        }
        return [];
    }

    /**
     * 检查平台是否可用
     * @param $coo_platform_id
     * @return array|GuahaoCooListModel[]|mixed|\yii\db\ActiveRecord[]
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/9
     */
    public static function checkCooPlatform($coo_platform_id)
    {
        $list = self::getCooPlatformListCache();
        if (!empty(ArrayHelper::getValue($list, $coo_platform_id))) {
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //更新缓存
        self::getCooPlatformListCache(true);
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