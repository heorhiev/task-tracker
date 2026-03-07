<?php

namespace api\tests\unit\services;

use api\services\ApiKeyAuthService;
use common\modules\users\models\User;
use Yii;
use yii\web\UnauthorizedHttpException;

class ApiKeyAuthServiceTest extends \Codeception\Test\Unit
{
    private const TEST_EMAIL = 'api_auth_test@example.com';
    private const TEST_API_KEY = 'test_api_key_1234567890';

    private ApiKeyAuthService $service;

    protected function _before(): void
    {
        $this->service = new ApiKeyAuthService();

        Yii::$app->user->logout();
        Yii::$app->request->setQueryParams([]);

        User::deleteAll(['email' => self::TEST_EMAIL]);

        $user = new User([
            'email' => self::TEST_EMAIL,
            'role' => User::ROLE_USER,
            'api_key' => self::TEST_API_KEY,
        ]);
        $user->setPassword('Secret123!');
        $this->assertTrue($user->save());
    }

    protected function _after(): void
    {
        Yii::$app->user->logout();
        Yii::$app->request->setQueryParams([]);
        User::deleteAll(['email' => self::TEST_EMAIL]);
    }

    public function testAuthenticateFromRequestSuccess(): void
    {
        Yii::$app->request->setQueryParams(['api-key' => self::TEST_API_KEY]);

        $user = $this->service->authenticateFromRequest();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(self::TEST_EMAIL, $user->email);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertSame(self::TEST_EMAIL, Yii::$app->user->identity->email);
    }

    public function testAuthenticateFromRequestMissingKeyThrows(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing api-key.');

        Yii::$app->request->setQueryParams([]);
        $this->service->authenticateFromRequest();
    }

    public function testAuthenticateFromRequestInvalidKeyThrows(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid api-key.');

        Yii::$app->request->setQueryParams(['api-key' => 'wrong_key']);
        $this->service->authenticateFromRequest();
    }
}
