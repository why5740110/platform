<?php

use \common\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

$this->registerCssFile( Url::getStaticUrl("css/list.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/list.min.js") );

?>
<?php 
    $type_name = '';
;?>
<?php if($type == 'hospital'): ?>
    <?php $type_name = '医院';?>
<?php elseif($type == 'doctor'): ?>
    <?php $type_name = '医生';?>
<?php elseif($type == 'disease'): ?>  
    <?php $type_name = '疾病';?>
<?php endif;?> 

<div class="w1200 ovH">
  <div class="w850 fl">     
    <div class="msyy_search_box01">
      <h3 class="big_title">“<span class="col_blue"><?=Html::encode($keyword);?></span>”相关<?=$type_name;?></h3>
      <div class="ms_search_doctorbox">
        <?php if(!empty($list)): ?>
        <ul>
        <?php 
            $num = ($page-1)*20;
        ;?>
        <?php foreach($list as $key=>$value):?>
        <?php 
            $num++;
        ;?>
        <?php if($type == 'hospital'): ?>
        
        <li class="hospitalDetail">
            <div class="rankIndex"><span><?=$num;?></span></div>
            <div class="hospitalDetail_l hospitalDetail_l_none">
                <a href="<?= Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]) ?>">
                    <img src="<?php if(isset($value['hospital_photo']) && !empty($value['hospital_photo'])): ?> <?=$value['hospital_photo'];?> <?php else:?>  https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg<?php endif;?>" >
                 </a>
                <div class="hospitalMain">
                    <a href="<?= Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]) ?>">
                        <div class="hospitalNames">
                            <h3>
                                <?php if(isset($value['highlight']['hospital_name_keyword'])): ?>
                                  <?=$value['highlight']['hospital_name_keyword'][0];?>
                                <?php else:?> 
                                  <?=Html::encode($value['hospital_name']);?>
                                <?php endif;?> 
                            </h3>

                            <ul>
                                <?php if(!empty(trim($value['hospital_level']))): ?><li><?=$value['hospital_level'];?></li><?php endif;?>
                                <?php if(!empty(trim($value['hospital_type']))): ?><li><?=$value['hospital_type'];?></li><?php endif;?>               
                             </ul>
                        </div>
                        <div class="commentQuantity">
                            <p>评分</p> 
                            <strong class="star star3"><del style="width:82%;"></del></strong>
                        </div>
                        <p class="hospitalText">地址：<?=Html::encode($value['hospital_address']);?></p>
                    </a>
                    <?php if(!empty($value['hospital_good_at'])): ?>
                    <?php 
                        $goodatlist = explode(',', $value['hospital_good_at']);
                    ;?>
                    <div class="diseaselist">
                        <?php foreach($goodatlist as $goodat):?>
                        <a href="javascript:void(0);"><?=Html::encode($goodat);?></a>
                        <?php endforeach;?>
                    </div>
                    <?php endif;?> 
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
                <p class="scoreQuantity">8.9</p>
            </div>
        </li>
        <?php elseif($type == 'doctor'):?> 
        <li class="hospitalDetail">
            <div class="rankIndex"><span><?=$num;?></span></div>
            <div class="hospitalDetail_l">
                <a href="<?= Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]) ?>">
                    <img src="<?php if(isset($value['doctor_avatar']) && !empty($value['doctor_avatar'])): ?> <?=$value['doctor_avatar'];?> <?php else:?>  https://u.nisiyacdn.com/avatar/default_2.jpg<?php endif;?>" alt="<?=$value['doctor_realname'];?>" class="photo">
                </a>
                <div class="hospitalMain" style="width: 80%">
                    <a href="<?=Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]);?>">
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
                        </div>
                        <div class="title">
                            <?php if(!empty(trim($value['doctor_title']))): ?><?=Html::encode($value['doctor_title']);?><?php endif;?>
                            <?php if(!empty(trim($value['doctor_professional_title']))): ?><?=Html::encode($value['doctor_professional_title']);?><?php endif;?>
                        </div>
                        <div class="commentQuantity ovH">
                            <p>10条评论 | </p>
                            <strong class="star star2"><del style="width:60%;"></del></strong>
                        </div>
                        <p class="hospitalText">擅长：<?=Html::encode($value['doctor_good_at']);?></p>
                    </a>
                    <div class="serviceList">
                        <a href="javascript:void(0);" class="disservicePhone" rel="nofollow" >
                            <span></span>
                            <p>电话咨询</p>
                        </a>
                        <a href="javascript:void(0);" class="disserviceConsult" rel="nofollow">
                            <span></span>
                            <p>图文咨询</p>
                        </a>
                        <a href="javascript:void(0);" class="serviceguahao" rel="nofollow">
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
        <?php elseif($type == 'disease'):?> 
        <li class="hospitalDetail">
            <a href="<?=Url::to(['hospitallist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>$value['pinyin'],'page'=>1])?>" style="cursor: pointer;"><?php if(isset($value['highlight']['disease_name'])): ?><?=$value['highlight']['disease_name'][0];?><?php else:?><?=Html::encode($value['disease_name']);?><?php endif;?></a>
        </li>
        <?php endif;?> 
         <?php endforeach;?>

          <div class="page_url">
          <?php
          echo LinkPager::widget([
          'pagination' => $pagination,
          'firstPageLabel' => '首页',
          'nextPageLabel' => '下一页',
          'prevPageLabel' => '上一页',
          'lastPageLabel' => '最后一页',
          'maxButtonCount' => 6,
          'options' => ['class' => 'page'],
          ]);
          ?>
          </div>
        </ul>
        <?php else:?> 
        <div class="hos_no_data_box">
            <img src="<?=Url::getStaticUrl("images/cf1d3904107a38c952e79f6ff01979c8.jpg");?>" width="180" height="180" alt="">
            <p>未找到相关医院数据哟~</p>
        </div>
        <?php endif;?> 
      </div>
    </div>
  </div>
  <?php echo \pc\widget\DoctorlistRightWidget::widget([])?>
</div>
