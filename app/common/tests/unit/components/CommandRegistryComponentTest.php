<?php

namespace common\tests\unit\components;

use Codeception\Test\Unit;
use common\components\CommandRegistryComponent;
use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;
use yii\base\BaseObject;

class CommandRegistryComponentTest extends Unit
{
    protected function _before(): void
    {
        parent::_before();
        LazyTestCommand::$instances = 0;
    }

    public function testCommandsAreCreatedLazilyAndCached(): void
    {
        $component = new CommandRegistryComponent([
            'commands' => [
                LazyTestCommand::class,
            ],
        ]);

        $this->assertSame(0, LazyTestCommand::$instances);

        $firstResult = $component->execute('ping');

        $this->assertTrue($firstResult->handled);
        $this->assertSame(1, LazyTestCommand::$instances);

        $secondResult = $component->execute('ping');

        $this->assertTrue($secondResult->handled);
        $this->assertSame(1, LazyTestCommand::$instances);
    }

    public function testRegisterAddsNewCommandAfterRegistryWasBuilt(): void
    {
        $component = new CommandRegistryComponent();

        $this->assertFalse($component->execute('ping')->handled);

        $component->register(LazyTestCommand::class);
        $result = $component->execute('ping');

        $this->assertTrue($result->handled);
        $this->assertSame(1, LazyTestCommand::$instances);
    }
}

class LazyTestCommand extends BaseObject implements CommandInterface
{
    public static int $instances = 0;

    public function __construct($config = [])
    {
        self::$instances++;
        parent::__construct($config);
    }

    public function name(): string
    {
        return 'lazy_test';
    }

    public function supports(string $text): bool
    {
        return $text === 'ping';
    }

    public function parse(string $text): array
    {
        return ['text' => $text];
    }

    public function execute(array $payload, array $context = []): CommandExecutionResult
    {
        return new CommandExecutionResult(true, $this->name(), 'pong', $payload);
    }
}
