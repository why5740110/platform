<?php
/**
 * 妙健康增加医生列表
 * @file Menu.php
 * @authoryangquanliang <yangquanliang@yuanxin-inc.com>
 * @version 1.0
 * @date 2020/11/03
 */

namespace mobile\widget;


use yii\base\Widget;
use yii\helpers\ArrayHelper;

class MiaojkDoctorWidget extends Widget
{
    public function run()
    {
        $html = <<<EOF
        <div class="doc_all_con_box">
            <div class="doc_con_box">
                <a href="/hospital/pihs.html" class="dflex">
                    <div class="hosp_img"><img src="https://u.nisiyacdn.com/avatar/006/86/94/006869485_mid.jpg" alt=""></div>
                    <div class="hosp_con_c flex1 ">
                        <p class="hosp_name text_over1"><span class="doc_name">杨毅</span>主任医师</p>
                        <p class="sanji_p">北京协和医院<span class="hui_jian"></span>妇科</p>
                        <p class="jieshao_p text_over2">擅长：HPV感染诊断与治疗；宫颈疾病：光动力无创治疗；外阴白斑。</p>
                    </div>
                </a>
            </div>
            <div class="doc_con_box">
                <a href="/hospital/pihs.html" class="dflex">
                    <div class="hosp_img"><img src="https://u.nisiyacdn.com/avatar/004/12/65/004126534_mid.jpg" alt=""></div>
                    <div class="hosp_con_c flex1 ">
                        <p class="hosp_name text_over1"><span class="doc_name">罗明</span>主任医师</p>
                        <p class="sanji_p">上海同济医院<span class="hui_jian"></span>心血管内科</p>
                        <p class="jieshao_p text_over2">擅长：高血压，包括顽固性高血压；慢性心力衰竭；冠心病的综合治疗。高脂血症，心肌炎，心肌病。心源性焦虑。各种心律失常。</p>
                    </div>
                </a>
            </div>
        </div>
EOF;
        return $html;
    }

}


