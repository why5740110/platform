<?php

use common\libs\CommonFunc;
use \common\helpers\Url;
use \yii\helpers\ArrayHelper;
use common\libs\HashUrl;
use yii\helpers\Html;

$this->title = (isset($doctor_info['doctor_realname']) && !empty($doctor_info['doctor_realname'])) ? $doctor_info['doctor_realname'] . '医生' : '';
$this->registerCssFile(Url::getStaticUrl("css/doctor.css"));
$this->registerJsFile(Url::getStaticUrl("js/doctor.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/doctor_home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo("pages/doctor_home/index.js"),['depends'=>'mobile\assets\AppAsset']);

$platformArr = CommonFunc::getTpPlatformNameList();
$dayArr = CommonFunc::$visit_nooncode_type;
$visit_type_list = CommonFunc::$visit_type;
$show_day = CommonFunc::SHOW_DAY;


$weekArr = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
$noonArr = [1 => '上午', 2 => '下午', 3 => '晚上', 4 => '其他'];
$kjTxt = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七',8=>'八',9=>'九',10=>'十'];
$visitArr = [1=>'普通',2=>'专家',3=>'专科',4=>'特需',5=>'夜间',6=>'会诊',7=>'老院',8=>'其他'];

$all_hos_num = 1;

?>
<div class="doctor_home">
    <?php $remaining_quantity = 0;?>
    <!-- 医生介绍 -->
    <div class="dh_top">
        <div class="dh_top_img">
            <img
                    src="<?=ArrayHelper::getValue($doctor_info, 'doctor_avatar')?>"
                    onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';"
                    alt="<?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_realname'))?>"
            />
        </div>
        <div class="doc_tags" style="hight:5px;">
            <?php if (isset($doctor_info['doctor_tags']) && !empty($doctor_info['doctor_tags'])): ?>
                <?php if (isset($doctor_info['doctor_tags'][0]) && !empty($doctor_info['doctor_tags'][0])): ?><span class="tags t_style01 t_short"><?=$doctor_info['doctor_tags'][0]?></span><?php endif; ?>
                <?php if (isset($doctor_info['doctor_tags'][1]) && !empty($doctor_info['doctor_tags'][1])): ?><span class="tags t_style02"><?=$doctor_info['doctor_tags'][1]?></span><?php endif; ?>
            <?php endif; ?>
        </div>
        <h4><?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_realname'));?><span><?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_title'));?></span><span><?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_second_department_name'));?></span></h4>
        <p class="dt_hospital"><?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_hospital_data.name'));?></p>
        <div class="dt_merit">
            <img src="<?=Url::getStaticUrlTwo('pages/doctor_home/img/doctor_home_icon01.png')?>" />
            <p class="introduce">
                <?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_good_at'));?><a><i></i>查看简介</a>
            </p>
        </div>
        <ul>
            <li><i></i>官方号源</li>
            <li><i></i>医生本人</li>
            <li><i></i>平台认证</li>
            <li><i></i>诊前退款</li>
        </ul>
    </div>
    <?php if (isset($guahao) && $guahao): ?>
        <?php $hj = 1;?>
        <?php foreach ($guahao as $gh=>$ghv): ?>
            <div class="dh_con">
                <div class="dh_con_title">
                    <div class="dct_left"><span>出诊地<?= $kjTxt[$hj];?></span></div>
                    <div class="dct_con">
                        <a href="<?= rtrim(\Yii::$app->params['domains']['mobile'], '/').'/hospital/hospital_'.HashUrl::getIdEncode($hos_infos[$gh]['hospital_id']).'.html'; ?>">
                        <h4><?= $gh;?><i></i></h4>
                        </a>
                        <div class="doc_tags">
                            <span class="tags t_short"><?php if (isset($hos_infos[$gh]['level'])): ?><?= $hos_infos[$gh]['level'];?><?php endif; ?></span>
                            <span class="tags"><?php if (isset($hos_infos[$gh]['kind'])): ?><?= $hos_infos[$gh]['kind'];?><?php endif; ?></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($ghv) && $ghv): ?>
                <div class="dc_scheduling">
                    <ul>
                        <?php $kj = 1;?>
                        <?php foreach ($ghv as $gk=>$kv): ?>
                            <li <?php if ($kj == 1): ?>class="xz"<?php endif; ?>><?=$gk;?><span></span></li>
                            <?php $kj++;?>
                        <?php endforeach; ?>
                    </ul>
                    <div class="ds_con">
                        <div class="ds_con_left">近四周</div>
                        <div class="ds_con_right">
                            <?php $dj = 1;$fmj=[];?>
                            <?php foreach ($ghv as $gk=>$kv): ?>
                                <?php $mj = 0;?>
                                <div class="ds_con_right01" <?php if ($dj > 1): ?>style="display: none"<?php endif; ?>>
                                <?php foreach ($kv as $dk=>$dv): ?>
                                        <?php foreach ($dv as $wk=>$wv): ?>
                                            <div class="ds_con_right_list">
                                                <div class="dcrl_left">
                                                    <h4>
                                                        <?= $dk;?>&nbsp;&nbsp;<?php $dayKey = date('w',strtotime($dk));?><?=ArrayHelper::getValue($weekArr,$dayKey)?>&nbsp;&nbsp;<?= $noonArr[$wv['visit_nooncode']];?>
                                                        <!--<span>明天</span>-->
                                                    </h4>
                                                    <p><?= $visitArr[$wv['visit_type']];?>：<span><?=ArrayHelper::getValue($wv, "visit_cost")/100;?>元</span></p>
                                                </div>

                                                <?php if (ArrayHelper::getValue($wv,'status') == 1): ?>
                                                    <div class="dcrl_but <?php if (ArrayHelper::getValue($wv,'is_section') == 1): ?> tc_click<?php else: ?> do_guahao <?php endif; ?>"
                                                         onclick="shenceOtherDate('<?php echo $dk;?>','<?=ArrayHelper::getValue($wv,"visit_cost")/100;?>')"
                                                        <?php if (ArrayHelper::getValue($wv,'is_section') == 1): ?>
                                                            <?php $remaining_quantity += ArrayHelper::getValue($wv,'schedule_available_count',0);?>
                                                            <?php $other_sectionArr = CommonFunc::group_section(ArrayHelper::getValue($wv,'sections'));?>  data-sections='<?=json_encode($other_sectionArr);?>' <?php endif; ?>
                                                         data-url="<?= htmlspecialchars_decode(Url::to(['register/choose-patient', 'doctor_id' => HashUrl::getIdEncode(ArrayHelper::getValue($wv,'doctor_id')), 'scheduling_id' => ArrayHelper::getValue($wv,'tp_scheduling_id'),'tp_platform'=>ArrayHelper::getValue($wv,'tp_platform')])) ?>"
                                                    >
                                                        <?php
                                                        if ((ArrayHelper::getValue($wv, 'schedule_available_count', 0)) > 0) {
                                                            if (ArrayHelper::getValue($wv, 'tp_platform', 0) == 13) {//民营医院号源数量/3
                                                                echo '剩余' . ceil(ArrayHelper::getValue($wv, 'schedule_available_count', 0) / 3);
                                                            } else {
                                                                echo '剩余' . ArrayHelper::getValue($wv, 'schedule_available_count', 0);
                                                            }
                                                        } else {
                                                            echo '去挂号';
                                                        }
                                                        ?>
                                                    </div>
                                                <?php elseif (ArrayHelper::getValue($wv,'status') == 2): ?>
                                                <div class="dcrl_but ym" disabled="disabled">停诊</div>
                                                <?php else: ?>
                                                    <div class="dcrl_but ym" disabled="disabled">约满</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php $mj++;?>
                                        <?php endforeach; ?>
                                <?php endforeach; ?>
                                <input type="hidden" name="section_count" value="<?php echo $mj;?>">
                                </div>
                                <?php $dj++ ;?>
                                <?php $fmj[] = $mj;?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p class="ds_more open_data" <?php if (reset($fmj) <=2): ?>style="display: none"<?php endif; ?>><i>展开全部排班</i><span></span></p>
                    <p class="ds_more stop_data" style="display: none">
                        <i>收起全部排班</i><span></span>
                    </p>

                    <span class="ds_tips">*以上号源由王氏健康专业认证</span>
                </div>
                <?php else: ?>
                <div class="dc_scheduling_no">
                    暂无本院号源，可选择该医生其他医院出诊号源
                </div>
                <?php endif; ?>
            </div>
            <?php $hj++;?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($doctor_list['doctor_list'])): ?>
        <h5 class="hr_title_text">其他相同科室医生</h5>
        <div class="list_content">
            <?php foreach ($doctor_list['doctor_list'] as $key => $val):?>
                <div class="doc_item">
                    <a class="doc_item_wrap" href="<?= Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($val, 'doctor_id')])  ?>">
                        <div class="doc_photo">
                            <img src="<?= ArrayHelper::getValue($val, 'doctor_avatar','');?>"
                                 onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';"
                                 alt="<?=Html::encode(ArrayHelper::getValue($val, 'doctor_realname'))?>"
                            />
                        </div>
                        <div class="doc_content">
                            <div class="doc_info">
                                <div style="flex: 1">
                                    <span class="doc_name"><?= Html::encode(ArrayHelper::getValue($val, 'doctor_realname',''));?></span>
                                    <span class="doc_title"><?= Html::encode(ArrayHelper::getValue($val, 'doctor_title',''));?></span>
                                </div>
                                <span class="btn_little">去挂号</span>
                            </div>
                            <div class="doc_text text_wrap">
                                <?= ArrayHelper::getValue($val, 'doctor_second_department_name','');?> | <?= ArrayHelper::getValue($val, 'doctor_hospital','');?>
                            </div>
                            <div class="doc_tags">
                                <?php if (isset($val['doctor_visit_type']) && !empty($val['doctor_visit_type'])): ?>
                                    <?php if (isset($val['doctor_visit_type']) && !empty($val['doctor_visit_type'])): ?><span class="tags t_style02"><?=$val['doctor_visit_type']?></span><?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <p class="doc_descript text_over2">
                                擅长：<?= Html::encode(ArrayHelper::getValue($val, 'doctor_good_at',''));?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 医生简介弹层 -->
