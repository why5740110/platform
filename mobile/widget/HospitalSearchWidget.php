<?php
/**
 * @file HospitalSearchWidget.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/4/27
 */


namespace mobile\widget;

use yii\base\Widget;
use common\helpers\Url;
use common\libs\CommonFunc;
use yii\helpers\ArrayHelper;

class HospitalSearchWidget extends Widget
{
    public function run()
    {
        $selectArr = $selectArr = CommonFunc::get_city_cookie();  //选择的定位

        $cityName = ArrayHelper::getValue($selectArr, 'city');

        $searchHtml = '<div class=search_box>
            <div class=placeChange localid="'.ArrayHelper::getValue($selectArr, 'city_pid').'" ><span class="placestyle"><b>' . $cityName . '</b></span></div>
            <div><i class=ssicon></i> <a href="' . Url::to(['search/so']) . '" class=ssletter>搜索医院、科室、医生</a></div>
        </div>';
        return $searchHtml;

    }
}