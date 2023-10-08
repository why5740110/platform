<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->context->seoTitle = '404';
?>

<div class="text-center" style="margin-top: 60px;margin-bottom: 60px;">
    <h1 style="font-size:50px;">404! <?= Html::encode($message)?></h1>
</div>