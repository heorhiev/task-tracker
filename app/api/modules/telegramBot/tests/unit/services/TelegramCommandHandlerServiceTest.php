<?php

namespace api\modules\telegramBot\tests\unit\services;

use api\modules\telegramBot\services\TelegramCommandHandlerService;
use Codeception\Test\Unit;
use common\models\Task;
use common\modules\tasks\models\Project;
use Yii;

class TelegramCommandHandlerServiceTest extends Unit
{
    private TelegramCommandHandlerService $service;

    protected function _before(): void
    {
        $this->service = new TelegramCommandHandlerService();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%project}}')->execute();
    }

    public function testHandleCreateProject(): void
    {
        $result = $this->service->handle([
            'command' => TelegramCommandHandlerService::COMMAND_CREATE_PROJECT,
            'payload' => ['name' => 'Bot Alpha'],
        ], null);

        $this->assertStringStartsWith('Project created: #', $result);
        $this->assertNotNull(Project::findOne(['name' => 'Bot Alpha']));
    }

    public function testHandleMarkProjectDefault(): void
    {
        $project = new Project([
            'name' => 'Bot Beta',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($project->save());

        $result = $this->service->handle([
            'command' => TelegramCommandHandlerService::COMMAND_SET_DEFAULT_PROJECT,
            'payload' => ['name' => 'Bot Beta'],
        ], null);

        $this->assertSame('Default project set: Bot Beta', $result);
        $project->refresh();
        $this->assertSame(1, (int) $project->is_default);
    }

    public function testHandleCreateTask(): void
    {
        $result = $this->service->handle([
            'command' => TelegramCommandHandlerService::COMMAND_CREATE_TASK,
            'payload' => ['title' => 'Bot Task'],
        ], 12345);

        $this->assertStringStartsWith('Task created: #', $result);
        $task = Task::find()->where(['title' => 'Bot Task'])->one();
        $this->assertNotNull($task);
    }

    public function testHandleUnknownCommand(): void
    {
        $result = $this->service->handle([
            'command' => TelegramCommandHandlerService::COMMAND_UNKNOWN,
            'payload' => [],
        ], null);

        $this->assertStringStartsWith('Unknown command.', $result);
    }

    public function testHandleGetDefaultProject(): void
    {
        $project = new Project([
            'name' => 'Default Bot Project',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 1,
        ]);
        $this->assertTrue($project->save());

        $result = $this->service->handle([
            'command' => TelegramCommandHandlerService::COMMAND_GET_DEFAULT_PROJECT,
            'payload' => [],
        ], null);

        $this->assertSame('Current default project: Default Bot Project (#' . $project->id . ')', $result);
    }
}
