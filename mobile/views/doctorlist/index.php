<?php

use \common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use \mobile\widget\DoclistViewWidget;

$this->registerCssFile(Url::getStaticUrl("css/doctor_list.css"));
$this->registerJsFile(Url::getStaticUrl("js/doctor_list.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/search/index.css"),['depends'=>'mobile\assets\AppAsset']);

$this->title = '按医生';
if (\Yii::$app->controller->getUserAgent() == 'patient') {
    $this->context->seoTitle = $this->title;
}

?>

    <div class="new_list_sx_all">
        <div class="new_list_sx_box ">
            <div class="search_top_box">
                <div class="search_top_con dflex">
                    <div class="ms_header_index_address " localid="1">
                        <span class="title"><?= $city['name'] ?? $province['name'] ?? '全国' ?></span>
                    </div>
                    <div class="search_top_con_l flex1 pr">
                        <a href="<?= Url::to(['search/so']) ?>">
                            <i class="ss_icon"></i>
                            <input type="text" placeholder="搜索医院、科室、医生" autocomplete="off" value="" id="search_input"
                                   class="search_input" readonly="readonly">
                            <!-- <i class="icon_search_close_bg"></i> -->
                        </a>
                        <div class="search_qd_btn">搜索</div>
                    </div>
                </div>
            </div>


            <div class="filtrate">
                <ul class="filtrate_ul ">
                    <li class="option_keshi">
                        <span><?php echo $skeshi_info['department_name'] ?? $fkeshi_info['department_name'] ?? '科室' ?></span>
                        <em class="icon_sj_xia"></em> <i></i></li>
                    <li class="option"><span><?php
                            switch ($sanjia) {
                                case '1':
                                    echo "主任医师";
                                    break;
                                case '6':
                                    echo "副主任医师";
                                    break;
                                case '3':
                                    echo "主治医师";
                                    break;
                                case '4':
                                    echo "住院医师";
                                    break;
                                default :
                                    echo "职称";
                            }
                            ?></span> <em class="icon_sj_xia"></em></li>
                </ul>
            </div>
        </div>

        <?php if (!empty($doctorlist)): ?>
            <div class="hosp_all_box_tow_new sr_con">
                <div class="doc_all_con_box" id="doc_list">

                    <?php foreach ($doctorlist as $k => $value): ?>
                        <?php $num = ($page - 1) * 20;; ?>
                        <?php $value['shece_doctor_hospital'] = $value['doctor_hospital']; ?>
                        <?= DoclistViewWidget::widget(['row' => $value, 'type' => 1]); ?>
                    <?php endforeach; ?>

                </div>

                <?php if ($page == 1 && count($doctorlist) < 15): ?>

                <?php else: ?>
                    <div class="hosp_moer_link searchLoadingMore">
                        <a href="javascript:void(0);">查看更多<i class="icon_right_bg"></i></a>
                    </div>
                    <div class="more_but nothing_data" style="display: none;">已经到底了~</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <input type="hidden" value="1" id="no_data">
        <?php endif; ?>

        <div style="display: none;" id="more_page"
             data-uri="/hospital/doctorlist/"
             keshiurl="<?php echo Url::to(['doctorlist/ajax-get-keshi']); ?>"
             region="<?= $region ?? 0 ?>"
             sanjia="<?= $sanjia ?? 0 ?>"
             keshi_id="<?= $keshi_id ?? 0 ?>"
             disease="0"
             type="region"
             dspinyin=""></div>
        <input type="hidden" id="page" value="<?= $page ?>">
        <div class="no_search_box nosearch" style="display: <?php if (isset($doctorlist) && empty($doctorlist)) {
            echo 'block';
        } else {
            echo 'none';
        } ?>">
            <img src="<?= Url::getStaticUrl('imgs/no_search.png') ?>" width="109" height="109" alt="">
            <p>您可以更换搜索内容试试~</p>
        </div>

    </div>


    <!-- 医生筛选弹窗 -->
