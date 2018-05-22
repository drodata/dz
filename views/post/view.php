<?php

use drodata\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Post */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="h6 text-info">wrote on <?= Yii::$app->formatter->asDateTime($model->created_at) ?> by <?= $model->creator->username ?></p>

    <p><?= Html::encode($model->content) ?></p>

    <p>
        <?= Html::a('点赞', ['favorite', 'id' => $model->id], [
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
    </p>

</div>
