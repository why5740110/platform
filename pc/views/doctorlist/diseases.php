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
                <a href="<?=Url::to(['doctorlist/index'])?>"><span >医生排行榜</span></a>
                <a href="<?=Url::to(['doctorlist/department','region'=>0,'sanjia'=>0,'keshi_id'=>0,'page'=>1])?>"><span >按科室找</span></a>
                <a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'page'=>1])?>"><span  class="selScreenActive">按疾病找</span></a>
            </div>
            <div class="conditionalScreenMain">
                <div class="screeningList">
                    <div class="screeningrangk">
                        <span class="screeningTitle" style="margin-top: 10px;">疾病检索：</span>
                        <div class="diseaseSearch">
                            <input type="text" placeholder="请输入疾病" value="<?=$search_disease_name;?>" id="diseaseSearchInput">
                            <span class="diseaseSearchBtn" data-type="doctor">搜索</span>
                        </div>
                        <ul style="margin-top: 10px;">
                            <li>热门疾病：</li>
                            <li><a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>'nangzhongxingjibing','page'=>1])?>">囊肿性疾病</a></li>
                            <li><a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>'waikeganran','page'=>1])?>">外科感染</a></li>
                            <li><a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>'shouzukoubing','page'=>1])?>">手足口病</a></li>
                            <li><a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>0,'diseases'=>0,'dspinyin'=>'shuidou','page'=>1])?>">水痘</a></li>
                        </ul>
                    </div>
                    <div class="screeningrangk">
                        <span class="screeningTitle">疾病字母：</span>
                        <ul class="letter_style">
                           <li <?php if(isset($diseases) && !$diseases): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>0,'page'=>1])?>">不限</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'a'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'a','page'=>1])?>">A</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'b'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'b','page'=>1]) ?>">B</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'c'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'c','page'=>1]) ?>">C</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'd'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'d','page'=>1]) ?>">D</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'e'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'e','page'=>1]) ?>">E</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'f'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'f','page'=>1]) ?>">F</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'g'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'g','page'=>1]) ?>">G</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'h'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'h','page'=>1]) ?>">H</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'i'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'i','page'=>1]) ?>">I</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'j'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'j','page'=>1]) ?>">J</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'k'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'k','page'=>1]) ?>">K</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'l'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'l','page'=>1]) ?>">L</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'm'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'m','page'=>1]) ?>">M</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'n'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'n','page'=>1]) ?>">N</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'o'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'o','page'=>1]) ?>">O</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'p'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'p','page'=>1]) ?>">P</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'q'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'q','page'=>1]) ?>">Q</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'r'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'r','page'=>1]) ?>">R</a></li>
                            <li <?php if(isset($diseases) && $diseases == 's'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'s','page'=>1]) ?>">S</a></li>
                            <li <?php if(isset($diseases) && $diseases == 't'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'t','page'=>1]) ?>">T</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'u'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'u','page'=>1]) ?>">U</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'v'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'v','page'=>1]) ?>">V</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'w'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'w','page'=>1]) ?>">W</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'x'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'x','page'=>1]) ?>">X</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'y'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'y','page'=>1]) ?>">Y</a></li>
                            <li <?php if(isset($diseases) && $diseases == 'z'): ?> class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'z','page'=>1]) ?>">Z</a></li>
                        </ul>
                    </div>
                   
                    <div class="screeningrangk related_diseases">
                        <span class="screeningTitle">相关疾病：</span>
                        <ul style="width: 80%;" class="openMoreMain">
                            <li><a href="">氨基酸代谢病</a></li>
                            <li><a href="">阿米巴性阴道炎</a></li>
                            <li><a href="">阿米巴痢疾</a></li>
                        </ul>
                        <a class="openMores fl" data-open="1">展开</a>  
                    </div>  
                    <div class="screeningrangk">
                        <span class="screeningTitle">省：</span>
                         <ul style="width: 80%;height: auto;">
                            <li <?php if(isset($region) && $region): ?> <?php else:?>  class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['doctorlist/diseases','region'=>0,'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">全国</a></li>
                            <?php if(!empty($province_list)): ?>
                            <?php foreach($province_list as $key_pro=>$value):?>
                                <li <?php if(isset($province['id']) && $value['id'] == $province['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$value['pinyin'],'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                        <!-- <a class="openMores fl" data-open="1">展开</a>   -->
                    </div> 
                    <?php if(!empty($city_list)): ?>    
                    <div class="screeningrangk">
                        <span class="screeningTitle">市/区：</span>
                        <ul style="width: 80%;height: auto;">
                            <li <?php if(isset($region) && $region == $province['pinyin']): ?> class="selScreeningBd" <?php endif;?> ><a href="<?=Url::to(['doctorlist/diseases','region'=>$province['pinyin'],'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">不限</a></li>
                            <?php if(!empty($city_list)): ?>
                            <?php foreach($city_list as $key_pro=>$value):?>
                                <li <?php if(isset($city['id']) && $value['id'] == $city['id']): ?>class="selScreeningBd" <?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$value['pinyin'],'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>"><?=$value['name'];?></a></li>
                            <?php endforeach;?>
                            <?php endif;?> 
                        </ul>
                         <!-- <a class="openMores fl" data-open="1">展开</a>        -->
                    </div>    
                    <?php endif;?>        
                    <div class="screeningrangk">
                        <span class="screeningTitle">职称：</span>
                        <ul style="width: 80%;">
                            <li <?php if(isset($sanjia) && $sanjia == 0): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>0,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">不限</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 1): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>1,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">主任医师</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 6): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>6,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">副主任医师</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 3): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>3,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">副主任医师</a></li>
                            <li <?php if(isset($sanjia) && $sanjia == 4): ?>class="selScreeningBd"<?php endif;?>><a href="<?=Url::to(['doctorlist/diseases','region'=>$region,'sanjia'=>4,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">副主任医师</a></li>
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