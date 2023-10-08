<?php

use common\libs\CommonFunc;
use \common\helpers\Url;
use \yii\helpers\ArrayHelper;

$this->title = (isset($doctor_info['doctor_realname']) && !empty($doctor_info['doctor_realname'])) ? $doctor_info['doctor_realname'] . '医生' : '';
$this->registerCssFile(Url::getStaticUrl("css/doctor.css"));
$this->registerJsFile(Url::getStaticUrl("js/doctor.js"));

$platformArr = CommonFunc::getTpPlatformNameList();
$dayArr = CommonFunc::$visit_nooncode_type;
$visit_type_list = CommonFunc::$visit_type;
$show_day = CommonFunc::SHOW_DAY;


$weekArr = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];

$all_hos_num = 1;



?>
<div class="doctor">

    <div class="doctor_head">
      <span></span>
    </div>

    <div class="doctor_top">
      <dl>
        <dt><img src="<?=ArrayHelper::getValue($doctor_info, 'doctor_avatar')?>" onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';" alt="<?=ArrayHelper::getValue($doctor_info, 'doctor_realname')?>"></dt>
        <dd>
          <h4><?=ArrayHelper::getValue($doctor_info, 'doctor_realname');?><span><?=ArrayHelper::getValue($doctor_info, 'doctor_title');?></span><span><?=ArrayHelper::getValue($doctor_info, 'doctor_second_department_name');?></span></h4>
          <p><i></i><span><?=ArrayHelper::getValue($doctor_info, 'doctor_hospital_data.name');?></span></p>
        </dd>
      </dl>
      <a href="<?=Url::to(['doctor/intro', 'doctor_id' => ArrayHelper::getValue($doctor_info, 'doctor_id')]);?>" class="doctor_top_con">
        <p><i>擅长</i><span class="doctor_top_con_text"><?=ArrayHelper::getValue($doctor_info, 'doctor_good_at');?></span></p>
        <b></b>
      </a>
      <h5>
        <p><span><?=ArrayHelper::getValue($doctor_info, 'comment_rate') ? ArrayHelper::getValue($doctor_info, 'comment_rate') : '暂无'?></span>满意度</p>
        <p><span><?=ArrayHelper::getValue($doctor_info, 'person_time') != 0 ? ArrayHelper::getValue($doctor_info, 'person_time') : '暂无'?></span>服务人次</p>
      </h5>
    </div>

    <div class="doctor_con">
        <?php $remaining_quantity = 0;?>
    	<?php if (isset($guahao) && $guahao): ?>
		<div class="doctor_con01">
			<?php if (isset($guahao) && $guahao): ?>
               <?php $m = 1;?>
               <?php $j = 1;?>
				<h4><b><?=$all_hos_num;?></b><p><i><?= ArrayHelper::getValue($doctor_info, 'doctor_hospital_data.name');?></i><strong>第一执业</strong></p><span><img src="<?=Url::getStaticUrl('imgs/doctor_icon07.png')?>"></span></h4>
			<?php endif; ?>

			<div class="doctor_con01_data">
				<div class="doctor_con01_dep">
					<div class="doctor_con01_dep_left">
						<ul>
							<?php $kj = 1;?>
	                        <?php foreach ($guahao as $gk=>$gv): ?>
								<li <?php if ($kj == 1): ?>class="dq"<?php endif; ?>><?=$gk;?><i></i></li>
							<?php $kj++;?>
							<?php endforeach; ?>
						</ul>
					</div>
					<b class="doctor_con01_dep_b"></b>
					<span id="refresh_doctor" data-status="0">刷新</span>
				</div>

				<div class="doctor_con01_list">
                    <?php $i = 0;?>
                    
                    <?php foreach ($guahao as $gk=>$g): ?>
                    	<?php $first_platform = '河南';?>
	                    <?php $activeFlag = false;?>
						<div <?php if ($i == 0): ?>class="dq" id="refresh_data"<?php endif;?> >
							<div class="doctor_con01_list_boy">
								<div class="doctor_con01_list_left">
									<ul>
										<li></li>
										<li><span>上午</span></li>
										<li><span>下午</span></li>
										<li><span>晚上</span></li>
									</ul>
								</div>
								
								<div class="doctor_con01_list_right"> 
									<?php $have_all_hao = false;?>
	                                <?php foreach ($g as $gkk=>$gvv): ?>
	                                <?php $have_hao = false;?>
	                                <?php if (!empty($gvv) && !$have_hao): ?>
	                                	<?php $have_hao = true;?>
	                                	<?php $have_all_hao = true;?>
	                                <?php endif; ?>

									<ul class="<?=$have_hao ? 'have' : '';?>"> 
										<li> <p> <span><?php $dayKey = date('w',strtotime($gkk));?><?=ArrayHelper::getValue($weekArr,$dayKey)?></span> <i><?=date('m-d',strtotime($gkk))?></i> </p> </li>
										
										<?php foreach ($dayArr as $n=>$n_text): ?>
										<?php if (ArrayHelper::getValue($gvv,$n)): ?>
                                                <?php $tp_platform = ArrayHelper::getValue($gvv, $n . '.tp_platform');
                                                $first_platform = $platformArr[$tp_platform] ?? ''; ?>

											<?php if (ArrayHelper::getValue($gvv,$n.'.status') == 1): ?>
											<li onclick="shenceOtherDate('<?php echo $gkk;?>','<?=ArrayHelper::getValue($gvv,$n.".visit_cost")/100;?>')" class="yh <?php if (ArrayHelper::getValue($gvv,$n.'.is_section') == 1): ?> tc_click<?php else: ?> do_guahao <?php endif; ?>"
				                			<?php if (ArrayHelper::getValue($gvv,$n.'.is_section') == 1): ?>
                                            <?php $remaining_quantity += ArrayHelper::getValue($gvv,$n.'.schedule_available_count',0);?>
				                				<?php $other_sectionArr = CommonFunc::group_section(ArrayHelper::getValue($gvv,$n.'.sections'));?>  data-sections='<?=json_encode($other_sectionArr);?>' <?php endif; ?>
				                			data-url="<?=   htmlspecialchars_decode(Url::to(['register/choose-patient', 'doctor_id' => \Yii::$app->request->get('doctor_id', ''), 'scheduling_id' => ArrayHelper::getValue($gvv,$n.'.tp_scheduling_id'),'tp_platform'=>ArrayHelper::getValue($gvv,$n.'.tp_platform')])) ?>"> <p> <span><?=(ArrayHelper::getValue($gvv,$n.'.schedule_available_count',0)) >0 ? '剩余'.ArrayHelper::getValue($gvv,$n.'.schedule_available_count',0) : '挂号';?></span> <i>¥<?=ArrayHelper::getValue($gvv,$n.'.visit_cost')/100;?> </i> </p> </li>
											<?php elseif (ArrayHelper::getValue($gvv,$n.'.status') == 2): ?>
								                <li class="tz"><p><strong>停诊</strong></p></li>
								                <?php else: ?>
		                                		<li class="tz"><p><strong>约满</strong></p></li>
		                                	<?php endif; ?>
											
										<?php else: ?>
											<li></li>
										<?php endif; ?>
										<?php endforeach; ?>
									</ul>
									<?php endforeach; ?>

								</div> 
							</div>

					        <div class="doctor_con_tips">
					          <p>注：展示近<?= $show_day; ?>天号源，该号源由<?= $first_platform; ?>挂号平台提供。</p>
					        </div>

						</div>
					<?php $i++;?>
					<?php endforeach; ?>

				</div>

				

			</div>

		</div>
		<?php $all_hos_num++;?>
		<?php endif; ?>
		<?php if (!empty($other_guahao)): ?>
		<?php foreach ($other_guahao as $ok => $ov): ?>
		<?php $om_tab = 0;?>
		<?php $om_content = 0;?>
	    <div class="doctor_con02">
	        <h4>
	          <b><?=$all_hos_num;?></b>
	          <p><i><?=$ok;?></i></p>
	          <span><img src="<?=Url::getStaticUrl('imgs/doctor_icon07.png')?>"></span>
	        </h4>

	        <div class="doctor_con01_dep doctor_con02_dep">
	          <ul>
	          	<?php foreach ($ov as $ork=>$orv): ?>
	            <li <?php if ($om_tab == 0): ?>class="dq"<?php endif;?>><?=$ork;?><i></i></li>
	            <?php $om_tab++;?>
	            <?php endforeach; ?>
	          </ul>
	        </div>
	        <div class="doctor_con02_list_box">
	        	<?php foreach ($ov as $ork=>$orv): ?>
	          	<div class="doctor_con02_list" <?php if ($om_content != 0): ?>style="display: none;"<?php endif;?>>
		            <ul>
		            	<?php foreach ($orv as $ook => $oov): ?>
		              	<li>
			                <div>
			                  <h5><?= date('Y年m月d日', strtotime($oov['visit_time'])) ?>&nbsp;&nbsp;<?php $dayKey = date('w', strtotime($oov['visit_time'])); ?> <?= ArrayHelper::getValue($weekArr, $dayKey) ?>&nbsp;&nbsp;<?=ArrayHelper::getValue($dayArr, $oov['visit_nooncode'],'上午');?></h5>
			                  <p><?=ArrayHelper::getValue($visit_type_list, $oov['visit_type'],'普通门诊');?>&nbsp;&nbsp;原价<i>¥<?php if (ArrayHelper::getValue($oov, 'visit_cost_original')): ?><?php echo ($oov['visit_cost_original'] / 100) ?? 0;?><?php else: ?><?php echo ($oov['visit_cost'] / 100) ?? 0;?><?php endif; ?></i>&nbsp;&nbsp;优惠价<i>¥<?php echo ($oov['visit_cost'] / 100) ?? 0;?></i></p>
			                </div>
			                <?php if ($oov && $oov['status'] == 1): ?>
			                	<?php if ($oov['is_section'] == 1): ?>
			                	<?php $other_sectionArr =  CommonFunc::group_section(ArrayHelper::getValue($oov, 'sections'));?>
			                	<a href="javascript:;" data-scheduling_id="<?=$oov['scheduling_id'];?>" data-url="<?= htmlspecialchars_decode(Url::to(['register/choose-patient', 'doctor_id' => \Yii::$app->request->get('doctor_id', ''), 'scheduling_id' => ArrayHelper::getValue($oov,  'tp_scheduling_id'),'tp_platform'=>ArrayHelper::getValue($oov,'tp_platform')])) ?>" data-sections='<?=json_encode($other_sectionArr);?>' class="tc_click">挂号</a>
			                	<?php else: ?>
			                		<a href="<?= Url::to(['register/choose-patient', 'doctor_id' => \Yii::$app->request->get('doctor_id', ''), 'scheduling_id' => ArrayHelper::getValue($oov,  'tp_scheduling_id'),'tp_platform'=>ArrayHelper::getValue($oov,'tp_platform')]) ?>">挂号</a>
			                	<?php endif; ?>

			                <?php elseif ($oov && $oov['status'] == 2): ?>
			                	<a href="javascript:;" class="no">停诊</a>
			                <?php else: ?>
			                	<a href="javascript:;" class="no">约满</a>
			                <?php endif; ?>
			              </li>
		              	<?php endforeach; ?>

		            </ul>

		            <?php if (count($orv) > 2): ?>
                    <span>展开更多</span>
                    <?php endif;?>
	            	
	          	</div>
	          	<?php $om_content++;?>
	          	<?php endforeach; ?>
	        </div>
	        <?php if ($oov['tp_platform']): ?>
	        <!--<div class="doctor_con_tips">
	          <p>注：展示近<?/*=$show_day;*/?>天号源，该号源由<?/*=$platformArr[$oov['tp_platform']] ?? ''; */?>挂号平台提供。</p>
	        </div>-->
	        <?php endif; ?>
	    </div>
	    <?php $all_hos_num++;?>
	    <?php endforeach; ?>
	    <?php endif; ?>
	      
    </div>

   
    	<!--<div class="doctor_adv">
	      <dl>
	        <dt><img src="<?/*=Url::getStaticUrl('imgs/doctor_icon06.png')*/?>"></dt>
	        <?php /*if (ArrayHelper::getValue($doctor_info, 'miao_doctor_id') > 0): */?>
	        <dd class="doctor_adv_dd01">
	          <h4>向<i><?/*= ArrayHelper::getValue($doctor_info, 'doctor_realname') */?></i>专家咨询</h4>
	          <p>24小时内医生一对一精准服务</p>
	          <?php /*if (\Yii::$app->controller->getUserAgent() == 'mini'): */?>
	          	<a <?php /*echo \Yii::$app->controller->wechatAppletUrl("/pages/askanddoctor/doctor_info/doctor_info?doctor_id=".ArrayHelper::getValue($doctor_info, 'miao_doctor_id'));*/?>>立即咨询</a>
	          <?php /*else: */?>
	          	<a href="<?/*= ArrayHelper::getValue(\Yii::$app->params, 'domains.mobile') */?>doctor/<?/*= ArrayHelper::getValue($doctor_info, 'miao_doctor_id') */?>.html">立即咨询</a>
	          <?php /*endif; */?>
	        </dd>
	        <?php /*else: */?>
	    	<dd class="doctor_adv_dd01">
	          <h4>快速问诊</h4>
	          <p>18万+公立医院医生智能匹配</p>
	          <?php /*if (\Yii::$app->controller->getUserAgent() == 'mini'): */?>
	         	 <a <?php /*echo \Yii::$app->controller->wechatAppletUrl("/subPackageA/pages/moneyAsk/selectServe/selectServe");*/?> >立即咨询</a>
	          <?php /*else: */?>
	          	 <a href="<?/*= ArrayHelper::getValue(\Yii::$app->params, 'domains.ihs') */?>consult-order/service?from=mfzx">立即咨询</a>
	          <?php /*endif; */?>
	        </dd>
		    <?php /*endif; */?>
	      </dl>
	    </div>-->
   
    

    <div class="doctor_popup" style="display: none;">
      <div class="doctor_popup_bg"></div>
      <div class="doctor_popup_con">
        <h3>选择就诊时间<i></i></h3>
        <div class="doctor_popup_con01">
          <ul>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
          </ul>
          <span>以下时段已约满</span>
          <ol>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
            <li>08:30-09:00</li>
          </ol>
        </div>

        <div class="doctor_popup_con_but">
            <button onclick="clickNumSourceShence()">确认</button>
        </div>
      </div>
    </div>

