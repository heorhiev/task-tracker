<?php

namespace common\modules\tasks\tests\unit\services;

use Codeception\Test\Unit;
use common\modules\tasks\models\forms\IdeaCreateForm;
use common\modules\tasks\models\forms\IdeaDeleteForm;
use common\modules\tasks\models\forms\IdeaUpdateForm;
use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use common\modules\tasks\services\IdeaService;
use Yii;

class IdeaServiceTest extends Unit
{
    private IdeaService $service;

    protected function _before(): void
    {
        $this->service = new IdeaService();
        Yii::$app->db->createCommand('DELETE FROM {{%idea}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%task}}')->execute();
        Yii::$app->db->createCommand('DELETE FROM {{%project}} WHERE is_default = false')->execute();
    }

    public function testCreateIdea(): void
    {
        $project = new Project([
            'name' => 'Discovery',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);
        $this->assertTrue($project->save());

        $form = new IdeaCreateForm([
            'title' => 'Voice notes digest',
            'description' => 'Summarize incoming voice notes into weekly themes.',
            'status' => Idea::STATUS_NEW,
            'project_id' => $project->id,
        ]);

        $idea = $this->service->create($form);

        $this->assertNotNull($idea);
        $this->assertNotNull($idea->id);
        $this->assertSame('Voice notes digest', $idea->title);
        $this->assertSame((int) $project->id, (int) $idea->project_id);
    }

    public function testUpdateIdea(): void
    {
        $idea = new Idea([
            'title' => 'Old title',
            'description' => 'Old description',
            'status' => Idea::STATUS_NEW,
        ]);
        $this->assertTrue($idea->save());

        $form = new IdeaUpdateForm([
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => Idea::STATUS_REVIEWING,
        ]);

        $updated = $this->service->update((int) $idea->id, $form);

        $this->assertNotNull($updated);
        $this->assertSame('Updated title', $updated->title);
        $this->assertSame(Idea::STATUS_REVIEWING, $updated->status);
    }

    public function testDeleteIdea(): void
    {
        $idea = new Idea([
            'title' => 'Delete me',
            'status' => Idea::STATUS_NEW,
        ]);
        $this->assertTrue($idea->save());

        $form = new IdeaDeleteForm(['id' => $idea->id]);

        $deleted = $this->service->delete($form);

        $this->assertTrue($deleted);
        $this->assertNull(Idea::findOne($idea->id));
    }
}
