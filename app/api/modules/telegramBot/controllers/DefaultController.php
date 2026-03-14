<?php

namespace api\modules\telegramBot\controllers;

use api\services\ApiKeyAuthService;
use api\modules\telegramBot\services\TelegramIncomingMessageService;
use api\modules\telegramBot\services\TelegramVoiceInboxService;
use common\modules\tasks\services\command\TaskCommandExecutor;
use common\modules\tasks\services\command\TaskCommandParser;
use Yii;
use yii\web\Response;

class DefaultController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    private ApiKeyAuthService $apiKeyAuthService;
    private TelegramIncomingMessageService $telegramIncomingMessageService;
    private TaskCommandParser $taskCommandParser;
    private TaskCommandExecutor $taskCommandExecutor;
    private TelegramVoiceInboxService $telegramVoiceInboxService;

    public function __construct(
        $id,
        $module,
        ApiKeyAuthService $apiKeyAuthService = null,
        TelegramIncomingMessageService $telegramIncomingMessageService = null,
        TaskCommandParser $taskCommandParser = null,
        TaskCommandExecutor $taskCommandExecutor = null,
        TelegramVoiceInboxService $telegramVoiceInboxService = null,
        $config = []
    )
    {
        $this->apiKeyAuthService = $apiKeyAuthService ?? new ApiKeyAuthService();
        $this->telegramIncomingMessageService = $telegramIncomingMessageService ?? new TelegramIncomingMessageService();
        $this->taskCommandParser = $taskCommandParser ?? new TaskCommandParser();
        $this->taskCommandExecutor = $taskCommandExecutor ?? new TaskCommandExecutor();
        $this->telegramVoiceInboxService = $telegramVoiceInboxService ?? new TelegramVoiceInboxService();
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
        $incomingMessage = $this->telegramIncomingMessageService->resolve($update);
        $chatId = $incomingMessage['chatId'];
        $text = $incomingMessage['text'];

        if ($incomingMessage['type'] === TelegramIncomingMessageService::TYPE_TEXT && $telegram->isStartCommand($text)) {
            if ($chatId !== null) {
                $telegram->sendMessage($chatId, 'hello world');
            }
        } elseif ($incomingMessage['type'] === TelegramIncomingMessageService::TYPE_TEXT && $text !== '') {
            $commandData = $this->taskCommandParser->parse($text);
            $messageText = $this->taskCommandExecutor->execute($commandData, $chatId);

            if ($chatId !== null) {
                $telegram->sendMessage($chatId, $messageText);
            }
        } elseif ($incomingMessage['type'] === TelegramIncomingMessageService::TYPE_VOICE) {
            $inboxMessage = $this->telegramVoiceInboxService->storeIncomingVoice($telegram, $incomingMessage);

            if ($chatId !== null) {
                $telegram->sendMessage(
                    $chatId,
                    $inboxMessage !== null ? 'Voice message queued for processing' : 'Voice message could not be saved'
                );
            }
        }

        return [
            'ok' => true,
            'received' => $incomingMessage['type'],
        ];
    }
}
