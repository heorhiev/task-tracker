<?php

namespace common\modules\tasks\services;

use common\modules\tasks\models\Project;

class ProjectService
{
    public function create(Project $model, array $data): bool
    {
        return $model->load($data) && $model->save();
    }

    public function update(Project $model, array $data): bool
    {
        return $model->load($data) && $model->save();
    }

    public function changeStatus(Project $model, string $status): bool
    {
        if (!array_key_exists($status, Project::statusOptions())) {
            return false;
        }

        $model->status = $status;

        return $model->save(true, ['status', 'updated_at']);
    }

    public function getDefaultProject(): Project
    {
        $project = Project::findOne(Project::DEFAULT_PROJECT_ID);
        if ($project !== null) {
            return $project;
        }

        $project = new Project([
            'id' => Project::DEFAULT_PROJECT_ID,
            'name' => 'Без проекта',
            'status' => Project::STATUS_ACTIVE,
        ]);
        $project->save(false);

        return $project;
    }
}
