<?php


namespace common\models;
use common\libs\CommonFunc;
use common\models\minying\MinHospitalModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\data\Pagination;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class GuahaoHospitalModel extends \common\models\BaseModel
{

    public static $status_list = [
        0=>'未关联',
        1=>'已关联',
        2=>'禁用',
    ];

    public static $import = [
        '-1' => '不可导入',
        '0'  => '未导入',
        '1'  => '已导入'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_guahao_hospital}}';
    }


    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::find()
            ->select('*');
        if (!empty($params['tp_platform'])) {
            $doctorQuery->where(['tp_platform'=>intval($params['tp_platform'])]);
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $doctorQuery->andWhere(['status' => intval($params['status'])]);
        }

        // if (!empty($params['hospital_id'])) {
        //     if($params['hospital_id']==1){
        //         $doctorQuery->andWhere(['>','hospital_id',0]);
        //     }elseif($params['hospital_id']==999){
        //         $doctorQuery->andWhere(['hospital_id'=>0]);
        //     }
        // }

        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like','hospital_name',$params['hospital_name']]);
        }
        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('id desc')->asArray()->all();
        //echo $doctorQuery->createCommand()->getRawSql();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::find()->select('*');
        if (!empty($params['tp_platform'])) {
            $doctorQuery->where(['tp_platform'=>intval($params['tp_platform'])]);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $doctorQuery->andWhere(['status' => intval($params['status'])]);
        }
        // if (!empty($params['hospital_id'])) {
        //     if($params['hospital_id']==1){
        //         $doctorQuery->andWhere(['>','hospital_id',0]);
        //     }elseif($params['hospital_id']==999){
        //         $doctorQuery->andWhere(['hospital_id'=>0]);
        //     }
        // }
        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like','hospital_name',$params['hospital_name']]);
        }

        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    /**
     * 获取第三方医院信息
     * @param $tp_platform
     * @param $tp_hospital_code
     * @param false $update_cache
     * @return array|GuahaoHospitalModel|mixed|\yii\db\ActiveRecord
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/7/28
     */
    public static function getTpHospitalCache($tp_platform, $tp_hospital_code, $update_cache = false)
    {
        $snisiyaSdk = new SnisiyaSdk();
        $key = sprintf(Yii::$app->params['cache_key']['tp_hospital_info'], $tp_platform, $tp_hospital_code);
        $data = [];
        if (!$update_cache) {
            $data = $snisiyaSdk->getTpHospitalInfo(['tp_platform' => $tp_platform, 'tp_hospital_code' => $tp_hospital_code]);
        }
        if (!isset($data['tp_hospital_code']) || $update_cache) {
            $data = self::find()->select('
            tp_hospital_code,
            tp_platform,
            hospital_id,
            hospital_name,
            corp_id,
            status,
            tp_guahao_section,
            tp_guahao_verify,
            tp_allowed_cancel_day,
            tp_allowed_cancel_time,
            tp_guahao_description,
            tp_open_day,
            tp_open_time
            ')->where(['tp_platform' => $tp_platform, 'tp_hospital_code' => $tp_hospital_code, 'status' => 1])->asArray()->one();

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
     * 前端获取王氏医院列表api接口调用，返回20条，超出范围使用条件搜索
     * @param $params
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-26
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public static function getMiaoHospitalListForApi($params)
    {
        $query = self::find()->where(['status' => 1]);
        // 非民营医院
        $query->andWhere(['!=', 'tp_platform', 13]);
        $limit = ArrayHelper::getValue($params, 'limit', 20);
        if ($hospital_name = ArrayHelper::getValue($params, 'hospital_name')) {
            $query->andWhere(['like', 'hospital_name', $hospital_name]);
        }
        return $query->select('hospital_id,hospital_name')->limit($limit)->asArray()->all();
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::getTpHospitalCache($this->tp_platform, $this->tp_hospital_code, true);
        //更新医院缓存
        BaseDoctorHospitals::HospitalDetail($this->hospital_id, true);
        //更新医院es
        $model = new BuildToEsModel();
        $model->db2esByIdHospital($this->hospital_id);
    }

    /**
     *  合作列表数据
     * @param $coo_tp_platform
     * @param $params
     * @return array|GuahaoHospitalModel[]|\yii\db\ActiveRecord[]
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public static function getHosptailListAssociateCoo($coo_tp_platform,$params)
    {
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $hospitalListQuery = self::getCommonQuery($coo_tp_platform,$params);
        $totalCountQuery = clone $hospitalListQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $hospitalListQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('hosp.id desc')->asArray()->all();
        return $posts;
    }

    /**
     * @param $coo_tp_platform
     * @param $params
     * @return bool|int|string|null
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public static function getHosptailCooCount($coo_tp_platform, $params){
        $hospitalListQuery = self::getCommonQuery($coo_tp_platform,$params);
        $posts = $hospitalListQuery->asArray()->count();
        return $posts;
    }

    /**
     *  共用搜索条件
     * @param $coo_tp_platform
     * @param $params
     * @return \yii\db\ActiveQuery
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public static function getCommonQuery($coo_tp_platform, $params)
    {

        if(isset($params['tp_platform']) && intval($params['tp_platform']) !== 0){
            $coo_tp_platform = [];
            array_push($coo_tp_platform,$params['tp_platform']);
        }
        $hospitalListQuery = self::find()
            ->alias('hosp')
            ->where('hosp.status=1')
            ->andWhere(['in', 'hosp.tp_platform', $coo_tp_platform])
            ->select('hosp.tp_hospital_code,hosp.hospital_name,hosp.tp_platform,hosp.id as hosp_id,hosp.province,hosp.tp_hospital_level,hosp.tp_platform');
        if (isset($params['status']) && $params['status'] == 3) {
            $tpCodeList = GuahaoPlatformRelationHospitalModel::find()->select('tp_hospital_code')->where(['coo_platform'=>$params['coo_id']])->asArray()->all();
            $list = array_column($tpCodeList, 'tp_hospital_code');
            $hospitalListQuery->andWhere(['not in', 'hosp.tp_hospital_code', $list]);
        }
        if (isset($params['status']) && in_array($params['status'],[1,2]) && isset($params['coo_id'])) {
            $hospitalListQuery->leftJoin(GuahaoPlatformRelationHospitalModel::tableName().' as rel_hosp', 'hosp.tp_hospital_code = rel_hosp.tp_hospital_code and hosp.tp_platform = rel_hosp.tp_platform');
            $arr = [1=>1, 2=>0];
            $selectStatus = $arr[intval($params['status'])];
            $hospitalListQuery->andWhere(['rel_hosp.status' => $selectStatus]);
            $hospitalListQuery->andWhere(['rel_hosp.coo_platform' => $params['coo_id']]);

        }
        if (!empty($params['hospital_name'])) {
            $hospitalListQuery->andWhere(['like','hosp.hospital_name',trim($params['hospital_name'])]);
        }

        return $hospitalListQuery;
    }

    /**
     *  根据id list 获取医院名称
     * @param $idList
     * @return array|false
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-06
     */
    public static function getHospitalNameListByIdList($idList)
    {
        $idsArr = explode(',', $idList);
        if (count($idsArr) == 0) {
            return false;
        }
        $hospitalName = [];
        foreach($idsArr as $k=>$v){
            $vArr = explode('||||', $v);
            $hospData = self::find()
                ->where(['tp_platform'=>$vArr[1],'tp_hospital_code'=>$vArr[0]])
                ->select('hospital_name')
                ->one();
            if($hospData){
                array_push($hospitalName,$hospData->hospital_name);
            }
        }
        return $hospitalName;
    }

    /**
     *  根据来源和第三方医院code 获取医院名称
     * @param $tp_platform
     * @param $tp_hospital_code
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-07
     */
    public static function getHospitalNameByTpCodeAndTpPlatform($tp_platform,$tp_hospital_code)
    {
        $hospitalName = '';
        $hospData = self::find()
            ->where(['tp_platform'=>$tp_platform,'tp_hospital_code'=>$tp_hospital_code])
            ->select('hospital_name')
            ->one();
        if($hospData){
            $hospitalName =  $hospData->hospital_name;
        }
        return $hospitalName;
    }

    //获取禁用医院的关联王氏医院id
    public function getDisableHospital()
    {
        return self::find()->where(['status' =>  2])->asArray()->all();
    }

    /**
     * 民营医院关联模型
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-27
     */
    public function getMinHospital(){
        return $this->hasOne(MinHospitalModel::class, ['min_hospital_id' => 'tp_hospital_code']);
    }
}
