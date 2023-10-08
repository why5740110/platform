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

$this->registerCssFile( Url::getStaticUrl("css/hos_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_detail.min.js") );

?>



<div class="ms_section_list w1200">
    <?=HospitalNavCrumbs::widget(['hospital_id' => $hospital_id,'hosp_data' => $data])?>
    <div class="title_h1" style="position:relative;">
        <h1 class="ms_hospital_title"><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h1>
        <span><?=ArrayHelper::getValue($data,'level')?></span>
    </div>
    <div class="ms_section_list_left left">

        <div class="list_title3"> <a href="<?=Url::to(['hospital/index','hospital_id'=>$hospital_id])?>" target="_self"><strong class="left"  style="font-size: 16px;"><?=Html::encode(ArrayHelper::getValue($data,'name'))?></strong></a>
            <!--<div class="inquire right">
                <form id="frmDepartment" action="#" method="post">
                    <input type="hidden" value="" name="token">
                    <input type="hidden" name="hid" id="hid" value="67" />
                    <input type="text" name="keyword" id="txtDepartmentKey" value="" placeholder="科室查询" />
                    <input id="btnDepartmentS" class="submit" type="button" value="快速查询" />
                </form>
            </div>-->
        </div>
        <?php
        if(is_array($sub)){
        foreach($sub as $frist){
        ?>
            <div class="classify_list clearfix">
                <h3 class="left" id="a7"><?=Html::encode($frist['frist_department_name'])?></h3>
                <ul class="list_ul right">
                    <?php
                    if(isset($frist['second_arr']) && is_array($frist['second_arr']) ){
                    foreach($frist['second_arr'] as $second){
                    ?>

                        <li> <a class="color_333" href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>$frist['frist_department_id'],'second_department_id'=>$second['second_department_id']])?>"><?=Html::encode($second['second_department_name'])?></a> <span class="color_999">(<?=$second['doctors_num']?>人)</span></li>

                    <?php }} ?>

                    </ul>
            </div>
        <?php } } ?>
    </div>
    <!--科室右边内容-->
    <div  class="ms_section_list_right right" style="display: none">
        <div id="advHospitalDepartmentRight" style="margin-bottom: 20px;width: 240px;">
        </div>
        <!--最新出诊-->  <!--最新出诊结束-->
        <!--医师资料-->
        <div class="doctor border "> <a href=""> <img class="on_line" src="" alt="" />
                <div class="doctor_data clearfix"> <img class="doctor_logo left" src="" alt="" />
                    <div class="doctor_name right">
                        <h3>黄汉源<span class="doctor_titles">( 主任医师、教授 )</span></h3>
                        <p>中国医学科学院北京协和医院&nbsp;乳腺外科门诊 <br/>
                            获得<span>50</span>好评</p>
                    </div>
                </div>
            </a>
            <div class="doctor__introduction">
                <p>擅长：乳腺外科多种乳腺疾病,巨乳症,乳房肥大,大乳房,乳腺囊肿,乳...</p>
            </div>
            <div class="registration">
                <a href="" target="_self"  class="registration_a left on"  onclick=''>预约挂号</a>
                <a href="" target="_self"  class="consulting right" >在线咨询</a>
            </div>
        </div>
        <!--医师资料结束-->
        <div id="adv_pr" class="msys-advertisement-pr"></div>



    </div>
    <div class="clr"></div>
</div>


<div class="ms_posi">
    <ul>
        <li class="top"><a href="#top" target="_self"></a></li>
    </ul>
</Div>
