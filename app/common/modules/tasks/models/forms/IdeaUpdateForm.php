<?php

namespace common\modules\tasks\models\forms;

use common\modules\tasks\models\Idea;

class IdeaUpdateForm extends IdeaCreateForm
{
    public static function fromIdea(Idea $idea): self
    {
        $form = new self();
        $form->title = $idea->title;
        $form->description = $idea->description;
        $form->status = $idea->status;
        $form->project_id = $idea->project_id;

        return $form;
    }
}
