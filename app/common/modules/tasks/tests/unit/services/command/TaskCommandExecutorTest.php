<?php

namespace common\modules\tasks\tests\unit\services\command;

use Codeception\Test\Unit;
use common\models\Task;
use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use common\modules\tasks\services\command\TaskCommandExecutor;
use Yii;

class TaskCommandExecutorTest extends Unit
{
    private TaskCommandExecutor $service;

    protected function _before(): void
    {
        $this->service = new TaskCommandExecutor();
        Yii::$app->db->createCommand('DELETE FROM {{%idea}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%project}}')->execute();
    }

    public function testExecuteCreateProject(): void
    {
        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_CREATE_PROJECT,
            'payload' => ['name' => 'Bot Alpha'],
        ], null);

        $this->assertStringStartsWith('Project created: #', $result);
        $this->assertNotNull(Project::findOne(['name' => 'Bot Alpha']));
    }

    public function testExecuteMarkProjectDefault(): void
    {
        $project = new Project([
            'name' => 'Bot Beta',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($project->save());

        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_SET_DEFAULT_PROJECT,
            'payload' => ['name' => 'Bot Beta'],
        ], null);

        $this->assertSame('Default project set: Bot Beta', $result);
        $project->refresh();
        $this->assertSame(1, (int) $project->is_default);
    }

    public function testExecuteCreateTask(): void
    {
        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_CREATE_TASK,
            'payload' => ['title' => 'Bot Task'],
        ], 12345);

        $this->assertStringStartsWith('Task created: #', $result);
        $task = Task::find()->where(['title' => 'Bot Task'])->one();
        $this->assertNotNull($task);
    }

    public function testExecuteCreateIdea(): void
    {
        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_CREATE_IDEA,
            'payload' => ['title' => 'Bot Idea'],
        ], 12345);

        $this->assertStringStartsWith('Idea created: #', $result);
        $idea = Idea::find()->where(['title' => 'Bot Idea'])->one();
        $this->assertNotNull($idea);
    }

    public function testExecuteUnknownCommand(): void
    {
        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_UNKNOWN,
            'payload' => [],
        ], null);

        $this->assertStringStartsWith('Unknown command.', $result);
    }

    public function testExecuteGetDefaultProject(): void
    {
        $project = new Project([
            'name' => 'Default Bot Project',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 1,
        ]);
        $this->assertTrue($project->save());

        $result = $this->service->execute([
            'command' => TaskCommandExecutor::COMMAND_GET_DEFAULT_PROJECT,
            'payload' => [],
        ], null);

        $this->assertSame('Current default project: Default Bot Project (#' . $project->id . ')', $result);
    }
}
