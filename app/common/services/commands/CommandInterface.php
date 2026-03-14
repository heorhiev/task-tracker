<?php

namespace common\services\commands;

interface CommandInterface
{
    public function name(): string;

    public function supports(string $text): bool;

    /**
     * @return array<string,mixed>
     */
    public function parse(string $text): array;

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $context
     */
    public function execute(array $payload, array $context = []): CommandExecutionResult;
}
