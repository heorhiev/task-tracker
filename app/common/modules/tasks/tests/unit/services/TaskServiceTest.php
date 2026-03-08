<?php

namespace common\modules\tasks\tests\unit\services;

use common\models\forms\TaskCreateForm;
use common\models\forms\TaskDeleteForm;
use common\models\forms\TaskUpdateForm;
use common\models\Task;
use common\modules\tasks\models\Project;
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
        $this->assertNotNull($persisted->project_id);

        $defaultProject = Project::find()->where(['is_default' => 1])->one();
        $this->assertNotNull($defaultProject);
        $this->assertSame((int) $defaultProject->id, (int) $persisted->project_id);
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

    public function testSetDefaultProject(): void
    {
        $project = new Project([
            'name' => 'Custom Project',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($project->save());

        $task = new Task([
            'title' => 'Switch project',
            'status' => Task::STATUS_NEW,
            'priority' => Task::PRIORITY_MEDIUM,
            'project_id' => (int) $project->id,
        ]);
        $this->assertTrue($task->save());

        $updated = $this->service->setDefaultProject((int) $task->id);

        $this->assertNotNull($updated);
        $this->assertNotNull($updated->project_id);

        $defaultProject = Project::find()->where(['is_default' => 1])->one();
        $this->assertNotNull($defaultProject);
        $this->assertSame((int) $defaultProject->id, (int) $updated->project_id);
    }
}
