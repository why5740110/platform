<?php

namespace common\models;

use common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\sdks\HospitalSdk;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\helpers\ArrayHelper;
use common\models\GuahaoHospitalModel;
use common\models\minying\MinHospitalModel;

/**
 * This is the model class for table "base_doctor_hospitals_online".
 *
 * @property int $id
 * @property int $province_id 省id
 * @property int $city_id 城市id
 * @property int $district_id 区id
 * @property string $province_name 省名称
 * @property string $city_name 市名称
 * @property string $district_name 区名称
 * @property string $name
 * @property string $nick_name
 * @property string $phone
 * @property string $address
 * @property string $site 网址
 * @property string $mail_num
 * @property string $fax_num 传真
 * @property string $email
 * @property string $routes 路线
 * @property string $level
 * @property string $insurance
 * @property string $type
 * @property string $kind
 * @property string $tsks
 * @property string $photo
 * @property string $description
 * @property string $city_uri
 * @property string $page_uri
 * @property string $url
 * @property string $updated_at
 * @property string $created_at
 * @property int $is_shequ 是否是社区服务站
 * @property string $new_insurance 新农合
 * @property string $insurance_num 医保编码
 * @property int $display_order 医院显示顺序
 * @property int $status 状态 0 正常 1未审核
 * @property int $level_num 医院级别对应数字
 * @property int $store_id 药店id
 * @property int $score 医院评分,用于排序
 * @property string $source 医院来源，默认，王氏:miaosh，家庭医生：familydoctor
 * @property string $is_hospital_project 是否是医院业务线数据，默认0：否，1：是
 * @property string $longitude 医院经度
 * @property string $latitude 医院纬度
 */
class BaseDoctorHospitals extends \common\models\BaseModel
{

    public static $levellist = [
        2=>'三级甲等',
        3=>'三级乙等',
        4=>'三级丙等',
        5=>'二级甲等',
        6=>'二级乙等',
        7=>'二级丙等',
        8=>'一级甲等',
        9=>'一级乙等',
        10 =>'一级丙等',
    ];

    public static $Typelist = [
        1=>'综合',
        2=>'专科',
    ]; 

    public static $Platformlist = [
        0=>'家庭医生',
        1=>'河南医生',
        2=>'南京医生',
        3=>'好大夫',
    ];

    public static $Kindlist = [
        1=>'公立',
        2=>'私立',
        3=>'其他',
        4=>'未定义',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        //return 'base_doctor_hospitals';
    }

