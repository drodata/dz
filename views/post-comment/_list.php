<?php
echo yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'summary' => '',
    'emptyText' => '暂无评论',
    'itemView' => '_list-item',
]);
