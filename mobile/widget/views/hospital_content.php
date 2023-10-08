<?php
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

?>
 <div class=toptenReviews>
    <p class=plain>本榜单共录有<?php echo $totalCount;?>家医院</p>
    <?php if(!empty($hospital_list)):?>
        <ul class="db">
            <?php if(!empty($hospital_list)):?>
                <?php $rankNum = 20*($page - 1) + 1;?>
                <?php $current_data = 0; ?>
                <?php foreach ($hospital_list as $hk=>$hv):?>
                    <?php $current_data ++;?>
                    <li>
                        <a href="<?= Url::to(['/hospital/index','hospital_id'=>$hv['hospital_id']]) ?>">
                            <div class=item>
                                <div class=detail>
                                    <div class=detailImg>
                                        <img src="<?=!empty($hv['hospital_photo']) ? $hv['hospital_photo'] : 'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';?>" onerror="'https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg'" alt="<?php echo Html::encode($hv['hospital_name']);?>">
                                    </div>
                                    <div class="detailContent detailContent2">
                                        <p class=detailScipe><?php echo Html::encode($hv['hospital_name']);?></p>
                                        <div class=detailContentLabel>
                                            <?php if($hv['hospital_level']):?>
                                                <span><?php echo $hv['hospital_level'];?></span>
                                            <?php endif;?>
                                            <?php if($hv['hospital_type']):?>
                                                <span><?php echo $hv['hospital_type'];?></span>
                                            <?php endif;?>
                                            <?php if($hv['hospital_kind']):?>
                                                <span><?php echo $hv['hospital_kind'];?></span>
                                            <?php endif;?>
                                        </div>
                                        <p>地址：<?php echo Html::encode($hv['hospital_address']);?></p>

                                    </div>

                                </div>
                                <div class=grade>
                                    <p>评分：<span>8.9</span></p>

                                    <?php if(\yii\helpers\ArrayHelper::getValue($hv,'hospital_is_plus')==1){ ?>
                                        <div class=youhao_tit02>有号</div>
                                    <?php } ?>
                                </div>
                            </div>
                        </a>
                        <div class=rankNum>
                            <span><i><?php echo $rankNum;?></i></span>
                        </div>
                    </li>
                    <?php $rankNum ++;?>
                <?php endforeach;?>
            <?php endif;?>
        </ul>
        <?php if($totalCount > 20 and !empty($hospital_list) and $current_data >= 20):?>
            <div class=loadingMore>
                加载更多
            </div>
        <?php endif;?>
    <?php else:?>
        <div class="nosearch" style="display:block" ></div>
    <?php endif;?>
    <input type='hidden' id='page' value="<?php echo $page?>">
    <input type='hidden' id='region' value="<?php echo $region;?>">
    <input type='hidden' id='sanjia' value="<?php echo $sanjia;?>">
    <input type='hidden' id='city_id' value="<?php echo $city['id']??'';?>">
    <input type='hidden' id='keshi_id' value="<?php echo $keshi_id??'0';?>">
    <input type='hidden' id='type' value="2">
    <input type='hidden' id='url' value="<?= Url::to(['hospitallist/department', 'region'=>$region ?? 0,'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'page'=>$page]) ?>">
</div> 
