<?php
/**
 * @file orther_hospital.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/31
 */

use common\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php
    if(isset($data) && is_array($data) && $data ){
?>
<div class="serviceRecommen">
    <h3>同区域同类别医院</h3>
    <ul class="similarHospital">
        <?php
            $num = 0;
            foreach($data as $k=>$v){

                //排除本身
                if($hospital_id ==ArrayHelper::getValue($v,'hospital_id')){
                    continue;
                }
                $num++;
        ?>
        <a href="<?=Url::to(['hospital/index','hospital_id'=>ArrayHelper::getValue($v,'hospital_id')])?>" onclick="">
            <div class="similarHospitalImg">
                <img src="<?=ArrayHelper::getValue($v,'hospital_photo')?>" onerror="" alt="">
                <dl>
                    <dd><?=ArrayHelper::getValue($v,'hospital_level')?></dd>
                    <dd><?=ArrayHelper::getValue($v,'hospital_type')?></dd>
                </dl>
            </div>
            <p class="similarHospitalText"><?=Html::encode(ArrayHelper::getValue($v,'hospital_name'))?></p>
            <p class="similarHospitalTextAddress">地址：<?=Html::encode(ArrayHelper::getValue($v,'hospital_address'))?></p>
        </a>
        <?php
                if($num>=5){
                    break;
                }
            }
        ?>
    </ul>
</div>
<?php } ?>