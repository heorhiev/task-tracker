<?php

namespace common\modules\inbox\services;

use common\components\CommandRegistryComponent;
use common\components\SpeechToTextComponent;
use common\modules\fileManager\models\StoredFile;
use common\modules\fileManager\services\FileStorageService;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\models\InboxMessageAttachment;
use common\services\speechTools\normalization\AudioNormalizationService;
use common\services\speechTools\SpeechToTextServiceInterface;
use common\services\speechTools\StubSpeechToTextService;
use Yii;
use yii\base\Exception;

class InboxVoiceProcessingService
{
    private InboxMessageStatusService $statusService;
    private InboxMessageService $inboxMessageService;
    private FileStorageService $fileStorageService;
    private AudioNormalizationService $audioNormalizationService;
    private SpeechToTextServiceInterface $speechToTextService;
    private CommandRegistryComponent $commandRegistry;

    public function __construct(
        InboxMessageStatusService $statusService = null,
        InboxMessageService $inboxMessageService = null,
        FileStorageService $fileStorageService = null,
        AudioNormalizationService $audioNormalizationService = null,
        SpeechToTextServiceInterface $speechToTextService = null,
        CommandRegistryComponent $commandRegistry = null
    )
    {
        $this->statusService = $statusService ?? new InboxMessageStatusService();
        $this->inboxMessageService = $inboxMessageService ?? new InboxMessageService();
        $this->fileStorageService = $fileStorageService ?? new FileStorageService();
        $this->audioNormalizationService = $audioNormalizationService ?? new AudioNormalizationService();
        $this->speechToTextService = $speechToTextService ?? $this->createSpeechToTextService();
        $this->commandRegistry = $commandRegistry ?? Yii::$app->commandRegistry;
    }

    /**
     * @return array{command:string,payload:array<string,string>,handlerMessage:string,transcriptionText:string}
     */
    public function process(InboxMessage $message): array
    {
        if ($message->message_type !== InboxMessage::TYPE_VOICE) {
            throw new Exception('Only voice inbox messages can be processed by this service.');
        }

        if (!$this->statusService->markProcessing($message)) {
            throw new Exception('Unable to mark inbox message as processing.');
        }

        try {
            $storedFile = $this->findOriginalStoredFile($message);
            $transcriptionFile = $this->resolveTranscriptionFile($message, $storedFile);
            $filePath = $this->fileStorageService->getAbsolutePath($transcriptionFile);
            if (!is_file($filePath)) {
                throw new Exception('Stored file does not exist: ' . $filePath);
            }

            $transcriptionText = trim($this->speechToTextService->transcribe($filePath, [
                'messageId' => $message->id,
                'source' => $message->source,
                'messageType' => $message->message_type,
                'externalChatId' => $message->external_chat_id,
                'externalUserId' => $message->external_user_id,
            ]));
            if ($transcriptionText === '') {
                throw new Exception('Speech-to-text returned an empty transcription.');
            }

            $result = $this->commandRegistry->execute($transcriptionText, [
                'chatId' => $message->external_chat_id,
                'source' => $message->source,
                'messageId' => $message->id,
                'externalUserId' => $message->external_user_id,
            ]);
            $resolvedCommand = json_encode([
                'command' => $result->commandName,
                'payload' => $result->payload,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (!$this->statusService->markProcessed($message, $transcriptionText, $resolvedCommand ?: null)) {
                throw new Exception('Unable to mark inbox message as processed.');
            }

            return [
                'command' => $result->commandName,
                'payload' => $result->payload,
                'handlerMessage' => $result->message,
                'transcriptionText' => $transcriptionText,
            ];
        } catch (\Throwable $exception) {
            $this->statusService->markFailed($message, $exception->getMessage());

            throw $exception;
        }
    }

    private function findOriginalStoredFile(InboxMessage $message): StoredFile
    {
        foreach ($message->attachments as $attachment) {
            if ($attachment->role === InboxMessageAttachment::ROLE_ORIGINAL && $attachment->storedFile !== null) {
                return $attachment->storedFile;
            }
        }

        throw new Exception('Original attachment was not found for inbox message #' . $message->id . '.');
    }

    private function resolveTranscriptionFile(InboxMessage $message, StoredFile $storedFile): StoredFile
    {
        $normalizedFile = $this->findAttachmentStoredFileByRole($message, InboxMessageAttachment::ROLE_NORMALIZED);
        if ($normalizedFile !== null) {
            return $normalizedFile;
        }

        if ($this->audioNormalizationService->supportsExtension($storedFile->extension)) {
            return $storedFile;
        }

        $sourcePath = $this->fileStorageService->getAbsolutePath($storedFile);
        $normalizedPath = $this->audioNormalizationService->normalizeToTranscriptionFormat($sourcePath);

        try {
            $normalizedFile = $this->fileStorageService->storeFromPath($normalizedPath, [
                'originalName' => $this->buildNormalizedOriginalName($storedFile),
                'mimeType' => 'audio/wav',
                'extension' => 'wav',
                'category' => 'voice-normalized',
                'source' => StoredFile::SOURCE_SYSTEM,
            ]);

            if ($normalizedFile === null) {
                throw new Exception('Unable to persist normalized audio file.');
            }

            if ($this->inboxMessageService->attachFile($message, $normalizedFile, InboxMessageAttachment::ROLE_NORMALIZED) === null) {
                throw new Exception('Unable to attach normalized audio file to inbox message.');
            }

            return $normalizedFile;
        } finally {
            if (is_file($normalizedPath)) {
                @unlink($normalizedPath);
            }
        }
    }

    private function findAttachmentStoredFileByRole(InboxMessage $message, string $role): ?StoredFile
    {
        foreach ($message->attachments as $attachment) {
            if ($attachment->role === $role && $attachment->storedFile !== null) {
                return $attachment->storedFile;
            }
        }

        return null;
    }

    private function buildNormalizedOriginalName(StoredFile $storedFile): string
    {
        $baseName = pathinfo((string) $storedFile->original_name, PATHINFO_FILENAME);
        if ($baseName === '') {
            $baseName = 'normalized-audio';
        }

        return $baseName . '.wav';
    }

    private function createSpeechToTextService(): SpeechToTextServiceInterface
    {
        if (Yii::$app->has('speechToText')) {
            $speechToTextService = Yii::$app->get('speechToText');
            if ($speechToTextService instanceof SpeechToTextComponent && $speechToTextService->isConfigured()) {
                return $speechToTextService;
            }
        }

        return new StubSpeechToTextService();
    }
}