</div>
<!-- <?php echo \mobile\widget\Menu::widget([]);?> -->

<input type="hidden" value="<?=ArrayHelper::getValue($doctor_info, 'doctor_id');?>" id="doctor_id">
<input type="hidden" value="<?= \Yii::$app->request->csrfToken;?>" id="_csrf-mobile">
<input type="hidden" value="<?= Url::to(['doctor/ajax-refresh']);?>" id="ajax_url">

<!--lodaing-->
<div class="loadings home_loadCon" style="display: none;">
    <div class="samePopul load_Popul">
        <div class="position_img"><img src="<?=Url::getStaticUrl("imgs/load2.gif")?>" width="100%"></div>
    </div>
</div>
<script src="https://www.nisiyacdn.com/static/js/ms-hybrid-1.1.6.js"></script>
<input id="time_interval" style="display: none" value="">
<input id="doctor_name" style="display: none" value="<?=ArrayHelper::getValue($doctor_info, 'doctor_realname');?>">
<input id="occupational_category" style="display: none" value="<?=ArrayHelper::getValue($doctor_info, 'doctor_title');?>">
<input id="amount" style="display: none" value="">
<input id="remaining_quantity" style="display: none" value="<?=$remaining_quantity > 0 ? $remaining_quantity-1 : 0 ?>">


