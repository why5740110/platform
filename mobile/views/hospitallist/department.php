<?php

use \common\helpers\Url;
use \mobile\widget\HospitalViewWidget;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use common\libs\CommonFunc;

$this->title = '按科室';
$this->registerCssFile(Url::getStaticUrl("css/departments_list.css"));
$this->registerJsFile(Url::getStaticUrl("js/departments_list.js"));

$page_size    = CommonFunc::PAGE_SIZE;
$current_data = 0;
$level_list = [0=>'不限']+CommonFunc::$level_list;
$hos_type_list = [0=>'不限']+CommonFunc::$hos_type_list;
?>
<div class="new_list_sx_all">
	<div class="new_list_sx_box">
	    <div class="search_top_box">
	    <div class="search_top_con dflex">
	        <div class="ms_header_index_address " localid="1">
	            <span class="title"><?php if($region == '0') {echo "全国"; }else{if(empty($city)) {echo $province['name'] ?? '全国'; }else{echo $city['name'] ?? '全国'; } } ?></span>
            </div>

	        <div class="search_top_con_l flex1 pr">
                <a href="<?= Url::to(['search/so']) ?>">
                    <i class="ss_icon"></i>
                    <input type="text" placeholder="搜索医院、科室、医生" autocomplete="off" readonly="readonly" value="" id="search_input" class="search_input">
                </a>
                <div class="search_qd_btn">搜索</div>
            </div>
	    </div> 
	    </div>

	    
	    <div class="filtrate">
	        <ul class="filtrate_ul ">
	            <li class="option_keshi"><span><?php if($keshi_id == '0') {echo "按科室"; }else{if(!empty($fkeshi_info) and !empty($skeshi_info)) {echo $skeshi_info['department_name']; } if(!empty($fkeshi_info) and empty($skeshi_info)) {echo $fkeshi_info['department_name']; } } ?></span> <em class="icon_sj_xia"></em> <i></i></li> 
                <li class="option2"><span><?php if($hosTypeStr == '0'){?> 医院类型 <?php }else{?> <?php echo $hosTypeStr;?> <?php }?></span> <em class="icon_sj_xia"></em> <i></i></li> 
                <li class="option"><span><?php if($sanjiaStr  == '0'){?> 医院等级 <?php }else{?> <?php echo $sanjiaStr;?> <?php }?></span> <em  class="icon_sj_xia"></em></li> 
            </ul>
	    </div>
	</div>


	<div class="hosp_all_box hosp_all_box_tow hosp_all_box_tow_new">

	    <div class="hosp_all_con_box">
    	<?php if (!empty($hospital_list)): ?>
            <?php $rankNum = $page_size * ($page - 1) + 1;?>
            <div class="db" id="doc_list">
                <?php foreach ($hospital_list as $hk => $hv): ?>
                    <?php $current_data++;?>
                    <?=HospitalViewWidget::widget(['row' => $hv, 'fkeshi' => $fkeshi_info['department_name'] ?? '', 'skeshi' => $skeshi_info['department_name'] ?? '', 'type' => 3, 'shence_type' => 1]);?>
                    <?php $rankNum++;?>
                <?php endforeach;?>
            </div>

        <?php else:?>
            <input type="hidden" value="1" id="no_data">
        <?php endif;?>

	    </div>

	    <?php if ($page == 1 && count($hospital_list) < $page_size): ?>

        <?php else: ?>
            <div class="hosp_moer_link searchLoadingMore">
                <a href="javascript:void(0);">查看更多<i class="icon_right_bg"></i></a>
            </div>
            <div class="more_but nothing_data" style="display: none;">已经到底了~</div>
        <?php endif; ?>

        <div class="no_search_box nosearch" style="display: <?php if(isset($hospital_list) && empty($hospital_list)) {echo 'block';}else{echo 'none';}?>">
            <img src="<?=Url::getStaticUrl('imgs/no_search.png')?>" width="109" height="109"  alt="">
            <p>您可以更换搜索内容试试~</p>
        </div>

	</div>


</div>

