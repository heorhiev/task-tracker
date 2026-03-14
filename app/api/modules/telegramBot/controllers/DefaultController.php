<?php

namespace api\modules\telegramBot\controllers;

use api\services\ApiKeyAuthService;
use common\components\CommandRegistryComponent;
use common\services\commands\CommandInterface;
use api\modules\telegramBot\services\TelegramIncomingMessageService;
use api\modules\telegramBot\services\TelegramVoiceInboxService;
use Yii;
use yii\web\Response;

class DefaultController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    private ApiKeyAuthService $apiKeyAuthService;
    private CommandRegistryComponent $commandRegistry;
    private TelegramIncomingMessageService $telegramIncomingMessageService;
    private TelegramVoiceInboxService $telegramVoiceInboxService;

    public function __construct(
        $id,
        $module,
        ApiKeyAuthService $apiKeyAuthService = null,
        TelegramIncomingMessageService $telegramIncomingMessageService = null,
        TelegramVoiceInboxService $telegramVoiceInboxService = null,
        $config = []
    )
    {
        $this->apiKeyAuthService = $apiKeyAuthService ?? new ApiKeyAuthService();
        $this->commandRegistry = Yii::$app->commandRegistry;
        $this->telegramIncomingMessageService = $telegramIncomingMessageService ?? new TelegramIncomingMessageService();
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
            $result = $this->commandRegistry->execute($text, [
                'chatId' => $chatId,
                'source' => 'telegram',
            ]);

            if (!$result->handled) {
                $registryCommands = [];
                foreach ($this->commandRegistry->getRegistry()->all() as $command) {
                    if (!$command instanceof CommandInterface) {
                        continue;
                    }

                    $registryCommands[] = [
                        'class' => get_class($command),
                        'supports' => $command->supports($text),
                    ];
                }

                Yii::warning([
                    'message' => 'Telegram text command was not recognized.',
                    'text' => $text,
                    'text_hex' => bin2hex($text),
                    'chat_id' => $chatId,
                    'registry_commands' => $registryCommands,
                ], __METHOD__);
            }

            if ($chatId !== null) {
                $telegram->sendMessage($chatId, $result->message);
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
