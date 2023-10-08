<?php
/**
 * 城市选择 存储选择的科室
 * @file HospitalCityWidget.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/4/27
 */


namespace mobile\widget;

use common\libs\CommonFunc;
use common\sdks\snisiya\SnisiyaSdk;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class HospitalCityWidget extends Widget
{

    public function run()
    {
        //获取地区
        $district = SnisiyaSdk::getInstance()->getDistrict();
        $selectArr = $selectArr = CommonFunc::get_city_cookie();  //选择的定位

        $cityHtml = '<div class="samePopul hos_citysPopul">
                        <div class=citysBox>
                            <div class=ms_regionalselection>
                                <div class=ms_regionalselection_l>
                                    <ul>';

        $cityHtml .= '<li class="localid_- ' . (ArrayHelper::getValue($selectArr, 'city_pid') == '-' || !ArrayHelper::getValue($selectArr, 'city_pid') ? 'selActive' : '') . '">全国</li>';

        if (isset($district) && is_array($district)) {
            foreach ($district as $v) {
                $cityHtml .= '<li p="' . $v['id'] . '" class="localid_' . $v['id'].' ' . (ArrayHelper::getValue($selectArr, 'city_pid') == $v['id'] ? 'selActive' : '') . '">' . $v['name'] . '</li>';
            }
        }

        $cityHtml .= '
                </ul>
            </div>
            <div class=ms_regionalselection_r>';
            if (empty($selectArr['pinyin'])){
                $cityHtml .=   '<ul>';
            } else{
                $cityHtml .=   '<ul style="display:none;">';
            }
            $cityHtml .=   '       <li class=js_selCity data-provid=- data-cityid=- city=全国 pinyin="">全国</li>
                </ul>';
        if (isset($district) && is_array($district)) {
            foreach ($district as $v) {
                if (isset($selectArr['city_pid']) && ArrayHelper::getValue($selectArr, 'city_pid') == $v['id']){
                    $cityHtml .= '<ul>';
                } else{
                    $cityHtml .= '<ul  style="display: none;">';
                }
                if (isset($v['city_arr']) && is_array($v['city_arr'])) {

                    $cityHtml .= '<li class="js_selCity ' . (ArrayHelper::getValue($selectArr, 'city_cid') == '-' && ArrayHelper::getValue($selectArr, 'city_pid') == $v['id'] ? 'selActive' : '') . '" data-provid="' . $v['id'] . '" data-cityid="-"
                            city="' . $v['name'] . '" pinyin="' . $v['pinyin'] . '">全部</li>';

                    foreach ($v['city_arr'] as $cityRow) {
                        $cityHtml .= '<li class="js_selCity ' . (ArrayHelper::getValue($selectArr, 'city_cid') == $cityRow['id'] ? 'selActive' : '') . '"
                                data-provid="' . $v['id'] . '" data-cityid="' . $cityRow['id'] . '"
                                city="' . $cityRow['name'] . '"
                                pinyin="' . $cityRow['pinyin'] . '">' . $cityRow['name'] . '</li>';
                    }
                }
                $cityHtml .= '</ul>';

            }
        }
        $cityHtml .= '
            </div>
        </div>
    </div>
</div>';

        return $cityHtml;

    }
}