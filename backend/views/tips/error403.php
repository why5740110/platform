<?php

use yii\helpers\Html;

$this->title = '403 当前用户无权限,请联系管理员';
?>

<section class="content">
    <div class="error-page">
        <h2 class="headline text-yellow"> 403</h2>

        <div class="error-content" style="padding-top: 15px">
            <h3><i class="fa fa-warning text-yellow"></i><?= nl2br(Html::encode($message)) ?></h3>
            <p>
                当前用户无权限,请联系管理员!!!请确认是否有此权限!!!！ <a href='<?= $redirect ?>'>返回</a>
            </p>
        </div>
    </div>
</section>
