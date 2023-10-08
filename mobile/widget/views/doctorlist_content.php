<?php
use yii\helpers\Html;
use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

?>
<div class=toptenReviews>
    <p class=plain>本榜单共录有<?= $totalCount; ?>位医生</p>
    <ul id="doc_list">
        <?php if(!empty($doctorlist)): ?>
        <?php foreach($doctorlist as $k=>$value):?>
            <?php $num = ($page-1)*20;;?>
            <li>
                <a href=" <?= Url::to(['/doctor/home', 'doctor_id' => ArrayHelper::getValue($value,'doctor_id')]); ?> ">
                    <div class=item>
                        <div class=detail>
                            <div class=detailImg>
                                <img src="<?= ArrayHelper::getValue($value,'doctor_avatar'); ?>" onerror="'https://u.nisiyacdn.com/avatar/default_2.jpg'" alt="<?= Html::encode(ArrayHelper::getValue($value,'doctor_realname')); ?> ">
                            </div>
                            <div class=detailContent>
                                <p class=detailDh> <?= Html::encode(ArrayHelper::getValue($value,'doctor_realname')); ?> </p>
                                <p class=actor>
                                    <?= Html::encode(ArrayHelper::getValue($value,'doctor_title')); ?> <?= Html::encode(ArrayHelper::getValue($value,'doctor_professional_title')) ; ?>
                                <div class=detailContentLabel>
                                    <div class="text_over1"><span><?= mb_substr(Html::encode(ArrayHelper::getValue($value,'doctor_hospital')),0,10); ?> </span><span> <?= Html::encode(ArrayHelper::getValue($value,'doctor_second_department_name')); ?></span></div>
                                </div>
                                <div class=detailContentComment>
                                    <p>10条评论 | </p>
                                    <strong class=stars><del style=width:82%;></del></strong>
                                </div>
                                <p> <?= Html::encode(ArrayHelper::getValue($value,'doctor_good_at')); ?> </p>
                            </div>
                        </div>
                        <div class=grade>
                            <p>评分：<span class=num>8.5</span></p>
                            <div class="ovH">
                            <?php if(ArrayHelper::getValue($value,'doctor_is_plus')){ ?>
                            <i>有号</i>
                            <?php } ?>
                            <?php if(ArrayHelper::getValue($value,'miao_doctor_id')){ ?>
                                <i>可咨询</i>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php /* <div class=serve>
                    <a href=# rel=nofollow>
                        <span class=servePhone></span>
                        <p>电话咨询</p>
                    </a>
                    <a href=# rel=nofollow>
                        <span class=serveConsult></span>
                        <p>图文咨询</p>
                    </a>
                    <a href=# rel=nofollow>
                        <span class=serveGuahao></span>
                        <p>预约挂号</p>
                    </a>
                </div> */?>
                <div class=rankNum>
                    <span><i> <?= ($num + $k + 1); ?> </i></span>
                </div>
            </li>
        <?php endforeach;?>
    </ul>
    <div class=loadingMore>
        加载更多
    </div>
    <div class="jb_no_data hide" id="nothing">已全部加载完成</div>
    <?php else:?>
        <input type="hidden" value="1" id="no_data">
    <?php endif;?>
</div>
