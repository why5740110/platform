<?php
use yii\helpers\Html;
use common\helpers\Url;

?>

<div class="docRankingList_right">
    <?php if(!empty($rightlist)): ?>
    <div class="msys_fdoc">
      <h5 class="indexTlt">名医推荐</h5>
      <ul class="fdoc_detail">
          <?php foreach($rightlist as $key=>$value):?>
            <li>
                <a target="_blank" href="<?= Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]) ?>" ><img class="fl" src="<?php if(isset($value['doctor_avatar']) && !empty($value['doctor_avatar'])): ?> <?=$value['doctor_avatar'];?> <?php else:?>  https://u.nisiyacdn.com/avatar/default_2.jpg<?php endif;?>" />
                <div class="fr">
                  <h4 class="fl"><?=Html::encode($value['doctor_realname']);?></h4>
                  <span class="fl doctor_titles"><?=Html::encode($value['doctor_title']);?></span><small class="text_over2"><?=Html::encode($value['doctor_hospital']);?>&nbsp;<?=Html::encode($value['doctor_second_department_name']);?></small>
                </div>
                </a>
                <a  href="javascript:;" target="_self"  class="yuyueguahao_a" onclick='' style="background: #ccc">预约挂号</a>
            </li> 
          <?php endforeach;?>
      </ul>
    </div>
    <?php endif;?> 
</div>
