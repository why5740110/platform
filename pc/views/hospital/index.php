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
use pc\widget\OrtherHospital;

$this->registerCssFile( Url::getStaticUrl("css/hos_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_detail.min.js") );

?>
<div class="consultContent">

    <!-- 导航 -->
    <div class="newdoctorDetail" style="margin-bottom: 30px;">

        <?=HospitalNavCrumbs::widget(['hospital_id' => $hospital_id,'hosp_data' => $data])?>

        <!-- 医院详情 -->
        <div class="newhospitalContent">
            <!-- 医院详情 -->
            <div class="newhospitalContent_l">
                <div class="hospitlaImg"><img src="<?=ArrayHelper::getValue($data,'photo')?>" alt=""></div>
                <div class="hospitalText">
                    <div class="newhospitalName">
                        <h1><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h1>
                        <div class="newhospitalNameIcon">
                            <?php if(ArrayHelper::getValue($data,'kind')){ ?>
                                <span><?=ArrayHelper::getValue($data,'kind')?></span>
                            <?php } ?>
                            <?php if(ArrayHelper::getValue($data,'level')){ ?>
                                <span><?=ArrayHelper::getValue($data,'level')?></span>
                            <?php } ?>
                            <?php if(ArrayHelper::getValue($data,'type')){ ?>
                                <span><?=ArrayHelper::getValue($data,'type')?></span>
                            <?php } ?></div>
                    </div>
                    <?php
                    if(ArrayHelper::getValue($data,'nick_name')){
                        ?>
                        <p>别称：<?=Html::encode(ArrayHelper::getValue($data,'nick_name'))?></p>
                    <?php } ?>
                    <div class="newhospitalAddress">
                        <p>地址：<?=Html::encode(ArrayHelper::getValue($data,'address'))?><a href="#" target="_blank"></a></p>
                    </div>
                    <p>电话：<?=Html::encode(ArrayHelper::getValue($data,'phone'))?></p>
                    <p>简介：<?php if(ArrayHelper::getValue($data,'description')){ ?> <?=mb_substr(trim(CommonFunc::filterContent(Html::encode(ArrayHelper::getValue($data,'description')))),0,50,'UTF8').'...'?><a href="<?=Url::to(['hospital/detail','hospital_id'=>$hospital_id])?>" style="color: #3D7BF4;">详情></a> <?php } ?>   </p>
                </div>
            </div>
            <!-- 相关排名 -->
            <!--
             -->
        </div>
    </div>

    <!-- 服务推荐 -->
    <div class="hospitalServiceMain">
        <!--挂号-->
        <a class="hospitalServiceLink borderBg js_addguahaokey">
            <div class="hospitalServiceIcon hospitalServiceIcon3"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>预约挂号</h5>
                    <span class="hospitalServiceType">10秒挂号 快速就医</span>
                </div>
                <p>4科室，6名医生</p>
                <div class="hospitalServiceText">
                    <span>去挂号</span>
                    <span></span>
                </div>
            </div>
        </a>

        <!--电话咨询-->
        <a class="hospitalServiceLink borderBg" onclick="">
            <div class="hospitalServiceIcon"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>名医电话</h5>
                    <span class="hospitalServiceType">医生本人回电</span>
                </div>
                <p>13科室，20名医生</p>
                <div class="hospitalServiceText">
                    <span>预约通话</span>
                    <span></span>
                </div>
            </div>
        </a>

        <!--图文咨询-->
        <a class="hospitalServiceLink borderBg" onclick="">
            <div class="hospitalServiceIcon hospitalServiceIcon2"></div>
            <div class="hospitalServiceTxt">
                <div class="hospitalServiceTitle">
                    <h5>图文咨询</h5>
                    <span class="hospitalServiceType">在线咨询问疾病</span>
                </div>
                <p>24个科室，38名医生</p>
                <div class="hospitalServiceText">
                    <span>立即咨询</span>
                    <span></span>
                </div>
            </div>
        </a>

    </div>

    <!--医院主页改版-->
    <div class="serviceDetails">
        <div class="serviceDetails_l">
            <!-- 我的医生 -->
            <div class="newMyDoctorContent" style="display: none;">
                <div class="newMyDoctorContentitle">
                    <h3>我的医生</h3>
                    <a href="#">更多></a>
                </div>
                <ul class="newMyDoctorContentBox">
                </ul>
            </div>

            <!-- 医生列表 -->
            <div class="newdoctorList">
                <div class="newdoctorListitle">
                    <h5 class="selnewdoctorListitle">推荐医生</h5>
                    <h5>全部科室</h5>
                    <!--                        <h5 class="selnewguahaodepartment" >挂号科室</h5>-->
                </div>
                <!-- 推荐医生 -->
                <!-- 推荐医生 -->
                <div class="newdoctorListBox" style="display: block;">
                    <!-- 二级科室 -->
                    <div class="newdoctorlistMain">
                        <ul class="newsecondDepartment">

                            <?php
                            if(is_array($sub)){
                                $i=0;
                                foreach($sub as $k=>$v){

                            ?>
                                    <li class="sub-doc <?php if($i==0){ ?>selnewdoctorListitle<?php } ?>" departmentId="<?=$v['frist_department_id']?>" ><?=Html::encode($v['frist_department_name'])?></li>

                            <?php
                                    $i++;
                                    if($i>9){break;}
                                } }
                            ?>

                        </ul>
                        <div class="keshi_con_box">
                            <?php
                            if(is_array($doctor_list)){
                                $i=0;
                                foreach($doctor_list as $k=>$v){

                            ?>
                            <div class="newdoctorLists" <?php if($i>0){ ?> style=" display:none;" <?php } ?> >

                                <?php
                                    if(isset($v['doctor_list']) && is_array($v['doctor_list'])  && $v['doctor_list'] ){
                                    foreach($v['doctor_list'] as $doc){
                                ?>
                                <li class="borderBg">
                                    <a href="<?=Url::to(['doctor/home','doctor_id'=>ArrayHelper::getValue($doc,'doctor_id')])?>" class="departemntDorDetail" target="_blank">
                                        <div class="doctorImg">
                                            <img src="<?=ArrayHelper::getValue($doc,'doctor_avatar')?>"  alt="陈有信 ">
                                            <span></span>
                                        </div>
                                        <p class="doctorNames"><span><?=Html::encode(ArrayHelper::getValue($doc,'doctor_realname'))?></span> <?=Html::encode(ArrayHelper::getValue($doc,'doctor_title'))?> <?php if(ArrayHelper::getValue($doc,'doctor_professional_title')){ ?> ,<?=Html::encode(ArrayHelper::getValue($doc,'doctor_professional_title'))?><?php } ?></p>
                                        <p class="doctorNames"><?=Html::encode(ArrayHelper::getValue($doc,'doctor_second_department_name'))?></p>
                                        <!--<p>能力领先<span> 98.16% </span>的同科医生</p>-->
                                        <?php if(trim(ArrayHelper::getValue($doc,'doctor_good_at'))){ ?>
                                        <p style="height: 42px;">擅长:<?=Html::encode(ArrayHelper::getValue($doc,'doctor_good_at'))?></p>
                                        <?php } ?>
                                    </a>
                                    <dl class="departemntDorService">
                                        <a  class="disService" target="_blank">电话咨询</a>
                                        <a  class="disService" target="_blank">图文咨询</a>
                                        <a class="disService"  target="_blank">预约挂号</a>
                                    </dl>
                                    <span class="departemntDorIcon">荐</span>
                                </li>
                                <?php } ?>
                                <li class="borderBg">
                                    <a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>$k])?>" class="doctorReadMore" >
                                        <p>查看更多医生&gt;</p>
                                    </a>
                                </li>
                                <?php $i++; } ?>
                            </div>

                            <?php }} ?>
                        </div>
                    </div>
                </div>

                <!-- 全部科室 -->
                <div class="newdoctorListBox" >
                    <?php
                    if(is_array($sub)){
                    foreach($sub as $frist){
                        ?>
                        <div class="newdepartmentlist">
                            <div style="overflow: hidden;">
                                <div class="newdepartmentlistitle"><?=Html::encode($frist['frist_department_name'])?></div>
                                <div class="newdepartmentModel">
                                    <?php
                                    if(isset($frist['second_arr']) && is_array($frist['second_arr']) ){
                                        foreach($frist['second_arr'] as $second){
                                            ?>
                                            <a href="<?=Url::to(['hospital/doclist','hospital_id'=>$hospital_id,'frist_department_id'=>$frist['frist_department_id'],'second_department_id'=>$second['second_department_id']])?>" class="">
                                                <p><span><?=Html::encode($second['second_department_name'])?></span>（<?=Html::encode($second['doctors_num'])?>人）</p>
                                            </a>
                                        <?php }} ?>
                                </div>
                            </div>
                        </div>
                    <?php } } ?>
                </div>
                <!-- 挂号科室 -->
                <div class="newdoctorListBox guahaoBx"></div>

            </div>

            <!-- 按疾病找医生 -->

        </div>

        <div class="serviceDetails_r">
            <!-- 医院地图 -->
            <div class="mapTitle">
                <h5>医院地图</h5>
