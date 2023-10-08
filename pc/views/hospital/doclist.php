<?php
/**
 * @file index.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/27
 */
use \common\helpers\Url;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use pc\widget\HospitalNavCrumbs;
use common\libs\CommonFunc;

$this->registerCssFile( Url::getStaticUrl("css/hos_doctor_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_doctor_detail.min.js") );

?>

<div class="ms_section_list w1200">
    <?=HospitalNavCrumbs::widget(['hospital_id' => $hospital_id,'hosp_data' => $data])?>
    <div class="title_h1" style="position:relative;">
        <h1 class="ms_hospital_title"><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h1>
        <span><?=ArrayHelper::getValue($data,'level')?></span>

    </div>
    <div class="ms_nav_traffic_guide ">

        <div class="ms_illness_classify border">
            <!--<div class="illness_list clearfix">
                <div class="inquire_doctor"> <a href="/hospital_67.html"><strong style="font-size: 16px;margin-right: 30px;" class="left">中国医学科学院北京协和医院预约挂号</strong></a>
                    <div class="inquire_input left">
                        <input type="hidden" id="hid" value="67">
                        <input type="hidden" id="sdid" value="0">
                        <input type="hidden" id="bdid" value="0">
                        <input type="text" name="txtDoctorV" id="txtDoctorV" value="" placeholder="请输入医生名称进行查找">
                        <input class="submit" type="button" id="btnGuahao" value="快速查询">
                    </div>
                </div>
            </div>-->

            <div class="illness_list clearfix  H_link">
                <div class="list_h2">一级科室：</div>
                <ul class="list_ul list_list clearfix">
                    <li class="<?php if(!\Yii::$app->request->get('frist_department_id')){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id])?>">全部</a></li>
                    <?php
                        foreach($sub as $v){
                    ?>
                        <li class="<?php if(\Yii::$app->request->get('frist_department_id')==$v['frist_department_id']){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>$v['frist_department_id']])?>"><?=Html::encode($v['frist_department_name'])?></a></li>
                    <?php } ?>
                </ul>
                <a class="unfold border H_open" href="javascript:void(0);" >展开 </a>
                <a class="unfold border H_close H_contraction" href="javascript:;" >收缩</a>
            </div>
            <?php
                if($second_sub){
            ?>
            <div class="illness_list clearfix  H_link">
                <div class="list_h2">二级科室：</div>
                <ul class="list_ul list_list clearfix">
                    <li class=" <?php if(!\Yii::$app->request->get('second_department_id')){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>\Yii::$app->request->get('frist_department_id')])?>">全部</a></li>
                    <?php
                        foreach($second_sub as $v){
                    ?>
                    <li class="<?php if(\Yii::$app->request->get('second_department_id')==$v['second_department_id']){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>\Yii::$app->request->get('frist_department_id'),'second_department_id'=>$v['second_department_id']])?>"><?=Html::encode($v['second_department_name'])?></a></li>
                    <?php } ?>
                </ul>
                <a class="unfold border H_open" href="javascript:void(0);" >展开 </a>
                <a class="unfold border H_close H_contraction" href="javascript:;" >收缩</a>
            </div>
            <?php } ?>
        </div>

    </div>
</div>



<!--擅长疾病医师列表-->
<div class="H-phone w1200">
    <div class="w1200 H_doc">
        <div class="H_doc_head">
            <ul>
                <li class="H_p"> 专家/医生</li>
                <li class="H_d"> 擅长疾病 </li>
                <li class="H_t"> 出诊时间 </li>
            </ul>
        </div>
        <div class="H_doc_con">
            <ul>
                <?php
                    $page = max(0,(\Yii::$app->request->get('page')-1));
                    if(isset($doc_list) && is_array($doc_list) ){
                        foreach($doc_list as $k=>$v){
                ?>
                <li>
                    <a href="<?=Url::to(['doctor/home','doctor_id'=>ArrayHelper::getValue($v,'doctor_id')])?>"> <img src="<?=ArrayHelper::getValue($v,'doctor_avatar')?>" class="fl H_p" />

                        <div class="H_ran">NO.<?=10*$page+$k+1?></div>
                    </a>
                    <div class="H_d_down H_d fl"> <a href="<?=Url::to(['doctor/home','doctor_id'=>ArrayHelper::getValue($v,'doctor_id')])?>">
                            <h3 class="fl"><?=Html::encode(ArrayHelper::getValue($v,'doctor_realname'))?></h3>
                        </a>
                        <Span class="fl doctor_titles">
                            <?php if(ArrayHelper::getValue($v,'doctor_title')){ ?>
                            ( <?=ArrayHelper::getValue($v,'doctor_title')?><?php if(ArrayHelper::getValue($v,'doctor_professional_title')){ ?>、<?=Html::encode(ArrayHelper::getValue($v,'doctor_professional_title'))?> <?php } ?>)
                            <?php } ?>
                                </Span> <small class="clr"><?=ArrayHelper::getValue($v,'doctor_second_department_name')?></small>
                        <?php if(CommonFunc::filterContent(ArrayHelper::getValue($v,'doctor_good_at'))){ ?>
                        <p class="H_bgt"> 擅长：<?=CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($v,'doctor_good_at')))?> </p>
                        <?php } ?>
                        <?php if(ArrayHelper::getValue($v,'doctor_disease_name')){ ?>
                        <dl>
                            <?php
                                $dis_arr = explode(',',$v['doctor_disease_name']);
                                foreach ($dis_arr as $k=>$dis_name){
                            ?>
                            <dd class="on"><?=$Html::encode(dis_name)?></dd>
                            <?php if($k>=3){break;}  } ?>

                            <div class="clr"> </div>
                        </dl>
                        <?php } ?>
<!--                        <p><span> 患友看病经验： </span>都挺好的看病的过程，讲话太快，也能理... <a href="#"> 详细></a> </p>-->
                    </div>
                    <div class="H_t_down H_t fl">
                        <table>
                            <tr>
                                <th>日期</th>
                                <th>周一</th>
                                <th>周二</th>
                                <th>周三</th>
                                <th>周四</th>
                                <th>周五</th>
                                <th>周六</th>
                                <th>周日</th>
                            </tr>
                            <tr>
                                <td><span>上午</span></td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td><span>下午</span></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td><span>夜间</span></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </div>

                    <div class="clr"> </div>
                </li>

                <?php } } ?>

            </ul>
        </div>
    </div>
    <div class="page_url">
        <?=\yii\widgets\LinkPager::widget([
            'pagination' => $pages,
            'firstPageLabel' => '首页',
            'nextPageLabel' => '下一页',
            'prevPageLabel' => '上一页',
            'lastPageLabel' => '最后一页',
            'maxButtonCount' => 6,
            'options' => ['class' => 'page'],
        ])?>
    </div>
</div>

<!-- 支付宝红包 -->
<div class="qrcode-populBox">
    <div class="erweima-main" id="advHospitalDptRight">
    </div>
</div>

<!-- 浮动窗口 -->
<Div class="H_posi">
    <ul>

        <li class="top"><a href="#top" target="_self"></a></li>
    </ul>
</Div>

