<?php

namespace common\modules\inbox\tests\unit\services;

use Codeception\Test\Unit;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\services\InboxMessageStatusService;
use Yii;

class InboxMessageStatusServiceTest extends Unit
{
    private InboxMessageStatusService $service;

    protected function _before(): void
    {
        $this->service = new InboxMessageStatusService();
        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message_attachment}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message}}')->execute();
    }

    public function testMarkProcessingIncrementsAttempts(): void
    {
        $message = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PENDING,
            'attempt_count' => 0,
        ]);
        $this->assertTrue($message->save());

        $result = $this->service->markProcessing($message);

        $this->assertTrue($result);
        $message->refresh();
        $this->assertSame(InboxMessage::STATUS_PROCESSING, $message->status);
        $this->assertSame(1, (int) $message->attempt_count);
        $this->assertNull($message->processing_error);
    }

    public function testMarkProcessedStoresTranscriptionAndCommand(): void
    {
        $message = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PROCESSING,
        ]);
        $this->assertTrue($message->save());

        $result = $this->service->markProcessed($message, 'create task "Call client"', 'create_task');

        $this->assertTrue($result);
        $message->refresh();
        $this->assertSame(InboxMessage::STATUS_PROCESSED, $message->status);
        $this->assertSame('create task "Call client"', $message->transcription_text);
        $this->assertSame('create_task', $message->resolved_command);
        $this->assertNotNull($message->processed_at);
    }

    public function testMarkFailedStoresError(): void
    {
        $message = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PROCESSING,
        ]);
        $this->assertTrue($message->save());

        $result = $this->service->markFailed($message, 'Transcription service timeout');

        $this->assertTrue($result);
        $message->refresh();
        $this->assertSame(InboxMessage::STATUS_FAILED, $message->status);
        $this->assertSame('Transcription service timeout', $message->processing_error);
        $this->assertNotNull($message->processed_at);
    }
}
