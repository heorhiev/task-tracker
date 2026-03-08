<?php

namespace api\modules\telegramBot\controllers;

use api\services\ApiKeyAuthService;
use api\modules\telegramBot\services\TelegramCommandHandlerService;
use api\modules\telegramBot\services\TelegramTaskResolverService;
use Yii;
use yii\web\Response;

class DefaultController extends \yii\web\Controller
{
    private ApiKeyAuthService $apiKeyAuthService;
    private TelegramTaskResolverService $telegramTaskResolverService;
    private TelegramCommandHandlerService $telegramCommandHandlerService;

    public function __construct(
        $id,
        $module,
        ApiKeyAuthService $apiKeyAuthService = null,
        TelegramTaskResolverService $telegramTaskResolverService = null,
        TelegramCommandHandlerService $telegramCommandHandlerService = null,
        $config = []
    )
    {
        $this->apiKeyAuthService = $apiKeyAuthService ?? new ApiKeyAuthService();
        $this->telegramTaskResolverService = $telegramTaskResolverService ?? new TelegramTaskResolverService();
        $this->telegramCommandHandlerService = $telegramCommandHandlerService ?? new TelegramCommandHandlerService();
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
            $commandData = $this->telegramTaskResolverService->resolve($text);
            $messageText = $this->telegramCommandHandlerService->handle($commandData, $chatId);

            if ($chatId !== null) {
                $telegram->sendMessage($chatId, $messageText);
            }
        }

        return ['ok' => true];
    }
}
