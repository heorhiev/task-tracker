<?php

namespace common\modules\tasks\tests\unit\commands;

use Codeception\Test\Unit;
use common\components\CommandRegistryComponent;
use common\models\Task;
use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use Yii;

class CommandRegistryIntegrationTest extends Unit
{
    private CommandRegistryComponent $registry;

    protected function _before(): void
    {
        $this->registry = Yii::$app->commandRegistry;
        Yii::$app->db->createCommand('DELETE FROM {{%idea}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%project}}')->execute();
    }

    public function testExecuteCreateTaskCommand(): void
    {
        $result = $this->registry->execute('create task "Buy milk"', ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy milk'])->one());
    }

    public function testExecuteCreateTaskCommandWithSmartQuotes(): void
    {
        $result = $this->registry->execute('create task “Buy bread”', ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy bread'])->one());
    }

    public function testExecuteCreateTaskCommandWithoutQuotes(): void
    {
        $result = $this->registry->execute('create task Buy eggs', ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy eggs'])->one());
    }

    public function testExecuteCreateTaskCommandWithGuillemets(): void
    {
        $result = $this->registry->execute('create task «Buy ccx»', ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy ccx'])->one());
    }

    public function testExecuteCreateTaskCommandWithMultilineTelegramInput(): void
    {
        $result = $this->registry->execute("create task\n\"Buy ccx\"", ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy ccx'])->one());
    }

    public function testExecuteCreateTaskCommandWithNonBreakingSpaces(): void
    {
        $result = $this->registry->execute("create\u{00A0}task\u{00A0}\"Buy ccx\"", ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_task', $result->commandName);
        $this->assertStringStartsWith('Task created: #', $result->message);
        $this->assertNotNull(Task::find()->where(['title' => 'Buy ccx'])->one());
    }

    public function testExecuteCreateIdeaCommand(): void
    {
        $result = $this->registry->execute('create idea "Improve inbox"', ['chatId' => 12345]);

        $this->assertTrue($result->handled);
        $this->assertSame('create_idea', $result->commandName);
        $this->assertStringStartsWith('Idea created: #', $result->message);
        $this->assertNotNull(Idea::find()->where(['title' => 'Improve inbox'])->one());
    }

    public function testExecuteSetDefaultProjectCommand(): void
    {
        $project = new Project([
            'name' => 'Bot Beta',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($project->save());

        $result = $this->registry->execute('set default project "Bot Beta"');

        $this->assertTrue($result->handled);
        $this->assertSame('set_default_project', $result->commandName);
        $project->refresh();
        $this->assertSame(1, (int) $project->is_default);
    }

    public function testExecuteUnknownCommand(): void
    {
        $result = $this->registry->execute('random text');

        $this->assertFalse($result->handled);
        $this->assertSame('unknown', $result->commandName);
        $this->assertStringStartsWith('Unknown command.', $result->message);
    }
}
