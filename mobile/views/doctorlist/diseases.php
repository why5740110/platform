<?php

use \common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;

$this->registerCssFile(Url::getStaticUrl("css/rankDoctorSickness.css"));
$this->registerJsFile(Url::getStaticUrl("js/rankDoctorSickness.js"));
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
    <a href="<?= Url::to(['doctorlist/department','region' =>  0, 'sanjia' => 0,'keshi_id'=>0,'page'=>1]) ?>">按科室找</a>
    <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'page' => 1]) ?>"
       class=tab_active>按疾病找</a>
</div>

<div style="display: none;" id="more_page"
     data-uri="<?php echo Url::to(['doctorlist/diseases','region' => $region??0, 'sanjia' => $sanjia??0, 'diseases' => $diseases, 'dspinyin' => $dspinyin??'','page'=>($page+1)]); ?>"
     keshiurl="<?php echo Url::to(['doctorlist/ajax-get-keshi']); ?>"
     sanjia="<?= $sanjia ?? 0 ?>"
     type="disease"
     region="<?= $region ?? '' ?>"
     sanjia="<?= $sanjia ?? 0 ?>"
     keshi_id="0"
     disease="<?= $disease ?? 0 ?>"
     dspinyin="<?= $dspinyin ?? 0 ?>" ></div>

<div  class=filtrate>
    <ul>
        <a class=diseaseSort>
            <p><?php echo ($search_disease_name == '') ? '疾病' : $search_disease_name; ?></p>
            <span></span>
        </a>
        <a class=area>
            <p><?php echo $city['name'] ?? $province['name'] ?? '全国' ?></p>
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
                <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => $diseases,'page'=>$page]) ?>">全国</a>
            </li>
            <?php if (!empty($province_list)): ?>
                <?php foreach ($province_list as $key_pro => $value): ?>
                    <li region="<?php echo $value['pinyin']; ?>"
                        <?php if (isset($province['id']) && $value['id'] == $province['id']): ?>class="sel" <?php endif; ?>>
                        <?= $value['name']; ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <div class=localtionlist_box>
            <div class=localtionlist <?php if (isset($city['pinyin'])||isset($province['pinyin'])): ?>style="display:none" <?php endif; ?>>
                <ul class=localtionlistcity>
                    <li>
                        <a href="<?= Url::to(['hospitallist/diseases', 'region' => $province['pinyin'] ?? 0, 'sanjia' => $sanjia, 'diseases' => $diseases??0, 'dspinyin' => $dspinyin??'','page'=>$page]) ?>">不限</a>
                    </li>
                </ul>
            </div>
            <?php if (!empty($province_list)): ?>
                <?php foreach ($province_list as $k => $item): ?>
                    <div class=localtionlist
                         <?php if (isset($province['pinyin']) && $province['pinyin'] != $item['pinyin']): ?>style="display:none" <?php endif; ?>>
                        <ul class=localtionlistcity>
                            <li>
                                <a href="<?= Url::to(['doctorlist/diseases', 'region' => $item['pinyin'] ?? '', 'sanjia' => $sanjia, 'diseases' => $diseases, 'dspinyin' => $dspinyin,'page'=>$page]) ?>">不限</a>
                            </li>
                            <?php foreach ($item['city_arr'] as $value): ?>
                                <li>
                                    <a <?php if (isset($city['pinyin']) && $city['pinyin'] == $value['pinyin']): ?>class="sel" <?php endif; ?>
                                       href="<?= Url::to(['doctorlist/diseases', 'region' => $value['pinyin'], 'sanjia' => $sanjia, 'diseases' => $diseases, 'dspinyin' => $dspinyin,'page'=>$page]) ?>">
                                        <?= $value['name']; ?>
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

<div class=disease>
    <div class=diseaseBox>
        <div class=nav>
            <span class="close closeDisease"></span>
            <span>选择疾病</span>
            <span style="opacity: 0"></span>
        </div>
        <div class=diseaseSearch>
            <div class=diseaseSearchFrom>
                <span class=icon></span>
                <input type=text value="<?= $search_disease_name; ?>" id="diseaseSearchInput">
                <span class=SearchFromText data-type="doctor">搜索</span>
            </div>
        </div>
        <div class=NameMain>
            <div class=NameList>
                <ul>
                    <li>
                        <span>热门疾病</span>
                        <dl>
                            <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => 'nangzhongxingjibing', 'page' => 1]) ?>">囊肿性疾病</a>
                            <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => 'waikeganran', 'page' => 1]) ?>">外科感染</a>
                            <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => 'shouzukoubing', 'page' => 1]) ?>">手足口病</a>
                            <a href="<?= Url::to(['doctorlist/diseases', 'region' => 0, 'sanjia' => 0, 'diseases' => 0, 'dspinyin' => 'shuidou', 'page' => 1]) ?>">水痘</a>
                        </dl>
                    </li>
                    <?php if (!empty($iniarr)): ?>
                        <?php foreach ($iniarr as $key_pro => $item): ?>
                            <li id="<?= $key_pro; ?>">
                                <span><?= $key_pro; ?></span>
                                <dl>
                                    <?php foreach ($item as $v): ?>
                                        <a href=<?= Url::to(['doctorlist/diseases', 'region' => $region, 'sanjia' => $sanjia, 'diseases' => 0, 'dspinyin' => $v['pinyin'], 'page' => 1]) ?>>
                                            <?= $v['disease_name']; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </dl>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class=sortList>
                <a value=A href=#A>A</a>
                <a value=B href=#B>B</a>
                <a value=C href=#C>C</a>
                <a value=D href=#D>D</a>
                <a value=E href=#E>E</a>
                <a value=F href=#F>F</a>
                <a value=G href=#G>G</a>
                <a value=H href=#H>H</a>
                <a value=I href=#I>I</a>
                <a value=J href=#J>J</a>
                <a value=K href=#K>K</a>
                <a value=L href=#L>L</a>
                <a value=M href=#M>M</a>
                <a value=N href=#N>N</a>
                <a value=O href=#O>O</a>
                <a value=P href=#P>P</a>
                <a value=Q href=#Q>Q</a>
                <a value=R href=#R>R</a>
                <a value=S href=#S>S</a>
                <a value=T href=#T>T</a>
                <a value=W href=#W>W</a>
                <a value=X href=#X>X</a>
                <a value=Y href=#Y>Y</a>
                <a value=Z href=#Z>Z</a>
            </div>
        </div>
    </div>
</div>
<div class=moreHeights></div>
<!-- 底部 -->
