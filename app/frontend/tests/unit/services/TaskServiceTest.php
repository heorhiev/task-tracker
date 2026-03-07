<?php

namespace frontend\tests\unit\services;

use common\models\forms\TaskCreateForm;
use common\models\forms\TaskDeleteForm;
use common\models\forms\TaskUpdateForm;
use common\models\Task;
use common\modules\tasks\services\TaskService;
use Codeception\Test\Unit;
use Yii;

class TaskServiceTest extends Unit
{
    private TaskService $service;

    protected function _before(): void
    {
        $this->service = new TaskService();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
    }

    public function testCreateTask(): void
    {
        $form = new TaskCreateForm([
            'title' => 'Create task test',
            'description' => 'Service create flow',
            'status' => Task::STATUS_NEW,
            'priority' => Task::PRIORITY_HIGH,
            'due_date' => '2026-03-10T12:30',
        ]);

        $task = $this->service->create($form);

        $this->assertNotNull($task);
        $this->assertNotNull($task->id);
        $this->assertSame('Create task test', $task->title);
        $this->assertSame(Task::STATUS_NEW, $task->status);

        $persisted = Task::findOne($task->id);
        $this->assertNotNull($persisted);
        $this->assertSame(Task::PRIORITY_HIGH, $persisted->priority);
    }

    public function testUpdateTask(): void
    {
        $source = new Task([
            'title' => 'Old title',
            'description' => 'Old description',
            'status' => Task::STATUS_NEW,
            'priority' => Task::PRIORITY_LOW,
        ]);
        $this->assertTrue($source->save());

        $form = new TaskUpdateForm([
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => Task::STATUS_IN_PROGRESS,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => '2026-03-11T09:00',
        ]);

        $updated = $this->service->update((int) $source->id, $form);

        $this->assertNotNull($updated);
        $this->assertSame('Updated title', $updated->title);
        $this->assertSame(Task::STATUS_IN_PROGRESS, $updated->status);

        $persisted = Task::findOne($source->id);
        $this->assertSame('Updated description', $persisted->description);
        $this->assertSame(Task::PRIORITY_MEDIUM, $persisted->priority);
    }

    public function testDeleteTask(): void
    {
        $task = new Task([
            'title' => 'Delete me',
            'status' => Task::STATUS_NEW,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);
        $this->assertTrue($task->save());

        $form = new TaskDeleteForm(['id' => $task->id]);

        $deleted = $this->service->delete($form);

        $this->assertTrue($deleted);
        $this->assertNull(Task::findOne($task->id));
    }
}
