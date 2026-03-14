<?php

return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['moduleBootstrap'],
    'components' => [
        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => 'common\models\User',
        ],
    ],
];
