<?php
/**
 * @file detail.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/8/11
 */


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\helpers\Url;
use common\libs\CommonFunc;
use mobile\widget\Menu;

$this->registerCssFile(Url::getStaticUrl("css/hospital_detail.css"));
$this->registerJsFile(Url::getStaticUrl("js/hospital_detail.js"));

?>

    <header>
    <?php if(\Yii::$app->controller->getUserAgent() != 'patient' && \Yii::$app->controller->getUserAgent() != 'mini'){ ?>
        <span class=goHistory onclick="window.history.go(-1);" ></span>
    <?php } ?>
        <div class="lineHeader" style="margin: 0 auto;">
            <a href=#detaillink class="lineHeaderActive addKey2">详情</a>
            <a href=#address>地址/交通</a>
            <a href=#phone class=addKey4>联系电话</a>
            <!--<a href=#Marquee class=addKey3>咨询</a>-->
        </div>
        <span></span>
    </header>

    <div class="descriptionContent descriptionContentPd" id=detaillink>
        <div class=hospitalText>
            <p></p><h1><?=Html::encode(ArrayHelper::getValue($data,'name'))?></h1><p></p>

            <?php
            if(ArrayHelper::getValue($data,'nick_name')){
                ?>
                <p>简称/别称： <?=Html::encode(ArrayHelper::getValue($data,'name'))?></p>
            <?php } ?>
            <p>类型/级别：<?=ArrayHelper::getValue($data,'type')?> <?=ArrayHelper::getValue($data,'level')?></p>

        </div>
        <div class=introducs>
            <p class=skill>详细介绍：<?=trim(Html::encode(CommonFunc::filterContent(ArrayHelper::getValue($data,'description'))))?></p>

        </div>

        <!--<div class=nowService id=Marquee>

            <ul class=nowServiceList>

                <a class="categorys categorysfast_consult" href="<?/*=ArrayHelper::getValue(\Yii::$app->params,'domains.ihs')*/?>case/quiz?from=mfzx">
                    <div class="categorysTitle categorysTitlefast_consult">
                        <span class="icon icon01"></span>
                        <div class=categorysName>
                            <h3>极速咨询</h3>
                            <p>急病找三甲，省时又省心<br>13万+三甲医生，3分钟回复</p>

                        </div>
                    </div>
                    <span class=categorysLink2>去提问</span>
                </a>
            </ul>
            <ul class=nowServiceList>
                <a class="categorys categorysguahao" href="<?/*=ArrayHelper::getValue(\Yii::$app->params,'domains.ihs')*/?>doctorlist/graphic" data-link=#>
                    <div class="categorysTitle categorysTitleguahao">
                        <span class="icon icon02"></span>
                        <div class=categorysName>
                            <h3>名医电话</h3>
                            <p>一个电话  常见病情  快速诊断</p>

                        </div>
                    </div>
                    <span class=categorysLink2>预约通话</span>
                </a>
                <a class="categorys categorysfast_consult" href="<?/*=ArrayHelper::getValue(\Yii::$app->params,'domains.ihs')*/?>doctorlist/graphic">
                    <div class="categorysTitle categorysTitlefast_consult">
                        <span class="icon icon03"></span>
                        <div class=categorysName>
                            <h3>图文咨询</h3>
                            <p>名医在线1对1，病情交流更清晰</p>
                            <i>在线咨询问疾病</i>
                        </div>
                    </div>
                    <span class=categorysLink2>立即咨询</span>
                </a>
            </ul>
        </div>-->

        <div class=hospitalAddressPd id=consultlink>
            <ul class=hospitalAddress id=address>
                <li>
                    <p>地址：<?=Html::encode(ArrayHelper::getValue($data,'address'))?></p>
                    <a><span></span></a>
                </li>
                <li>
                    <p>乘车路线：<?=Html::encode(ArrayHelper::getValue($data,'routes'))?></p>
                </li>
                <li onclick="">
                    <p id=phone>医院电话：<?=Html::encode(ArrayHelper::getValue($data,'phone'))?></p>
                    <span></span>        </li>

            </ul>
        </div>

    </div>


