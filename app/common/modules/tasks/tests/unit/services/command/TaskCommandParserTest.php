<?php

namespace common\modules\tasks\tests\unit\services\command;

use Codeception\Test\Unit;
use common\modules\tasks\services\command\TaskCommandExecutor;
use common\modules\tasks\services\command\TaskCommandParser;

class TaskCommandParserTest extends Unit
{
    private TaskCommandParser $service;

    protected function _before(): void
    {
        $this->service = new TaskCommandParser();
    }

    public function testParseCreateProjectCommand(): void
    {
        $result = $this->service->parse('create project "Alpha"');

        $this->assertSame(TaskCommandExecutor::COMMAND_CREATE_PROJECT, $result['command']);
        $this->assertSame('Alpha', $result['payload']['name']);
    }

    public function testParseSetDefaultProjectCommand(): void
    {
        $result = $this->service->parse('set default project "Alpha"');

        $this->assertSame(TaskCommandExecutor::COMMAND_SET_DEFAULT_PROJECT, $result['command']);
        $this->assertSame('Alpha', $result['payload']['name']);
    }

    public function testParseCreateTaskCommand(): void
    {
        $result = $this->service->parse('create task "Write docs"');

        $this->assertSame(TaskCommandExecutor::COMMAND_CREATE_TASK, $result['command']);
        $this->assertSame('Write docs', $result['payload']['title']);
    }

    public function testParseCreateIdeaCommand(): void
    {
        $result = $this->service->parse('create idea "Voice inbox digest"');

        $this->assertSame(TaskCommandExecutor::COMMAND_CREATE_IDEA, $result['command']);
        $this->assertSame('Voice inbox digest', $result['payload']['title']);
    }

    public function testParseUnknownCommand(): void
    {
        $result = $this->service->parse('random text');

        $this->assertSame(TaskCommandExecutor::COMMAND_UNKNOWN, $result['command']);
        $this->assertSame([], $result['payload']);
    }

    public function testParseDefaultProjectCommand(): void
    {
        $result = $this->service->parse('default project');

        $this->assertSame(TaskCommandExecutor::COMMAND_GET_DEFAULT_PROJECT, $result['command']);
        $this->assertSame([], $result['payload']);
    }
}
