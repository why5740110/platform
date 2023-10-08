<?php
/**
 * PC首页
 * @file index.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-25
 */

use \common\helpers\Url;
use common\libs\HashUrl;
$this->registerCssFile( Url::getStaticUrl("css/index.min.css") );
$this->registerJsFile(Url::getStaticUrl("js/index.min.js"));

?>

<div class="ms_index_banner w1200 ovH">
    <div class="swiper-container">
        <div class="swiper-wrapper" id="indexAdvImg">
            <?php if (!empty($lunbo)): ?>
                <?php foreach ($lunbo as $value) : ?>
                    <div class="swiper-slide">
                        <li>
                            <a onclick="" href="<?php echo $value['link']; ?>">
                                <img src="<?php echo $value['imagelink']; ?>" width="1200">
                            </a>
                        </li>
                        <div></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
<div class="w1200 ovH">
    <div class="w900 fl">
        <!--医院排行榜-->
        <div class="msys_rank">
            <h5 class="fl indexTlt">医院排行榜</h5>
            <p class="fl">本榜单共录有<span class="msysSum"> 15,826 </span>家医院，由 <span>300w+</span> 位患者投票选出</p>
            <ul class="fr">
                <li class="on tab"><a href="<?= Url::to(["hospitallist/index"]) ?>" target="_self">全国</a>
                </li>
                <li class=" tab"><a href="<?= Url::to(["hospitallist/index",'region'=>'beijing','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">北京</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'shanghai','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">上海</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'guangzhou','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">广州</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'hangzhou','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">杭州</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'tianjin','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">天津</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'nanjing1','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">南京</a></li>
                <li class=" tab"><a href="<?php echo Url::to(["hospitallist/index",'region'=>'jinan1','sanjia'=>0,'page'=>1]) ?>"
                                    target="_blank">济南</a></li>
                <li><a target="_blank" href="<?= Url::to(["hospitallist/index"]) ?>">更多></a></li>
            </ul>
            <div class="clr msys_tab_con block">
                <ul class="msys_cla ovH">
                    <a class="on" target="_blank" href="<?=Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>1,'page'=>1])?>" title="内科">内科</a>
                    <a class="" target="_blank" href="<?=Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>53,'page'=>1])?>" title="外科">外科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>3,'page'=>1])?>" title="妇产科">妇产科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>4,'page'=>1])?>" title="男科">男科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>5,'page'=>1])?>" title="生殖健康">生殖健康</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>58,'page'=>1])?>" title="眼科">眼科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>6,'page'=>1])?>" title="儿科">儿科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>7,'page'=>1])?>" title="五官科">五官科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>8,'page'=>1])?>" title="肿瘤科">肿瘤科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>9,'page'=>1])?>" title="皮肤性病科">皮肤性病科</a>
                    <a class="" target="_blank" href="<?= Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>10,'page'=>1])?>" title="精神心理科">精神心理科</a>
                </ul>

                <?php if (!empty($hos_list)) : ?>
                    <?php $j = 0;
                    $block = "style='display:block'" ?>
                    <?php foreach ($hos_list as $key => $value): ?>
                        <div class="msys_nei_tab " <?php echo $j++; ?> <?php echo ($j == 1) ? $block : ''; ?>>
                            <ul class="msys_th ovH">
                                <?php foreach ($value as $k => $item): ?>
                                    <?php if ($k < 3) : ?>
                                    <?php $imgs=[
                                            0=>'pc/hospital/static/images/feeb609498fc958a36254982c7b0f0dd.jpg',
                                            1=>'pc/hospital/static/images/94f6500d8131ffaf23c4b2025b27812d.jpg',
                                            2=>'pc/hospital/static/images/069c864e70bf683b6b51dba8ba27ba60.jpg',
                                        ]?>
                                        <li>
                                            <a target="_blank" href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($item['hospital_id'])]) ?>">
                                                <img style="width:286px;height:214px"
                                                     src="<?php echo $item['hospital_photo'] ?>">
                                                <img class="msys_no"
                                                     src="<?php echo \Yii::$app->params['domains']['cdn'].$imgs[$k] ?>">
                                                <h3><?php echo $item['hospital_name'] ?></h3>

                                                <div class="ispublic_box"><span
                                                            class="ispublic"><?php echo $item['hospital_level'] ?></span>
                                                </div>
                                                <div class="clr"></div>
                                                <p>疗效满意度：<span class="star star2"></span><span class="starwz">82%</span>
                                                </p>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                            <ul class="msys_more ovH">
                                <?php foreach ($value as $k => $item): ?>
                                    <?php if ($k >= 3) : ?>
                                        <li>
                                            <a target="_blank" href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($item['hospital_id'])]) ?>" title="<?php echo $item['hospital_name'] ?>"><?php echo $item['hospital_name'] ?></a>
                                            <span class="ispublic"><?php echo $item['hospital_level'] ?></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!--专家在线-->
        <div class="msys_expert ovH">
            <div class="ms_line ms_online_expert">
                <h5 class="fl indexTlt">专家在线</h5>
                <p class="fl none">正规医院的<span> 218,628</span>位专家预约挂号</p>
                <a target="_blank" href="<?php echo Url::to(['doctorlist/index']); ?>" class="fr hy_more">更多</a>
                <p class="msys_online_expert_top"></p>
                <ul class="keshiList">
                    <li class="on ms_line_tab"><a href="javascript:;" target="_self">内科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">外科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">妇产科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">男科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">生殖健康</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">眼科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">儿科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">五官科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">肿瘤科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">皮肤性病科</a></li>
                    <li class=" ms_line_tab"><a href="javascript:;" target="_self">精神心理科</a></li>
                </ul>

                <?php if (!empty($doc_list)) : ?>
                    <?php $i = 0; ?>
                    <?php foreach ($doc_list as $key => $value): ?>

                        <div class="clr msys_line_active msys_line_active_<?php echo $i++; ?> <?php echo ($i == 1) ? 'block' : ''; ?>">
                            <div class="msys_line_lbtn msys_line_btn_all" onclick="msys_line_lbtn(1)"><</div>
                            <div class="msys_line_lbtn_hidden msys_line_btn_all"></div>
                            <ul class="msys_exp_line">
                                <?php foreach ($value as $item): ?>
                                    <li>
                                        <img class="on_line"
                                             src="<?php echo \Yii::$app->params['domains']['cdn'] ?>pc/hospital/static/images/6cd3ef95d6c9fe6ee654a967121b0c70.png"
                                             alt="专家在线">
                                        <div class="clr"></div>
                                        <div class="msys_bri">
                                            <a target="_blank"
                                               href="<?= Url::to(['/doctor/home','doctor_id'=>$item['doctor_id']]) ?>">
                                                <img src="<?php echo $item['doctor_avatar'] ?>"
                                                     alt="<?php echo $item['doctor_realname'] ?>" class="fl"></a>
                                            <div class="msys_bri_right fr">
                                                <h4 class="fl">
                                                    <a target="_blank" href="<?= Url::to(['/doctor/home','doctor_id'=>$item['doctor_id']]) ?>"><?php echo $item['doctor_realname'] ?></a>
                                                </h4>
                                                <span class="fl doctor_titles"><?php echo $item['doctor_title'] ?></span>
                                                <p>患者好评：<span class="star star2"></span><span class="starwz"></span>
                                                </p>
                                                <small><?php echo $item['doctor_hospital'] ?><?php echo $item['doctor_second_department_name'] ?></small>
                                            </div>
                                            <div class="clr"></div>
                                        </div>
                                        <p class="msys_fri"
                                           style="height:40px;overflow: hidden;-webkit-line-clamp: 3;display: -webkit-box;-webkit-box-orient: vertical;">
                                            擅长：<?php echo $item['doctor_good_at'] ?>...</p>
                                        <div class="border ovH">
                                            <a class="on" rel="nofollow">在线咨询</a>
                                            <a class="on" rel="nofollow">电话咨询</a>

                                            <a class="on" rel="nofollow">预约挂号</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="msys_line_rbtn msys_line_btn_all" onclick="msys_line_rbtn(1)">></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
        <!--患友咨询-->
        <div class="msys_expert">
            <div class="ms_line">
                <h5 class="fl indexTlt">专家问答</h5>
                <p class="fl">成功帮助患者提供<span>652,022</span>次在线咨询</p>
                <a href="<?php echo \Yii::$app->params['domains']['pc'] ?>question/list.html" class="fr hy_more">更多</a>
                <div class="clr">
                    <ul class="msys_pat ovH">
                        <?php if (!empty($ques_list)) : ?>
                            <?php foreach ($ques_list as $value): ?>
                                <li>
                                    <a href="<?php echo \Yii::$app->params['domains']['pc'] . HashUrl::getQuestionDetailUrl($value['post_id']); ?>"
                                       target="_blank" title="<?php echo $value['title'] ?>">
                                        <?php echo $value['title'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!--按疾病找医生-->
        <div class="msys_expert">
            <div class="ms_line ">
                <h5 class="fl indexTlt">按疾病找医生</h5>
                <a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','page'=>1]); ?>" class="fr hy_more">更多</a>
                <div class="clr">

                    <?php /*if (!empty($keshi)) : */ ?><!--
                        <?php /*foreach ($keshi as $key=>$value): */ ?>
                            <ul class="msys_cdis fl">
                                <h6><?php /*echo $key; */ ?></h6>
                                <?php /*foreach ($value['second_arr'] as $item): */ ?>
                                <li><a target="_blank" href="<?php /*echo Url::to(['/hospital/departments/0_0_'. $item['department_id'].'_1.html']); */ ?>"><?php /*echo $item['department_name']; */ ?></a></li>
                                <?php /*endforeach;*/ ?>
                                <div class="clr"></div>
                            </ul>
                        <?php /*endforeach;*/ ?>
                    --><?php /*endif;*/ ?>

                    <ul class="msys_cdis fl">
                        <h6>口腔科</h6>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'yasuiyan','page'=>1]); ?>">牙髓炎</a>
                        </li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'guanzhouyan','page'=>1]); ?>">冠周炎</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'kouqiangai','page'=>1]); ?>">口腔癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'kouyan','page'=>1]); ?>">口炎</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'yazhouyan','page'=>1]); ?>">牙周炎</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'kouchuang','page'=>1]); ?>">口疮</a></li>
                        <div class="clr"></div>
                    </ul>
                    <ul class="msys_cdis fl">
                        <h6>妇产科</h6>
                        <li><a target="_blank"
                               href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'buyunzheng','page'=>1]); ?>">不孕症</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'penqiangyan','page'=>1]); ?>">盆腔炎</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'zigongjiliu','page'=>1]); ?>">子宫肌瘤</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'luanchaonangzhong','page'=>1]); ?>">卵巢囊肿</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'yuejingbutiao','page'=>1]); ?>">月经不调</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'shuluanguanbutong','page'=>1]); ?>">输卵管不通</a></li>
                        <div class="clr"></div>
                    </ul>
                    <ul class="msys_cdis fl">
                        <h6>肿瘤科</h6>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'feiai','page'=>1]); ?>">肺癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'ganai','page'=>1]); ?>">肝癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'weiai','page'=>1]); ?>">胃癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'zhichangai','page'=>1]); ?>">直肠癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'ruxianai','page'=>1]); ?>">乳腺癌</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'linbaai','page'=>1]); ?>">淋巴癌</a></li>

                        <div class="clr"></div>
                    </ul>
                    <ul class="msys_cdis fl">
                        <h6>外科</h6>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'qixiong','page'=>1]); ?>">气胸</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'danjieshi','page'=>1]); ?>">胆结石</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'shenjieshi','page'=>1]); ?>">肾结石</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'zhichuang','page'=>1]); ?>">痔疮</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'ruxianzengsheng','page'=>1]); ?>">乳腺增生</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'jingzhuibing','page'=>1]); ?>">颈椎病</a></li>

                        <div class="clr"></div>
                    </ul>
                    <ul class="msys_cdis fl">
                        <h6>内科</h6>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'jiakang','page'=>1]); ?>">甲亢</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'weikuiyang','page'=>1]); ?>">胃溃疡</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'dianxian','page'=>1]); ?>">癫痫</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'yigan','page'=>1]); ?>">乙肝</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'zhongzhengjiwuli','page'=>1]); ?>">重症肌无力</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'zhiqiguanxiaochuan','page'=>1]); ?>">支气管哮喘</a></li>
                        <div class="clr"></div>
                    </ul>
                    <ul class="msys_cdis fl">
                        <h6>儿科</h6>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xiaoerdianxian','page'=>1]); ?>">小儿癫痫</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xiaoerxiaochuan','page'=>1]); ?>">小儿哮喘</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xiaoernaotan','page'=>1]); ?>">小儿脑瘫</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xiaoerduodongzheng','page'=>1]); ?>">小儿多动症</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xianyangtifeida','page'=>1]); ?>">腺样体肥大</a></li>
                        <li><a target="_blank" href="<?php echo Url::to(['doctorlist/diseases','region'=>'0','sanjia'=>'0','diseases'=>'0','dspinyin'=>'xiaoerfeiyan','page'=>1]); ?>">小儿肺炎</a></li>
                        <div class="clr"></div>
                    </ul>

                </div>
            </div>

        </div>
    </div>
    <!--右侧-->
    <div class="ms_right_column fr">
        <!--名医推荐-->
        <?php echo \pc\widget\DoctorlistRightWidget::widget([])?>
    </div>

</div>
<div class="w1200">
    <div class="mt20">
        <a target="_blank" href="https://www.nisiya.net/about.html">
            <img src="https://www.nisiyacdn.com/net/pc/images/add_net_pic.jpg">
        </a>
    </div>
</div>
<div class="friendly_link_main">
    <div class="w1200">
        <div class="friendly_link ">
            <div class="fl l_title">友情链接</div>
            <div class="fl l_others">
                <a target="_blank" href="https://news.120ask.com">健康新闻</a>
                <a target="_blank" href="https://www.iqiyi.com/health/">爱奇艺健康</a>
                <a target="_blank" href="http://www.familydoctor.com.cn/">家庭医生</a>
                <a target="_blank" href="http://www.vodjk.com/">健康一线</a>
                <a target="_blank" href="http://www.mingyihui.net">名医汇</a>
            </div>
        </div>
    </div>
</div>

<?php

$this->registerJsFile(Url::getStaticUrl("js/index.min.js"));

?>



