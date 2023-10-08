<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\minying\MinAccountModel */

$this->title = '创建账号';
?>
<div class="account-model-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
