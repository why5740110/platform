<?php
/**
 * @file HospitalViewWidget.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/12/31
 */


namespace mobile\widget;


use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;
use common\helpers\Url;
use yii\helpers\Html;

class HospitalViewWidget extends \yii\base\Widget
{

    public $row;
    public $type=1;
    public $fkeshi;
    public $skeshi;
    public $order_key; //神策埋点首页公司排序
    public $shence_type; //神策埋点类型，如果是按医院列表，才去埋点数据

    public function run()
    {
    	$type = $this->type;
    	$row = $this->row;
        $fkeshi = $this->fkeshi;
        $skeshi = $this->skeshi;
        $shence_type = $this->shence_type;
        // $html = CommonFunc::returnHospView($this->row,$this->type);
        $html = '';
        $km = ArrayHelper::getValue($row,'sort.3');
        if ($km != 'Infinity') {
            if($km<1){
                $km =intval($km*1000).'m';
            }elseif ($km>1 && $km<100) {
                $km =number_format($km, 1).'km';
            }else{
                $km = '>99km';
            }
        }else{
            $km = '';
        }
        $hospital_photo = ArrayHelper::getValue($row, 'hospital_logo') ?: ArrayHelper::getValue($row, 'hospital_photo', '');

        $hospital_name =$row['hospital_name'] ?? '';
        $hospital_level =$row['hospital_level'] ?? '';
        $hospital_type =$row['hospital_type'] ?? '';
        $html = '';
        if ($type == 1) {
            //王氏埋点
            $maidian = "{'click_id':'挂号-M首页-有号按钮' , 'click_url':'". rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')])."'}";
            $url = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['guahao/keshilist', 'hospital_id' => $row['hospital_id']]);;
            if (ArrayHelper::getValue($row,'hospital_real_plus') == 1) {
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }else{
                $url = Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')]);
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }
//            $html.= '<div onclick="clickHospitalShence({\'current_page\':\'msapp_register_hosptial\',\'current_page_name\':\'挂号医院页\',\'hospital_name\':\''.$hospital_name.'\',\'hospital_rank\':\''.$hospital_level.'\',\'hospital_type\':\''.$hospital_type.'\'})" class="hosp_con_box">';
            $html.= '<a class=list_item_wrap '.$href.'>';
            $html.=     '<div class=hos_logo> <img src="' . $hospital_photo . '" onerror="javascript:this.src='."'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'".';"></div>';
            $html.=     '<div class=hos_item>';
            $html.=         '<h3 class="hos_name text_wrap">'. ArrayHelper::getValue($row, 'hospital_name')  .'</h3>';
            $html.=         '<div class=hos_tags>';
            if (ArrayHelper::getValue($row, 'hospital_kind')) {
                $html .= '<span class="tags t_style01">' . ArrayHelper::getValue($row, 'hospital_kind', '公立') . '</span>';
            }
            $html.=         '<span class="tags t_style01 t_short">'. ArrayHelper::getValue($row, 'hospital_level','三甲')  .'</span>';
//            $html.=         '<span class=hos_distance>'. $km .'</span>';
            $html.=         '</div>';
            $html.=         '<p class="keshi_descript text_wrap">科室：'. ArrayHelper::getValue($row, 'hospital_department_name','')  .'</p>';
            $html.=         '<p class="hos_address text_wrap">地址：'.ArrayHelper::getValue($row, 'hospital_address').'</p>';
            $html.=     '</div>';
            $html.= '</a>';
        }
        if ($type == 2) {
            $order_key = $this->order_key + 1;
            //获取地区
            $sSdk = SnisiyaSdk::getInstance();
            $district = $sSdk->getDistrict();

            $p_name = ArrayHelper::getValue($district,$row['province_id'].'.name');
            $cityArr = ArrayHelper::getValue($district,$row['province_id'].'.city_arr');
            $cityArr = array_column($cityArr,'name','id');
            $c_name = ArrayHelper::getValue($cityArr,$row['city_id']);
            $row['hosp_name'] = $c_name?$c_name:$p_name;

            //王氏埋点
            $maidian = "{'click_id':'挂号-M首页-有号按钮' , 'click_url':'". rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')])."'}";
            $url = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['guahao/keshilist', 'hospital_id' => $row['hospital_id']]);;
            if (ArrayHelper::getValue($row,'hospital_real_plus') == 1) {
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }else{
                $url = Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')]);
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }

            if ($shence_type == 1) {
//                $html .= '<div class=list_item onclick="shenceHomeData({\'current_page\':\'msapp_register_home\',\'current_page_name\':\'挂号首页\',\'hospital_name\':\'' . ArrayHelper::getValue($row, 'hospital_name') . '\',\'hospital_order\':\'' . $order_key . '\'})">';
                $html .= '<div class=list_item>';
            }else {
                $html .= '<div class=list_item>';
            }
            $html.=     '<a class=list_item_wrap '.$href.'>';
            $html.=         '<div class=hos_logo>';
            $html.=             '<img src="' . $hospital_photo . '" onerror="javascript:this.src='."'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'".';">';
            $html.=         '</div>';
            $html.=         '<div class=hos_item>';
            $html.=             '<h3 class="hos_name text_wrap">'.ArrayHelper::getValue($row, 'hospital_name') .'</h3>';
            $html.=             '<div class="hos_tags">';
            if (ArrayHelper::getValue($row, 'hospital_kind')) {
                $html .= '<span class="tags t_style01">' . ArrayHelper::getValue($row, 'hospital_kind', '公立') . '</span>';
            }
            $html.=                 '<span class="tags t_style01 t_short">'. ArrayHelper::getValue($row, 'hospital_level','三甲') .'</span>';
            $html.=                 '<span class=hos_distance>'. $km .'</span>';
            $html.=             '</div>';
            $html.=             '<p class="keshi_descript text_wrap">科室：'. ArrayHelper::getValue($row, 'hospital_department_name','') .'</p>';
            $html.=             '<p class="hos_address text_wrap">地址：'. ArrayHelper::getValue($row, 'hospital_address').'</p>';
            $html.=         '</div>';
            $html.=     '</a>';
            $html.= '</div>';
        }

        if ($type == 3) {
             //王氏埋点
            $maidian = "{'click_id':'挂号-M首页-有号按钮' , 'click_url':'". rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')])."'}";
            $url = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['guahao/keshilist', 'hospital_id' => $row['hospital_id']]);;
            if (ArrayHelper::getValue($row,'hospital_real_plus') == 1) {
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }else{
                $url = Url::to(['hospital/index', 'hospital_id' => ArrayHelper::getValue($row, 'hospital_id')]);
                $href = 'onclick="_maq.click(' . $maidian . ')"  href="' . $url . '" class="dflex"';
            }

            if ($shence_type == 1) {
//                $html .= '<div class="db_list" onclick="clickDepartmentShence({\'current_page\':\'msapp_register_department\',\'current_page_name\':\'挂号科室页\',\'hospital_name\':\''.$hospital_name.'\',\'hospital_rank\':\''.$hospital_level.'\',\'hospital_type\':\''.$hospital_type.'\',\'department_first_level_name\':\''.$fkeshi.'\',\'department_second_level_name\':\''.$skeshi.'\'})">';
                $html .= '<div class="db_list">';
            }else {
                $html .= '<div class="db_list">';
            }
            $html .= '<a class="list_item_wrap"'.$href.'>';
            $html .= '<div class="hos_logo">';
            $html .= '<img src="'. $hospital_photo .'" onerror="javascript:this.src='."'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'".';">';
            $html .= '</div>';
            $html .= '<div class="hos_item">';
            $html .= '<h3 class="hos_name text_wrap">' . ArrayHelper::getValue($row, 'hospital_name') . '</h3>';
            $html .= '<div class="hos_tags">';
            if (ArrayHelper::getValue($row, 'hospital_kind')) {
                $html .= '<span class="tags t_style01">' . ArrayHelper::getValue($row, 'hospital_kind') . '</span>';
            }
            $html .= '<span class="tags t_style01 t_short">' . ArrayHelper::getValue($row, 'hospital_level','三甲') . '</span>';
            $html .= '</div>';
            $html .= '<p class="keshi_descript text_wrap">科室：'. ArrayHelper::getValue($row, 'hospital_department_name')  .'</p>';
            $html .= '<p class="hos_address text_wrap">地址：' . ArrayHelper::getValue($row, 'hospital_address') . '</p>';
            $html .= '<div class="ht_department">';
            $html .= '<p>' . ArrayHelper::getValue($row, 'doctor_second_department_name') . '</p>';
            $html .= '<span class="btn_little">去挂号</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
        }

        return $html;
    }
}