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

        if (preg_match('/^set\s+default\s+project\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_SET_DEFAULT_PROJECT,
                'payload' => ['name' => trim((string) $matches[1])],
            ];
        }

        if (preg_match('/^create\s+task\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_CREATE_TASK,
                'payload' => ['title' => trim((string) $matches[1])],
            ];
        }

        if (preg_match('/^create\s+idea\s+"([^"]+)"$/i', $normalized, $matches) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_CREATE_IDEA,
                'payload' => ['title' => trim((string) $matches[1])],
            ];
        }

        if (preg_match('/^default\s+project$/i', $normalized) === 1) {
            return [
                'command' => TelegramCommandHandlerService::COMMAND_GET_DEFAULT_PROJECT,
                'payload' => [],
            ];
        }

        return [
            'command' => TelegramCommandHandlerService::COMMAND_UNKNOWN,
            'payload' => [],
        ];
    }
}
