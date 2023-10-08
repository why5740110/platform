<?php

use \common\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->registerCssFile( Url::getStaticUrl("css/list.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/list.min.js") );

$keyword = isset($keyword) ? $keyword : '';
?>

<div class="w1200 ovH">
  <div class="w850 fl">
      <div class="msyy_search_box01">
          <h3 class="big_title">“<span class="col_blue"><?=Html::encode($keyword);?></span>”相关医院<a href="<?= Url::to(['search/so','type'=>'hospital','keyword'=>Html::encode($keyword)]);?>" class="more">更多></a></h3>
          <?php if(!empty($hospital_list)): ?>
          <div class="hospitalmain">
              <ul>
                <?php 
                    $hostnum = 0;
                ;?>
                 <?php foreach($hospital_list as $key=>$value):?>
                 <?php $hostnum++; ;?>
                  <li>
                    <a href="<?= Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]) ?>">
                      <div class="hospitalImg">
                        <img src="<?php if(isset($value['hospital_photo']) && !empty($value['hospital_photo'])): ?> <?=$value['hospital_photo'];?> <?php else:?>  https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg<?php endif;?>"><span ><i ><?=$hostnum;?></i></span>
                      </div>
                      <h5>
                        <?php if(isset($value['highlight']['hospital_name_keyword'])): ?>
                          <?=$value['highlight']['hospital_name_keyword'][0];?>
                        <?php else:?> 
                          <?=Html::encode($value['hospital_name']);?>
                        <?php endif;?> 
                      </h5>
                      <p>
                        <?php if(!empty(trim($value['hospital_level']))): ?><?=$value['hospital_level'];?><?php endif;?>
                        <?php if(!empty(trim($value['hospital_type']))): ?><?=$value['hospital_type'];?><?php endif;?> 
                      </p>
                    </a>
                  </li>
                  <?php endforeach;?>
              </ul>
          </div>
          <?php else:?> 
            <div class="hos_no_data_box">
                <img src="<?=Url::getStaticUrl("images/cf1d3904107a38c952e79f6ff01979c8.jpg");?>" width="180" height="180" alt="">
                <p>未找到相关医院数据哟~</p>
            </div>
          <?php endif;?> 
      </div>
      <div class="msyy_search_box01">
        <h3 class="big_title">“<span class="col_blue"><?=Html::encode($keyword);?></span>”相关医生<a href="<?= Url::to(['search/so','type'=>'doctor','keyword'=>Html::encode($keyword)]);?>" class="more">更多></a></h3>
        <?php if(!empty($doctor_list)): ?>
        <div class="ms_search_doctorbox">
            <ul>
                <?php foreach($doctor_list as $key=>$value):?>
                <li class="hospitalDetail">
                    <div class="hospitalDetail_l">
                        <a href="<?= Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]) ?>">
                            <img src="<?php if(isset($value['doctor_avatar']) && !empty($value['doctor_avatar'])): ?> <?=$value['doctor_avatar'];?> <?php else:?>  https://u.nisiyacdn.com/avatar/default_2.jpg<?php endif;?>" alt="<?=Html::encode($value['doctor_realname']);?>" class="photo">
                        </a>
                        <div class="hospitalMain">
                            <a href="<?= Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]) ?>">
                                <div class="hospitalNames">
                                    <h3>
                                      <?php if(isset($value['highlight']['doctor_realname_keyword'])): ?>
                                        <?=$value['highlight']['doctor_realname_keyword'][0];?>
                                      <?php else:?> 
                                        <?=Html::encode($value['doctor_realname']);?>
                                      <?php endif;?> 
                                    </h3>                                          
                                    <span class="hos_style"><?=Html::encode($value['doctor_hospital']);?></span>
                                    <span><?=Html::encode($value['doctor_second_department_name']);?></span>
                                    <span class="ddzy">多点执业</span>
                                </div>
                                <div class="title"><?php if(!empty(trim($value['doctor_title']))): ?><?=Html::encode($value['doctor_title']);?><?php endif;?>
                            <?php if(!empty(trim($value['doctor_professional_title']))): ?><?=Html::encode($value['doctor_professional_title']);?><?php endif;?></div>
                                <p class="hospitalText">擅长：<?=Html::encode($value['doctor_good_at']);?></p>
                            </a>

                            <div class="serviceList">
                                <a href="javascript:void(0);" class="disservicePhone " rel="nofollow">
                                    <span></span>
                                    <p>电话咨询</p>
                                </a>
                                <a href="javascript:void(0);" class="disserviceConsult " rel="nofollow">
                                    <span></span>
                                    <p>图文咨询</p>
                                </a>
                                <a href="javascript:void(0);" class="serviceguahao">
                                    <span></span>
                                    <p>预约挂号</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="hospitalDetail_r">
                    <p>综合评分</p>
                    <p class="scoreQuantity">9.5分</p>
                    </div>
                </li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php else:?> 
            <div class="hos_no_data_box">
                <img src="<?=Url::getStaticUrl("images/cf1d3904107a38c952e79f6ff01979c8.jpg");?>" width="180" height="180" alt="">
                <p>未找到相关医生数据哟~</p>
            </div>
          <?php endif;?> 
      </div>
      <div class="msyy_search_box01">
        <h3 class="big_title">“<span class="col_blue"><?=Html::encode($keyword);?></span>”相关疾病</h3>
        <?php if(!empty($disease_list)): ?>
        <div class="kong_jb_box">
          <?php foreach($disease_list as $key=>$value):?>
            <a href="<?=Url::to(['hospitallist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>$value['pinyin'],'page'=>1])?>" style="cursor: pointer;"><?php if(isset($value['highlight']['disease_keyword'])): ?><?=$value['highlight']['disease_keyword'][0];?><?php else:?><?=Html::encode($value['disease_name']);?><?php endif;?></a>
          <?php endforeach;?>
        </div>
        <?php else:?> 
            <div class="hos_no_data_box">
                <img src="<?=Url::getStaticUrl("images/cf1d3904107a38c952e79f6ff01979c8.jpg");?>" width="180" height="180" alt="">
                <p>未找到相关疾病数据哟~</p>
            </div>
        <?php endif;?> 
      </div>
  </div>
  <?php echo \pc\widget\DoctorlistRightWidget::widget([])?>
    <?php  /*<div class="w300 fr">
      <div class="ms_tuijie_doctor">
        <h5 class="indexTlt">推荐医院</h5>
        <ul>
            <li>
              <p  class="title">上海中医药大学协爱中医院</p>
              <p class="dec">地址：奉贤区瓦洪公路3318号（上海电子信息职业技术学院旁边）</p>
            </li>
            <li>
              <p  class="title">上海中医药大学协爱中医院</p>
              <p class="dec">地址：奉贤区瓦洪公路3318号（上海电子信息职业技术学院旁边）</p>
            </li>
            <li>
              <p  class="title">上海中医药大学协爱中医院</p>
              <p class="dec">地址：奉贤区瓦洪公路3318号（上海电子信息职业技术学院旁边）</p>
            </li>
            <li>
              <p  class="title">上海中医药大学协爱中医院</p>
              <p class="dec">地址：奉贤区瓦洪公路3318号（上海电子信息职业技术学院旁边）</p>
            </li>
            <li>
              <p  class="title">上海中医药大学协爱中医院</p>
              <p class="dec">地址：奉贤区瓦洪公路3318号（上海电子信息职业技术学院旁边）</p>
            </li>
        </ul>
      </div>
  </div>*/ ?>
</div>
