<?php

namespace frontend\modules\auth\services;

use common\modules\users\models\User;
use frontend\modules\auth\forms\LoginForm;
use Yii;

class AuthService
{
    public function login(LoginForm $form): bool
    {
        if (!$form->validate()) {
            return false;
        }

        $user = User::findByEmail((string) $form->email);

        if ($user === null || !$user->validatePassword((string) $form->password)) {
            $form->addError('password', 'Invalid email or password.');
            return false;
        }

        $duration = $form->rememberMe ? 3600 * 24 * 30 : 0;

        return Yii::$app->user->login($user, $duration);
    }

    public function logout(): void
    {
        Yii::$app->user->logout();
    }
}
