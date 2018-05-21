<?php
use drodata\helpers\Html;
?>

<?= Html::a(Html::encode($model->name), ['view', 'id' => $model->id]) ?> (帖子数： <?= $model->post_count ?>)
<hr>
