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
        <a href="<?= Url::to(['/index/index/']) ?>">首页 > </a><a href="<?=Url::to(['hospitallist/index'])?>">找医院<span></span></a>&nbsp;>&nbsp;<span class="on">全国医院排行榜</span>
    </div>
    <div class="docRankingList">
        <div class="docRankingList_left">
            <div class="conditionalScreen">
                <a href="<?=Url::to(['hospitallist/index'])?>"><span >医院排行榜</span></a>
                <a href="<?=Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>0,'page'=>1])?>"><span class="selScreenActive">按科室找</span></a>
                <a href="<?=Url::to(['hospitallist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'page'=>1])?>"><span>按疾病找</span></a>
            </div>
            <div class="conditionalScreenMain">
                <div class="screeningList">
                    <div class="screeningrangk">
                        <span class="screeningTitle">省：</span>
                         <ul style="width: 80%;height: auto;">
                            <li <?php if(isset($region) && $region): ?> <?php else:?>  class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['hospitallist/department','region'=>0,'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'page'=>1])?>">全国</a></li>
                            <?php if(!empty($province_list)): ?>
                            <?php foreach($province_list as $key_pro=>$value):?>
                                <li <?php if(isset($province['id']) && $value['id'] == $province['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$value['pinyin'],'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                        <!-- <a class="openMores fl" data-open="1">展开</a>   -->
                    </div> 
                    <?php if(!empty($city_list)): ?>    
                    <div class="screeningrangk">
                        <span class="screeningTitle">市/区：</span>
                        <ul style="width: 80%;height: auto;">
                            <li <?php if(isset($region) && $region == $province['pinyin']): ?> class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['hospitallist/department','region'=>$province['pinyin'],'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'page'=>1])?>">不限</a></li>
                            <?php if(!empty($city_list)): ?>
                            <?php foreach($city_list as $key_pro=>$value):?>
                                <li <?php if(isset($city['id']) && $value['id'] == $city['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$value['pinyin'],'sanjia'=>$sanjia,'keshi_id'=>$keshi_id,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                         <!-- <a class="openMores fl" data-open="1">展开</a>        -->
                    </div>    
                    <?php endif;?>
                    
                    <?php if(isset($fkeshi_list) && !empty($fkeshi_list)): ?>   
                    <div class="screeningrangk">
                        <span class="screeningTitle">一级科室：</span>
                        <ul style="width: 80%;">
                            <li <?php if(isset($keshi_id) && $keshi_id): ?> <?php else:?>  class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>0,'page'=>1])?>">不限</a></li>
                            <?php foreach($fkeshi_list as $key=>$value):?>
                                <li <?php if(isset($fkeshi_info['department_id']) && $fkeshi_info['department_id'] == $value['department_id']):?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$value['department_id'],'page'=>1])?>"><?=Html::encode($value['department_name']);?></a></li>
                            <?php endforeach;?>
                        </ul>
                         <a class="openMores fl" data-open="1">展开</a>
                    </div>  
                    <?php endif;?>   

                    <?php if(isset($skeshi_list) && !empty($skeshi_list)): ?>   
                    <div class="screeningrangk">
                        <span class="screeningTitle">二级科室：</span>
                        <ul style="width: 80%;">
                            <li <?php if(isset($keshi_id) && $keshi_id == $fkeshi_info['department_id']): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$fkeshi_info['department_id'],'page'=>1])?>">不限</a></li>
                            <?php foreach($skeshi_list as $key=>$value):?>
                                <li <?php if(isset($skeshi_info['department_id']) && $skeshi_info['department_id'] == $value['department_id']):?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>$sanjia,'keshi_id'=>$value['department_id'],'page'=>1])?>"><?=Html::encode($value['department_name']);?></a></li>
                            <?php endforeach;?>
                        </ul>
                         <a class="openMores fl" data-open="1">展开</a>     
                    </div>  
                    <?php endif;?>                  
                    <div class="screeningrangk">
                        <span class="screeningTitle">等级：</span>
                        <ul style="width: 80%;">
                            <li <?php if(isset($sanjia) && $sanjia == 0): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>0,'keshi_id'=>$keshi_id,'page'=>1])?>">不限</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 2): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>2,'keshi_id'=>$keshi_id,'page'=>1])?>">三级甲等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 3): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>3,'keshi_id'=>$keshi_id,'page'=>1])?>">三级乙等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 4): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>4,'keshi_id'=>$keshi_id,'page'=>1])?>">三级丙等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 5): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>5,'keshi_id'=>$keshi_id,'page'=>1])?>">二级甲等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 6): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>6,'keshi_id'=>$keshi_id,'page'=>1])?>">二级乙等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 7): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>7,'keshi_id'=>$keshi_id,'page'=>1])?>">二级丙等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 8): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>8,'keshi_id'=>$keshi_id,'page'=>1])?>">一级甲等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 9): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>9,'keshi_id'=>$keshi_id,'page'=>1])?>">一级乙等</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 10): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['hospitallist/department','region'=>$region,'sanjia'=>10,'keshi_id'=>$keshi_id,'page'=>1])?>">一级丙等</a></li>
                        </ul>
                         <a class="openMores fl" data-open="1">展开</a>     
                    </div>

                    <?php  /*<div class="screeningrangk">
                        <span class="screeningTitle">服务：</span>
                        <ul style="width: 80%;">
                            <a href="" rel="nofollow"><li>全部</li></a>
                            <a href="" rel="nofollow"><li class="selScreeningBd">有开通服务</li></a>
                        </ul>
                    </div>*/ ?>
                </div>
                <?php echo \pc\widget\HospitallistContentWidget::widget(['hospital_list'=>$hospital_list,'totalCount'=>$totalCount,'pagination'=>$pagination,'page'=>$page])?>
            </div>
        </div>
        <?php echo \pc\widget\DoctorlistRightWidget::widget([])?>
    </div>
</div>

