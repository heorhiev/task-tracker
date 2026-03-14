<?php

namespace common\components;

use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;
use common\services\commands\CommandRegistry;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class CommandRegistryComponent extends Component
{
    /**
     * @var array<int,mixed>
     */
    public array $commands = [];
    public string $unknownMessage = 'Unknown command. Use: create project "name", set default project "name", create task "title", create idea "title", default project';

    private ?CommandRegistry $registry = null;

    public function init(): void
    {
        parent::init();
        $this->registry = new CommandRegistry([], $this->unknownMessage);

        foreach ($this->commands as $commandDefinition) {
            $this->registry->register($this->createCommand($commandDefinition));
        }
    }

    public function execute(string $text, array $context = []): CommandExecutionResult
    {
        return $this->getRegistry()->execute($text, $context);
    }

    public function register(mixed $commandDefinition): void
    {
        $this->getRegistry()->register($this->createCommand($commandDefinition));
    }

    public function getRegistry(): CommandRegistry
    {
        if ($this->registry === null) {
            throw new InvalidConfigException('Command registry has not been initialized.');
        }

        return $this->registry;
    }

    private function createCommand(mixed $definition): CommandInterface
    {
        $command = Yii::createObject($definition);

        if (!$command instanceof CommandInterface) {
            throw new InvalidConfigException('Command definitions must create instances of ' . CommandInterface::class . '.');
        }

        return $command;
    }
}
