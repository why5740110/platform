<?php

use yii\helpers\Html;

$this->title = '系统提示';
?>

<section class="content">
<?php if ($type == 'failed') : ?>
<div class="callout callout-danger">
          <h4><?php echo $content; ?></h4>

          <p>系统自动跳转在  <span class="time" id="time"><?php echo $timeout; ?></span>  秒后，如果不想等待，<a  href="<?php echo $redirect ?>" target="_self" >点击这里跳转</a></p>
        </div>
<?php else : ?>
<div class="callout callout-success">
          <h4><?php echo $content; ?></h4>

          <p>系统自动跳转在  <span class="time" id="time"><?php echo $timeout; ?></span>  秒后，如果不想等待，<a  href="<?php echo $redirect ?>" target="_self" >点击这里跳转</a></p>
        </div>
<?php endif; ?>
</section>
<script type="text/javascript">

   function delayURL() {
       var delay = document.getElementById("time").innerHTML;
       if(delay > 0){
            delay--;
            document.getElementById("time").innerHTML = delay;
            window.setTimeout(function(){delayURL()},1000);
       } else {
            window.location.href = '<?php echo $redirect ?>';
       }

   }

   delayURL();

</script>
