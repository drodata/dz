<?php

use drodata\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'title',
        [
            'attribute' => 'created_by',
            'value' => function ($model, $key, $index, $column) {
                return $model->creator->username;
            }
        ],
        'view_count',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('查看', ['/post/view', 'id' => $model->id]);
                },
            ],
        ],
    ],
]);
