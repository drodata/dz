<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email:email',
            'credit',
            //'status',
            //'group',
            [
                'attribute' => 'group',
                'value' => function ($model, $key, $index, $column) {
                    return $model->groupName($model->group);
                },
            ],
            'created_at:datetime',
            'last_logined_at:relativeTime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
