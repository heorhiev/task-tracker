<?php

namespace api\modules\telegramBot\tests\unit\services;

use api\modules\telegramBot\services\TelegramIncomingMessageService;
use Codeception\Test\Unit;

class TelegramIncomingMessageServiceTest extends Unit
{
    private TelegramIncomingMessageService $service;

    protected function _before(): void
    {
        $this->service = new TelegramIncomingMessageService();
    }

    public function testResolveTextMessage(): void
    {
        $update = new FakeTelegramEntity([
            'message' => new FakeTelegramEntity([
                'message_id' => 333,
                'text' => 'create task "Voice follow-up"',
                'from' => new FakeTelegramEntity([
                    'id' => 555,
                ]),
            ]),
            'chat' => new FakeTelegramEntity([
                'id' => 12345,
            ]),
        ]);

        $result = $this->service->resolve($update);

        $this->assertSame(TelegramIncomingMessageService::TYPE_TEXT, $result['type']);
        $this->assertSame(12345, $result['chatId']);
        $this->assertSame(333, $result['messageId']);
        $this->assertSame(555, $result['userId']);
        $this->assertSame('create task "Voice follow-up"', $result['text']);
        $this->assertNull($result['voice']);
    }

    public function testResolveVoiceMessage(): void
    {
        $update = new FakeTelegramEntity([
            'message' => new FakeTelegramEntity([
                'message_id' => 444,
                'from' => new FakeTelegramEntity([
                    'id' => 666,
                ]),
                'voice' => new FakeTelegramEntity([
                    'file_id' => 'voice-file-id',
                    'file_unique_id' => 'voice-unique-id',
                    'duration' => 7,
                    'mime_type' => 'audio/ogg',
                    'file_size' => 2048,
                ]),
            ]),
            'chat' => new FakeTelegramEntity([
                'id' => 67890,
            ]),
        ]);

        $result = $this->service->resolve($update);

        $this->assertSame(TelegramIncomingMessageService::TYPE_VOICE, $result['type']);
        $this->assertSame(67890, $result['chatId']);
        $this->assertSame(444, $result['messageId']);
        $this->assertSame(666, $result['userId']);
        $this->assertSame('', $result['text']);
        $this->assertSame('voice-file-id', $result['voice']['file_id']);
        $this->assertSame(7, $result['voice']['duration']);
        $this->assertSame('audio/ogg', $result['voice']['mime_type']);
    }

    public function testResolveUnsupportedMessage(): void
    {
        $update = new FakeTelegramEntity([
            'message' => new FakeTelegramEntity([
                'message_id' => 777,
                'from' => new FakeTelegramEntity([
                    'id' => 888,
                ]),
            ]),
            'chat' => new FakeTelegramEntity([
                'id' => 98765,
            ]),
        ]);

        $result = $this->service->resolve($update);

        $this->assertSame(TelegramIncomingMessageService::TYPE_UNSUPPORTED, $result['type']);
        $this->assertSame(98765, $result['chatId']);
        $this->assertSame(777, $result['messageId']);
        $this->assertSame(888, $result['userId']);
        $this->assertSame('', $result['text']);
        $this->assertNull($result['voice']);
    }
}

class FakeTelegramEntity
{
    public function __construct(private array $data)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getMessage(): ?self
    {
        $message = $this->data['message'] ?? null;

        return $message instanceof self ? $message : null;
    }

    public function getChat(): ?self
    {
        $chat = $this->data['chat'] ?? null;

        return $chat instanceof self ? $chat : null;
    }

    public function getFrom(): ?self
    {
        $from = $this->data['from'] ?? null;

        return $from instanceof self ? $from : null;
    }
}
