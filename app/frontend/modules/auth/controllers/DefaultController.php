<?php

namespace frontend\modules\auth\controllers;

use frontend\modules\auth\forms\LoginForm;
use frontend\modules\auth\services\AuthService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    private AuthService $authService;

    public function __construct($id, $module, AuthService $authService = null, $config = [])
    {
        $this->authService = $authService ?? new AuthService();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/tasks/default/index']);
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $this->authService->login($model)) {
            return $this->redirect(['/tasks/default/index']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout(): Response
    {
        $this->authService->logout();

        return $this->redirect(['/auth/default/login']);
    }
}
