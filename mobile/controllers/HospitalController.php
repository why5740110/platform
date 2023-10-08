<?php
/**
 * @file HospitalController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/8/11
 */


namespace mobile\controllers;


use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\helpers\Url;
use common\sdks\BapiAdSdkModel;
use mobile\widget\WechatShareWidget;
use common\models\BaseDoctorHospitals;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\sRpcSdk;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\models\MedicalModel;
use common\sdks\snisiya\SnisiyaSdk;
use yii\web\Response;
use Yii;

class HospitalController extends CommonController
{

    public $pageSize = 10;
    public $hospital_id;
    public $data;

    public function init()
    {
        $this->hospital_id = \Yii::$app->request->get('hospital_id');

        if($this->hospital_id){
            $this->hospital_id = HashUrl::getIdDecode($this->hospital_id);
        }

        $this->data = BaseDoctorHospitals::HospitalDetail($this->hospital_id);
        if (!empty($this->data['longitude']) && !empty($this->data['latitude'])) {
            // 将医院的百度经纬度转换成高德经纬度
            $tmpLatLng = CommonFunc::baidu2GaodeGnote($this->data['longitude'], $this->data['latitude']);
            $this->data['longitude'] = !empty($tmpLatLng['lng']) ? $tmpLatLng['lng'] : $this->data['longitude'];
            $this->data['latitude'] = !empty($tmpLatLng['lat']) ? $tmpLatLng['lat'] : $this->data['latitude'];
        }

        if(!$this->hospital_id || !$this->data){
            throw new NotFoundHttpException();
        }
        if (isset($this->data['photo']) && empty($this->data['photo'])) {
            $this->data['photo'] = 'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';
        }
        parent::init();
    }

