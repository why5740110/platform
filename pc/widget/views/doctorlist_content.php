<?php
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

?>

<div class="screeningPrompt">
    <h1>全国医生排行榜</h1>
    注:本榜单共录有<?=$totalCount;?>名医生；王氏医生排行榜以百万患友口碑为基础打造公立医院排行，杜绝一切广告，保证客观公正，为患友寻医就诊提供实用指南。
</div>
<div>
    <?php if(!empty($doctorlist)): ?>
    <ul>
         <?php 
            $num = ($page-1)*20;
        ;?>
        <?php foreach($doctorlist as $key=>$value):?>
        <?php 
            $num++;
        ;?>

        <li class="hospitalDetail">
            <div class="rankIndex"><span><?=$num;?></span></div>
            <div class="hospitalDetail_l">
                <a href="<?= Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]) ?>">
                    <img src="<?php if(isset($value['doctor_avatar']) && !empty($value['doctor_avatar'])): ?> <?=$value['doctor_avatar'];?> <?php else:?>  https://u.nisiyacdn.com/avatar/default_2.jpg<?php endif;?>" alt="<?=Html::encode($value['doctor_realname']);?>" class="photo">
                </a>
                <div class="hospitalMain" style="width: 80%">
                    <a href="<?=Url::to(['/doctor/home','doctor_id'=>$value['doctor_id']]);?>">
                        <div class="hospitalNames">
                            <h3><?=Html::encode($value['doctor_realname']);?></h3>
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
        <p>未找到相关数据哟~</p>
    </div>

     <?php endif;?> 
</div>