<?php
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

?>

<div class="screeningPrompt">
    <h1>全国医院排行榜</h1>
    注:本榜单共录有<?=$totalCount;?>家医院；王氏医生排行榜以百万患友口碑为基础打造公立医院排行，杜绝一切广告，保证客观公正，为患友寻医就诊提供实用指南。
</div>
<div>
    <?php if(!empty($hospital_list)): ?>
    <ul>
        <?php 
            $num = ($page-1)*20;
        ;?>
        <?php foreach($hospital_list as $key=>$value):?>
        <?php 
            $num++;
        ;?>
        
        <li class="hospitalDetail">
            <div class="rankIndex"><span><?=$num;?></span></div>
            <div class="hospitalDetail_l hospitalDetail_l_none">
                <a href="<?= Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]) ?>">
                    <img src="<?php if(isset($value['hospital_photo']) && !empty($value['hospital_photo'])): ?> <?=$value['hospital_photo'];?> <?php else:?>  https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg<?php endif;?>" >
                 </a>
                <div class="hospitalMain">
                    <a href="<?= Url::to(['/hospital/index','hospital_id'=>$value['hospital_id']]) ?>">
                        <div class="hospitalNames">
                            <h3><?=Html::encode($value['hospital_name']);?></h3>
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