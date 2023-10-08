<?php
use dmstr\widgets\Alert;

?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php
if ($this->title) {
    ?>
            <section class="content-header">
                <h1>
                    <?php echo $this->title; ?>
                </h1>

            </section>
            <?php
}
?>

    <section class="content">
        <?=Alert::widget()?>
        <?=$content?>
    </section>
</div>

<footer class="main-footer">
     <audio id="chatAudio" style="display:block" ><source src="/audio/notify.wav" type="audio/mpeg"><source src="/audio/notify.wav" type="audio/wav"></audio>
    <script type="text/javascript">
        function showLoad() {
            return layer.msg('拼命执行中...', {icon: 16,shade: [0.5, '#f5f5f5'],scrollbar: false,offset: 'auto', time:20000});
        };
        function dovoice() {
          $('#chatAudio')[0].play(); //播放声音
        };
    </script>
    <div class="pull-right hidden-xs">
        <b>Version</b> 1.0
    </div>
    <strong>Copyright © 2015-<?php echo date('Y'); ?>.</strong>  王氏集团股份有限公司版权所有
</footer>