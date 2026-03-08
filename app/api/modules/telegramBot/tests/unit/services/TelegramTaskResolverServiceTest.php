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

    public function testResolveMarkAsDefaultCommand(): void
    {
        $result = $this->service->resolve('mark as default "Alpha"');

        $this->assertSame(TelegramCommandHandlerService::COMMAND_MARK_PROJECT_DEFAULT, $result['command']);
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
}
