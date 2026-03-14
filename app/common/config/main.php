<?php

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'speechToText' => [
            'class' => \common\components\SpeechToTextComponent::class,
            'provider' => 'openai',
            'apiKey' => getenv('OPENAI_API_KEY') ?: '',
            'model' => getenv('OPENAI_STT_MODEL') ?: 'gpt-4o-mini-transcribe',
            'timeout' => (int) (getenv('OPENAI_STT_TIMEOUT') ?: 120),
            'prompt' => getenv('OPENAI_STT_PROMPT') ?: null,
            'baseUrl' => getenv('OPENAI_STT_BASE_URL') ?: 'https://api.openai.com/v1',
        ],
        'telegram' => [
            'class' => \common\components\TelegramComponent::class,
            'botToken' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        ],
    ],
];
