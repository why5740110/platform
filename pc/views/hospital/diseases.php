<?php
/**
 * @file index.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/27
 */
use \common\helpers\Url;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use pc\widget\HospitalNavCrumbs;

$this->registerCssFile( Url::getStaticUrl("css/hos_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_detail.min.js") );

?>

<div class="ms_section_list w1200">
    <?=HospitalNavCrumbs::widget(['hospital_id' => $hospital_id,'hosp_data' => $data])?>
    <div class="title_h1" style="position:relative;">
        <h1 class="ms_hospital_title"><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h1>
        <span><?=ArrayHelper::getValue($data,'level')?></span>
    </div>
    <div class="ms_nav_traffic_guide">

        <div class="ms_illness_classify border">

            <div class="illness_list clearfix  H_link">
                <div class="list_h2">一级科室：</div>
                <ul class="list_ul list_list clearfix">
                    <li class="<?php if(!\Yii::$app->request->get('frist_department_id')){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/diseases','hospital_id'=>$hospital_id])?>">全部</a></li>
                    <?php
                    foreach($sub as $v){
                        ?>
                        <li class="<?php if(\Yii::$app->request->get('frist_department_id')==$v['department_id']){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/diseases','hospital_id'=>$hospital_id,'frist_department_id'=>$v['department_id']])?>"><?=Html::encode($v['department_name'])?></a></li>
                    <?php } ?>
                </ul>
                <a class="unfold border H_open" href="javascript:void(0);" >展开 </a>
                <a class="unfold border H_close H_contraction" href="javascript:;" >收缩</a>
            </div>
            <?php
            if($second_sub){
                ?>
                <div class="illness_list clearfix  H_link">
                    <div class="list_h2">二级科室：</div>
                    <ul class="list_ul list_list clearfix">
                        <li class=" <?php if(!\Yii::$app->request->get('second_department_id')){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/diseases','hospital_id'=>$hospital_id,'frist_department_id'=>\Yii::$app->request->get('department_id')])?>">全部</a></li>
                        <?php
                        foreach($second_sub as $v){
                            ?>
                            <li class="<?php if(\Yii::$app->request->get('second_department_id')==$v['department_id']){ ?> on <?php } ?>"><a href="<?=Url::to(['hospital/diseases','hospital_id'=>$hospital_id,'frist_department_id'=>\Yii::$app->request->get('frist_department_id'),'second_department_id'=>$v['department_id']])?>"><?=Html::encode($v['department_name'])?></a></li>
                        <?php } ?>
                    </ul>
                    <a class="unfold border H_open" href="javascript:void(0);" >展开 </a>
                    <a class="unfold border H_close H_contraction" href="javascript:;" >收缩</a>
                </div>
            <?php } ?>

        </div>
        <div class="inquire_doctor">
            <strong class="left">擅长疾病</strong>
            <div class="inquire_input left">
                <a href="#"  target="_self" style="font-size: 14px;color: #333;line-height: 25px;margin-left: 20px;">中国医学科学院北京协和医院预约挂号</a>
            </div>
        </div>
        <!--擅长疾病医师列表-->
        <div id="list">
            <div class="ms_illness_classify_list ">
                <div class="ranking">
                    <p><span>70336</span>位病友分享了看病经验，点评<span>213</span>个科室<span>1262 </span>位医生，得出 <span>1788</span>种疾病医生排名</p>
                </div>
                <ul>
                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="#" >
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">肿瘤</p>
                                <span class="color_999">(366人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">马东来</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">孙爱达</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">黄汉源</a> </div>

                    </li>

                    <li class="clearfix">
                        <a href="# ">
                            <div class="classify_h3 left hscDisease">
                                <p class="color_333">糖尿病</p>
                                <span class="color_999">(117人) ：</span>
                            </div>
                        </a>
                        <div class="ms_classify_name left"> <span class="yellow">NO.1</span> <a class="color_333" href="#">陈有信</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.2</span> <a class="color_333" href="#">闵寒毅</a> </div>
                        <div class="ms_classify_name left"> <span class="yellow">NO.3</span> <a class="color_333" href="#">谭柯</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.4</span> <a class="color_333" href="#">景雅莉</a> </div>
                        <div class="ms_classify_name left"> <span class="neworange">NO.5</span> <a class="color_333" href="#">李学旺</a> </div>

                    </li>

                </ul>
            </div>
                <!--<ul>
                    <li class="on"><a href="#" target="_self">1</a></li>
                    <li ><a href="#" target="_self">2</a></li>
                    <li ><a href="#" target="_self">3</a></li>
                    <li ><a href="#" target="_self">末页</a></li>
                    <li ><a href="#" target="_self">下一页</a></li>
                    <div class="clr"> </div>
                </ul>-->
                <div class="page_url">
                <?=\yii\widgets\LinkPager::widget([
                        'pagination' => $pages,
                        'firstPageLabel' => '首页',
                        'nextPageLabel' => '下一页',
                        'prevPageLabel' => '上一页',
                        'lastPageLabel' => '最后一页',
                        'maxButtonCount' => 6,
                        'options' => ['class' => 'page'],
                ])?>
                </div>

        </div>
    </div>
</div>
<!-- 浮动窗口 -->
<div class="ms_posi">
    <ul>
        <li class="top"><a href="#top" target="_self"></a></li>
    </ul>
</div>

