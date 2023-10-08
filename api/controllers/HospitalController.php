<?php
/**
 * @file HospitalController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/25
 */


namespace api\controllers;


use common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\CryptoTools;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class HospitalController extends CommonController
{

    public $params;

    public function init()
    {

        $data = [];
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
        }
        if (\Yii::$app->request->isGet) {
            $data = \Yii::$app->request->get();
        }

        //获取 appkey
        $appid = ArrayHelper::getValue($data,'appid');
        $configs = \Yii::$app->params['appidcryptokey'];
        $encryptKey = ArrayHelper::getValue($configs,$appid.'.appkey');
        CryptoTools::setKey($encryptKey);
        //获取参数
        if (ArrayHelper::getValue($configs, $appid . '.checkrules') == 'AES-256-ECB') {
            $encryptData = ArrayHelper::getValue($data,'data');
            $encryptData = urldecode($encryptData);
            $this->params = json_decode(CryptoTools::AES256ECBDecrypt($encryptData), true);
        } else {
            $this->params = $data;
        }

        parent::init();
    }

    /**
     * 医院详情接口
     * @return array
     * @author xiujianying
     * @date 2020/7/25
     */
    public function actionDetail()
    {
        $hospital_id = \Yii::$app->request->get('hospital_id');
        $update_cache = \Yii::$app->request->get('update_cache', 0);
        if ($hospital_id) {
            $detail = BaseDoctorHospitals::HospitalDetail($hospital_id,$update_cache);
            return $this->jsonSuccess($detail);
        } else {
            return $this->jsonError('无医院id');
        }
    }

    /**
     * 医院下科室
     * @return array
     * @author xiujianying
     * @date 2020/7/25
     */
    public function actionDepartment()
    {
        $hospital_id = \Yii::$app->request->get('hospital_id');
        $update_cache = \Yii::$app->request->get('update_cache', 0);
        if ($hospital_id) {
            $data = HospitalDepartmentRelation::hospitalDepartment($hospital_id,$update_cache);
            return $this->jsonSuccess($data);
        } else {
            return $this->jsonError('无医院id');
        }
    }

    /**
     * 常见科室
     * @return array
     * @author xiujianying
     * @date 2020/7/25
     */
    public function actionCommonDepartment()
    {
        $data = Department::department();
        return $this->jsonSuccess($data);
    }

    public function actionDistrict(){
        $data = Department::district(true);
        print_r($data);exit;
    }

    /**
     * 医链医院搜索接口
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/6/25
     */
    public function actionSo()
    {
        $keyword = ArrayHelper::getValue($this->params,'keyword');
        $page = ArrayHelper::getValue($this->params,'page');
        $page = intval($page);
        $pagesize = ArrayHelper::getValue($this->params,'pagesize');
        $pagesize = intval($pagesize);
        $pagesize = min($pagesize, 20);

        $province_id = ArrayHelper::getValue($this->params,'province_id');
        $province_id = intval($province_id);
        $city_id = ArrayHelper::getValue($this->params,'city_id');
        $city_id = intval($city_id);
        $region_pinyin = '';
        //获取地区
        $district = SnisiyaSdk::getInstance()->getDistrict();
        if($province_id){
            $pArr = ArrayHelper::getValue($district,$province_id);
            $region_pinyin = ArrayHelper::getValue($pArr,'pinyin');
            if($city_id && isset($pArr['city_arr']) && is_array($pArr['city_arr']) ){
                foreach ($pArr['city_arr'] as $v){
                    if($v['id']==$city_id){
                        $region_pinyin = $v['pinyin'];
                    }
                }
            }
        }
        $sanjia = ArrayHelper::getValue($this->params,'hospital_level');
        $sanjia = intval($sanjia);
        $hospList = [];
        $totalcount = 0;
        if ($keyword) {
            $data = SnisiyaSdk::getInstance()->getSearchList([
                'type' => 'hospital',
                'keyword' => $keyword,
                'region_pinyin' => $region_pinyin,
                'sanjia' => $sanjia,
                'pagesize' => $pagesize,
                'page' => $page
            ]);
            if ($data) {
                $totalcount = ArrayHelper::getValue($data,'totalCount');
                $data = ArrayHelper::getValue($data, 'list');
                foreach ($data as $v) {
                    $row['hospital_id'] = HashUrl::getIdEncode(ArrayHelper::getValue($v, 'hospital_id'));
                    $row['hospital_name'] = ArrayHelper::getValue($v, 'hospital_name');
                    $row['hospital_photo'] = ArrayHelper::getValue($v, 'hospital_photo');
                    $row['hospital_level'] = ArrayHelper::getValue($v, 'hospital_level_alias');
                    $row['tips'] = CommonFunc::openTimeStr(ArrayHelper::getValue($v, 'hospital_open_day', 0)); //08:30放第7天号源
                    if(ArrayHelper::getValue($v,'hospital_real_plus')==1){
                        $row['url'] = rtrim(ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile'), '/') . Url::to(['guahao/keshilist', 'hospital_id' => ArrayHelper::getValue($v, 'hospital_id')]);
                    }else{
                        $row['url'] = rtrim(ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile'), '/') . Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($v, 'hospital_id')]);
                    }

                    $hospList[] = $row;
                }

            }
        }

        $return['page'] = $page;
        $return['pagesize'] = $pagesize;
        $return['totalcount'] = $totalcount;
        $return['data']['list'] = $hospList;

        return $this->jsonSuccess($return);
    }



}