    public static function getDb()
    {
        //return Yii::$app->get('data_base_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'city_id', 'district_id', 'is_shequ', 'display_order', 'status', 'level_num', 'store_id', 'score'], 'integer'],
            [['name', 'level_num'], 'required'],
            [['routes', 'description'], 'string'],
            [['updated_at', 'created_at'], 'safe'],
            [['province_name', 'city_name', 'district_name'], 'string', 'max' => 100],
            [['name', 'nick_name', 'phone', 'address', 'site', 'city_uri', 'page_uri', 'url'], 'string', 'max' => 255],
            [['mail_num', 'fax_num', 'email', 'level', 'insurance', 'type', 'kind'], 'string', 'max' => 64],
            [['tsks', 'photo'], 'string', 'max' => 512],
            [['new_insurance'], 'string', 'max' => 8],
            [['insurance_num'], 'string', 'max' => 20],
            [['source'], 'string', 'max' => 30],
            [['longitude', 'latitude'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
            'province_name' => 'Province Name',
            'city_name' => 'City Name',
            'district_name' => 'District Name',
            'name' => 'Name',
            'nick_name' => 'Nick Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'site' => 'Site',
            'mail_num' => 'Mail Num',
            'fax_num' => 'Fax Num',
            'email' => 'Email',
            'routes' => 'Routes',
            'level' => 'Level',
            'insurance' => 'Insurance',
            'type' => 'Type',
            'kind' => 'Kind',
            'tsks' => 'Tsks',
            'photo' => 'Photo',
            'description' => 'Description',
            'city_uri' => 'City Uri',
            'page_uri' => 'Page Uri',
            'url' => 'Url',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'is_shequ' => 'Is Shequ',
            'new_insurance' => 'New Insurance',
            'insurance_num' => 'Insurance Num',
            'display_order' => 'Display Order',
            'status' => 'Status',
            'level_num' => 'Level Num',
            'store_id' => 'Store ID',
            'score' => 'Score',
            'source' => 'Source',
        ];
    }

    /**
     * 设置医院详情缓存
     * @param $id
     * @param bool $update_cache
     * @return array|bool|mixed|\yii\db\ActiveRecord|null
     * @author xiujianying
     * @date 2020/7/24
     */
    public static function HospitalDetail($id, $update_cache = false)
    {
        $hospital_key = 'hospital:detail:'.$id;

        //$data = CommonFunc::getCodisCache($hospital_key);
        $sdk = SnisiyaSdk::getInstance();
        $hash_id = HashUrl::getIdEncode($id);
        if(!$update_cache) {
            $data = $sdk->getHospitalDetail(['hospital_id' => $hash_id]);
        }
        if ($update_cache) {
            /*$model = new BaseDoctorHospitals();
            $data = $model->find()->where(['id' => $id])->select([
                'province_id',
                'city_id',
                'province_name',
                'city_name',
                'name',
                'district_id',
                'nick_name',
                'phone',
                'address',
                'mail_num',
                'fax_num',
                'email',
                'routes',
                'level',
                'type',
                'kind',
                'photo',
                'description',
                'logo',
                'longitude',
                'latitude',
            ])->asArray()->one();
            if($data){
            */
            $fields = [
                'province_id',
                'city_id',
                'province_name',
                'city_name',
                'name',
                'district_id',
                'nick_name',
                'phone',
                'address',
                'mail_num',
                'fax_num',
                'email',
                'routes',
                'level',
                'type',
                'kind',
                'photo',
                'description',
                'logo',
                'longitude',
                'latitude',
                'status',
                'is_hospital_project',
            ];
            $hosData = BaseDoctorHospitals::getHospitalDetail($id);
            if($hosData){
                foreach($fields as $v){
                    $data[$v] = ArrayHelper::getValue($hosData,$v,'');
                }

                $data['id'] = HashUrl::getIdEncode($id);
                if (in_array($data['province_name'], ['北京','上海','天津','重庆'])) {
                    $data['city_id'] = $data['district_id'];
                }
                //挂号信息
                $data['tp_hospital_code'] = '';
                $data['tp_platform'] = 0;
                $guahaoRow = GuahaoHospitalModel::find()->where(['hospital_id'=>$id,'status'=>1])->select('tp_hospital_code,tp_platform,tp_guahao_section,tp_guahao_verify')->asArray()->one();
                if($guahaoRow){
                    $data['tp_hospital_code'] = ArrayHelper::getValue($guahaoRow,'tp_hospital_code');
                    $data['tp_platform'] = ArrayHelper::getValue($guahaoRow,'tp_platform');
                    $data['tp_guahao_section'] = ArrayHelper::getValue($guahaoRow,'tp_guahao_section');
                    $data['tp_guahao_verify'] = ArrayHelper::getValue($guahaoRow,'tp_guahao_verify');

                    //更新民营医院标签到缓存中
                    if ($data['tp_platform'] == 13 && !empty($data['tp_hospital_code'])) {
                        $minHospitalModel = MinHospitalModel::findOne($data['tp_hospital_code']);
                        $data['hospital_tags'] = $minHospitalModel->min_hospital_tags ? MinHospitalModel::getTagsInfo($minHospitalModel->min_hospital_tags) : "";
                    }
                }
                $tmpInfo = GuahaoHospitalModel::find()->select('tp_platform,tp_hospital_code,tp_guahao_section,tp_guahao_verify,tp_allowed_cancel_day,tp_allowed_cancel_time,tp_guahao_description')->where([
                    'hospital_id' => $id,
                    'status' => 1,
                ])->asArray()->all();

                //是否有号
                $data['hospital_is_plus'] = 0;
                $incr_num = DoctorModel::find()->where(['hospital_id' => $id,'is_plus'=>1])->count();
                $guahaoInfo = GuahaoHospitalModel::find()->where(['hospital_id' => $id,'status'=>1])->one();
                if ($guahaoInfo || $incr_num > 0) {
                    $data['hospital_is_plus'] = 1;
                }

                if($tmpInfo){
                    $data['tb_third_party_relation']=$tmpInfo;
                }

                //获取放号时间
                $hospitalTimeConfig = GuahaoHospitalModel::find()
                    ->select('tp_open_day,tp_open_time')
                    ->where(['hospital_id' => $id, 'status' => 1])
                    ->andWhere(['>', 'tp_open_day', '0'])
                    ->andWhere(['<>', 'tp_open_time', ''])
                    ->orderBy('id desc')
                    ->asArray()
                    ->one();
                $data['hospital_open_day'] = ArrayHelper::getValue($hospitalTimeConfig, 'tp_open_day', 0);
                $data['hospital_open_time'] = ArrayHelper::getValue($hospitalTimeConfig, 'tp_open_time', '');

                CommonFunc::setCodisCache($hospital_key, $data);
            }
        }
        return $data??[];

    }

    /**
     * 查询单条医院信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-22
     * @version v1.0
     * @param   string     $hospital_id [description]
     * @return  [type]           [description]
     */
    public static function getInfo($hospital_id = 0)
    {
        $model = new BaseDoctorHospitals();
        $data = [];
        /*$query = BaseDoctorHospitals::find()->select('id,name,level,kind')->where(['id'=>$hospital_id]);
        $data = $query->asArray()->one();*/

        $data = BaseDoctorHospitals::getHospitalDetail($hospital_id);

        return $data;
    }

    public static function getList($name = '')
    {
        $model = new BaseDoctorHospitals();
        $data = [];
        if (!empty($name)) {
            if (strlen(intval(trim($name))) == strlen(trim($name))) {
//                $data = $model->find()->where(['id'=>$name,/*'status'=>0,'is_hospital_project'=>1*/])->select([
//                    'id','name','level'
//                ])->asArray()->one();

                $data = BaseDoctorHospitals::getHospitalSearch(['id'=>$name],'one');

            } else {
//                $data = $model->find()->where(['like','name',trim($name)])->andWhere(['status'=>0,'is_hospital_project'=>1])->select([
//                    'id','name','level'
//                ])->asArray()->all();
                $data = BaseDoctorHospitals::getHospitalSearch(['status'=>0,'is_hospital_project'=>1,'name'=>trim($name)]);
            }



        }

        return $data;
    }

    /**
     * 根据名称获取医生列表
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-15
     * @version v1.0
     * @param   string     $name [description]
     * @return  [type]           [description]
     */
    public static function getListByName($name = '')
    {
        /*$query   = BaseDoctorHospitals::find()->select('id,name,level')->where(['status'=>0,'is_hospital_project'=>1]);
        if (is_numeric($name)) {
            $query->andWhere(['id' => $name]);
        } else {
            $query->andWhere(['like', 'name', $name]);
        }
        $result = $query->asArray()->all();*/

        $where = ['status'=>0,'is_hospital_project'=>1];
        if (is_numeric($name)) {
            $where['id'] = $name;
        } else {
            $where['name'] = $name;
        }
        $result = BaseDoctorHospitals::getHospitalSearch($where);
        if ($result) {
            foreach ($result as &$item) {
                $item['name'] = $item['id'].'-'.$item['name'];
            }
        }
        return $result;
    }

    /**
     * 获取base 医院信息
     * @param $hospital_id
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/10/14
     */
    public static function getHospitalDetail($hospital_id){
        $hosSdk = HospitalSdk::getInstance();
        $hosData = $hosSdk->getDetail($hospital_id);
        return $hosData;
    }

    /**
     * 查询base 医院信息
     * @param array $where
     * @param string $returnFormt  all:多条记录 one:一条记录  scalar:单个字段（$fields 必填）
     * @param string $fields
     * @return array|mixed
     * @throws \Exception
     * @author xiujianying
     * @date 2021/10/14
     */
    public static function getHospitalSearch($where=[],$returnFormt='all',$fields=''){
        $data = [];
        $hosSdk = HospitalSdk::getInstance();

        if($where){
            if(isset($where['name'])){
                $where['hospital_name'] = $where['name'];
                unset($where['name']);
            }

            $params = $where;
            $hosData = $hosSdk->search($params);
            if($hosData){
                $data = ArrayHelper::getValue($hosData,'list');
                if($data && is_array($data)){
                    if($returnFormt=='one'){
                        $data = current($data);
                    }elseif ($returnFormt=='scalar'){
                        $data = current($data);
                        if($fields){
                            $data = ArrayHelper::getValue($data,$fields);
                        }else{
                            $data = '';
                        }
                    }
                }
            }
        }

        return $data;

    }



}