<div class="dh_pop_up doctor_introduce" style="display: none;">
    <div class="dh_pop_up_bg"></div>
    <div class="di_con">
        <h3>执业简介<span></span></h3>
        <div class="di_con_01">
            <h4><i></i>个人简介</h4>
            <p class="di_con_text">
                <?=CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_profile')));?>
            </p>
            <h4><i></i>专业擅长</h4>
            <p class="di_con_text">
                <?=CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_good_at')));?>
            </p>
        </div>
    </div>
</div>
<!-- 选择时间弹层 -->
<div class="dh_pop_up time_pop_up" style="display: none;">
    <div class="dh_pop_up_bg"></div>
    <div class="di_con">
        <h3>选择就诊时间<span></span></h3>
        <div class="time_con_list">
            <ul class="time_list">
                <!--            <li class="xz" time_id="0">-->
                <!--                <p>14:30-14:45</p>-->
                <!--                <span>剩余<i>12</i></span>-->
                <!--            </li>-->
                <!--            <li time_id="1">-->
                <!--                <p>14:30-14:45</p>-->
                <!--                <span>剩余<i>12</i></span>-->
                <!--            </li>-->
                <!--            <li time_id="2">-->
                <!--                <p>14:30-14:45</p>-->
                <!--                <span>剩余<i>12</i></span>-->
                <!--            </li>-->
                <!--            <li time_id="3">-->
                <!--                <p>14:30-14:45</p>-->
                <!--                <span>剩余<i>12</i></span>-->
                <!--            </li>-->

            </ul>
        </div>
        <div class="time_but">确定</div>
    </div>
