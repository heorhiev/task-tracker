<?php

namespace common\modules\tasks\services;

use common\modules\tasks\models\Project;

class ProjectService
{
    public function create(Project $model, array $data): bool
    {
        if (!$model->load($data)) {
            return false;
        }

        if ((bool) $model->is_default) {
            $this->unsetDefaultForOthers(null);
        }

        return $model->save();
    }

    public function update(Project $model, array $data): bool
    {
        if (!$model->load($data)) {
            return false;
        }

        if ((bool) $model->is_default) {
            $this->unsetDefaultForOthers((int) $model->id);
        }

        return $model->save();
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
        $project = Project::find()->where(['is_default' => 1])->one();
        if ($project !== null) {
            return $project;
        }

        $project = Project::find()->orderBy(['id' => SORT_ASC])->one();
        if ($project !== null) {
            $project->is_default = 1;
            $project->save(false, ['is_default', 'updated_at']);
            return $project;
        }

        $project = new Project([
            'name' => 'No Project',
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 1,
        ]);
        $project->save(false);

        return $project;
    }

    public function setDefaultProject(int $id): ?Project
    {
        $project = Project::findOne($id);
        if ($project === null) {
            return null;
        }

        $this->unsetDefaultForOthers($id);
        $project->is_default = 1;

        return $project->save(true, ['is_default', 'updated_at']) ? $project : null;
    }

    public function findByName(string $name): ?Project
    {
        return Project::findOne(['name' => $name]);
    }

    public function findOrCreateByName(string $name): ?Project
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return null;
        }

        $project = $this->findByName($normalized);
        if ($project !== null) {
            return $project;
        }

        $project = new Project([
            'name' => $normalized,
            'status' => Project::STATUS_ACTIVE,
            'is_default' => 0,
        ]);

        return $project->save() ? $project : null;
    }

    public function setDefaultProjectByName(string $name): ?Project
    {
        $project = $this->findByName(trim($name));
        if ($project === null) {
            return null;
        }

        return $this->setDefaultProject((int) $project->id);
    }

    private function unsetDefaultForOthers(?int $exceptId): void
    {
        $condition = ['is_default' => 1];
        if ($exceptId !== null) {
            $condition = ['and', ['is_default' => 1], ['<>', 'id', $exceptId]];
        }

        Project::updateAll(['is_default' => 0], $condition);
    }
}
