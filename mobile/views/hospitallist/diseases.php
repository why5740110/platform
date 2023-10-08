<?php

use \common\helpers\Url;

$this->registerCssFile(Url::getStaticUrl("css/rankHospitalSickness.css"));
$this->registerJsFile( Url::getStaticUrl("js/rankHospitalSickness.js") );
?>

<?php if(\Yii::$app->controller->getUserAgent() != 'patient' && \Yii::$app->controller->getUserAgent() != 'mini'){ ?>
    <header>
        <span onclick=window.history.go(-1);></span>
        <h1>全国医院排行榜</h1>
        <span></span>
    </header>
    <?php } ?>
    <div class=tab>
        <a href="<?=Url::to(['hospitallist/index'])?>">医院排行榜</a>
        <a href="<?=Url::to(['hospitallist/department','region'=>0,'sanjia'=>0,'keshi_id'=>0,'page'=>1])?>">按科室找</a>
        <a href=# class=tab_active>按疾病找</a>
    </div>
    
    <div class=filtrate>
        <ul>
            <a class=diseaseSort>
                <p>
                    <?php
                    if($dspinyin == '0')
                    {
                        echo "疾病";
                    }else{
                        echo $search_disease_name;
                    }
                    ?>
                </p>
                <span></span>
            </a>
            <a class=area>
                <p>
                    <?php
                    if($region == '0')
                    {
                        echo "全国";
                    }else{
                        if(empty($city))
                        {
                            echo $province['name'];
                        }else{
                            echo $city['name'];
                        }
                    }
                    ?>
                    </p>
                <span></span>
            </a>
            <a class=option>
                <p>
                    筛选
                </p>
                <span></span>
            </a>
        </ul>
    </div>
    
    <?php echo \mobile\widget\HospitallistContentWidget::widget(['hospital_list'=>$hospital_list,'totalCount'=>$totalCount,'pagination'=>$pagination,'page'=>$page,'region'=>$region,'city'=>$city ?? [],'sanjia'=>$sanjia,'keshi_id'=>$keshi_id ?? 0]);?>
 
    
    <div class=areaMain style="display: none;">
        
        <div class=nav>
            <span class="close closeArea"></span>
            <span>选择地区</span>
            <span style="opacity: 0"></span>
        </div>
        <div class=localtionMain>
            <ul class="localtionlist localtionlists">
                <?php if($dspinyin != '0'):?>
                    <li  <?php if(isset($region) && $region): ?> <?php else:?>  class="sel" <?php endif;?> ><a href="<?=Url::to(['hospitallist/diseases','region'=>0,'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">全部</a></li>
                <?php else:?>
                    <li  <?php if(isset($region) && $region): ?> <?php else:?>  class="sel" <?php endif;?> ><a href="<?=Url::to(['hospitallist/diseases','region'=>0,'sanjia'=>$sanjia,'diseases'=>$diseases,'page'=>1])?>">全部</a></li>
                <?php endif;?>

                <?php if(!empty($province_list)): ?>
                    <?php foreach($province_list as $key_pro=>$value):?>
                        <li <?php if(isset($province['id']) && $value['id'] == $province['id']): ?>class="sel" <?php endif;?> class="getcity" province_pinyin="<?php echo $value['pinyin'];?>"><?=$value['name'];?></li>
                    <?php endforeach;?>
                <?php endif;?>
            </ul>

            <div class=localtionlist_box>
                <div class=localtionlist <?php if (isset($city['pinyin'])): ?>style="display:none" <?php endif; ?>>
                    <ul class=localtionlistcity>
                        <li><a href="<?= Url::to(['hospitallist/diseases', 'region'=>$province['pinyin'] ?? 0,'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1]) ?>">不限</a></li>
                    </ul>
                </div>
                <?php if(!empty($province_list)): ?>
                    <?php foreach($province_list as $key_pro=>$value):?>
                        <div class=localtionlist <?php if (isset($province['pinyin']) && $province['pinyin'] != $value['pinyin']): ?>style="display:none" <?php endif; ?>>
                            <ul class=localtionlistcity>
                                <li <?php if(isset($region) && isset($province['pinyin']) && $region == $province['pinyin']): ?> class="sel" <?php endif;?> >
                                    <a href="<?=Url::to(['hospitallist/diseases','region'=>$value['pinyin'] ?? 0,'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>">全部</a>
                                </li>
                                <?php if(!empty($value['city_arr'])):?>
                                    <?php foreach ($value['city_arr'] as $ck=>$cv):?>
                                        <li <?php if(isset($city['id']) && $cv['id'] == $city['id']): ?>class="sel" <?php endif;?>><a href="<?=Url::to(['hospitallist/diseases','region'=>$cv['pinyin'],'sanjia'=>$sanjia,'diseases'=>$diseases,'dspinyin'=>$dspinyin,'page'=>1])?>"><?=$cv['name'];?></a></li>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </ul>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>
            </div>
        </div>

    </div>
    
    <div class=screening>
        <div class=screeningPopul>
            <div class=nav>
                <span class="close closeSreen"></span>
                <span>筛选</span>
                <span style="opacity: 0"></span>
            </div>
            <ul>
                <li>
                    <div class=screenTitle>医院等级</div>
                    <div class=screenlist>
                        <label for=kind0 <?php if(isset($sanjia) && $sanjia == 0): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=0 <?php if(isset($sanjia) && $sanjia == 0): ?>checked=checked<?php endif;?> id=kind0>
                            <span class=radioCore>不限</span>
                        </label>
                        <label for=kind2 <?php if(isset($sanjia) && $sanjia == 2): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=2 <?php if(isset($sanjia) && $sanjia == 2): ?>checked=checked<?php endif;?> id=kind2>
                            <span class=radioCore>三级甲等</span>
                        </label>
                        <label for=kind3 <?php if(isset($sanjia) && $sanjia == 3): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=3 <?php if(isset($sanjia) && $sanjia == 3): ?>checked=checked<?php endif;?> id=kind3>
                            <span class=radioCore>三级乙等</span>
                        </label>
                        <label for=kind4 <?php if(isset($sanjia) && $sanjia == 4): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=4 <?php if(isset($sanjia) && $sanjia == 4): ?>checked=checked<?php endif;?> id=kind4>
                            <span class=radioCore>三级丙等</span>
                        </label>
                        <label for=kind5 <?php if(isset($sanjia) && $sanjia == 5): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=5 <?php if(isset($sanjia) && $sanjia == 5): ?>checked=checked<?php endif;?> id=kind5>
                            <span class=radioCore>二级甲等</span>
                        </label>
                        <label for=kind6 <?php if(isset($sanjia) && $sanjia == 6): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=6 <?php if(isset($sanjia) && $sanjia == 6): ?>checked=checked<?php endif;?> id=kind6>
                            <span class=radioCore>二级乙等</span>
                        </label>
                        <label for=kind7 <?php if(isset($sanjia) && $sanjia == 7): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=7 <?php if(isset($sanjia) && $sanjia == 7): ?>checked=checked<?php endif;?> id=kind7>
                            <span class=radioCore>二级丙等</span>
                        </label>
                        <label for=kind8 <?php if(isset($sanjia) && $sanjia == 8): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=8 <?php if(isset($sanjia) && $sanjia == 8): ?>checked=checked<?php endif;?> id=kind8>
                            <span class=radioCore>一级甲等</span>
                        </label>
                        <label for=kind9 <?php if(isset($sanjia) && $sanjia == 9): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=9 <?php if(isset($sanjia) && $sanjia == 9): ?>checked=checked<?php endif;?> id=kind9>
                            <span class=radioCore>一级乙等</span>
                        </label>
                        <label for=kind10 <?php if(isset($sanjia) && $sanjia == 10): ?>class="on"<?php endif;?>>
                            <input type=radio name=kind value=10 <?php if(isset($sanjia) && $sanjia == 10): ?>checked=checked<?php endif;?> id=kind10>
                            <span class=radioCore>一级丙等</span>
                        </label>
                                                                                                                                                                                                                            </div>
                </li>
            </ul>
            <div class=screenSubmit>确定</div>
        </div>
    </div>
    
    <div class=disease>
        <div class=diseaseBox>
        
        <div class=nav>
            <span class="close closeDisease"></span>
            <span>选择疾病</span>
            <span style="opacity: 0"></span>
        </div>
        
        <div class=diseaseSearch>
            <div class=diseaseSearchFrom>
                <span class=icon></span>
                <input type=text id=diseaseSearchInput value="<?=$search_disease_name;?>">
                <span class=SearchFromText data-type="hospital">搜索</span>
            </div>
        </div>
        
        <div class=NameMain>
            <div class=NameList>
                <ul>
                    <li>
                        <span>热门疾病</span>
                        <dl>
                            <a href=<?=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"zhiqiguanyan",'page'=>1])?>>支气管炎</a>
                            <a href=<?=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"gaoxueya",'page'=>1])?>>高血压</a>
                            <a href=<?=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"guanxinbing",'page'=>1])?>>冠心病</a>
                            <a href=<?=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"xinlishuaijie",'page'=>1])?>>心力衰竭</a>
                            <!--<a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"quetiexingpinxue",'page'=>1])*/?>>缺铁性贫血</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"nangzhongxingjibing",'page'=>1])*/?>>囊肿性疾病</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"waikeganran",'page'=>1])*/?>>外科感染</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"shouzukoubing",'page'=>1])*/?>>手足口病</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"shuidou",'page'=>1])*/?>>水痘</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"tangniaobing",'page'=>1])*/?>>糖尿病</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"tongfeng",'page'=>1])*/?>>痛风</a>
                            <a href=<?/*=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>'0','dspinyin'=>"fengshixingguanjieyan",'page'=>1])*/?>>风湿性关节炎</a>-->
                        </dl>
                    </li>
                    <li id=A>
                        <span>A</span>
                        <dl>
                            <?php if(!empty($diseases_list)):?>
                                <?php foreach ($diseases_list as $dk=>$dv):?>
                                    <a href=<?=Url::to(['hospitallist/diseases','region'=>$region,'sanjia'=>$sanjia,'diseases'=>$dv['initial'],'dspinyin'=>$dv['pinyin'],'page'=>1])?>><?php echo $dv['disease_name'];?></a>
                                <?php endforeach;?>
                            <?php endif;?>
                        </dl>
                    </li>

                </ul>
            </div>
            <div class=sortList>
                <a value=A href=#A class="getdisease">A</a>
                <a value=B href=#B class="getdisease">B</a>
                <a value=C href=#C class="getdisease">C</a>
                <a value=D href=#D class="getdisease">D</a>
                <a value=E href=#E class="getdisease">E</a>
                <a value=F href=#F class="getdisease">F</a>
                <a value=G href=#G class="getdisease">G</a>
                <a value=H href=#H class="getdisease">H</a>
                <a value=I href=#I class="getdisease">I</a>
                <a value=J href=#J class="getdisease">J</a>
                <a value=K href=#K class="getdisease">K</a>
                <a value=L href=#L class="getdisease">L</a>
                <a value=M href=#M class="getdisease">M</a>
                <a value=N href=#N class="getdisease">N</a>
                <a value=O href=#O class="getdisease">O</a>
                <a value=P href=#P class="getdisease">P</a>
                <a value=Q href=#Q class="getdisease">Q</a>
                <a value=R href=#R class="getdisease">R</a>
                <a value=S href=#S class="getdisease">S</a>
                <a value=T href=#T class="getdisease">T</a>
                <a value=W href=#W class="getdisease">W</a>
                <a value=X href=#X class="getdisease">X</a>
                <a value=Y href=#Y class="getdisease">Y</a>
                <a value=Z href=#Z class="getdisease">Z</a>
            </div>
        </div>
    </div>
</div>
<div class=moreHeights></div>
<?php echo \mobile\widget\Menu::widget([]);?>

</body>
</html>