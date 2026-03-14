<?php

namespace api\modules\telegramBot\services;

use common\components\TelegramComponent;
use common\modules\fileManager\models\StoredFile;
use common\modules\fileManager\services\FileStorageService;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\models\InboxMessageAttachment;
use common\modules\inbox\services\InboxMessageService;
use Yii;
use yii\base\Exception;

class TelegramVoiceInboxService
{
    private FileStorageService $fileStorageService;
    private InboxMessageService $inboxMessageService;
    private string $tempPathAlias;

    public function __construct(
        FileStorageService $fileStorageService = null,
        InboxMessageService $inboxMessageService = null,
        string $tempPathAlias = '@runtime/telegram-temp'
    )
    {
        $this->fileStorageService = $fileStorageService ?? new FileStorageService();
        $this->inboxMessageService = $inboxMessageService ?? new InboxMessageService();
        $this->tempPathAlias = $tempPathAlias;
    }

    /**
     * @param array{
     *     chatId:int|string|null,
     *     messageId:int|string|null,
     *     userId:int|string|null,
     *     voice:array<string,int|string>|null
     * } $incomingMessage
     */
    public function storeIncomingVoice(TelegramComponent $telegram, array $incomingMessage): ?InboxMessage
    {
        $voice = $incomingMessage['voice'] ?? null;
        $fileId = trim((string) ($voice['file_id'] ?? ''));
        if ($fileId === '') {
            return null;
        }

        $telegramFile = $telegram->getFile($fileId);
        $extension = $this->extractExtension((string) $telegramFile->get('file_path', ''));
        $tempFilePath = $this->buildTempFilePath($extension);

        try {
            $telegram->downloadFile($telegramFile, $tempFilePath);
            $content = file_get_contents($tempFilePath);
            if ($content === false || $content === '') {
                throw new Exception('Downloaded Telegram voice file is empty.');
            }

            $storedFile = $this->fileStorageService->store($content, [
                'originalName' => $this->buildOriginalName($incomingMessage, $extension),
                'mimeType' => (string) ($voice['mime_type'] ?? ''),
                'extension' => $extension,
                'category' => InboxMessage::TYPE_VOICE,
                'source' => StoredFile::SOURCE_TELEGRAM,
                'sourceFileId' => $fileId,
                'sourceUniqueId' => (string) ($voice['file_unique_id'] ?? ''),
            ]);

            if ($storedFile === null) {
                return null;
            }

            return $this->inboxMessageService->createWithAttachment([
                'source' => InboxMessage::SOURCE_TELEGRAM,
                'external_message_id' => $this->stringOrNull($incomingMessage['messageId'] ?? null),
                'external_chat_id' => $this->stringOrNull($incomingMessage['chatId'] ?? null),
                'external_user_id' => $this->stringOrNull($incomingMessage['userId'] ?? null),
                'message_type' => InboxMessage::TYPE_VOICE,
                'status' => InboxMessage::STATUS_PENDING,
                'received_at' => date('Y-m-d H:i:s'),
            ], $storedFile, InboxMessageAttachment::ROLE_ORIGINAL);
        } finally {
            if (is_file($tempFilePath)) {
                @unlink($tempFilePath);
            }
        }
    }

    private function buildTempFilePath(?string $extension): string
    {
        $directory = Yii::getAlias($this->tempPathAlias);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $fileName = Yii::$app->security->generateRandomString(24);
        if ($extension !== null) {
            $fileName .= '.' . $extension;
        }

        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    }

    private function buildOriginalName(array $incomingMessage, ?string $extension): string
    {
        $messageId = $this->stringOrNull($incomingMessage['messageId'] ?? null) ?? Yii::$app->security->generateRandomString(8);
        $fileName = 'telegram-voice-' . $messageId;

        return $extension !== null ? $fileName . '.' . $extension : $fileName;
    }

    private function extractExtension(string $filePath): ?string
    {
        if ($filePath === '') {
            return null;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return $extension !== '' ? mb_strtolower($extension) : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
