<?php

namespace api\modules\telegramBot\services;

class TelegramTaskResolverService
{
    /**
     * @return array{command:string,payload:array<string,string>}
     */
    public function resolve(string $text): array
    {
        $normalized = trim($text);

        if (preg_match('/^create\s+project\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_CREATE_PROJECT,
                'payload' => ['name' => trim((string) $matches[1])],
            ];
        }

        if (preg_match('/^mark\s+as\s+default\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_MARK_PROJECT_DEFAULT,
                'payload' => ['name' => trim((string) $matches[1])],
            ];
        }

        if (preg_match('/^create\s+task\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_CREATE_TASK,
                'payload' => ['title' => trim((string) $matches[1])],
            ];
        }

        return [
            'command' => TelegramCommandHandlerService::COMMAND_UNKNOWN,
            'payload' => [],
        ];
    }
}
