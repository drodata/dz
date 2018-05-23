<?php

/* @var $model app\models\PostComment */
use drodata\helpers\Html;
?>

<p><?= $model->creator->username ?>于<?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>说道：</p>
<p><?= $model->comment->content ?></p>
<hr>
