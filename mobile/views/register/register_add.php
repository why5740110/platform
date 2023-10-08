<?php

use \common\helpers\Url;
use yii\helpers\Html;
$this->registerCssFile(Url::getStaticUrl("css/jzr_info.css"));
$this->registerJsFile( Url::getStaticUrl("js/popup.js") );
$this->registerJsFile( Url::getStaticUrl("js/jzr_info.js") );
?>
<style>

    .zqxy_pop_all{ background:rgba(0,0,0,.5); width:100%; height:100%; position:fixed; left:0; top:0 ; right:0; bottom:0; z-index:99999;}
    .zqxy_pop_all .zqxy_pop_box{ width:90%; height:auto; position:fixed; left:50%; top:50%; -webkit-transform: translate(-50%,-50%);
        transform: translate(-50%,-50%); background:#fff; border-radius:10px; overflow:hidden;}
    .zqxy_pop_all .zqxy_pop_box h3{ text-align:center; font-size:16px; padding-top:15px;}
    .zqxy_pop_all .zqxy_pop_box .zqxy_con{ margin:20px 10px 10px 10px; font-size:13px; max-height:380px; overflow:scroll; box-sizing:border-box; text-align:justify;}
    .zqxy_pop_all .zqxy_pop_box .zqxy_con p{ padding-bottom:10px;}
    .zqxy_pop_all .zqxy_pop_box .btns_box>div{ width:50%; box-sizing:border-box; height:48px; line-height:48px; text-align:center; float:left;font-size:16px; border-top:1px solid #eee; cursor:pointer;}
    .zqxy_pop_all .zqxy_pop_box .btns_box .bty_btn{ color:#999;}
    .zqxy_pop_all .zqxy_pop_box .btns_box .ty_btn{ color:#0078fd; float:right;  border-left:1px solid #eee;}

</style>
<div class="jzr_info">
<!--<div class=jzr_info_tit>就诊人信息</div>-->
    <form id=formLogin action="/hospital/register/ajax-patient-info-up" method="post">
        <input id='jzr_choose_doctor_id' type='hidden' name='jzr_choose_doctor_id' value='<?php  echo $jzr_choose_doctor_id; ?>'>
        <input id='jzr_choose_scheduling_id' type='hidden' name='jzr_choose_scheduling_id' value='<?php  echo $jzr_choose_scheduling_id; ?>'>
        <input id='jzr_choose_section_id' type='hidden' name='jzr_choose_section_id' value='<?php  echo $jzr_choose_section_id; ?>'>
        <input id='jzr_choose_tp_platform' type='hidden' name='jzr_choose_tp_platform' value='<?php  echo $jzr_choose_tp_platform; ?>'>

        <div class="meetingLogin bgfff">
            <?php if (!empty($info_data['is_auth_card']) and $info_data['is_auth_card'] == 1){ ?>
            <div class="jzr_info_sm">
                <div class="jzr_info_sm_img">
                    <img src="<?=url::to('@static/imgs/jzr_icon03.png')?>" />
                </div>
            <?php } else{ ?>
                <div>
            <?php } ?>
                <div class=items><label>姓名</label>
                    <input type=text name=realname <?php if(!empty($info_data['is_auth_card']) and $info_data['is_auth_card'] == 1){ echo 'readonly'; }?> class=req req=请输入就诊人姓名 placeholder=请输入就诊人姓名 value="<?php if(!empty($info_data['realname'])){ echo Html::encode($info_data['realname']); }?>"></div>
                <div class=items><label>身份证号</label>
                    <input type=text id="id_card" name="id_card" <?php if(!empty($info_data['is_auth_card']) and $info_data['is_auth_card'] == 1){ echo 'readonly'; }?> class=req req=请输入证件号码 placeholder=请输入证件号码 value="<?php if(!empty($info_data['id_card'])){ echo $info_data['id_card']; }?>"></div>
                <div class=items><label>性别</label>
                    <div class=sex_box>
                        <span class="man <?php if((!empty($info_data['sex']) and $info_data['sex'] == 1)){ echo 'on'; }?>" data_sex="1"><i></i>男</span>
                        <span class="women <?php if(!empty($info_data['sex']) and $info_data['sex'] == 2){ echo 'on'; }?>" data_sex="2"><i></i>女</span></div>
                    <input type="hidden" name="sex" id="sex" value="<?php if(!empty($info_data['sex'])){ echo $info_data['sex']; }else{ echo 1;}?>">
                </div>
                <div class=items><label>出生日期</label>
                    <input type=text name="birth_time" readonly id=trigger_date placeholder=点击选择出生日期 value="<?php if(!empty($info_data['birth_time'])){ echo $info_data['birth_time']; }?>">
                </div>
            </div>
            <div class="bg_f4f4f4 h10"></div>
            <div class=items><label>手机号</label>
                <input type=text name=mobile class="req req-mobile" id=mobile req=请填写手机号 maxlength=11 placeholder=请填写手机号 value="<?php if(!empty($info_data['tel'])){ echo $info_data['tel']; }?>"></div>
            <!--<div class=items><label>城市</label>
                <div class="case_info taR">
                    <span id=other_address class="pr30 fs17" data_check="<?php /*if(!empty($info_data['province']) || !empty($info_data['city'])){ echo '2';}else{ echo 1;}*/?>"><?php /*if(!empty($info_data['province']) || !empty($info_data['city'])){ echo $info_data['province']."  ".$info_data['city']; }else{echo "点击选择省、市、区";}*/?></span>
                    <input id='other_address_province' class=req req=请选择省、市、区 type='hidden' name='other_address_province' value=<?php /*if(!empty($info_data['province'])){ echo $info_data['province'];}*/?>>
                    <input id='other_address_city' class=req req=请选择省、市、区 type='hidden' name='other_address_city' value=<?php /*if(!empty($info_data['city'])){ echo $info_data['city'];}*/?>>
                    <input id='other_address_district' type='hidden' name='other_address_district' value='0'>
                </div>
                <i class=icon style=z-index:0></i>
            </div>-->
            <!--<div class="items bdbn"><label>详细地址</label>
                <input type=text name=address placeholder=应公安机关要求，请填写现真实地址 value="<?php /*if(!empty($info_data['address'])){ echo Html::encode($info_data['address']); }*/?>"></div>-->
        </div>
        <?php /*if(!empty($info_data) && $info_data['id'] && $info_data['age'] < 18): */?><!--
        <div class="meetingLogin bgfff guarder_info">
        <?php /*else: */?>
        <div class="meetingLogin bgfff guarder_info" style="display:none">
        <?php /*endif; */?>
            <div class=jzr_info_tit>监护人信息</div>
            <div class=items><label>监护人姓名</label>
                <input type=text name="guarder_name" placeholder=请输入监护人姓名 value="<?php /*if(!empty($info_data['guarder_name'])){ echo $info_data['guarder_name']; }*/?>"></div>
            <div class=items><label>监护人身份证号</label>
                <input type=text id="guarder_card" name="guarder_card" placeholder=请输入监护人证件号码 value="<?php /*if(!empty($info_data['guarder_card'])){ echo $info_data['guarder_card']; }*/?>"></div>
            <div class=items><label>监护人手机号</label>
                <input type=text name="guarder_tel" class="req-mobile" id="guarder_tel" maxlength=11 placeholder=请填写监护人手机号 value="<?php /*if(!empty($info_data['guarder_tel'])){ echo $info_data['guarder_tel']; }*/?>"></div>
        </div>-->
        <div class="jzr_info_tips">
            <i>*</i
            >为保证顺利就诊，姓名、手机号、身份证等务必真实有效。个人信息提供您就诊时使用，请您放心填写。
        </div>
        <input type="hidden" value="<?php if(!empty($info_data['id'])){echo $info_data['id'];}?>" name="id">
        <input type="hidden" value="<?= \Yii::$app->request->csrfToken ?>" name="_csrf-mobile">
        <div class=regBcon><input id="" type=submit class=m_regBtn value=<?php if(empty($info_data['id'])){ echo "添加就诊人";}else{ if($info_data['is_auth_card'] != 1){ echo "去实名认证";}else{echo "保存";}}?>></div>
        <span class=met_toast_box style=display:none>请输入手机号</span>
    </form>
    <div class="tools_assembly" style="display: none;">
        <div class="tools_assembly_bg"></div>
        <div class="tools_assembly_con">
            <p>就诊人信息不完善，请重新选择！</p>
        </div>
    </div>
    <!--弹框文案提示框-->
    <div class="zqxy_pop_all" style="display: none;">
        <div class="zqxy_pop_box">
            <h3>知情协议</h3>
            <div class="zqxy_con">
                <p>我们可能会收集您或您亲友的<b>姓名、手机号、年龄、出生日期、关系标签、身份证号、所在地区、详细地址,</b>以创建就诊人/健康档案信息，便于您或您亲友更快捷地使用问诊、开方、门诊预约、挂号服务。如您或您亲友不提供这类信息，您将无法完成添加，您也将无法使用问诊、开方、门诊预约、挂号服务。</p>
            </div>
            <div class="btns_box">
                <div class="bty_btn">不同意</div>
                <div class="ty_btn">同意</div>
            </div>
        </div>
    </div>
</div>
<script src="<?=Url::getStaticUrl("js/jquery-1.11.1.min.js");?>"></script>
<script src="<?=Url::getStaticUrl("js/mobiscroll.custom.min.js");?>"></script>
<script src="<?=Url::getStaticUrl("js/mobileSelect.min.js");?>"></script>


<script>
    $(function () {
        /*
        //监护人信息
        $("body").on('blur','input[name=id_card]',function(){
            var id_card = $(this).val();
            calculate(id_card);
        });

        function calculate(id_card) {
            var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
            if (reg.test(id_card) !== false && id_card.length !== 15) {
                //获取性别
                var sex = (parseInt(id_card.substr(16, 1)) % 2 == 1) ? 1 : 2;console.log(sex);
                $("#sex").attr("value",sex);
                var index = (sex == 1) ? 0 : 1;
                $('.sex_box span').eq(index).addClass('on').siblings().removeClass('on');

                //获取出生日期
                var birth = id_card.substring(6, 10) + "-" + id_card.substring(10, 12) + "-" + id_card.substring(12, 14);
                $("input[name=birth_time]").attr("value",birth);

                //获取年龄
                var myDate = new Date();
                var month = myDate.getMonth() + 1;
                var day = myDate.getDate();
                var age = myDate.getFullYear() - id_card.substring(6, 10) - 1;
                if (id_card.substring(10, 12) < month || id_card.substring(10, 12) == month && id_card.substring(12, 14) <= day) {
                    age++;
                }
                if (age < 18) {
                    $(".guarder_info").show();
                } else {
                    $(".guarder_info").hide();
                }
            }
        }*/

        //先弹框提示
        var md5_useId = "<?php echo $md5_useId;?>";
        var cookie_key = md5_useId + '_tools_title';
        var tools_title = getCookie(cookie_key);
        if (tools_title <= 0) {
            $('.zqxy_pop_all').show();
        }

        $('.ty_btn').click(function () {
            setCookie(cookie_key, 1, 30);
            $('.zqxy_pop_all').hide();
        });

        $('.bty_btn').click(function () {
            $('.zqxy_pop_all').hide();
            window.history.go(-1);
        });

        var baseRegion = <?=$region?>;
        //新增地址选择
        var item = "other_address",
            itemVal = "other_address",
            displayData = false;
        //$("#" + item).after("<input id='" + item + "_province' type='hidden' name='" + item + "_province' value='0'/>" + "<input id='" + item + "_city' type='hidden' name='" + item + "_city' value='0'/>" + "<input id='" + item + "_district' type='hidden' name='" + item + "_district' value='0'/>");
        /*var other_address = new MobileSelect({
            trigger: '#' + itemVal,
            title: '选择地址',
            keyMap: {
                id: 'id',
                value: 'name',
                childs: 'city_arr'
            },
            wheels: [{
                data: baseRegion
            }],
            triggerDisplayData: displayData,
            callback: function (indexArr, data) {
                console.log(data);
                if (displayData == false) {
                    $("#" + item).addClass("col222")
                    var data1 =""
                    if(data.length>1){
                        data1=data[1]['name']
                    }
                    $("#" + item).html(data[0]['name'] + " " + data1)
                } else {
                    item = $(this.trigger).prop('id')
                }
                callBackData(data)
            }
        });*/

        function callBackData(data) {
            if (typeof data[0] != 'undefined') {
                $("#" + item + "_province").val(data[0]['name']);
            }

            if (typeof data[1] != 'undefined') {
                $("#" + item + "_city").val(data[1]['name']);
            }
            $("#" + item).attr('data_check', 2);
        }

        function setCookie(cname,cvalue,exdays){
            var d = new Date();
            d.setTime(d.getTime()+(exdays*24*60*60*1000));
            var expires = "expires="+d.toGMTString();
            document.cookie = cname+"="+cvalue+"; "+expires;
        }

        function getCookie(name)
        {
            var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
            if(arr=document.cookie.match(reg))
                return unescape(arr[2]);
            else
                return null;
        }
    })
</script>