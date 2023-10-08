<?php
/**
 *  第三方平台表
 * @file GuahaoPlatformModel.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/6/16
 */

namespace common\models;

use backend\controllers\GuahaoPlatformController;
use common\libs\CommonFunc;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;


class GuahaoPlatformListModel extends \yii\db\ActiveRecord
{

    public static $status_list = [
        0 => '禁用',
        1 => '正常',
    ];

    public static $schedule_type = [
        1=>"挂号",
        2=>"加号",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_guahao_platform_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'tp_platform', 'create_time', 'update_time', 'admin_id',], 'integer'],
            [['platform_name'], 'string', 'max' => 100],
            [['get_paiban_type'], 'string', 'max' => 50],
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
            'tp_platform' => 'Tp Platform',
            'status' => 'Status',
            'platform_name' => 'Platform Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * 第三方平台 tp_platform 名称
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-08
     */
    public static function getTpPlatformList()
    {
        $list = self::find()->where(['status'=>1])->select('tp_platform, platform_name')->asArray()->all();
        $arr = [];
        if($list){
            foreach ($list as $k=>$v){
                $arr[$v['tp_platform']] = $v['platform_name'];
            }
        }
        return $arr;
    }

    /**
     * 获取第三方平台缓存
     * @param false $update_cache
     * @return array|GuahaoCooListModel[]|mixed|\yii\db\ActiveRecord[]
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/11
     */
    public static function getPlatformListCache($update_cache = false)
    {
        $snisiyaSdk = new SnisiyaSdk();
        $key = Yii::$app->params['cache_key']['platform_list'];
        $data = [];
        if (!$update_cache) {
            $data = $snisiyaSdk->getPlatformList();
        }
        if (empty($data) || $update_cache) {
            $data = self::find()->select('tp_platform, platform_name, tp_type, sdk, skd, get_paiban_type,schedule_type,status,open_time')->asArray()->all();
            if ($data) {
                $data = array_column($data, null, 'tp_platform');
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //更新缓存
        self::getPlatformListCache(true);
    }

    /**
     *  获取列表
     * @return array|GuahaoPlatformListModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-11
     */
    public static function getList($params)
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $tpPlatformQuery = self::find()
            ->select('*');

        if (isset($params['status']) && $params['status'] !== '') {
            $tpPlatformQuery->andWhere(['status' => trim($params['status'])]);
        }

        if (!empty($params['platform_name'])) {
            $tpPlatformQuery->andWhere(['like','platform_name',$params['platform_name']]);
        }
        $totalCountQuery = clone $tpPlatformQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $tpPlatformQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('tp_platform')->asArray()->all();
        return $posts;
    }

    /**
     *  总数
     * @param $requestParams
     * @return bool|int|string|null
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-11
     */
    public static function getCount($params)
    {
        $doctorQuery = self::find()->select('*');
        if (isset($params['status']) && $params['status'] !== '') {
            $doctorQuery->andWhere(['status' => trim($params['status'])]);
        }

        if (!empty($params['platform_name'])) {
            $doctorQuery->andWhere(['like','platform_name',$params['platform_name']]);
        }
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    /**
     *  报存数据
     * @param $data
     * @return bool|string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-15
     */
    public static function dataSave($data)
    {
        $id = $data['id'] ?? 0;

        if($id){
            $platformModel = self::find()->where(['id'=>$id])->one();
        }else{
            $platformModel = new GuahaoPlatformListModel();
            $platformModel->create_time = time();
        }
        try {
            $platformModel->tp_platform = $data['tp_platform'];
            $platformModel->platform_name = $data['platform_name'];
            $platformModel->tp_type = $data['tp_type'];
            $platformModel->sdk = $data['sdk'];
            $platformModel->get_paiban_type = $data['get_paiban_type'];
            $platformModel->schedule_type = $data['schedule_type'];
            $platformModel->status = $data['status'];
            $platformModel->open_time = $data['open_time'];
            $platformModel->admin_name = $data['admin_name'];
            $platformModel->admin_id = $data['admin_id'];
            $platformModel->update_time = time();
            return $platformModel->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *  获取医院数据
     * @param $params
     * @return array|GuahaoPlatformListModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-14
     */
    public static function getReaList($params)
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $cooPlatform = isset($params['coo_platform']) ? intval($params['coo_platform']) : '';
        $hospitalListQuery = self::find()
            ->alias('plat_list')
            ->where('plat_list.status=1')
            ->select('plat_list.tp_platform ,plat_list.id,plat_list.platform_name,guahao_plat.id as plat_id,guahao_plat.coo_platform,guahao_plat.status,guahao_plat.remarks,guahao_plat.id as guahao_platform_id')
            ->leftJoin(GuahaoPlatformModel::tableName().' as guahao_plat', 'guahao_plat.tp_platform=plat_list.tp_platform');

        if ($cooPlatform) {
            $hospitalListQuery->andWhere(['guahao_plat.coo_platform'=>$cooPlatform]);
        }
        $totalCountQuery = clone $hospitalListQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $hospitalListQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('plat_list.tp_platform')->asArray()->all();
        return $posts;
    }


    /**
     * 获取第三方类型
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/14
     */
    public static function getPlatformType()
    {
        $data = self::find()->select('tp_platform, tp_type')->where(['status' => 1])->asArray()->all();
        if ($data) {
            return array_column($data, 'tp_platform', 'tp_type');
        } else {
            return [];
        }
    }

    /**
     * 获取对应SDK
     * @param string $tp_type
     * @return array|string|string[]
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/28
     */
    public static function getPlatformSdk($tp_type = '')
    {
        $data = self::find()->select('tp_platform,sdk')->where(['status' => 1, 'tp_type' => $tp_type])->asArray()->one();
        if ($data) {
            $data['sdk'] = str_replace(['Gh', 'Sdk'], '', $data['sdk']);
            return $data;
        } else {
            return [];
        }
    }

    /**
     *  获取开放后的来源id
     * @param $cooId
     * @return array
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-14
     */
    public static function getOpenCooTpPlatformIdListByCooId($cooId, $type=0)
    {
        $tpPlatformlist = GuahaoPlatformModel::getTpPlatformIdListByCooId($cooId);

        $platformList = GuahaoPlatformListModel::getPlatformListCache();
        $platArr = [];
        // 有效的来源
        foreach($platformList as $tpk=>$tpv){
            if($tpv['status'] == 1){
                $platArr[$tpv['tp_platform']] = $tpv['platform_name'];
            }
        }
        $tpList = [];

        foreach($tpPlatformlist as $tpk2=>$tpv2){
            if(isset($platArr[$tpv2]) && $platArr[$tpv2]){
                if($type==1){
                    $tpList[$tpv2] = $platArr[$tpv2];
                }else{
                    array_push($tpList, $tpv2);
                }
            }
        }
        return $tpList;
    }

    /**
     *  获取开放后的来源名称
     * @param $tp_platform
     * @return string
     * @throws \Exception
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-03-08
     */
    public static function getPlatformNameByTpPlatform($tp_platform)
    {
        //获取缓存
        $platformList = GuahaoPlatformListModel::getPlatformListCache();
        if (!empty($platformList)) {
            $platArr = [];
            // 有效的来源
            foreach($platformList as $tpk=>$tpv){
                if($tpv['status'] == 1){
                    $platArr[$tpv['tp_platform']] = $tpv['platform_name'];
                }
            }
            $platform_name = isset($platArr[$tp_platform]) ? $platArr[$tp_platform] : "";
        } else {
            $platArr = self::find()->select('id,platform_name')->where(['status' => 1, 'tp_platform' => $tp_platform])->asArray()->one();
            $platform_name = isset($platArr['platform_name']) ? $platArr['platform_name'] : "";
        }
        return $platform_name;
    }
}