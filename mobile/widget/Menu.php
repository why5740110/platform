<?php
/**
 * @file Menu.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/8/11
 */

namespace mobile\widget;


use yii\base\Widget;
use yii\helpers\ArrayHelper;

class Menu extends Widget
{


    public function run()
    {
        $c = \Yii::$app->controller->id;

        $class = 'class=active';

        $index = $c == 'index' ? $class : '';
        $hospital = in_array($c,['hospital','hospitallist']) ? $class : '';
        $doctor = in_array($c,['doctor','doctorlist']) ? $class : '';

        $my = ArrayHelper::getValue(\Yii::$app->params,'domains.ihs').'my/index';
        $ua = \Yii::$app->controller->getUserAgent();
        //患者端app不显示底部导航
        if(in_array($ua, ['patient', 'mimi', 'haoyiapp'])){
            return '';
        }
         $html = '<div class="kongdiv"></div>
         <div class="menu_list">
            <ul>
              <li '. $index .'><a href="/hospital.html"><i class="icon icon01"></i><p>医院首页</p></a></li>
              <li ' . $hospital . '><a href="/hospital/hospitallist.html"><i class="icon icon02"></i><p>医院排行</p></a></li>
              <li ' . $doctor . '><a href="/hospital/doctorlist.html"><i class="icon icon03"></i><p>医生排行</p></a></li>
              <li class=""><a href="'.$my.'"><i class="icon icon04"></i><p>我的</p></a></li>
            </ul>
        </div>';

        // $html = '<menu>
        //     <ul>
        //         <li ' . $index . '><a href="/hospital.html"> <i class="icon icon-m1"></i>
        //                 <p>医院首页</p>
        //             </a></li>
        //         <li ' . $hospital . '><a href="/hospital/hospitallist.html"> <i class="icon icon-m4"></i>
        //                 <p>医院排行</p>
        //             </a></li>
        //         <li ' . $doctor . '><a href="/hospital/doctorlist.html"> <i class="icon icon-m6"></i>
        //                 <p>医生排行</p>
        //             </a></li>
        //         <li><a href="'.$my.'"> <i class="icon icon-m3"></i>
        //                 <p>我的</p>
        //             </a></li>
        //     </ul>
        // </menu>';


        return $html;
    }

}


