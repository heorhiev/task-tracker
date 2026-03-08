<?php

namespace api\modules\telegramBot\tests\unit\services;

use api\modules\telegramBot\services\TelegramTaskResolverService;
use api\modules\telegramBot\services\TelegramCommandHandlerService;
use Codeception\Test\Unit;

class TelegramTaskResolverServiceTest extends Unit
{
    private TelegramTaskResolverService $service;

    protected function _before(): void
    {
        $this->service = new TelegramTaskResolverService();
    }

    public function testResolveCreateProjectCommand(): void
    {
        $result = $this->service->resolve('create project "Alpha"');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_CREATE_PROJECT, $result['command']);
        $this->assertSame('Alpha', $result['payload']['name']);
    }

    public function testResolveSetDefaultProjectCommand(): void
    {
        $result = $this->service->resolve('set default project "Alpha"');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_SET_DEFAULT_PROJECT, $result['command']);
        $this->assertSame('Alpha', $result['payload']['name']);
    }

    public function testResolveCreateTaskCommand(): void
    {
        $result = $this->service->resolve('create task "Write docs"');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_CREATE_TASK, $result['command']);
        $this->assertSame('Write docs', $result['payload']['title']);
    }

    public function testResolveUnknownCommand(): void
    {
        $result = $this->service->resolve('random text');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_UNKNOWN, $result['command']);
        $this->assertSame([], $result['payload']);
    }

    public function testResolveDefaultProjectCommand(): void
    {
        $result = $this->service->resolve('default project');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_GET_DEFAULT_PROJECT, $result['command']);
        $this->assertSame([], $result['payload']);
    }
}
