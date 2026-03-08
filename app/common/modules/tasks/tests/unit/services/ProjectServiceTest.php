<?php

namespace common\modules\tasks\tests\unit\services;

use Codeception\Test\Unit;
use common\modules\tasks\models\Project;
use common\modules\tasks\services\ProjectService;
use Yii;

class ProjectServiceTest extends Unit
{
    private ProjectService $service;

    protected function _before(): void
    {
        $this->service = new ProjectService();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%project}}')->execute();
    }

    public function testCreateProject(): void
    {
        $model = new Project();

        $created = $this->service->create($model, [
            'Project' => [
                'name' => 'Backend',
                'status' => Project::STATUS_ACTIVE,
            ],
        ]);

        $this->assertTrue($created);
        $this->assertNotNull($model->id);

        $persisted = Project::findOne($model->id);
        $this->assertNotNull($persisted);
        $this->assertSame('Backend', $persisted->name);
        $this->assertSame(Project::STATUS_ACTIVE, $persisted->status);
    }

    public function testUpdateProject(): void
    {
        $project = new Project([
            'name' => 'Old Name',
            'status' => Project::STATUS_ACTIVE,
        ]);
        $this->assertTrue($project->save());

        $updated = $this->service->update($project, [
            'Project' => [
                'name' => 'Updated Name',
                'status' => Project::STATUS_ARCHIVED,
            ],
        ]);

        $this->assertTrue($updated);

        $persisted = Project::findOne($project->id);
        $this->assertNotNull($persisted);
        $this->assertSame('Updated Name', $persisted->name);
        $this->assertSame(Project::STATUS_ARCHIVED, $persisted->status);
    }

    public function testChangeStatus(): void
    {
        $project = new Project([
            'name' => 'Mobile',
            'status' => Project::STATUS_ACTIVE,
        ]);
        $this->assertTrue($project->save());

        $changed = $this->service->changeStatus($project, Project::STATUS_ARCHIVED);

        $this->assertTrue($changed);

        $persisted = Project::findOne($project->id);
        $this->assertNotNull($persisted);
        $this->assertSame(Project::STATUS_ARCHIVED, $persisted->status);
    }

    public function testChangeStatusWithInvalidValue(): void
    {
        $project = new Project([
            'name' => 'Web',
            'status' => Project::STATUS_ACTIVE,
        ]);
        $this->assertTrue($project->save());

        $changed = $this->service->changeStatus($project, 'invalid');

        $this->assertFalse($changed);

        $persisted = Project::findOne($project->id);
        $this->assertNotNull($persisted);
        $this->assertSame(Project::STATUS_ACTIVE, $persisted->status);
    }

    public function testSetDefaultProject(): void
    {
        $first = new Project([
            'name' => 'First',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 1,
        ]);
        $this->assertTrue($first->save());

        $second = new Project([
            'name' => 'Second',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($second->save());

        $result = $this->service->setDefaultProject((int) $second->id);

        $this->assertNotNull($result);
        $this->assertSame((int) $second->id, (int) $result->id);
        $this->assertSame(1, (int) $result->is_default);

        $first->refresh();
        $second->refresh();
        $this->assertSame(0, (int) $first->is_default);
        $this->assertSame(1, (int) $second->is_default);
    }

    public function testSetDefaultProjectReturnsNullForMissingProject(): void
    {
        $this->assertNull($this->service->setDefaultProject(999999));
    }
}