<div style="display: none;" id="more_page"
     data-uri="/hospital/hospitallist"
     region="<?= $region ?? '' ?>"
     sanjia="<?= $sanjia ?? 0 ?>"
     hos_type="<?= $hos_type ?? 0 ?>"
     keshi_id="<?= $keshi_id ?? 0 ?>"
     page="<?= $page ?? 1 ?>"
     disease="0"
     type="keshi"
     dspinyin="<?= $dspinyin ?? 0 ?>" ></div>
<input type="hidden" id="page" value="<?=$page?>">
<input type="hidden" id="page_size" value="<?=$page_size?>">

<div id="searchLoadingMore" style="display: none;">
    <a href="javascript:void(0);">查看更多<i class="icon_right_bg"></i></a>
</div>


<!-- 医院类型弹窗 -->
    <div class="screening2" >
        <div class="zezao"></div>
        <div class="screeningPopul">
            <div class="sx_con_box">
                <ul>
                    <li>
                        <div class="screenlist" id="screenlist1">
                            <?php foreach($hos_type_list as $hos_key=>$hos_val):?>
							    <label <?php if(isset($hos_type) && $hos_type == $hos_key): ?>class="on"<?php endif;?>>
							        <input type=radio name="hos_type" value="<?=$hos_key;?>" <?php if(isset($hos_type) && $hos_type == $hos_key): ?>checked=checked<?php endif;?> id="hos_type<?=$hos_key;?>">
							        <span class="radioCore"><?=$hos_val;?></span>
							    </label>
							<?php endforeach;?>
                        </div>
                    </li>
                </ul>
                <div class="screenSubmit screenSubmit_hostype">确定</div>
            </div>
    
        </div>
    </div>

<!-- 医院等级弹窗 -->
<div class="screening" >
    <div class="zezao"></div>
    <div class="screeningPopul">

        <div class="sx_con_box">
            <ul >
                <li>
                    <div class="screenlist" id="screenlist">
                        <?php foreach($level_list as $level_key=>$level_val):?>
						    <label for="kind<?=$level_key;?>" <?php if(isset($sanjia) && $sanjia == $level_key): ?>class="on"<?php endif;?>>
						        <input type=radio name="kind" value="<?=$level_key;?>" <?php if(isset($hos_type) && $hos_type == $level_key): ?>checked=checked<?php endif;?> id="kind<?=$level_key;?>">
						        <span class="radioCore"><?=$level_val;?></span>
						    </label>
						<?php endforeach;?>
                    </div>
                </li>
            </ul>
            <div class="screenSubmit">确定</div>
        </div>

    </div>
</div>

<div class="departmentMain" >
    <div class=departmentMain_con>
        <?php if ($ua != 'patient') { ?>
            <div class="nav">
                <span class="close closeDepartment"></span>
                <span class="fs16">科室选择</span>
                <span class="opcity_none"></span>
            </div>
        <?php } ?>

        <div class="classificationList">

            <ul class="populMainSel <?php if ($ua == 'patient') { ?>populMainSel2<?php } ?>">
                <li <?php if (isset($keshi_id) && $keshi_id): ?>class="getkeshi"<?php else: ?>  class="dep_active" <?php endif; ?>><s></s>全部
                </li>
                <?php if (isset($fkeshi_list) && !empty($fkeshi_list)): ?>
                <?php foreach ($fkeshi_list as $key => $value): ?>
                    <li keshi_id="<?php echo $value['department_id']; ?>" <?php if (isset($fkeshi_info['department_id']) && $fkeshi_info['department_id'] == $value['department_id']): ?> class="dep_active" <?php else: ?>class="getkeshi"<?php endif; ?>>
                        <s></s><?= Html::encode($value['department_name']); ?>
                    </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <div class="populMainTexth">
                 <ul class="populMainText" <?php if(isset($keshi_id) && $keshi_id):?>style="display: none;" <?php else:?>  <?php endif;?>>
                    <li ><a <?php if(isset($keshi_id) && $keshi_id):?><?php else:?>class="dep_active"<?php endif;?> href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>0,'hostype'=>$hos_type,'page'=>1])?>">不限</a></li>
                </ul>
                <?php if (isset($fkeshi_list) && !empty($fkeshi_list)): ?>
                <?php foreach ($fkeshi_list as $fk=>$fv):?>
                <ul class="populMainText" <?php if(isset($fkeshi_info['department_id']) && $fkeshi_info['department_id'] == $fv['department_id']):?> <?php else:?> style="display: none;" <?php endif;?>>
                    <li <?php if (isset($keshi_id) && isset($fkeshi_info['department_id']) && $keshi_id == $fkeshi_info['department_id']): ?> class="dep_active" <?php endif; ?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$fv['department_id'] ?? 0,'hostype'=>$hos_type,'page'=>1])?>">不限</a></li>
                    <?php foreach ($fv['second_arr'] as $sk=>$sv):?>
                        <li>
                            <a <?php if (isset($keshi_id) && $keshi_id == $sv['department_id']): ?> class="dep_active" <?php endif; ?>href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$sv['department_id'] ?? 0,'hostype'=>$hos_type,'page'=>1])?>"><?= Html::encode($sv['department_name']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endforeach;?>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<!--选择地区弹窗 -->
