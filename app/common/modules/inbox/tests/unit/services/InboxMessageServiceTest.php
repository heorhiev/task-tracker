<?php

namespace common\modules\inbox\tests\unit\services;

use Codeception\Test\Unit;
use common\modules\fileManager\models\StoredFile;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\models\InboxMessageAttachment;
use common\modules\inbox\services\InboxMessageService;
use Yii;

class InboxMessageServiceTest extends Unit
{
    private InboxMessageService $service;

    protected function _before(): void
    {
        $this->service = new InboxMessageService();
        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message_attachment}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%inbox_message}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%stored_file}}')->execute();
    }

    public function testCreateWithAttachment(): void
    {
        $storedFile = new StoredFile([
            'storage' => StoredFile::STORAGE_LOCAL,
            'path' => 'telegram/voice/2026/03/14/file.ogg',
            'original_name' => 'file.ogg',
            'extension' => 'ogg',
            'mime_type' => 'audio/ogg',
            'size_bytes' => 123,
            'checksum_sha256' => str_repeat('a', 64),
            'source' => StoredFile::SOURCE_TELEGRAM,
            'source_file_id' => 'file-id',
            'source_unique_id' => 'unique-id',
        ]);
        $this->assertTrue($storedFile->save());

        $message = $this->service->createWithAttachment([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'external_message_id' => '101',
            'external_chat_id' => '202',
            'external_user_id' => '303',
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PENDING,
            'received_at' => '2026-03-14 10:00:00',
        ], $storedFile, InboxMessageAttachment::ROLE_ORIGINAL);

        $this->assertNotNull($message);
        $this->assertNotNull($message->id);
        $this->assertSame(InboxMessage::STATUS_PENDING, $message->status);

        $attachment = InboxMessageAttachment::find()->where(['inbox_message_id' => $message->id])->one();
        $this->assertNotNull($attachment);
        $this->assertSame((int) $storedFile->id, (int) $attachment->stored_file_id);
        $this->assertSame(InboxMessageAttachment::ROLE_ORIGINAL, $attachment->role);
    }

    public function testGetPendingMessagesReturnsOnlyPendingFilteredByTypeAndSource(): void
    {
        $pendingVoice = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PENDING,
            'received_at' => '2026-03-14 09:00:00',
        ]);
        $this->assertTrue($pendingVoice->save());

        $processedVoice = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_VOICE,
            'status' => InboxMessage::STATUS_PROCESSED,
            'received_at' => '2026-03-14 10:00:00',
        ]);
        $this->assertTrue($processedVoice->save());

        $pendingText = new InboxMessage([
            'source' => InboxMessage::SOURCE_TELEGRAM,
            'message_type' => InboxMessage::TYPE_TEXT,
            'status' => InboxMessage::STATUS_PENDING,
            'received_at' => '2026-03-14 08:00:00',
        ]);
        $this->assertTrue($pendingText->save());

        $messages = $this->service->getPendingMessages(10, InboxMessage::TYPE_VOICE, InboxMessage::SOURCE_TELEGRAM);

        $this->assertCount(1, $messages);
        $this->assertSame((int) $pendingVoice->id, (int) $messages[0]->id);
    }
}
