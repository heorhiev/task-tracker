<?php

namespace api\services;

use common\modules\users\models\User;
use Yii;
use yii\web\UnauthorizedHttpException;

class ApiKeyAuthService
{
    public function authenticateFromRequest(): User
    {
        $apiKey = Yii::$app->request->get('api-key');

        if (!is_string($apiKey) || $apiKey === '') {
            throw new UnauthorizedHttpException('Missing api-key.');
        }

        $user = User::findByApiKey($apiKey);

        if ($user === null) {
            throw new UnauthorizedHttpException('Invalid api-key.');
        }

        Yii::$app->user->setIdentity($user);

        return $user;
    }
}
