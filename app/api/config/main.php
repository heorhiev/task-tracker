<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'name' => 'Task Tracker API',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'moduleBootstrap'],
    'controllerNamespace' => 'api\\controllers',
    'defaultRoute' => 'telegramBot/default/index',
    'modules' => [
        'telegramBot' => [
            'class' => \api\modules\telegramBot\Module::class,
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
        ],
        'user' => [
            'identityClass' => \common\modules\users\models\User::class,
            'enableSession' => false,
        ],
        'session' => [
            'class' => \yii\web\Session::class,
            'name' => 'advanced-api',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
    ],
    'params' => $params,
];
