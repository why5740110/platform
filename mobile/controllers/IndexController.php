<?php
/**
 * 首页
 * @file IndexController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-10
 */

namespace mobile\controllers;

use common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\Map;
use common\models\Department;
use common\sdks\BapiAdSdkModel;
use common\sdks\news\NewsSdk;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\snisiya\sRpcSdk;
use mobile\widget\HospitalViewWidget;
use mobile\widget\WechatShareWidget;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\Response;
use Yii;

class IndexController extends CommonController
{

    /**
     * 首页
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-10
     * @return mixed|string
     */
    public function actionIndex()
    {
        $confirm = '';
        //app传地区 免定位
        if (\Yii::$app->request->get('p')) { // 由原接收 省， 市 为必须条件=》 可以不传市， 显示省全部
            $cityIdArr = CommonFunc::city2id(\Yii::$app->request->get('p'), \Yii::$app->request->get('c'), false);
            extract($cityIdArr);
            //存储定位
            $autoArr = CommonFunc::city_cookie($city_pid, $city_cid, $local_city, $pinyin, true);
            $selectArr = CommonFunc::city_cookie($city_pid, $city_cid, $local_city, $pinyin);
            //重定向首页 防止app返回后反复重新定位
            $url = rtrim(\Yii::$app->params['domains']['mobile'], '/') . Url::to(['index/index']);
            //return $this->redirect($url);
        } else {
            $cityIdArr = [];
            $selectArr = CommonFunc::get_city_cookie();  //选择的定位
            if (isset($selectArr['city_pid']) && !empty($selectArr['city_pid'])) {
                $autoArr['city_pid'] = $selectArr['city_pid'];
                $autoArr['city_cid'] = $selectArr['city_cid'];
                $autoArr['city'] = $selectArr['city'];
            } else {
                $autoArr = CommonFunc::get_city_cookie(true);  //自动定位 不传 地区就自动定位
                if (empty($autoArr['city_pid']) && empty($autoArr['city_cid'])) {
                    $autoArr = CommonFunc::getLocalDistrict(); // 根据ip 定位
                    $latLon = CommonFunc::city2latlngGd($autoArr['city']);
                    $proCity = CommonFunc::latlng2cityGd($latLon['lat'],$latLon['lng']);
                    $cityId = CommonFunc::city2id($proCity['province'],$proCity['city']);
                    $autoArr['province_id'] = $cityId['city_pid'];
                    $autoArr['city_id'] = $cityId['city_cid'];
                }
            }


            //暂时停用
            if(ArrayHelper::getValue($autoArr,'city')){  //已有定位
                if(ArrayHelper::getValue($autoArr,'city') != ArrayHelper::getValue($selectArr,'city')){
                    $confirm = true;
                    //直辖市 区之间不提示更换位置
                    if($autoArr['city_pid']==$selectArr['city_pid']){
                        if(in_array($autoArr['city_pid'],[1,2,3,4])){
                            $confirm = '';
                        }
                    }
                }
            }
        }

        // 如果没有定位 没有省市区
        if (empty($autoArr['city_pid']) && empty($autoArr['city_cid']) && empty($autoArr['city']) && empty($autoArr['pinyin'])) {
            // 显示查询的全国数据
            $autoArr = [
                "city_pid"=>'-',
                "city_cid"=>'-',
                "city"=>'全国',
                "pinyin"=>'',
            ];
        }

        $where = [];
        if($selectArr['city_pid'] && $selectArr['city_cid']){
            if ($selectArr['city_pid'] != '-') {
                $where['province_id'] = $selectArr['city_pid'];
            }
            if ($selectArr['city_cid'] != '-') {
                $where['city_id'] = $selectArr['city_cid'];
            }
        } else {
            if ($autoArr['city_pid'] && $autoArr['city_cid']) {
                if ($autoArr['city_pid'] != '-') {
                    $where['province_id'] = $autoArr['city_pid'];
                }
                if ($autoArr['city_cid'] != '-') {
                    $where['city_id'] = $autoArr['city_cid'];
                }
            }
        }

        $where['pagesize'] = 15;
        //不在根据选择地区获取经纬度
        $cookies = \Yii::$app->request->cookies;
        $lat = $cookies->getValue('lat');
        $lon = $cookies->getValue('lon');
        $where['lat'] = $lat;
        $where['lon'] = $lon;

        $baiduLat = '';
        $baiduLon = '';
        $gaodeLat = $lat;
        $gaodeLon = $lon;
        //获取经纬度
        if (empty($lat) || empty($lon)) {
            $address = ArrayHelper::getValue($autoArr,'city');
            $longitudeArr = CommonFunc::getLongitude($address);
            $gaodeLat = $longitudeArr['lat'];
            $gaodeLon = $longitudeArr['lon'];
            // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
            $tmpLatLon = CommonFunc::gaode2BaiduGnote($longitudeArr['lon'], $longitudeArr['lat']);
            if ($tmpLatLon) {
                $baiduLat = $tmpLatLon['lat'];
                $baiduLon = $tmpLatLon['lng'];
                $where['lat'] = $baiduLat;
                $where['lon'] = $baiduLon;
            }
        }

        if (!empty($where['lat']) && !empty($where['lon']) && empty($baiduLon) && empty($baiduLat)) {
            // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
            $tmpLatLon = CommonFunc::gaode2BaiduGnote($where['lon'], $where['lat']);
            if ($tmpLatLon) {
                $where['lat'] = $tmpLatLon['lat'];
                $where['lon'] = $tmpLatLon['lng'];
            }
        }

        $index = $this->getHospList($where);
        //地区
        $district = ArrayHelper::getValue($index,'district');
        $hospital_list = ArrayHelper::getValue($index,'hospital_list');
        if (!empty($hospital_list)) {
            array_walk($hospital_list, function(&$oneHospital) use($gaodeLat, $gaodeLon){
                $tmpLatLon = CommonFunc::baidu2GaodeGnote($oneHospital['hospital_location']['lon'], $oneHospital['hospital_location']['lat']);
                $oneHospital['hospital_location']['lon'] = $tmpLatLon['lng'];
                $oneHospital['hospital_location']['lat'] = $tmpLatLon['lat'];
                $oneHospital['sort'][3] = CommonFunc::getDistanceByLngLat(['lng'=>$oneHospital['hospital_location']['lon'], 'lat' => $oneHospital['hospital_location']['lat']], ['lng' => $gaodeLon, 'lat'=> $gaodeLat]);
            });
        }
        $hosp_total = ArrayHelper::getValue($index,'hosp_total');
        $region_type = ArrayHelper::getValue($index, 'region_type', 0);
        $department = ArrayHelper::getValue($index,'department');
        $doctor_list = ArrayHelper::getValue($index,'doctor');
        if($this->getUserAgent()=='patient'){
            $this->seoTitle = "挂号";
        }else{
            $this->seoTitle = "预约挂号";
        }
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";

        $cookies = \Yii::$app->request->cookies;
        $first = $cookies->getValue('first');
        $data = [
            'first' => $first,
            'city' => ArrayHelper::getValue($selectArr,'city'),
            'pinyin' => ArrayHelper::getValue($selectArr,'pinyin')?ArrayHelper::getValue($selectArr,'pinyin'):NULL,
            'district' => $district,
            'hospital_list' => $hospital_list,
            'hosp_total' => $hosp_total,
            'department' => $department,
            'doctor_list' => $doctor_list,
            'selectArr' => $selectArr,
            'autoArr' => $autoArr,
            'confirm' => $confirm,
            'ua' => $this->getUserAgent(),
            'region_type' => $region_type
        ];
        $shareData = [];
        $shareData['title'] = $this->seoTitle;
        $shareData['link'] =  Yii::$app->params['hospitalUrl'];
        $shareData['desc'] =  $this->seoDescription;
        $shareData['imgUrl'] =  'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png';
        $data['shareData'] = $shareData;
        $data['md5_useId'] = md5($this->user_id);
        $data['is_position'] = 'no'; // 去除弹框
        $data['province_id'] = (isset($where['province_id']) && !empty($where['province_id'])) ? $where['province_id'] : '';
        $data['city_id'] = (isset($where['city_id']) && !empty($where['city_id'])) ? $where['city_id']: '';
        //埋点数据处理
        $eventParam = [
            'page_title' => '医院首页',
            'page' => '医院首页',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        WechatShareWidget::widget([
            'title'=>$shareData['title'],
            'link'=>$shareData['link'],
            'imgUrl'=>$shareData['imgUrl'],
            'description'=>$shareData['desc'],
        ]);

        //首页轮播
        $badSdkModel = new BapiAdSdkModel();
        $lunbo = $badSdkModel->getPcLunBo(10, '142', 'M站医院挂号首页', '轮播图');
        $lunbo = $lunbo ?? [];//顶部轮播图
        $data['lunbo'] = $lunbo;

        return $this->render('index',$data);
    }

    /**
     * 选择地区 ajax
     * @return array|int[]
     * @author xiujianying
     * @date 2020/8/12
     */
    public function actionSelectCity()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $p = \Yii::$app->request->get('p');
        $c = \Yii::$app->request->get('c');
        $city = \Yii::$app->request->get('city');
        $pinyin = \Yii::$app->request->get('pinyin','');

        if ($p && $c && $city) {
            CommonFunc::city_cookie($p, $c, $city,$pinyin);
            return ['code' => 200];
        } else {
            return ['code' => 400, 'msg' => 'params error'];
        }
    }

