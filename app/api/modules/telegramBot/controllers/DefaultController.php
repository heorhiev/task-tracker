<?php

namespace api\modules\telegramBot\controllers;

use api\services\ApiKeyAuthService;
use common\models\forms\TaskCreateForm;
use common\modules\tasks\services\TaskService;
use Yii;
use yii\web\Response;

class DefaultController extends \yii\web\Controller
{
    private ApiKeyAuthService $apiKeyAuthService;
    private TaskService $taskService;

    public function __construct($id, $module, ApiKeyAuthService $apiKeyAuthService = null, TaskService $taskService = null, $config = [])
    {
        $this->apiKeyAuthService = $apiKeyAuthService ?? new ApiKeyAuthService();
        $this->taskService = $taskService ?? new TaskService();
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action): bool
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->apiKeyAuthService->authenticateFromRequest();

        return parent::beforeAction($action);
    }

    public function actionIndex(): array
    {
        /** @var \common\components\TelegramComponent $telegram */
        $telegram = Yii::$app->telegram;

        if (!$telegram->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'telegram bot token is not configured',
            ];
        }

        $update = $telegram->getWebhookUpdate();

        $message = $update->getMessage();
        $text = trim((string) $message->get('text', ''));
        $chatId = $update->getChat()->get('id');

        if ($telegram->isStartCommand($text)) {
            if ($chatId !== null) {
                $telegram->sendMessage($chatId, 'hello world');
            }
        } elseif ($text !== '') {
            $form = new TaskCreateForm([
                'title' => $text,
                'description' => $chatId !== null ? 'Created from Telegram chat ' . $chatId : 'Created from Telegram',
            ]);

            $task = $this->taskService->create($form);

            if ($chatId !== null) {
                $telegram->sendMessage(
                    $chatId,
                    $task !== null
                        ? 'Task created: #' . $task->id
                        : 'Task creation failed'
                );
            }
        }

        return ['ok' => true];
    }
}