<!--                <a href="#">展开</a>-->
            </div>
            <div id="bigMap" style="width: 300px;height: 200px"></div>

            <script type="text/javascript">
                window._AMapSecurityConfig = {
                    serviceHost:"<?php echo Yii::$app->params['domains']['pc']?>"+"_AMapService",
                }
            </script>
            <script src="https://webapi.amap.com/maps?v=1.4.15&key=c3cdca626e44bff38f5734dfcefb363a&plugin=AMap.Geocoder"></script>
            <script type="text/javascript">
                var map = new AMap.Map("bigMap", {
                    //zooms: 10,
                    resizeEnable: true
                });
                var geocoder = new AMap.Geocoder({
                    city: "<?=Html::encode(ArrayHelper::getValue($data,'city_name'))?>",
                });
                var marker = new AMap.Marker();
                var address  = "<?=Html::encode(ArrayHelper::getValue($data,'name'))?>";
                geocoder.getLocation(address, function(status, result) {
                    if (status === 'complete'&&result.geocodes.length) {
                        var lnglat = result.geocodes[0].location
                        console.log(lnglat);
                        marker.setPosition(lnglat);
                        map.add(marker);
                        map.setZoom(16);
                        map.setCenter(lnglat);
                        //map.setFitView(marker);
                    }else{
                        console.log('根据地址查询位置失败');
                    }
                });
            </script>

        </div>


    </div>

    <!--医院主页改版-->

    <!-- 本院名医 -->
    <?php
        if(isset($hospital_doc) && is_array($hospital_doc) && $hospital_doc ){

    ?>
    <div class="famousDoctor">
        <h5>本院名医</h5>
        <ul>
            <?php  foreach ($hospital_doc as $k=>$v){ ?>
            <a href="<?=Url::to(['doctor/home','doctor_id'=>ArrayHelper::getValue($v,'doctor_id')])?>">
                <div class="famousDoctorName">
                    <p><span><?=Html::encode(ArrayHelper::getValue($v,'doctor_realname'))?></span><?=Html::encode(ArrayHelper::getValue($v,'doctor_title'))?><?php if(ArrayHelper::getValue($v,'doctor_professional_title')){ ?> ,<?=Html::encode(ArrayHelper::getValue($v,'doctor_professional_title'))?><?php } ?></>

                    <p><?=Html::encode(ArrayHelper::getValue($v,'doctor_hospital'))?> <?=Html::encode(ArrayHelper::getValue($v,'doctor_second_department_name'))?></p>
                    <i><?=$k+1?></i>
                </div>
                <div class="famousDoctorTitle">
<!--                    <p style="overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;">中国医师协会重症医学医师分会副会长</p>-->
                    <!--<p>综合能力：领先<span>90%</span>同科医生</p>-->
                </div>
            </a>
            <?php } ?>
        </ul>
    </div>
    <?php  } ?>

    <!-- 同区域同类别医院 -->
    <?=OrtherHospital::widget(['city_id' => ArrayHelper::getValue($data,'city_id'),'type' => ArrayHelper::getValue($data,'type'),'hospital_id' => $hospital_id])?>



    <!-- 右边浮动导航 -->
    <div class="ms_posi">
        <ul>

            <li class="top"><a href="#top" target="_self"></a></li>
        </ul>
    </div>
</div>
