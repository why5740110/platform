<?php
/**
 * @file GuahaoController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/10/9
 */

namespace mobile\controllers;

use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use mobile\widget\WechatShareWidget;
use Yii;
use common\helpers\Url;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class GuahaoController extends CommonController
{
    public $hospital_id;
    public $data;
    public $enableCsrfValidation = false;

    public function init()
    {
        $this->hospital_id = \Yii::$app->request->get('hospital_id');

        if ($this->hospital_id) {
            $this->hospital_id = HashUrl::getIdDecode($this->hospital_id);
        }

        $this->data = BaseDoctorHospitals::HospitalDetail($this->hospital_id);
        //
        if (!$this->hospital_id || !$this->data) {
            throw new NotFoundHttpException();
        }
        parent::init();
    }

    /**
     * 科室列表
     * @return string
     * @author xiujianying
     * @date 2020/10/14
     */
    public function actionKeshilist()
    {
        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;

        //科室
        $sub = HospitalDepartmentRelation::hospitalDepartment($hospital_id);
        //1只展示有号的 0全部展示
        $show_has_paiban = 1;
        $showSub         = [];
        if ($sub) {
            foreach ($sub as $k => $v) {
                $showRow         = [];
                $v['has_paiban'] = 0;
                foreach ($v['second_arr'] as $second_arr) {
                    if (isset($second_arr['has_paiban']) && $second_arr['has_paiban'] == 1) {
                        $showRow[$second_arr['second_department_id']] = $second_arr;
                        $v['has_paiban'] = 1;
                    } elseif ($show_has_paiban == 0) {
                        $showRow[$second_arr['second_department_id']] = $second_arr;
                    }
                }
                if ($showRow) {
                    //排序
                    $v['second_arr'] = CommonFunc::arraySort($showRow, 'has_paiban');
                    $showSub[]       = $v;
                }
            }
        }
        $sub = CommonFunc::arraySort($showSub, 'has_paiban');
        //放号时间
        $hospitalArr['河南省中医院']                      = '9:30';
        $hospitalArr['洛阳市东方医院']                   = '9:30';
        $hospitalArr['南阳市中心医院']                   = '10:00';
        $hospitalArr['河南中医药大学第三附属医药'] = '10:30';
        $hospitalArr['濮阳市油田总医院']                = '10:30';
        $hospitalArr['河南省人民医院']                   = '24:00';
        $hospitalArr['郑州大学附属郑州中心医院']    = '24:00';

        $hosp_name = ArrayHelper::getValue($data, 'name');

        $this->seoTitle = "{$hosp_name}-预约挂号-专家医生-王氏医生";
        if ($this->getUserAgent() == 'patient') {
            $this->seoTitle = $hosp_name;
        }
        $this->seoKeywords    = "{$hosp_name}预约挂号,{$hosp_name}挂号平台,{$hosp_name}网上挂号";
        $this->seoDescription = "{$hosp_name}预约挂号平台。王氏医生网上预约统一挂号平台汇集{$hosp_name}多个挂号渠道号源，为您提供网上|在线预约挂号、线下门诊预约号源，{$hosp_name}挂号攻略帮助您方便快速地实现网上挂号，预约专家更轻松！";

        $shareData = [];
        $shareData['title'] = $this->seoTitle;
        $shareData['link'] = rtrim(ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile'), '/') . Url::to(['guahao/keshilist', 'hospital_id' => $hospital_id]);
        $shareData['desc'] = $this->seoDescription;
        $shareData['imgUrl'] = 'https://www.nisiyacdn.com/static/images/logo/logo-100x100.png';
        $data['shareData'] = $shareData;

        WechatShareWidget::widget([
            'title' => $shareData['title'],
            'link' => $shareData['link'],
            'imgUrl' => $shareData['imgUrl'],
            'description' => $shareData['desc'],
        ]);

        //埋点数据处理
        $eventParam = [
            'hospital_id' => $hospital_id,
            'hospital_name' => $hosp_name,
            'provice' => ArrayHelper::getValue($data, 'province_name'),
            'city' => ArrayHelper::getValue($data, 'city_name'),
            'page_title' => '医院选择科室页',
            'page' => '医院选择科室页',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);
        return $this->render('keshilist', ['data' => $data, 'hospital_id' => $hospital_id, 'sub' => $sub, 'hospitalArr' => $hospitalArr]);
    }

    /**
     * 排班医生列表
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/10/14
     */
    public function actionDoclist()
    {
        $hospital_id = $this->hospital_id;
        //详情
        $data      = $this->data;
        $startdate = date('Y-m-d', strtotime('+1 days'));
        $enddate   = date('Y-m-d', strtotime('+31 days'));

        //$tp_keshi_id = '16090916185006050';
        $tp_keshi_id = \Yii::$app->request->get('tp_department_id');

        $paibanSdk = SnisiyaSdk::getInstance();
        $params    = [
            'hospital_id'   => $hospital_id,
            'department_id' => $tp_keshi_id,
            'startdate'     => $startdate,
            'enddate'       => $enddate,
            // 'status'        => 1,
        ];
        // $paibanResult = $paibanSdk->getDepartmentPaiban($params);
        $paibanResult      = $paibanSdk->getPaibanStatus($params);
        $paibanList        = ArrayHelper::getValue($paibanResult, 'list', []);
        $frist_youhao_date = '';
        if ($paibanList) {
            foreach ($paibanList as $hao_item) {
                if (!empty(ArrayHelper::getValue($hao_item, 'visit_time','')) && (ArrayHelper::getValue($hao_item, 'status') == 1)) {
                    $frist_youhao_date = ArrayHelper::getValue($hao_item, 'visit_time','') ?? '';
                    break;
                }
            }
        }
       
        $paibanData        = [];
        for ($i = 1; $i <= 30; $i++) {
            $day              = date('Y-m-d', strtotime('+' . $i . ' days'));
            $paibanData[$day] = [];
            if ($paibanList) {
                foreach ($paibanList as $v) {
                    if ($v['visit_time'] == $day) {
                        $paibanData[$day] = $v;
                    }
                }
            }
        }

        $hosp_name             = ArrayHelper::getValue($data, 'name');
        $department            = ArrayHelper::getValue($paibanResult, 'department_name', '');
        $frist_department_name = ArrayHelper::getValue($paibanResult, 'frist_department_name', '');
        $open_time = ArrayHelper::getValue($paibanResult, 'open_time', '');
        $data['open_time'] = $open_time;

        $this->seoTitle = "{$hosp_name}{$department}挂号-专家出诊时间表-王氏医生";
        if ($this->getUserAgent() == 'patient') {
            $this->seoTitle = "选择号源";
        }
        $this->seoKeywords    = "{$hosp_name}{$department}专家排名，{$hosp_name}{$department}专家，{$hosp_name}{$department}预约挂号，{$hosp_name}{$department}出诊时间表-王氏医生";
        $this->seoDescription = "{$hosp_name}{$department}预约挂号入口。王氏医生网上预约统一挂号平台汇集多个挂号渠道号源，为您提供网上|在线预约挂号、线下门诊预约号源，{$hosp_name}和医院挂号攻略帮助您方便快速地实现网上挂号，预约专家更轻松！";

        $data['hosp_name']             = $hosp_name;
        $data['department']            = $department;
        $data['frist_department_name'] = $frist_department_name;
        $month_list                    = [];
        if ($paibanData) {
            foreach ($paibanData as $key => $value) {
                $month = date('Y-m', strtotime($key));
                $day   = date('d', strtotime($key));
                if (isset($month_list[$month])) {
                    $month_list[$month][$day] = $value;
                } else {
                    $month_list[$month][$day] = $value;
                }
            }
        }
        $data['month_list'] = $month_list;
        $first_day_paiban   = [];
        $info_params        = [
            'hospital_id'   => $hospital_id,
            'department_id' => $tp_keshi_id,
            // 'status'        => 1,
            'visit_time'    => !empty($frist_youhao_date) ? $frist_youhao_date : $startdate,
        ];
        $visit_nooncode_list = $this->getPaibanInfoByVisitTime($info_params);
        $this->getView()->title = "选择号源";

        //获取其他相同科室医生
        $fkid = ArrayHelper::getValue($paibanResult, 'miao_frist_department_id', 0);
        $skid = ArrayHelper::getValue($paibanResult, 'miao_second_department_id', 0);

        $params    = [
            'fkid'   => $fkid,
            'skid' => $skid,
            'ro_hospital_id'     => $hospital_id,
            'province_id'       => ArrayHelper::getValue($data,'province_id'),
            'city_id'        => ArrayHelper::getValue($data,'city_id'),
            'page'        => 1,
            'pagesize'        => 3,
        ];
        $doctor_list = $paibanSdk->getDepartmentDoctor($params);

        //埋点数据处理
        $eventParam = [
            'page_title' => '选择号源',
            'page' => '选择号源',
            'hospital_id' => $hospital_id,
            'hospital_name' => $hosp_name,
            'provice' => ArrayHelper::getValue($data,'province_name'),
            'city' => ArrayHelper::getValue($data,'city_name')
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);
        return $this->render('doclist', ['data' => $data, 'hospital_id' => $hospital_id, 'department_id' => $tp_keshi_id, 'paibanData' => $paibanData,'frist_youhao_date'=>$frist_youhao_date,'visit_nooncode_list' => $visit_nooncode_list,'doctor_list'=>$doctor_list]);
    }

    /**
     * 根据日期获取医院科室排班
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-27
     * @version v1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getPaibanInfoByVisitTime($params = [])
    {
        $paibanSdk           = SnisiyaSdk::getInstance();
        $first_day_paiban    = $paibanSdk->getPaibanInfo($params);
        $firstpaiban_list    = ArrayHelper::getValue($first_day_paiban, 'list', []);
        $visit_nooncode_list = [];
        $visit_nooncode_type = CommonFunc::$visit_nooncode_type;
        if ($firstpaiban_list) {
            foreach ($visit_nooncode_type as $nooncode => $noon_val) {
                $visit_nooncode_list[$nooncode] = [];
                foreach ($firstpaiban_list as $key => $value) {
                    if ($value['visit_nooncode'] == $nooncode) {
                        $visit_nooncode_list[$nooncode][] = $value;
                    }
                }
            }
        }
        ##计算同一个时间段同一个医生数量
        if ($visit_nooncode_list) {
            foreach ($visit_nooncode_list as $vk => &$v_item) {
                $arr_item = array_column($v_item,'doctor_id');
                $arr_item_count = array_count_values($arr_item);
                if ($v_item) {
                    foreach ($v_item as &$son_item) {
                        if ($son_item) {
                            $son_item['doc_num'] = $arr_item_count[$son_item['doctor_id']] ?? 0;
                        }
                    }
                }
                unset($arr_item,$arr_item_count);
            }
        }
        return $visit_nooncode_list;
    }

    /**
     * 按照天获取排班信息列表
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-06
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionAjaxGetDoctor()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $request                     = Yii::$app->request;
        $html                        = '';
        $post_data                   = $request->post();
        if ($request->isPost && $request->isAjax) {
            $visit_nooncode_type = CommonFunc::$visit_nooncode_type;
            $platformArr = CommonFunc::getTpPlatformNameList();
            $info_params = [
                'hospital_id'   => trim($request->post('hospital_id', '')),
                'department_id' => trim($request->post('department_id', '')),
                // 'status'        => 1,
                'visit_time'    => trim($request->post('visit_time', '')),
            ];
            $visit_nooncode_list = $this->getPaibanInfoByVisitTime($info_params);
            if ($visit_nooncode_list) {
                foreach ($visit_nooncode_list as $noon_key => $noon_item) {
                    if (!empty($noon_item)) {
                        foreach ($noon_item as $sched_key => $sched_item) {
                            $doctor_id = (ArrayHelper::getValue($sched_item, 'primary_id') > 0) ? ArrayHelper::getValue($sched_item, 'primary_id') : ArrayHelper::getValue($sched_item, 'doctor_id');
                            $html .= '<div class="doc_item">';
                            $html .=    '<a class="doc_item_wrap" href="'. Url::to(['/doctor/home', 'doctor_id' => $doctor_id]) .'">';
                            $html .=    '<div class="doc_photo">';
                            $html .=        '<img src="'.ArrayHelper::getValue($sched_item, 'doctor_avatar').'"  >';
                            $html .=    '</div>';
                            $html .=    '<div class="doc_content">';
                            $html .=        '<div class="doc_info">';
                            $html .=            '<div> <span class="doc_name">'.ArrayHelper::getValue($sched_item, 'realname').'</span><span class="doc_title">'.ArrayHelper::getValue($sched_item, 'doctor_title').'</span>';
                            $html .=            '</div>';
                            $html .=            '<span class="btn_little">去挂号</span>';
                            $html .=        '</div>';
                            $html .=        '<div class="doc_text text_wrap">'.ArrayHelper::getValue($sched_item,'department_name','').' | '.ArrayHelper::getValue($sched_item,'scheduleplace_name','').'</div>';

                            if (isset($sched_item['doctor_visit_type']) && !empty($sched_item['doctor_visit_type'])){
                                $html .=        '<div class="doc_tags"><span class="tags t_style01 t_short">'.$sched_item['doctor_visit_type'].'</span></div>';
                            }
                            $html .=        '<p class="doc_descript text_over2">擅长：'.ArrayHelper::getValue($sched_item, 'doctor_good_at','');
                            $html .=    '</div>';
                            $html .=    '</a>';
                            $html .= '</div>';
                        }
                    }
                }
            } 
           
        }
        return $html;
    }

}
