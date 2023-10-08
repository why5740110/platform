<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\minying\MinAccountModel */

$this->title = '编辑账号';
?>
<div class="account-model-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