    /**
     * 获取定位 返回首页医院数据
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/4
     */
    public function actionAjaxLatLon()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $lat = \Yii::$app->request->get('lat');
            $lon = \Yii::$app->request->get('lon');
            $auto = \Yii::$app->request->get('auto', 0);   //每次定位 判断定位是否更改   0：返回首页数据 1：返回定位城市
            //自动定位 传的省市名称
            $province = \Yii::$app->request->get('p');
            $city = \Yii::$app->request->get('c');

            $cookieKeyOk = "position";
            $useIdIsPositionOk = isset($_COOKIE[$cookieKeyOk]) ? $_COOKIE[$cookieKeyOk] : "";
            if ($province && $city) {
                $cityArr = [
                    'province' => $province,
                    'city' => $city
                ];
            } else {

                $cok =  CommonFunc::get_city_cookie();
                if (intval($useIdIsPositionOk) !== 1) {
                    if(empty($cok['city'])){
                        $lat = 0;
                        $lon = 0;
                    }
                    if(!empty($cok['city']) && $cok['city'] == "全国"){
                        $lat = 0;
                        $lon = 0;
                    }
                }
                if(!empty($cok['city'])){
                    $d = CommonFunc::city2latlngGd($cok['city']);
                    $lat = $d['lat'];
                    $lon = $d['lng'];
                }
                if(intval($useIdIsPositionOk) == 0 && ($cok['city'] == "全国" || $cok['city'] == "")){
                    $lat = 0;
                    $lon = 0;
                }
                if(intval($useIdIsPositionOk) == 2 && $cok['city'] == "全国"){
                    $lat = 0;
                    $lon = 0;
                }
                if(intval($useIdIsPositionOk) == 2 && !empty($cok['city']) &&  $cok['city'] !== "全国"){
                    $d = CommonFunc::city2latlngGd($cok['city']);
                    $lat = $d['lat'];
                    $lon = $d['lng'];
                }
                CommonFunc::common_set_cookie('lat', $lat);
                CommonFunc::common_set_cookie('lon', $lon);
                $cityArr = CommonFunc::latlng2cityGd($lat, $lon);
            }

