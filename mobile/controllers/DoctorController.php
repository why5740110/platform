<?php

namespace mobile\controllers;

use common\libs\HashUrl;
use common\models\DoctorModel;
use common\models\GuahaoHospitalModel;
use common\models\HospitalEsModel;
use common\models\MedicalModel;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\ucenter\PihsSDK;
use Yii;
use mobile\widget\WechatShareWidget;
use \common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;

class DoctorController extends CommonController
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
    }
    /**
     * 详情
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/27
     */
    public function actionHome()
    {
        $showDay   = CommonFunc::SHOW_DAY;
        $doctor_id = \Yii::$app->request->get('doctor_id', '');

        $doctor_id = HashUrl::getIdDecode($doctor_id);

        $infos = DoctorModel::getInfo($doctor_id);
        //只展示主医生
        $primary_id = ArrayHelper::getValue($infos,'primary_id');
        if (!$infos || $primary_id ) {
            throw new NotFoundHttpException();
        }

        //医生标签
        $doctor_tags = ArrayHelper::getValue($infos, 'doctor_tags', '');
        $infos['doctor_tags'] = $this->getDoctorTags($doctor_tags);

        $doctor_realname = ArrayHelper::getValue($infos, 'doctor_realname');
        $hospital_name   = ArrayHelper::getValue($infos, 'doctor_hospital_data.name');
        //加号业务
        //$infos['miao_doctor_id'] = 4128936;
        //$infos['doctor_is_plus'] =1;
        $plusList = [];
        if (ArrayHelper::getValue($infos, 'miao_doctor_id')) {
            $miao_id = ArrayHelper::getValue($infos, 'miao_doctor_id');
            //$miao_id = 4128936;
            if (ArrayHelper::getValue($infos, 'doctor_is_plus') == 1) {

                $plusData = PihsSDK::getInstance()->plus_list(['doctor_ids' => $miao_id]);
                //print_r($plusData);exit;
                if (ArrayHelper::getValue($plusData, $miao_id . '.jiahao_info') && is_array(ArrayHelper::getValue($plusData, $miao_id . '.jiahao_info'))) {
                    $plusList = ArrayHelper::getValue($plusData, $miao_id . '.jiahao_info');
                }
            }
        }

        $this->seoTitle       = $doctor_realname . ",医生预约挂号,出诊时间," . $hospital_name . ",王氏医生";
        $this->seoKeywords    = $doctor_realname . ',' . $doctor_realname . '评价,' . $doctor_realname . '怎么样' . ',' . $doctor_realname . '预约挂号';
        $this->seoDescription = "王氏医生为您提供" . $doctor_realname . "的介绍、出诊时间、评价、预约挂号等，患者真实评价及就医分享，介绍" . $doctor_realname . "擅长治疗疾病，助您找到合适自己的医生在线咨询、预约挂号等。";
        if ($this->getUserAgent() == 'patient') {
            $this->seoTitle = $doctor_realname . '医生';
        }
        $data = [
            'doctor_info' => $infos,
            'plusList'    => $plusList,
        ];
        $shareData = [
            'title'  => $this->seoTitle,
            'link'   => rtrim(Yii::$app->params['domains']['mobile'], '/') . Yii::$app->request->url,
            'desc'   => $this->seoDescription,
            'imgUrl' => CommonFunc::SHARE_LOGO,
        ];
        $data['shareData'] = $shareData;

        //挂号
        if (ArrayHelper::getValue($infos, 'tb_third_party_relation')) {
            $startdate = date('Y-m-d', strtotime('+1 days'));
            $enddate   = date('Y-m-d', strtotime('+' . $showDay . ' days'));

            $paibanSdk = SnisiyaSdk::getInstance();
            $params    = [
                'doctor_id' => $doctor_id,
                'startdate' => $startdate,
                'enddate'   => $enddate,
                //'status' => 1 约满和停诊也获取
            ];
            $paiban_data = $paibanSdk->guahao_paiban($params);
            //print_r($paiban_data);

            //获取医院等级和类型
            $hospital_id = ArrayHelper::getValue($infos, 'hospital_id', 0);
            $hospitalInfo = BaseDoctorHospitals::HospitalDetail($hospital_id);

            $paibanList = ArrayHelper::getValue($paiban_data, 'list');
            $paibanData = $hosInfos = [];
            $paibanData[$infos['hospital_name']] = [];
            $hosInfos[$infos['hospital_name']] = [
                'hospital_id' => $infos['hospital_id'],
                'level' => isset($hospitalInfo['level']) ? $hospitalInfo['level'] : '',
                'kind' => isset($hospitalInfo['kind']) ? $hospitalInfo['kind'] : '',
            ];
            if (!empty($paibanList)) {

                /*for ($i = 1; $i <= $showDay; $i++) {
                    $day[] = date('Y-m-d', strtotime('+' . $i . ' days'));
                }*/
                foreach ($paibanList as $v) {
                    $paibanData[$v['scheduleplace_name']][$v['department_name']][$v['visit_time']][$v['visit_nooncode']] = $v;
                    if(isset($v['sections'])){
                        $v['sections'] = CommonFunc::secondArrayUniqueByKey($v['sections'],'tp_section_id');
                    }
                    $hospitalInfos = BaseDoctorHospitals::HospitalDetail($v['hospital_id']);
                    $item = [
                        'hospital_id' => $v['hospital_id'],
                        'level' => isset($hospitalInfos['level']) ? $hospitalInfos['level'] : '',
                        'kind' => isset($hospitalInfos['kind']) ? $hospitalInfos['kind'] : '',
                    ];
                    $hosInfos[$v['scheduleplace_name']] = $item;
                }
                /*foreach ($paibanData as $pk => $pv) {
                    foreach ($day as $dv) {
                        if (empty($pv[$dv])) {
                            $paibanData[$pk][$dv] = [];
                        }
                    }
                }*/
                foreach ($paibanData as &$v) {
                    ksort($v);
                }
            }
            $data['guahao']    = $paibanData;
            $data['hos_infos'] = $hosInfos;
            $data['guahao_deparatment'] = array_keys($paibanData);
            $other_guahao_list = ArrayHelper::getValue($paiban_data, 'other_list');
            $other_guahao      = [];
            if (!empty($other_guahao_list)) {
                foreach ($other_guahao_list as $ok => $ov) {
                    $other_guahao[$ov['scheduleplace_name']][$ov['department_name']][] = $ov;
                    if(isset($ov['sections'])){
                        $ov['sections'] = CommonFunc::secondArrayUniqueByKey($ov['sections'],'tp_section_id');
                    }
                }
            }
            $data['other_guahao'] = $other_guahao;
        } else {
            $data['guahao']       = [];
            $data['hos_infos']    = [];
            $data['other_guahao'] = [];
        }
        //获取其他相同科室医生
        $fkid = ArrayHelper::getValue($infos, 'miao_frist_department_id', 0);
        $skid = ArrayHelper::getValue($infos, 'miao_second_department_id', 0);
        $params    = [
            'fkid'   => $fkid,
            'skid' => $skid,
            'ro_hospital_id'     => $hospital_id,
            'province_id'       => ArrayHelper::getValue($hospitalInfo,'province_id'),
            'city_id'        => ArrayHelper::getValue($hospitalInfo,'city_id'),
            'page'        => 1,
            'pagesize'        => 3,
        ];
        $doctor_list = $paibanSdk->getDepartmentDoctor($params);
        if (!empty($doctor_list) && isset($doctor_list['doctor_list']) && !empty($doctor_list['doctor_list'])) {
            foreach ($doctor_list['doctor_list'] as &$val) {
                $doctor_tags = isset($val['doctor_tags']) ? $val['doctor_tags'] : '';
                $val['doctor_tags'] = $this->getDoctorTags($doctor_tags);
            }
        }
        $data['doctor_list'] = $doctor_list;

        $data['showDay'] = $showDay;
        \mobile\widget\WechatShareWidget::widget([
        'title'=>$shareData['title'],
        'link'=>$shareData['link'],
        'imgUrl'=>$shareData['imgUrl'],
        'description'=>$shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'page_title' => '医生主页',
            'page' => '医生主页',
            'hospital_id' => ArrayHelper::getValue($infos,'hospital_id'),
            'hospital_name' => $hospital_name,
            'doctor_id' => $doctor_id,
            'doctor_name' => $doctor_realname,
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        return $this->render('home', $data);
    }

    public function getDoctorTags($doctor_tags)
    {
        $tags = [];
        if (!empty($doctor_tags)) {
            $tags = explode('、', $doctor_tags);
        }
        return $tags;
    }

    public function actionIntro()
    {
        $doctor_id = \Yii::$app->request->get('doctor_id', '');

        $doctor_id = HashUrl::getIdDecode($doctor_id);

        $infos = DoctorModel::getInfo($doctor_id);
        if (!$infos) {
            throw new NotFoundHttpException();
        }

        $doctor_realname = ArrayHelper::getValue($infos, 'doctor_realname');
        $hospital_name   = ArrayHelper::getValue($infos, 'doctor_hospital_data.name');

        $this->seoTitle       = $doctor_realname . ",医生预约挂号,出诊时间," . $hospital_name . ",王氏医生";
        $this->seoKeywords    = $doctor_realname . ',' . $doctor_realname . '评价,' . $doctor_realname . '怎么样' . ',' . $doctor_realname . '预约挂号';
        $this->seoDescription = "王氏医生为您提供" . $doctor_realname . "的介绍、出诊时间、评价、预约挂号等，患者真实评价及就医分享，介绍" . $doctor_realname . "擅长治疗疾病，助您找到合适自己的医生在线咨询、预约挂号等。";
        $data                 = [
            'doctor_info' => $infos,
        ];

        $shareData = [
            'title'  => $this->seoTitle ?? '',
            'link'   => rtrim(Yii::$app->params['domains']['mobile'], '/') . Yii::$app->request->url,
            'desc'   => $this->seoDescription ?? '',
            'imgUrl' => 'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png',
        ];
        \mobile\widget\WechatShareWidget::widget([
        'title'=>$shareData['title'],
        'link'=>$shareData['link'],
        'imgUrl'=>$shareData['imgUrl'],
        'description'=>$shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'page_title' => '医生详情页',
            'page' => '医生详情页',
            'hospital_id' => ArrayHelper::getValue($infos,'hospital_id'),
            'hospital_name' => $hospital_name,
            'doctor_id' => $doctor_id,
            'doctor_name' => $doctor_realname,
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);
        return $this->render('intro', $data);
    }

    public function actionAjaxRefresh()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $request                     = Yii::$app->request;
        $html                        = '';
        $post_data                   = $request->post();
        $hash_doctor_id                   = trim($request->post('doctor_id', ''));
        $department_name             = trim($request->post('department_name', ''));

        if ($request->isPost && $request->isAjax) {
           return $this->getRefreshHtml($hash_doctor_id,$department_name);
        }
        return '';
    }

    /**
     * 获取刷新的html
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-08
     * @version v1.0
     * @param   string     $hash_doctor_id       [description]
     * @param   string     $department_name [description]
     * @return  [type]                      [description]
     */
    public function getRefreshHtml($hash_doctor_id = '',$department_name = '')
    {
        $html = '';
        $doctor_id = HashUrl::getIdDecode($hash_doctor_id);
        if (!$doctor_id) {
            return $html;
        }
        $show_day   = CommonFunc::SHOW_DAY;
        $platformArr = CommonFunc::getTpPlatformNameList();
        $dayArr = CommonFunc::$visit_nooncode_type;
        $first_platform = '';
        $weekArr = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        $paiban_data = $this->getDoctorPaiban($doctor_id,$department_name);
        if ($paiban_data) {
            $g = current($paiban_data);

            $html.='<div class="doctor_con01_list_boy">
                <div class="doctor_con01_list_left">
                  <ul>
                    <li></li>
                    <li><span>上午</span></li>
                    <li><span>下午</span></li>
                    <li><span>晚上</span></li>
                  </ul>
                </div>';
            $html.='<div class="doctor_con01_list_right">';
            $have_all_hao = false;
            foreach ($g as $gkk=>$gvv) {
                $have_hao = false;
                if (!empty($gvv) && !$have_hao){
                    $have_hao = true;
                    $have_all_hao = true;
                }
                if ($have_hao) {
                    $html.='<ul class="have">';
                }else{
                    $html.='<ul>';
                }
                $dayKey = date('w',strtotime($gkk));
                $data_week = ArrayHelper::getValue($weekArr,$dayKey,'');
                $date_md = date('m-d',strtotime($gkk));
                $html.='<li><p><span>'.$data_week.'</span><i>'.$date_md.'</i></p></li>';
                foreach ($dayArr as $n=>$n_text) {
                    if (ArrayHelper::getValue($gvv,$n)) {
                        $tp_platform = ArrayHelper::getValue($gvv, $n . '.tp_platform');
                        $first_platform = $platformArr[$tp_platform] ?? '';
                        if (ArrayHelper::getValue($gvv,$n.'.status') == 1) {
                            $html.='<li data-url="'.Url::to(['register/choose-patient', 'doctor_id' => $hash_doctor_id, 'scheduling_id' => ArrayHelper::getValue($gvv,$n.'.tp_scheduling_id'),'tp_platform'=>ArrayHelper::getValue($gvv,$n.'.tp_platform')]).'" class="yh';
                            if (ArrayHelper::getValue($gvv,$n.'.is_section') == 1) {
                                $other_sectionArr = CommonFunc::group_section(ArrayHelper::getValue($gvv,$n.'.sections'));
                                $html.=' tc_click"';
                                $html.=" data-sections='".json_encode($other_sectionArr)."'";
                            }else{
                                $html.=' do_guahao"';
                            }

                            $html.='">';
                            if ((ArrayHelper::getValue($gvv,$n.'.schedule_available_count',0)) > 0) {
                                $html.='<p><span>剩余'.ArrayHelper::getValue($gvv,$n.'.schedule_available_count',0).'</span>';
                            }else{
                                $html.='<p><span>挂号</span>';
                            }
                            $price = ArrayHelper::getValue($gvv,$n.'.visit_cost')/100;
                            
                            $html.='<i>¥'.$price.'</i></p>';
                            $html.='</li>';
                        }elseif (ArrayHelper::getValue($gvv,$n.'.status') == 2) {
                            $html.='<li class="tz"><p><strong>停诊</strong></p></li>';
                        }else{
                            $html.='<li class="tz"><p><strong>约满</strong></p></li>';
                        }
                        
                    }else{
                        $html.='<li></li>';
                    }
                
                }
                $html.='</ul>';
            }

           
            $html.='</div>';
            $html.='</div>';
            if ($first_platform) {
               $html.='<div class="doctor_con_tips">';
                $html.='<p>注：展示近'.$show_day.'天号源，该号源由'.$first_platform.'挂号平台提供。</p>';
                $html.='</div>';
            }
            
        }
        return $html;
    }

    public function getDoctorPaiban($doctor_id = '',$department_name = '')
    {
        ##请求更新队列
        SnisiyaSdk::getInstance()->updateScheduleCache(['doctor_id' => $doctor_id]);
        $showDay   = CommonFunc::SHOW_DAY;
        $startdate = date('Y-m-d', strtotime('+1 days'));
        $enddate   = date('Y-m-d', strtotime('+' . $showDay . ' days'));

        $paibanSdk = SnisiyaSdk::getInstance();
        $params    = [
            'doctor_id' => $doctor_id,
            'startdate' => $startdate,
            'enddate'   => $enddate,
            //'status' => 1 约满和停诊也获取
        ];
        $paiban_data = $paibanSdk->guahao_paiban($params);
        $paibanList = ArrayHelper::getValue($paiban_data, 'list');
        $paibanData = [];
        if (!empty($paibanList)) {
            for ($i = 1; $i <= $showDay; $i++) {
                $day[] = date('Y-m-d', strtotime('+' . $i . ' days'));
            }
            foreach ($paibanList as $v) {
                $paibanData[$v['department_name']][$v['visit_time']][$v['visit_nooncode']] = $v;
            }
            foreach ($paibanData as $pk => $pv) {
                foreach ($day as $dv) {
                    if (empty($pv[$dv])) {
                        $paibanData[$pk][$dv] = [];
                    }
                }
            }
            foreach ($paibanData as &$v) {
                ksort($v);
            }
        }
        if ($department_name) {
            foreach ($paibanData as $key => $value) {
                if ($key != $department_name) {
                    unset($paibanData[$key]);
                }
            }
        }
        return $paibanData;
    }

    public function actionMedicalClick()
    {
        $id = \Yii::$app->request->post('id', '');
        if (!empty($id)) {
            //查询判断当前medical推荐信息是否存在
            $res = MedicalModel::find()->where(['id' => $id])->one();
            if (!empty($res)) {
                $hits = $res->getOldAttribute('hits');
                $res->setAttribute('hits', $hits + 1);
                $res->save();
            }
        }
    }

}
