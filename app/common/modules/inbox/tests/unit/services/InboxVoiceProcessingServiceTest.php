<?php

namespace common\modules\inbox\tests\unit\services;

use Codeception\Test\Unit;
use common\models\Task;
use common\modules\fileManager\models\StoredFile;
use common\modules\fileManager\services\FileStorageService;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\models\InboxMessageAttachment;
use common\modules\inbox\services\InboxMessageService;
use common\modules\inbox\services\InboxMessageStatusService;
use common\modules\inbox\services\InboxVoiceProcessingService;
use common\services\speechTools\normalization\AudioNormalizationService;
use common\services\speechTools\StubSpeechToTextService;
use Yii;

class InboxVoiceProcessingServiceTest extends Unit
{
    private string $basePath;

    protected function _before(): void
    {
        $this->basePath = codecept_output_dir('inbox-voice-processing');

        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message_attachment}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%stored_file}}')->execute();
    }

    protected function _after(): void
    {
        $this->removeDirectory($this->basePath);
    }

    public function testProcessPendingVoiceMessageCreatesTaskAndMarksMessageProcessed(): void
    {
        $fileStorageService = new FileStorageService($this->basePath);
        $storedFile = $fileStorageService->store('audio payload', [
            'originalName' => 'voice-note.wav',
            'mimeType' => 'audio/wav',
            'category' => InboxMessage::TYPE_VOICE,
            'source' => StoredFile::SOURCE_TELEGRAM,
            'sourceFileId' => 'voice-file-id',
            'sourceUniqueId' => 'voice-unique-id',
        ]);
        $this->assertNotNull($storedFile);

        $audioPath = $fileStorageService->getAbsolutePath($storedFile);
        $this->assertNotFalse(file_put_contents($audioPath . '.txt', 'create task "Buy milk"'));

        $messageService = new InboxMessageService();
        $message = $messageService->createWithAttachment([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'external_message_id' => '1001',
            'external_chat_id' => '5555',
            'external_user_id' => '7777',
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PENDING,
            'received_at' => '2026-03-14 12:00:00',
        ], $storedFile, InboxMessageAttachment::ROLE_ORIGINAL);
        $this->assertNotNull($message);

        $service = new InboxVoiceProcessingService(
            new InboxMessageStatusService(),
            $messageService,
            $fileStorageService,
            new AudioNormalizationService(),
            new StubSpeechToTextService()
        );

        $message->refresh();
        $result = $service->process($message);

        $message->refresh();
        $this->assertSame(InboxMessage::STATUS_PROCESSED, $message->status);
        $this->assertSame('create task "Buy milk"', $message->transcription_text);
        $this->assertStringContainsString('"command":"create_task"', (string) $message->resolved_command);
        $this->assertSame('create task "Buy milk"', $result['transcriptionText']);
        $this->assertSame('create_task', $result['command']);

        $task = Task::find()->where(['title' => 'Buy milk'])->one();
        $this->assertNotNull($task);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