<?= \mobile\widget\SelectWidget::widget(['sanjia' => $sanjia, 'ua' => $ua]) ?>

    <!-- 科室弹框 -->
    <div class="departmentMain">
        <div class=departmentMain_con>
            <?php if ($ua != 'patient') { ?>
                <div class="nav">
                    <span class="close closeDepartment"></span>
                    <span class="fs16">科室选择</span>
                    <span class="opcity_none"></span>
                </div>
            <?php } ?>
            <div class="classificationList">
                <?php if (isset($fkeshi_list) && !empty($fkeshi_list)): ?>
                    <ul class="populMainSel <?php if ($ua == 'patient') { ?>populMainSel2<?php } ?>">
                        <li <?php if (isset($keshi_id) && $keshi_id): ?>class="getkeshi"<?php else: ?>  class="dep_active" <?php endif; ?>>
                            <s></s>不限
                        </li>
                        <?php foreach ($fkeshi_list as $key => $value): ?>
                            <li keshi_id="<?php echo $value['department_id']; ?>" <?php if (isset($fkeshi_info['department_id']) && $fkeshi_info['department_id'] == $value['department_id']): ?> class="dep_active" <?php else: ?>class="getkeshi"<?php endif; ?>>
                                <s></s>
                                <?= Html::encode($value['department_name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (isset($skeshi_list) && !empty($skeshi_list)): ?>
                    <div class="populMainTexth">
                        <ul class="populMainText">
                            <li <?php if (isset($keshi_id) && $keshi_id == $fkeshi_info['department_id']): ?> class="dep_active" <?php endif; ?>>
                                <a href="<?= Url::to(['doctorlist/index', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => $fkeshi_info['department_id'], 'page' => 1]) ?>">不限</a>
                            </li>
                            <?php foreach ($skeshi_list as $key => $value): ?>
                                <li>
                                    <a <?php if (isset($keshi_id) && $keshi_id == $value['department_id']): ?> class="dep_active" <?php endif; ?>
                                            href="<?= Url::to(['doctorlist/index', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => $value['department_id'], 'page' => 1]) ?>"><?= Html::encode($value['department_name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="populMainTexth">
                        <ul class="populMainText">
                            <li <?php if (isset($keshi_id) && $keshi_id == 0): ?> class="dep_active" <?php endif; ?>>
                                <a href="<?= Url::to(['doctorlist/index', 'region' => $region, 'sanjia' => $sanjia, 'keshi_id' => 0, 'page' => 1]) ?>">不限</a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!--选择地区弹窗 -->
    <div class="areaMain">
        <?php if (\Yii::$app->controller->getUserAgent() != 'patient' && \Yii::$app->controller->getUserAgent() != 'mini') { ?>
            <div class="nav">
                <span class="close closeArea"></span>
                <span>选择地区</span>
            </div>
        <?php } ?>

        <div class="localtionMain">
            <ul class="localtionlist localtionlists">
                <li <?php if (isset($region) && $region): ?> class="getcity" <?php else: ?>  class="getcity sel" <?php endif; ?> >
                    全国
                </li>
                <?php if (!empty($province_list)): ?>
                    <?php foreach ($province_list as $key_pro => $value): ?>
                        <li region="<?= $value['pinyin']; ?>"
                            <?php if (isset($province['id']) && $value['id'] == $province['id']): ?> class="getcity sel" <?php else: ?> class="getcity" <?php endif; ?>><?= Html::encode($value['name']); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <div class="localtionlist_box">
                <div class=localtionlist
                     <?php if (isset($city['pinyin']) || isset($province['pinyin'])): ?>style="display:none" <?php endif; ?>>
                    <ul class=localtionlistcity>
                        <li>
                            <a href="<?= Url::to(['doctorlist/index', 'region' => 0, 'sanjia' => $sanjia, 'keshi_id' => $keshi_id, 'page' => $page]) ?>">不限</a>
                        </li>
                    </ul>
                </div>

                <?php if (!empty($province_list)): ?>
                    <?php foreach ($province_list as $k => $item): ?>
                        <div class=localtionlist
                             <?php if (isset($province['pinyin']) && $province['pinyin'] != $item['pinyin']): ?>style="display:none" <?php endif; ?>>
                            <ul class=localtionlistcity>
                                <li>
                                    <a <?php if (isset($region) && isset($province['pinyin']) && $region == $item['pinyin']): ?>class="sel" <?php endif; ?>
                                       href="<?= Url::to(['doctorlist/index', 'region' => $item['pinyin'] ?? '', 'sanjia' => $sanjia, 'keshi_id' => $keshi_id, 'page' => $page]) ?>">不限</a>
                                </li>
                                <?php foreach ($item['city_arr'] as $value): ?>
                                    <li>
                                        <a <?php if (isset($city['pinyin']) && $city['pinyin'] == $value['pinyin']): ?>class="sel" <?php endif; ?>
                                           href="<?= Url::to(['doctorlist/index', 'region' => $value['pinyin'], 'sanjia' => $sanjia, 'keshi_id' => $keshi_id, 'page' => $page]) ?>">
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

<?php
echo \mobile\widget\ShenceStatisticsWidget::widget(['type' => '','data'=>[]]);
?>
<input style="display: none" id="shenceplatform_type" value="<?=\Yii::$app->controller->getUserAgent()?>">
<script>
    /**
     * 医生埋点
     * 首页按医生点击-》进入医生列表-》点击医生埋点
     * @param data
     */
    function clickDoctorShence(data) {
        if ($("#shenceplatform_type").val() == 'patient') {
            //sensors.track('DoctorClick', data);
        }
    }
</script>
