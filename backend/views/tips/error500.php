<?php

use yii\helpers\Html;


$this->title = '500 Error';
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-red">500</h2>

        <div class="error-content" style="padding-top: 15px">
            <h3><i class="fa fa-warning text-red"></i> <?= nl2br(Html::encode($message)) ?> </h3>

            <p>
            我们将立即努力解决这个问题！ <a href='<?= $redirect ?>'>返回</a>
            </p>

        </div>
    </div>

</section>
