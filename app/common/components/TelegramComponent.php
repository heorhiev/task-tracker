<?php

namespace common\components;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\File;
use Telegram\Bot\Objects\Update;
use yii\base\Component;
use yii\base\InvalidConfigException;

class TelegramComponent extends Component
{
    public string $botToken = '';

    public function isConfigured(): bool
    {
        return trim($this->botToken) !== '';
    }

    public function getWebhookUpdate(): Update
    {
        return $this->createClient()->getWebhookUpdate();
    }

    public function sendMessage(int|string $chatId, string $text): void
    {
        $this->createClient()->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function getFile(string $fileId): File
    {
        return $this->createClient()->getFile([
            'file_id' => $fileId,
        ]);
    }

    public function downloadFile(File|string $file, string $filename): string
    {
        return $this->createClient()->downloadFile($file, $filename);
    }

    public function isStartCommand(string $text): bool
    {
        $normalized = mb_strtolower(trim($text));

        return in_array($normalized, ['/start', 'start'], true);
    }

    private function createClient(): Api
    {
        if (!$this->isConfigured()) {
            throw new InvalidConfigException('Telegram bot token is not configured.');
        }

        return new Api($this->botToken);
    }
}
