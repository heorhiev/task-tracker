<?php

namespace common\modules\tasks\models\forms;

use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use yii\base\Model;

class IdeaCreateForm extends Model
{
    public $title;
    public $description;
    public $status = Idea::STATUS_NEW;
    public $project_id;

    public function rules(): array
    {
        return [
            [['title', 'status'], 'required'],
            [['description'], 'string'],
            [['project_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(Idea::statusOptions())],
            [
                ['project_id'],
                'exist',
                'targetClass' => Project::class,
                'targetAttribute' => ['project_id' => 'id'],
                'skipOnEmpty' => true,
            ],
        ];
    }
}
