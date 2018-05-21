<?php

/* @var $this yii\web\View */
use app\models\User;

$this->title = 'My Yii Application';
echo Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username;
?>
