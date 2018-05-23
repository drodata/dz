<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PostComment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-comment-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'post_id')->textInput() ?>

    <?= $form->field($model, 'comment_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
