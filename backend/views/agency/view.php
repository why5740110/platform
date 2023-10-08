<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\minying\MinAgencyModel */

$this->title = $model->agency_id;
$this->params['breadcrumbs'][] = ['label' => 'Min Agency Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="min-agency-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->agency_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->agency_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'agency_id',
            'agency_name',
            'contact_mobile',
            'contact_name',
            'min_created_time:datetime',
            'min_update_time:datetime',
            'min_admin_id',
            'min_admin_name',
        ],
    ]) ?>

</div>