<?php
//浏览医生详情埋点
if ($doctor_info['doctor_realname'] || $doctor_info['doctor_id']){
    $shence_data = [
        'current_page' => 'msapp_register_doctor_detail',
        'current_page_name' => '挂号医生详情页',
        'doctor_id' => $doctor_info['doctor_id'],
        'doctor_name' => $doctor_info['doctor_realname'],
        'page_source' => 'anyisheng',
        'page_source_name' => '按医生页',
    ];
    $shence_data = json_encode($shence_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if (in_array(\Yii::$app->controller->getUserAgent(),['patient'])) {
        \mobile\widget\ShenceStatisticsWidget::widget(['type' => 'PageView', 'data' => $shence_data]);
    }
}
?>
<script src="https://www.nisiyacdn.com/static/js/ms-hybrid-1.1.6.js"></script>
<?php
    // 医生详情分享参数
    $sensorsShareParams = [
        'current_page' => 'msapp_register_doctor',
        'current_page_name' => '挂号医生页',
        'page_title' => empty($doctor_info['doctor_realname']) ? '' : $doctor_info['doctor_realname'],
        'element_id' => empty($doctor_info['doctor_id']) ? '' : $doctor_info['doctor_id'],
        'element_name' => '医生详情',
        'element_type' => '医生'
    ];
echo \mobile\widget\ShenceStatisticsWidget::widget(['type' => '','data'=>[]]);
?>
<input style="display: none" id="shenceplatform_type" value="<?=\Yii::$app->controller->getUserAgent()?>">
<script type="text/javascript">
    function appShare() {
        //设置app 分享
        if (window.MSHybridJS.msBrowserEnv == 'msPatientApp') {
            window.MSHybridJS.onEnv('msPatientApp', function () {
                MSHybridJS.updateAppMessageShareData({
                    desc: '<?= $shareData['desc'] ?>', // 分享描述
                    link: '<?= $shareData['link'] ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: '<?= $shareData['imgUrl'] ?>', // 分享图标
                    title: '<?= $shareData['title'] ?>', // 分享标题
                    sensors_params: '<?= json_encode($sensorsShareParams, JSON_UNESCAPED_UNICODE); ?>', // 神策埋点数据
                    success: function () {
                        // 设置成功
                        console.log('设置成功');
                    }
                })
            })
        }
    }

    //app分享
    appShare();

    /**
     * 获取挂号当天日期和挂号费
     * @param date
     * @param price
     */
    function shenceOtherDate(date,price) {
        $('#time_interval').val(date);
        $('#amount').val(price);
    }

    //选择号源买点
    function clickNumSourceShence() {
        //就诊时间 08:00
        var shenceTime = $('.doctor_popup>.doctor_popup_con>.doctor_popup_con01>ul>.dq').text();
        if (!shenceTime){
            return false;
        }
        //接诊日期2022-01-01
        var time_interval = $('#time_interval').val();
        var doctor_name = $('#doctor_name').val();
        var occupational_category = $('#occupational_category').val();
        var amount = $('#amount').val();
        var remaining_quantity = $('#remaining_quantity').val();

        var num_source_data = {
            current_page : 'msapp_register_surplus',
            current_page_name : '挂号号源页',
            remaining_quantity: remaining_quantity,
            time_interval: time_interval,
            doctor_name: doctor_name,
            occupational_category: occupational_category,
            amount: amount,
            see_a_doctor_time: shenceTime
        }
        if (time_interval && $("#shenceplatform_type").val() == 'patient') {
            sensors.track('SurplusClick', num_source_data);
        }
    }
</script>
