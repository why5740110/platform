<?php
/**
 * @file keshilist.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/10/10
 */

use common\helpers\Url;
use common\libs\HashUrl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->registerCssFile(Url::getStaticUrl("css/department.css"));
$this->registerJsFile(Url::getStaticUrl("js/department.js"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/hospital_department_list/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/hospital_department_list/index.js'),['depends'=>'mobile\assets\AppAsset']);

$this->title = '选择科室';
if (\Yii::$app->controller->getUserAgent() == 'patient') {
    $this->context->seoTitle = $this->title;
}

?>

<div class="main_wrapper hospital_department_list">
    <div class="hdl_top">
        <a class="list_item_wrap" href="<?= rtrim(\Yii::$app->params['domains']['mobile'], '/').'/hospital/hospital_'.HashUrl::getIdEncode($hospital_id).'.html'; ?>">
            <div class="hos_logo">
                <img src="<?= ArrayHelper::getValue($data, 'logo', '') ?: ArrayHelper::getValue($data, 'photo') ?>"
                     onerror="javascript:this.src='https://public-nisiya.oss-cn-shanghai.aliyuncs.com/hospital/hospital_default.jpg';"
                     alt="<?= Html::encode(ArrayHelper::getValue($data, 'name')) ?>">
            </div>
            <div class="hos_item">
                <h3 class="hos_name text_wrap"><?= Html::encode(ArrayHelper::getValue($data, 'name')) ?></h3>
                <div class="hos_tags">
                    <?php if (ArrayHelper::getValue($data, 'level')): ?>
                        <span class="tags t_style01 t_short"><?= ArrayHelper::getValue($data, 'level') ?></span>
                    <?php endif; ?>
                    <?php if (ArrayHelper::getValue($data, 'kind')): ?>
                        <span class="tags t_style01 t_short"><?= ArrayHelper::getValue($data, 'kind') ?></span>
                    <?php endif; ?>
                    <?php if (ArrayHelper::getValue($data, 'type')): ?>
                        <span class="tags t_style01 t_short"><?= ArrayHelper::getValue($data, 'type') ?></span>
                    <?php endif; ?>
                </div>
                <p class="hos_address text_wrap">
                    地址：<?= Html::encode(ArrayHelper::getValue($data, 'address', ''))?>
                </p>
            </div>
            <div class="hos_icon">
                <img src="<?=url::to('@staticTwo/pages/component/img/icon_arrow_right02.png')?>" />
            </div>
        </a>
    </div>
    <?php if ($sub) : ?>
        <h3 class="hdl_title">科室列表</h3>
        <div class="classificationList">
            <ul class="populMainSel">
                <?php $i = 0;foreach ($sub as $v): ?>
                <li class="getkeshi <?php if ($i == 0) : ?>dep_active<?php endif; ?>"><s></s><?= Html::encode(ArrayHelper::getValue($v, 'frist_department_name')) ?></li>
                <?php $i++; endforeach; ?>
            </ul>
            <div class="populMainTexth">
                <?php $i = 0; foreach ($sub as $secondSub) { ?>
                <ul class="populMainText" <?php if ($i != 0) : ?>style="display: none"<?php endif; ?>>
                    <?php
                    $j = 0;
                    if (isset($secondSub['second_arr']) && is_array($secondSub['second_arr'])) {
                        foreach ($secondSub['second_arr'] as $l) {
                        ?>
                            <li><a href="<?= Url::to(['guahao/doclist', 'hospital_id' => $hospital_id, 'tp_department_id' => $l['id']]) ?>"><?= Html::encode(ArrayHelper::getValue($l, 'second_department_name')) ?></a></li>
                        <?php $j++;
                        }
                    } ?>
                </ul>
                <?php $i++; } ?>
            </div>
        </div>
        <!-- 挂号须知 -->
<!--        <div class="register_tips">-->
<!--            <div class="rt_con">-->
<!--                <h4>挂号须知</h4>-->
<!--                <p>-->
<!--                    1、初诊和未携带门急诊手册的患者需先在填表处填写就诊信息登记表后再挂号。-->
<!--                </p>-->
<!--                <p>2、没有门急诊手册的患者挂号时必须购买门急诊手册，否则无法看病。</p>-->
<!--                <p>-->
<!--                    3、医保患者须持医保卡和门急诊手册挂号，缺一不可。医保代开药的患者须拿双方有效身份证件，如双方户口不在一起的必须到社区或街道办事处办理关系证明方可挂号就诊。-->
<!--                </p>-->
<!--                <p>4、门急诊患者更改姓名</p>-->
<!--                <p>（1）未发生费用的，可持有效身份证件到挂号窗口直接办理；</p>-->
<!--                <p>-->
<!--                    （2）已发生费用的，需持有效身份证件办理相关手续后再到挂号窗口办理。-->
<!--                </p>-->
<!--                <div class="rt_but">确定</div>-->
<!--            </div>-->
<!--        </div>-->
    <?php endif; ?>
    <div class="sr_nothing" <?php if ($sub) : ?>style="display: none;"<?php endif; ?>>
        <img src="<?=url::to('@staticTwo/pages/component/img/search_page_img01.png')?>" />
        <p>未找到相关内容</p>
    </div>
</div>