<div class="areaMain" >
    <?php if (\Yii::$app->controller->getUserAgent() != 'patient' && \Yii::$app->controller->getUserAgent() != 'mini') { ?>
        <div class="nav">
            <span class="close closeArea"></span>
            <span>选择地区</span>
        </div>
    <?php } ?>
    <div class="localtionMain">
        <ul class="localtionlist localtionlists">
            <li <?php if (isset($region) && $region): ?> class="getcity" <?php else: ?>  class="getcity sel" <?php endif; ?> >全国</li>

            <?php if (!empty($province_list)): ?>
                <?php foreach ($province_list as $key_pro => $value): ?>
                    <li <?php if (isset($province['id']) && $value['id'] == $province['id']): ?>class="getcity sel" <?php else: ?> class="getcity" <?php endif; ?> province_pinyin="<?php echo $value['pinyin']; ?>"><?= $value['name']; ?></li>
                <?php endforeach; ?>
            <?php endif; ?>

        </ul>

        <div class="localtionlist_box">
            <div class="localtionlist" <?php if (isset($city['pinyin']) || isset($province['pinyin'])): ?>style="display:none" <?php endif; ?>>
                <ul class="localtionlistcity">
                    <li>
                        <a href="<?= Url::to(['hospitallist/department', 'region'=>0,'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'hostype'=>$hos_type,'page'=>1]) ?>">不限</a>
                    </li>
                </ul>
            </div>

            <?php if (!empty($province_list)): ?>
            <?php foreach ($province_list as $key_pro => $value): ?>
            <div class="localtionlist" <?php if (isset($province['pinyin']) && $province['pinyin'] != $value['pinyin']): ?>style="display:none" <?php endif; ?>>
                <ul class="localtionlistcity" >
                    <li <?php if (isset($region) && isset($province['pinyin']) && $region == $value['pinyin']): ?> class="sel" <?php endif; ?>><a href="<?=Url::to(['hospitallist/department','region'=>$value['pinyin'] ?? 0,'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'hostype'=>$hos_type,'page'=>1])?>">不限</a></li>

                    <?php if (!empty($value['city_arr'])): ?>
                    <?php foreach ($value['city_arr'] as $ck => $cv): ?>
                        <li <?php if (isset($city['id']) && $cv['id'] == $city['id']): ?> class="sel" <?php endif; ?>><a href="<?=Url::to(['hospitallist/department','region'=>$cv['pinyin'],'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'hostype'=>$hos_type,'page'=>1])?>"><?= $cv['name']; ?></a>
                        </li>
                    <?php endforeach; ?>
                    <?php endif; ?>

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
    function clickDepartmentShence(data) {
        if ($("#shenceplatform_type").val() == 'patient') {
            //sensors.track('DepartmentClick', data);
        }
    }
</script>
