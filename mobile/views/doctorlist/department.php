<?php

use \common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;

$this->registerCssFile(Url::getStaticUrl("css/rankDoctorRoom.css"));
$this->registerJsFile(Url::getStaticUrl("js/rankDoctorRoom.js"));
?>
<?php if(\Yii::$app->controller->getUserAgent() != 'patient' && \Yii::$app->controller->getUserAgent() != 'mini' ){ ?>
<header>
    <span onclick=window.history.go(-1);></span>
    <h1><?php echo $city['name'] ?? $province['name'] ?? '全国' ?>医生排行榜</h1>
    <span></span>
</header>
<?php } ?>

<div class=tab>
    <a href="<?= Url::to(['doctorlist/index','region' => 0, 'sanjia' => 0,'page'=>1]) ?>">医生排行榜</a>
    <a href="<?= Url::to(['doctorlist/department','region' =>  0, 'sanjia' => 0,'keshi_id'=>0,'page'=>1]) ?>"
       class=tab_active>按科室找</a>
    <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'page' => 1]) ?>">按疾病找</a>
</div>
<div style="display: none;" id="more_page"
     data-uri="<?php echo Url::to(['doctorlist/department','region' => $region??0, 'sanjia' => $sanjia??0,'keshi_id'=>$keshi_id??0,'page'=>($page+1)]); ?>"
     keshiurl="<?php echo Url::to(['doctorlist/ajax-get-keshi']); ?>"
     region="<?= $region ?? '' ?>"
     sanjia="<?= $sanjia ?? 0 ?>"
     keshi_id="<?= $keshi_id ?? 0 ?>"
     disease="0"
     type="keshi"
     dspinyin="" ></div>

<div class=filtrate>
    <ul>
        <a class=area>
            <p>
                <?php echo $city['name'] ?? $province['name'] ?? '全国' ?>
            </p>
            <span></span>
        </a>
        <a class=department>
            <p><?php echo $skeshi_info['department_name'] ?? $fkeshi_info['department_name'] ?? '科室' ?></p>
            <span></span>
        </a>
        <a class=option>
            <p>筛选</p>
            <span></span>
        </a>
    </ul>
</div>

<?php echo \mobile\widget\DoctorlistContentWidget::widget(['doctorlist'=>$doctorlist,'pagination'=>$pagination,'totalCount'=>$totalCount,'page'=>$page,'region'=>$region,'sanjia'=>$sanjia]);?>

<div class="nosearch" style="display: <?php if(isset($doctorlist) && empty($doctorlist)) {echo 'block';}else{echo 'none';}   ?>" ></div>

<div class=areaMain style="display: none;">
    <div class=nav>
        <span class="close closeArea"></span>
        <span>选择地区</span>
        <span style="opacity: 0"></span>
    </div>
    <div class=localtionMain>
        <ul class="localtionlist localtionlists">
            <li <?php if (isset($region) && $region): ?><?php else: ?>  class="sel" <?php endif; ?> >
                <a href="<?= Url::to(['doctorlist/department', 'region' => 0, 'sanjia' => $sanjia, 'keshi_id' => $keshi_id,'page'=>$page]) ?>">全国</a>
            </li>
            <?php if (!empty($province_list)): ?>
                <?php foreach ($province_list as $key_pro => $value): ?>
                    <li region="<?php echo $value['pinyin']; ?>"
                        <?php if (isset($province['id']) && $value['id'] == $province['id']): ?>class="sel" <?php endif; ?>><?= Html::encode($value['name']); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <div class=localtionlist_box>
            <div class=localtionlist <?php if (isset($city['pinyin'])||isset($province['pinyin'])): ?>style="display:none" <?php endif; ?>>
                <ul class=localtionlistcity>
                    <li>
                        <a href="<?= Url::to(['doctorlist/department', 'region' => $province['pinyin'] ?? 0, 'sanjia' => $sanjia, 'keshi_id' => $keshi_id,'page'=>$page]) ?>">不限</a>
                    </li>
                </ul>
            </div>
            <?php if (!empty($province_list)): ?>
                <?php foreach ($province_list as $k => $item): ?>
                    <div class=localtionlist
                         <?php if (isset($province['pinyin']) && $province['pinyin'] != $item['pinyin']): ?>style="display:none" <?php endif; ?>>
                        <ul class=localtionlistcity>
                            <li>
                                <a href="<?= Url::to(['doctorlist/department', 'region' => $item['pinyin'] ?? '', 'sanjia' => $sanjia, 'keshi_id' => $keshi_id,'page'=>$page]) ?>">不限</a>
                            </li>
                            <?php foreach ($item['city_arr'] as $value): ?>
                                <li>
                                    <a <?php if (isset($city['pinyin']) && $city['pinyin'] == $value['pinyin']): ?>class="sel" <?php endif; ?>
                                       href="<?= Url::to(['doctorlist/department', 'region' => $value['pinyin'], 'sanjia' => $sanjia, 'keshi_id' => $keshi_id,'page'=>$page]) ?>">
                                        <?= Html::encode($value['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!--  筛选  -->
<?php echo \mobile\widget\SelectWidget::widget(['sanjia' => $sanjia]) ?>

<div class=departmentMain>
    <div class=nav>
        <span class="close closeDepartment"></span>
        <span>科室选择</span>
        <span style="opacity: 0"></span>
    </div>
    <div class=classificationList>
        <?php if (isset($fkeshi_list) && !empty($fkeshi_list)): ?>
            <ul class=populMainSel>
                <li <?php if (isset($keshi_id) && $keshi_id): ?><?php else: ?>  class="dep_active" <?php endif; ?>>
                    <a href="<?= Url::to(['doctorlist/department', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => 0, 'page' => 1]) ?>">不限</a>
                </li>
                <?php foreach ($fkeshi_list as $key => $value): ?>
                    <li keshi_id="<?php echo $value['department_id']; ?>" <?php if (isset($fkeshi_info['department_id']) && $fkeshi_info['department_id'] == $value['department_id']): ?> class="dep_active" <?php endif; ?>>
                        <?= Html::encode($value['department_name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (isset($skeshi_list) && !empty($skeshi_list)): ?>
            <div class="populMainTexth">
                <ul class="populMainText">
                    <li <?php if (isset($keshi_id) && $keshi_id == $fkeshi_info['department_id']): ?> class="dep_active" <?php endif; ?>>
                        <a href="<?= Url::to(['doctorlist/department', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => $fkeshi_info['department_id'], 'page' => 1]) ?>">不限</a>
                    </li>
                    <?php foreach ($skeshi_list as $key => $value): ?>
                        <li>
                            <a <?php if (isset($keshi_id) && $keshi_id == $value['department_id']): ?> class="dep_active" <?php endif; ?>
                                    href="<?= Url::to(['doctorlist/department', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => $value['department_id'], 'page' => 1]) ?>"><?= Html::encode($value['department_name']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="populMainTexth">
                <ul class="populMainText">
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class=moreHeights></div>
