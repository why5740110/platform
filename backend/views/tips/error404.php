<?php

use yii\helpers\Html;

$this->title = '404 Page not found';
?>

<section class="content">
    <div class="error-page">
        <h2 class="headline text-yellow"> 404</h2>

        <div class="error-content" style="padding-top: 15px">
            <h3><i class="fa fa-warning text-yellow"></i><?= nl2br(Html::encode($message)) ?></h3>
            <p>
                我们找不到您要查找的页面. 请确认是否有此权限！2 <a href='<?= $redirect ?>'>返回</a>
            </p>
        </div>
    </div>
</section>
