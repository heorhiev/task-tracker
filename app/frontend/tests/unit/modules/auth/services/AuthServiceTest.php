<?php

namespace frontend\tests\unit\modules\auth\services;

use common\modules\users\models\User;
use frontend\modules\auth\forms\LoginForm;
use frontend\modules\auth\services\AuthService;
use Yii;

class AuthServiceTest extends \Codeception\Test\Unit
{
    private AuthService $authService;

    protected function _before(): void
    {
        $this->authService = new AuthService();
        Yii::$app->user->logout();

        User::deleteAll(['email' => 'auth_test@example.com']);

        $user = new User([
            'email' => 'auth_test@example.com',
            'role' => User::ROLE_USER,
        ]);
        $user->setPassword('Secret123!');
        $this->assertTrue($user->save());
    }

    protected function _after(): void
    {
        Yii::$app->user->logout();
        User::deleteAll(['email' => 'auth_test@example.com']);
    }

    public function testLoginSuccess(): void
    {
        $form = new LoginForm([
            'email' => 'auth_test@example.com',
            'password' => 'Secret123!',
            'rememberMe' => false,
        ]);

        $result = $this->authService->login($form);

        $this->assertTrue($result);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertSame('auth_test@example.com', Yii::$app->user->identity->email);
    }

    public function testLoginFailWithWrongPassword(): void
    {
        $form = new LoginForm([
            'email' => 'auth_test@example.com',
            'password' => 'WrongPass!',
            'rememberMe' => false,
        ]);

        $result = $this->authService->login($form);

        $this->assertFalse($result);
        $this->assertTrue(Yii::$app->user->isGuest);
        $this->assertArrayHasKey('password', $form->getErrors());
    }
}
