<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Forum */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Forums', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forum-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('新建帖子', ['/post/create', 'forum_id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= $this->render('/post/_grid', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
    ]) ?>
</div>