</div>
<script src="https://www.nisiyacdn.com/static/js/ms-hybrid-1.1.6.js"></script>
<input id="time_interval" style="display: none" value="">
<input id="doctor_name" style="display: none" value="<?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_realname'));?>">
<input id="occupational_category" style="display: none" value="<?=Html::encode(ArrayHelper::getValue($doctor_info, 'doctor_title'));?>">
<input id="amount" style="display: none" value="">
<input id="remaining_quantity" style="display: none" value="<?=$remaining_quantity > 0 ? $remaining_quantity-1 : 0 ?>">


<?php
//浏览医生详情埋点
if ($doctor_info['doctor_realname'] || $doctor_info['doctor_id']){
    $shence_data = [
        'current_page' => 'msapp_register_doctor_detail',
        'current_page_name' => '挂号医生详情页',
        'doctor_id' => $doctor_info['doctor_id'],
        'doctor_name' => Html::encode($doctor_info['doctor_realname']),
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
<script src="https://branddoctor.nisiyacdn.com/mobile/js/jquery-1.11.1.min.js"></script>
<?php
    // 医生详情分享参数
    $sensorsShareParams = [
        'current_page' => 'msapp_register_doctor',
        'current_page_name' => '挂号医生页',
        'page_title' => empty($doctor_info['doctor_realname']) ? '' : Html::encode($doctor_info['doctor_realname']),
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
                    desc: '<?= Html::encode($shareData['desc']) ?>', // 分享描述
                    link: '<?= $shareData['link'] ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: '<?= $shareData['imgUrl'] ?>', // 分享图标
                    title: '<?= Html::encode($shareData['title']) ?>', // 分享标题
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
            //sensors.track('SurplusClick', num_source_data);
        }
    }

</script>

<script  type="text/javascript">
    // 时间弹窗
    //var that = this; //点击挂号：弹出选择时间段页

    $(document).delegate(".ds_con .tc_click", "click", function () {
        var liData = $(this).attr('data-sections');

        if (liData) {
            liData = JSON.parse(liData);
        }

        console.log(liData);
        var data_url = $(this).attr('data-url');

        if (!liData || liData.may.length < 1) {
            window.location.href = data_url;
        }

        var liHtml = "";
        var olHtml = "";

        for (var _i = 0; _i < liData.may.length; _i++) {
            var show_time = liData.may[_i].starttime;

            if (liData.may[_i].endtime) {
                show_time += '-' + liData.may[_i].endtime;
            }
            var showTitle = "";
            if (liData.may[_i].schedule_available_count > 0) {
                showTitle = "<span>剩余<i>" + liData.may[_i].schedule_available_count + "</i></span>";
            } else {
                showTitle = "";
            }

            var xz = "";
            if (_i == 0) {
                xz = "xz";
            }

            //liHtml += '<li tp_scheduling_id="' + liData.may[_i].tp_scheduling_id + '" tp_section_id="' + liData.may[_i].tp_section_id + '" data-url="' + data_url + '">' + show_time + '</li>';
            liHtml += '<li class="' + xz + '" tp_scheduling_id="' + liData.may[_i].tp_scheduling_id + '" tp_section_id="' + liData.may[_i].tp_section_id + '" data-url="' + data_url + '"><p>' + show_time + '</p> ' + showTitle + ' </li>';
        }

        console.log(liHtml);
        $(".time_list").html(liHtml);

        if (liData.maynot) {
            for (var q = 0; q < liData.maynot.length; q++) {
                var show_off_time = liData.maynot[q].starttime;

                if (liData.maynot[q].endtime) {
                    show_off_time += '-' + liData.maynot[q].endtime;
                }

                olHtml += "<li>" + show_off_time + "</li>";
            }

            $(".doctor_popup_con01 ol").html(olHtml);
        } else {
            $(".doctor_popup_con01 span").hide();
            $(".doctor_popup_con01 ol").hide();
        }

        $(".time_pop_up").show();
        // $("body,html").addClass("no_scrolling");
    });
    $(".doctor_home").delegate(".ds_con .do_guahao", "click", function () {
        var do_url = $(this).attr('data-url');
        window.location.href = do_url;
    });
    $(".doctor_popup_con h3 i").on("click", function () {
        //点击关闭弹窗
        $(".time_pop_up").hide();
        // $("body,html").removeClass("no_scrolling");
    });
    $(".di_con").delegate("ul li", "click", function () {
        $(this).addClass("xz").siblings().removeClass("xz");
    });
    $(".time_but").on("click", function () {
        var tp_section_id = $(".time_list").find('.xz').attr('tp_section_id');
        var tp_scheduling_id = $(".time_list").find('.xz').attr('tp_scheduling_id');

        if (!tp_scheduling_id) {
            return false;
        }

        var data_url = $(".time_list").find('.xz').attr('data-url');
        var tag_url = data_url + '&scheduling_id=' + tp_scheduling_id + '&tp_section_id=' + tp_section_id;
        $(".time_pop_up").hide();
        window.location.href = tag_url;
    });
</script>
