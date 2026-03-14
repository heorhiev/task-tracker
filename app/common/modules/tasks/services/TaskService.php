<?php

namespace common\modules\tasks\services;

use common\models\forms\TaskCreateForm;
use common\models\forms\TaskDeleteForm;
use common\models\forms\TaskUpdateForm;
use common\models\Task;

class TaskService
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService = null)
    {
        $this->projectService = $projectService ?? new ProjectService();
    }

    public function create(TaskCreateForm $form): ?Task
    {
        $form->project_id = $this->resolveProjectId($form->project_id);

        if (!$form->validate()) {
            return null;
        }

        $task = new Task();
        $task->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'priority' => $form->priority,
            'project_id' => $form->project_id,
            'idea_id' => $this->resolveIdeaId($form->idea_id),
            'due_date' => $form->due_date,
        ], false);

        return $task->save() ? $task : null;
    }

    public function update(int $id, TaskUpdateForm $form): ?Task
    {
        $task = Task::findOne($id);
        if ($task === null) {
            return null;
        }

        $form->project_id = $this->resolveProjectId($form->project_id);

        if (!$form->validate()) {
            return null;
        }

        $task->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'priority' => $form->priority,
            'project_id' => $form->project_id,
            'idea_id' => $this->resolveIdeaId($form->idea_id),
            'due_date' => $form->due_date,
        ], false);

        return $task->save() ? $task : null;
    }

    public function delete(TaskDeleteForm $form): bool
    {
        if (!$form->validate()) {
            return false;
        }

        $task = Task::findOne((int) $form->id);
        if ($task === null) {
            return false;
        }

        return $task->delete() !== false;
    }

    public function setDefaultProject(int $id): ?Task
    {
        $task = Task::findOne($id);
        if ($task === null) {
            return null;
        }

        $task->project_id = (int) $this->projectService->getDefaultProject()->id;

        return $task->save(true, ['project_id', 'updated_at']) ? $task : null;
    }

    private function resolveProjectId($projectId): int
    {
        if ($projectId !== null && $projectId !== '') {
            return (int) $projectId;
        }

        return (int) $this->projectService->getDefaultProject()->id;
    }

    private function resolveIdeaId($ideaId): ?int
    {
        if ($ideaId === null || $ideaId === '') {
            return null;
        }

        return (int) $ideaId;
    }
}
