<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */

/* @var $content string */

use yii\helpers\Url;

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <?php
        $url = \Yii::$app->request->getPathInfo();
        $items = [];
        if (isset(Yii::$app->controller->menulist)) {

            foreach (Yii::$app->controller->menulist as $key => $item) {

                if ($item['child']) {

                    $items[$key] = ['label' => $item['name'], 'icon' => 'dashboard', 'url' => '#'];
                    foreach ($item['child'] as $k=> $subItem) {
                        if($subItem['is_show'] == 1){
                            $items[$key]['items'][] = ['label' => $subItem['name'], 'icon' => 'circle-o', 'url' => [$subItem['url']]];
                        }elseif($subItem['url'] == $url){//二级菜单隐藏时显示父菜单
                            $items[$key]['options']['class'] = "active menu-open";
                        }
                    }

                } else {
                    //$items[$key] = ['label' => $item['name'], 'icon' => 'file-code-o', 'url' => [$item['url']]];
                }
            }

        }
        ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => $items,
            ]
        ) ?>

    </section>

</aside>
