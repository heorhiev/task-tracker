<?php

namespace common\models\forms;

use common\models\Task;

class TaskUpdateForm extends TaskCreateForm
{
    public static function fromTask(Task $task): self
    {
        $form = new self();
        $form->title = $task->title;
        $form->description = $task->description;
        $form->status = $task->status;
        $form->priority = $task->priority;
        $form->due_date = $task->due_date !== null ? date('Y-m-d\TH:i', strtotime((string) $task->due_date)) : null;

        return $form;
    }
}
