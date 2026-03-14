<?php

namespace api\modules\telegramBot\services;

class TelegramIncomingMessageService
{
    public const TYPE_TEXT = 'text';
    public const TYPE_VOICE = 'voice';
    public const TYPE_UNSUPPORTED = 'unsupported';

    /**
     * @return array{
     *     type:string,
     *     chatId:int|string|null,
     *     messageId:int|string|null,
     *     userId:int|string|null,
     *     text:string,
     *     voice:array<string,int|string>|null
     * }
     */
    public function resolve(object $update): array
    {
        $message = $this->readMethod($update, 'getMessage');
        $chat = $this->readMethod($update, 'getChat');
        $from = $this->readField($message, 'from');
        $chatId = $this->readField($chat, 'id');
        $messageId = $this->readField($message, 'message_id');
        $userId = $this->readField($from, 'id');
        $text = trim((string) $this->readField($message, 'text', ''));

        if ($text !== '') {
            return [
                'type' => self::TYPE_TEXT,
                'chatId' => $chatId,
                'messageId' => $messageId,
                'userId' => $userId,
                'text' => $text,
                'voice' => null,
            ];
        }

        $voice = $this->readField($message, 'voice');
        if ($voice !== null) {
            return [
                'type' => self::TYPE_VOICE,
                'chatId' => $chatId,
                'messageId' => $messageId,
                'userId' => $userId,
                'text' => '',
                'voice' => [
                    'file_id' => (string) $this->readField($voice, 'file_id', ''),
                    'file_unique_id' => (string) $this->readField($voice, 'file_unique_id', ''),
                    'duration' => (int) $this->readField($voice, 'duration', 0),
                    'mime_type' => (string) $this->readField($voice, 'mime_type', ''),
                    'file_size' => (int) $this->readField($voice, 'file_size', 0),
                ],
            ];
        }

        return [
            'type' => self::TYPE_UNSUPPORTED,
            'chatId' => $chatId,
            'messageId' => $messageId,
            'userId' => $userId,
            'text' => '',
            'voice' => null,
        ];
    }

    private function readMethod(object $value, string $method): mixed
    {
        if (!method_exists($value, $method)) {
            return null;
        }

        return $value->{$method}();
    }

    private function readField(mixed $value, string $key, mixed $default = null): mixed
    {
        if ($value === null) {
            return $default;
        }

        if (is_array($value)) {
            return array_key_exists($key, $value) ? $value[$key] : $default;
        }

        if (is_object($value) && method_exists($value, 'get')) {
            try {
                return $value->get($key, $default);
            } catch (\ArgumentCountError) {
                $result = $value->get($key);

                return $result ?? $default;
            }
        }

        if (is_object($value) && isset($value->{$key})) {
            return $value->{$key};
        }

        return $default;
    }
}
