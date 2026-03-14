<?php

namespace common\modules\tasks\services;

use common\modules\tasks\models\forms\IdeaCreateForm;
use common\modules\tasks\models\forms\IdeaDeleteForm;
use common\modules\tasks\models\forms\IdeaUpdateForm;
use common\modules\tasks\models\Idea;

class IdeaService
{
    public function create(IdeaCreateForm $form): ?Idea
    {
        if (!$form->validate()) {
            return null;
        }

        $idea = new Idea();
        $idea->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'project_id' => $this->resolveProjectId($form->project_id),
        ], false);

        return $idea->save() ? $idea : null;
    }

    public function update(int $id, IdeaUpdateForm $form): ?Idea
    {
        $idea = Idea::findOne($id);
        if ($idea === null) {
            return null;
        }

        if (!$form->validate()) {
            return null;
        }

        $idea->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'project_id' => $this->resolveProjectId($form->project_id),
        ], false);

        return $idea->save() ? $idea : null;
    }

    public function delete(IdeaDeleteForm $form): bool
    {
        if (!$form->validate()) {
            return false;
        }

        $idea = Idea::findOne((int) $form->id);
        if ($idea === null) {
            return false;
        }

        return $idea->delete() !== false;
    }

    private function resolveProjectId($projectId): ?int
    {
        if ($projectId === null || $projectId === '') {
            return null;
        }

        return (int) $projectId;
    }
}