    /**
     * 医院页
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/8/12
     */
    public function actionIndex(){
        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;

        //科室
        $sub = HospitalDepartmentRelation::hospitalDepartment($hospital_id);

        //获取推荐医生
        $doctor_list = [];
//        if (is_array($sub)) {
//            $i = 0;
//            foreach ($sub as $k => $v) {
//
//                $doctor_list[$v['frist_department_id']] = [];
//                sRpcSdk::getInstance()->doctorList($doctor_list[$v['frist_department_id']],['hospital_id'=>$hospital_id,'frist_department_id'=>$v['frist_department_id'],'pagesize'=>3]);
//
//                $i++;
//                if ($i > 9) {
//                    break;
//                }
//            }
//        }
        sRpcSdk::getInstance()->doctorList($doctor_list,['hospital_id'=>$hospital_id,'pagesize'=>3]);

        sRpcSdk::getInstance()->startAsync();
//        $hospital_doc = [];
//        ##增加王氏id
//        foreach ($doctor_list as $key => &$value) {
//            $value['miao_frist_department_id'] = $sub[$key]['miao_frist_department_id'];
//            if (!$value['miao_frist_department_id']) {
//                $value['miao_frist_department_id'] = ArrayHelper::getValue($value,'doctor_list.0.miao_frist_department_id');
//            }
//        }
        $hosp_name = ArrayHelper::getValue($data,'name');
        $desc = mb_substr(trim(strip_tags(ArrayHelper::getValue($data,'description'))),0,50,'UTF8');
        $this->seoTitle = $hosp_name."网上预约挂号_怎么样_挂号平台-王氏医生";
        $this->seoKeywords = "$hosp_name,{$hosp_name}网上挂号,{$hosp_name}预约挂号,{$hosp_name}挂号平台,{$hosp_name}怎么样";
        $this->seoDescription = "{$hosp_name},".$desc;

        $shareData = [
            'title'=>$this->seoTitle,
            'link'=> rtrim(ArrayHelper::getValue(\Yii::$app->params,'domains.mobile'),'/').Url::to(['hospital/index','hospital_id'=>$hospital_id]),
            'desc'=>$this->seoDescription,
            'imgUrl'=>'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png',
        ];
        WechatShareWidget::widget([
            'title'=>$shareData['title'],
            'link'=>$shareData['link'],
            'imgUrl'=>$shareData['imgUrl'],
            'description'=>$shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'page_title' => '医院主页',
            'page' => '医院主页',
            'hospital_id' => $hospital_id,
            'hospital_name' => $hosp_name,
            'provice' => ArrayHelper::getValue($data,'province_name'),
            'city' => ArrayHelper::getValue($data,'city_name')
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        //首页轮播
        $badSdkModel = new BapiAdSdkModel();
        $lunbo = $badSdkModel->getPcLunBo(10, '142', 'M站医院挂号首页', '轮播图');
        $lunbo = $lunbo ?? [];//顶部轮播图

        $distance = 0;
        $cookies = \Yii::$app->request->cookies;
        $lat = $cookies->getValue('lat');
        $lon = $cookies->getValue('lon');

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $origin = ['lng' => $lon, 'lat' => $lat];
            $destination = ['lng' => $data['longitude'], 'lat' => $data['latitude']];
            $distance = CommonFunc::getDistanceByLngLat($origin, $destination);
        }

        return $this->render('index', ['data' => $data, 'doctor_list' => $doctor_list, 'sub' => $sub, 'hospital_id' => $hospital_id, 'lunbo' => $lunbo,'distance'=>$distance]);

    }

    /**
     * 获取医院下的医生按照科室id
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-02
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionAjaxDoctor()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $request                     = Yii::$app->request;
        $html                        = '';
        $post_data                   = $request->post();
        $hospital_id = trim(ArrayHelper::getValue($post_data,'hospital_id',''));
        $keshi_id = trim(ArrayHelper::getValue($post_data,'keshi_id',1));
        $page = (int)ArrayHelper::getValue($post_data,'page',2);
        $pagesize = 3;
        if (!$keshi_id) {
            return $html;
        }
        $params = [
            'hospital_id'=>$hospital_id,
            'frist_department_id'=>$keshi_id,
            'page'=>$page,
            'pagesize'=>$pagesize
        ];

        $data = SnisiyaSdk::getInstance()->getDoctorList($params);
        $doc_list = ArrayHelper::getValue($data,'doctor_list',[]);
        echo "<pre>";print_r($doc_list);die();
        if ($request->isPost && $request->isAjax) {
            if ($doc_list) {
                foreach ($doc_list as $key => $value) {
                    # code...
                }
            }

        }
        return $html;
    }

    /**
     * 医院详细页
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/8/12
     */
    public function actionDetail(){

        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;

        $hosp_name = ArrayHelper::getValue($data,'name');

        $this->seoTitle = "{$hosp_name}官网是什么_电话_地址-王氏医生";
        $this->seoKeywords = "{$hosp_name}官网是什么,{$hosp_name}电话,{$hosp_name}地址";
        $this->seoDescription = "王氏医生为您提供{$hosp_name}的官网地址、详细介绍、电话、地址、乘车路线等，方便患者寻找{$hosp_name}，方便就医。";

        $shareData = [
            'title'=>$this->seoTitle,
            'link'=> rtrim(ArrayHelper::getValue(\Yii::$app->params,'domains.mobile'),'/').Url::to(['hospital/detail','hospital_id'=>$hospital_id]),
            'desc'=>$this->seoDescription,
            'imgUrl'=>'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png',
        ];
        WechatShareWidget::widget([
            'title'=>$shareData['title'],
            'link'=>$shareData['link'],
            'imgUrl'=>$shareData['imgUrl'],
            'description'=>$shareData['desc'],
        ]);
        //埋点数据处理
        $eventParam = [
            'page_title' => '医院详情',
            'page' => '医院详情',
            'hospital_id' => $hospital_id,
            'hospital_name' => $hosp_name,
            'provice' => ArrayHelper::getValue($data,'province_name'),
            'city' => ArrayHelper::getValue($data,'city_name')
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);
        return $this->render('detail', ['data' => $data, 'hospital_id' => $hospital_id]);
    }

}