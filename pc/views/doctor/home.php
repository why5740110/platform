<?php
use \common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;

$this->registerCssFile( Url::getStaticUrl("css/doc_detail.min.css") );
$this->registerJsFile( Url::getStaticUrl("js/hos_detail.min.js") );

?>
<?php if (!empty($doctor_info)) : ?>

<div class="w1200">
    <div class="anchorNav">
        <ul>
            <a>当前位置：</a>
            <a href="/">首页</a>
            <?php if(isset($doctor_info['doctor_hospital_data']['name'])&&!empty($doctor_info['doctor_hospital_data']['name'])) : ?>
                <a href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($doctor_info['hospital_id'])]) ?>"><?php echo Html::encode($doctor_info['doctor_hospital_data']['name']); ?></a>
            <?php endif;?>
            <?php if(!empty($doctor_info['doctor_second_department_id'])) : ?>
            <a href="<?= Url::to(['/hospital/doctorlist/departments/0_0_'.$doctor_info['doctor_second_department_id'].'_1.html']) ?>"><?php echo $doctor_info['doctor_second_department_name'] ? Html::encode($doctor_info['doctor_second_department_name']) : ''; ?></a>
            <?php endif;?>
            <span><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?></span>
        </ul>
    </div>
    <div class="doctoeMains clr">
        <?php echo \pc\widget\DoctorInfoMenu::widget(['url'=>'overview','id'=>$doctor_info['doctor_id']??''])?>
        <div class="doctorDetail">
            <div class="doctorDetail_l">
                <img src="<?php echo $doctor_info['doctor_avatar']??''?>" alt="<?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?>">
            </div>
            <div class="doctorDetail_r">
                <div class="doctorName">
                    <h1><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?></h1>
                    <ul>
                        <?php if(!empty($doctor_info['doctor_title'])) : ?>
                            <li><?php echo Html::encode($doctor_info['doctor_title']); ?></li>
                        <?php endif;?>
                        <?php if(!empty($doctor_info['doctor_professional_title'])) : ?>
                        <li><?php echo Html::encode($doctor_info['doctor_professional_title']); ?></li>
                        <?php endif;?>
                    </ul>
                </div>
                <div class="doctortail">

                    <div class="hospitalocation ">
                        <p><a class="doctortailTitle">出诊地点：</a>
                            <a class="c14" target="_blank" href="<?= Url::to(['/hospital/index','hospital_id'=>HashUrl::getIdEncode($doctor_info['hospital_id']??'')]) ?>"><?php echo $doctor_info['doctor_hospital_data']['name']??''; ?></a>&nbsp;
                            <a class="c14" target="_blank" href="<?= Url::to(['/hospital/doctorlist/departments/0_0_'.ArrayHelper::getValue($doctor_info,'doctor_second_department_id').'_1.html']) ?>"><?php echo $doctor_info['doctor_second_department_name']??''; ?></a>
                        </p>
                        <?php /* <p class="js_hospitalocation" data-tar="1">其他8个执业点 <span>「展开」</span></p> */?>
                    </div>
                </div>
                <div class="doctortail">
                    <p class="doctorSkill "><a class="doctortailTitle">擅长领域：</a>
                        <?php echo $doctor_info['doctor_good_at'] ? Html::encode($doctor_info['doctor_good_at']) : ''; ?>
                    </p>
                    <?php /* <a href="" class="doctorDetailIntro" ref="nofollow">详细&gt;</a> */?>
                </div>
                <div class="doctortail">

                    <p class="doctorSkill doctorExp"><a class="doctortailTitle">执业经历：</a>
                       <?php echo CommonFunc::filterContent($doctor_info['doctor_profile'] ? Html::encode($doctor_info['doctor_profile']) : ''); ?>
                   </p>
                    <a href="<?= Url::to(['/doctor/intro','doctor_id'=>$doctor_info['doctor_id']??'']) ?>" class="doctorDetailIntro" ref="nofollow">详细介绍&gt;</a>
                </div>
               <?php /* <div class="otherHospital js_otherHospital">
                    <ul>
                        <a href=""><li>北京熙仁医院 眼科</li></a>
                        <a href="/"><li>北京熙仁医院 葡萄膜炎</li></a>
                        <a href="/"><li>北京熙仁医院 黄斑病变</li></a>
                        <a href="/"><li>北京熙仁医院 玻璃体疾病</li></a>
                        <a href="/"><li>北京熙仁医院 眼底出血</li></a>
                        <a href="/"><li>北京熙仁医院 视网膜脱离</li></a>
                        <a href="/"><li>北京熙仁医院 眼底病科</li></a>
                        <a href="/"><li>北京熙仁医院 眼外伤科</li></a>
                    </ul>
                    <div class="closePopul" >
                        <span>「收起」</span>
                    </div>
                </div> */?>
            </div>
            <div class="clr"></div>
        </div>
    </div>
    <!-- 服务推荐 -->
    <?php echo \pc\widget\DoctorService::widget(['doctor_info'=>$doctor_info])?>
    <div class="doctoeMids">
        <h3 class="title"><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : ''; ?> 出诊时间/挂号费用</h3>
        <div class="visitingTime">
            <div class="newVisiTime" >查看最新时间表</div>
        </div>
        <table cellspacing="0" cellpadding="0"  width="100%" class="docoutcallMain">
            <thead>
            <th>日期</th>
            <th>星期一</th>
            <th>星期二</th>
            <th>星期三</th>
            <th>星期四</th>
            <th>星期五</th>
            <th>星期六</th>
            <th>星期日</th>
            </thead>
            <tbody>
            <tr>
                <td>上午</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>下午</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>夜间</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="doctoeMids">
        <h3 class="title"><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : '' ?> 回复的患友咨询</h3>
        <div class="visitingTime">
            <div class="newVisiTime">我要咨询</div>
        </div>
        <div class="otherConsultMain">
            <div class="kong">暂无咨询</div>
        </div>
    </div>
    <div class="doctoeMids">
        <h3 class="title"><?php echo $doctor_info['doctor_realname'] ? Html::encode($doctor_info['doctor_realname']) : '' ?>的患友评价</h3>
        <div class="visitingTime">
            <div class="newVisiTime">我要评价</div>
        </div>
        <div class="otherConsultMain">
            <dl class="evaluateTypes">
                <dd class="selEvaluateTypes">全部（0）</dd>
                <dd>非常满意（0）</dd>
                <dd>满意（0）</dd>
                <dd>一般（0）</dd>
                <dd>不满意（0）</dd>
            </dl>
        </div>
    </div>
</div>
<?php endif;?>
