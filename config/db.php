<?php
$sensitive = json_decode(file_get_contents(__DIR__ . '/sensitive.json'));

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=dz',
    'username' => 'root',
    'password' => $sensitive->password,
    'charset' => 'utf8',
];