            $cityIdArr = CommonFunc::city2id(ArrayHelper::getValue($cityArr,'province'),ArrayHelper::getValue($cityArr,'city'),true);
            extract($cityIdArr);
            //存储经纬度定位的
            CommonFunc::city_cookie($city_pid, $city_cid, $local_city, $pinyin, true);

            if ($auto) {
                $autoArr = CommonFunc::get_city_cookie(true); //自动定位
                $data = [
                    'isEdit' => $autoArr['city'] != $local_city ? 1 : 0,
                    'city' => $local_city
                ];
            } else {
                if (!empty($lat) && !empty($lon)) {
                    // 前端cookie存储的是高德经纬度，请求基础数据之前转换成百度经纬度
                    $tmpLatLon = CommonFunc::gaode2BaiduGnote($lat, $lon);
                    if ($tmpLatLon) {
                        $lat = $tmpLatLon['lat'];
                        $lon = $tmpLatLon['lng'];
                    }
                }
                //获取首页数据
                $indexData = $this->getHospList([
                    'pagesize' => 15,
                    'province_id' => $city_pid,
                    'city_id' => $city_cid,
                    'lat' => $lat,
                    'lon' => $lon,
                ]);
                $html = '';
                if ($indexData['hospital_list'] && is_array($indexData['hospital_list'])) {
                    foreach ($indexData['hospital_list'] as $row) {
                        $html .= HospitalViewWidget::widget(['row' => $row, 'type' => 2, 'shence_type' => 2]);
                    }
                }

                $data = [
                    'city_pid' => $city_pid,
                    'city_cid' => $city_cid,
                    'city' => $local_city,
                    'pinyin' => $pinyin,
                    'hospital_list' => $html,
                    'hosp_total' => ArrayHelper::getValue($indexData, 'hosp_total'),
                    'url_hosp' => Url::to(['hospitallist/index', 'region' => $pinyin]),
                    'url_dep' => Url::to(['hospitallist/department-list', 'region' => $pinyin]),
                    'url_doc' => Url::to(['doctorlist/index', 'region' => $pinyin]),
                    'url_more' => Url::to(['hospitallist/index', 'region' => $pinyin]),
                ];
                CommonFunc::city_cookie($city_pid, $city_cid, $local_city, $pinyin);
            }
            $data['isLocaltion'] = $cityArr['province'] ? 1 : 0;

