<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PostComment */

$this->title = 'Create Post Comment';
$this->params['breadcrumbs'][] = ['label' => 'Post Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-comment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
