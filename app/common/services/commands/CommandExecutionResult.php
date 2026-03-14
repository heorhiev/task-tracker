<?php

namespace common\services\commands;

class CommandExecutionResult
{
    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(
        public bool $handled,
        public string $commandName,
        public string $message,
        public array $payload = []
    )
    {
    }

    public static function unknown(string $message): self
    {
        return new self(false, 'unknown', $message, []);
    }
}
