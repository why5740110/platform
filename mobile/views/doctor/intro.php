<?php

use \common\helpers\Url;
use common\libs\CommonFunc;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;


$this->title = ArrayHelper::getValue($doctor_info,'doctor_realname','');
$this->registerCssFile(Url::getStaticUrl("css/doctor_index.css"));
$this->registerJsFile(Url::getStaticUrl("js/doctor_index.js"));

?>
<?php if (!empty($doctor_info)): ?>
    <div class="doctor_index">
        <div class="doctor_index_top">
          <dl>
            <dt>
                <img src="<?= ArrayHelper::getValue($doctor_info,'doctor_avatar') ?>"
                         onerror="javascript:this.src='https://u.nisiyacdn.com/avatar/default_2.jpg';"
                         alt="<?= ArrayHelper::getValue($doctor_info,'doctor_realname'); ?>">
            </dt>
            <dd>
              <h4><?=ArrayHelper::getValue($doctor_info,'doctor_realname');?><span><?=ArrayHelper::getValue($doctor_info,'doctor_title','');?></span><span><?= ArrayHelper::getValue($doctor_info,'doctor_second_department_name','');?></span></h4>
              <p><i></i><span><?= ArrayHelper::getValue($doctor_info,'doctor_hospital_data.name'); ?></span></p>
            </dd>
          </dl>
        </div>
        <div class="doctor_index_con">
          <div>
            <h4>医生擅长</h4>
            <p><?= ArrayHelper::getValue($doctor_info,'doctor_good_at'); ?></p>
          </div>
          <div>
            <h4>个人简介</h4>
            <p><?= CommonFunc::filterContent(ArrayHelper::getValue($doctor_info,'doctor_profile')); ?></p>
          </div>
        </div>
    </div>

<?php endif;?>
<!-- <?php echo \mobile\widget\Menu::widget([]);?> -->