            return $data;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 走s接口获取首页数据
     * @param $params
     * @return array
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/4
     */
    public function getHospList($params)
    {
        $index = SnisiyaSdk::getInstance()->getIndex($params);

        //地区
        $district = ArrayHelper::getValue($index, 'district');
        $hospital_list = ArrayHelper::getValue($index, 'hospital.hospital_list');
        $hosp_total = ArrayHelper::getValue($index, 'hospital.totalCount');
        $doctor = ArrayHelper::getValue($index, 'doctor.doctor_list');
        $region_type = ArrayHelper::getValue($index, 'region_type', 0);
        $department = ArrayHelper::getValue($index, 'department', 0);
        return ['hospital_list' => $hospital_list, 'district' => $district,'doctor'=>$doctor,'hosp_total'=>$hosp_total, 'region_type' => $region_type, 'department'=>$department];
    }

    /**
     * 根据科室id获取热门医生
     * @return string
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-08-03
     */
    public function actionAjaxDoctor()
    {
        $department_id = Yii::$app->request->get('department_id', 0);
        $province_id = Yii::$app->request->get('province_id', 0);
        $city_id = Yii::$app->request->get('city_id', 0);
        $result = SnisiyaSdk::getInstance()->getDoctor(['skid'=>$department_id,'province_id'=>$province_id,'city_id'=>$city_id,'page'=>1,'pagesize'=>20]);
        $html = '';
        if ($result){
            foreach ($result['doctor_list'] as $key => $val){
                $doctor_good_at = ArrayHelper::getValue($val, 'doctor_good_at') ? '擅长：' . ArrayHelper::getValue($val, 'doctor_good_at') : '';
                $html .= '<div class="doc_item">';
                $html.= '<a href="' . Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($val, 'doctor_id')]) . '" class=doc_item_wrap>';
                $html.=     '<div class=doc_photo> <img src="' . ArrayHelper::getValue($val, 'doctor_avatar') . '" onerror="javascript:this.src=' . "'https://u.nisiyacdn.com/avatar/default_2.jpg'" . ';" alt="' . ArrayHelper::getValue($val, 'doctor_realname') . '"></div>';
                $html.=     '<div class=doc_content>';
                $html.=         '<div class=doc_info>';
                $html.=             '<div>';
                $html.=                 '<span class=doc_name>'. ArrayHelper::getValue($val, 'doctor_realname') .'</span>';
                $html.=                 '<span class=doc_title>'. ArrayHelper::getValue($val, 'doctor_title') .'</span>';
                $html.=             '</div>';
                $html .= '<span class=btn_little>去挂号</span>';
                $html.=         '</div>';
                $html.=         '<div class="doc_text text_wrap">'. ArrayHelper::getValue($val, 'doctor_second_department_name')  .' | '. ArrayHelper::getValue($val, 'doctor_hospital') .'</div>';
                if (!empty($row['doctor_visit_type'])){
                    $html.=         '<div class=doc_tags>';
                    $html.=             '<span class="tags t_style01 t_short">'. ArrayHelper::getValue($val, 'doctor_visit_type') .'</span>';
                    $html.=         '</div>';
                }
                $html.=         '<p class="doc_descript text_over2">'. $doctor_good_at .'</p>';
                $html.=     '</div>';
                $html.= '</a>';
                $html .= '</div>';
            }
        }
        return $html;
    }

}

?>
