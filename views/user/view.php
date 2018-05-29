<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id, 'status' => $model->status], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id, 'status' => $model->status], [
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
            'id',
            'username',
            'email:email',
            'status',
            'group.name',
            [
                'label' => '总积分',
                'value' => $model->getCredits() . '(规则：credit1 * 2 + credit2 * 3 + credit3 + credit4)',
            ],
            'data.credit1',
            'data.credit2',
            'data.credit3',
            'data.credit4',
            'created_at',
            'updated_at',
            'last_logined_at',
        ],
    ]) ?>

</div>
