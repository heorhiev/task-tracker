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
}
