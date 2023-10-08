<?php

use \common\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;


$this->registerCssFile( Url::getStaticUrl("css/list.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/list.min.js") );


?>

<div class="w1200">
    <div class="mt30 loction_size">
        <a href="<?= Url::to(['/index/index/']) ?>">首页 > </a><a href="<?=Url::to(['doctorlist/index'])?>">找医生<span></span></a>&nbsp;>&nbsp;<span class="on">全国医生排行榜</span>
    </div>
    <div class="docRankingList">
        <div class="docRankingList_left">
            <div class="conditionalScreen">
                <a href="<?=Url::to(['doctorlist/index'])?>"><span class="selScreenActive">医生排行榜</span></a>
                <a href="<?=Url::to(['doctorlist/department','region'=>0,'sanjia'=>0,'keshi_id'=>0,'page'=>1])?>"><span >按科室找</span></a>
                <a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'page'=>1])?>"><span>按疾病找</span></a>
            </div>
            <div class="conditionalScreenMain">
                <div class="screeningList">
                  
                    <div class="screeningrangk">
                        <span class="screeningTitle">省：</span>
                         <ul style="width: 80%;height: auto;">
                            <li  <?php if(isset($region) && $region): ?> <?php else:?>  class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['doctorlist/index','region'=>0,'sanjia'=>$sanjia,'page'=>1])?>">全国</a></li>
                            <?php if(!empty($province_list)): ?>
                            <?php foreach($province_list as $key_pro=>$value):?>
                                <li <?php if(isset($province['id']) && $value['id'] == $province['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$value['pinyin'],'sanjia'=>$sanjia,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                    </div> 
                    <?php if(!empty($city_list)): ?>    
                    <div class="screeningrangk">
                        <span class="screeningTitle">市/区：</span>
                        <ul style="width: 80%;height: auto;">
                            <li <?php if(isset($region) && $region == $province['pinyin']): ?> class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['doctorlist/index','region'=>$province['pinyin'],'sanjia'=>$sanjia,'page'=>1])?>">不限</a></li>
                            <?php if(!empty($city_list)): ?>
                            <?php foreach($city_list as $key_pro=>$value):?>
                                <li <?php if(isset($city['id']) && $value['id'] == $city['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$value['pinyin'],'sanjia'=>$sanjia,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                    </div>    
                    <?php endif;?>
                                     
                    <div class="screeningrangk">
                        <span class="screeningTitle">职称：</span>
                        <ul style="width: 80%;">

                            <li <?php if(isset($sanjia) && $sanjia == 0): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$region,'sanjia'=>0,'page'=>1])?>">不限</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 1): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$region,'sanjia'=>1,'page'=>1])?>">主任医师</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 6): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$region,'sanjia'=>6,'page'=>1])?>">副主任医师</a></li>                            
                            <li <?php if(isset($sanjia) && $sanjia == 3): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$region,'sanjia'=>3,'page'=>1])?>">主治医师</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 4): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/index','region'=>$region,'sanjia'=>4,'page'=>1])?>">住院医师</a></li>
                        </ul>
                    </div>

                    <?php  /*<div class="screeningrangk">
                        <span class="screeningTitle">服务：</span>
                        <ul style="width: 80%;">
                            <a href="" rel="nofollow"><li>全部</li></a>
                            <a href="" rel="nofollow"><li class="selScreeningBd">有开通服务</li></a>
                        </ul>
                    </div>*/ ?>
                </div>
                <?php echo \pc\widget\DoctorlistContentWidget::widget(['doctorlist'=>$doctorlist,'totalCount'=>$totalCount,'pagination'=>$pagination,'page'=>$page])?>
            </div>
        </div>
        <?php echo \pc\widget\DoctorlistRightWidget::widget([])?>
    </div>
</div>

