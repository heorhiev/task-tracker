<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class HealthController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action): bool
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    public function actionWebhook(): array
    {
        return [
            'ok' => true,
            'service' => 'api',
            'endpoint' => 'health/webhook',
            'time' => gmdate('c'),
        ];
    }
}
