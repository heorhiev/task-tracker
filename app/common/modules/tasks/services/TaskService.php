<?php

namespace common\modules\tasks\services;

use common\models\forms\TaskCreateForm;
use common\models\forms\TaskDeleteForm;
use common\models\forms\TaskUpdateForm;
use common\models\Task;

class TaskService
{
    public function create(TaskCreateForm $form): ?Task
    {
        if (!$form->validate()) {
            return null;
        }

        $task = new Task();
        $task->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'priority' => $form->priority,
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

        if (!$form->validate()) {
            return null;
        }

        $task->setAttributes([
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'priority' => $form->priority,
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
}
