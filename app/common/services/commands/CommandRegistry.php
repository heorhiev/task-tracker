<?php

namespace common\services\commands;

use yii\base\InvalidArgumentException;

class CommandRegistry
{
    /**
     * @var CommandInterface[]
     */
    private array $commands = [];

    public function __construct(array $commands = [], private string $unknownMessage = 'Unknown command.')
    {
        foreach ($commands as $command) {
            $this->register($command);
        }
    }

    public function register(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    public function execute(string $text, array $context = []): CommandExecutionResult
    {
        $normalizedText = trim($text);
        if ($normalizedText === '') {
            return CommandExecutionResult::unknown($this->unknownMessage);
        }

        foreach ($this->commands as $command) {
            if (!$command->supports($normalizedText)) {
                continue;
            }

            $payload = $command->parse($normalizedText);

            return $command->execute($payload, $context);
        }

        return CommandExecutionResult::unknown($this->unknownMessage);
    }

    /**
     * @return CommandInterface[]
     */
    public function all(): array
    {
        return $this->commands;
    }
}
