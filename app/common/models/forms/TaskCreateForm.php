<?php

namespace common\models\forms;

use common\models\Task;
use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use yii\base\Model;

class TaskCreateForm extends Model
{
    public $title;
    public $description;
    public $status = Task::STATUS_NEW;
    public $priority = Task::PRIORITY_MEDIUM;
    public $project_id;
    public $idea_id;
    public $due_date;

    public function rules(): array
    {
        return [
            [['title', 'status', 'priority'], 'required'],
            [['description'], 'string'],
            [['project_id', 'idea_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(Task::statusOptions())],
            [['priority'], 'in', 'range' => array_keys(Task::priorityOptions())],
            [['due_date'], 'safe'],
            [
                ['project_id'],
                'exist',
                'targetClass' => Project::class,
                'targetAttribute' => ['project_id' => 'id'],
                'skipOnEmpty' => true,
            ],
            [
                ['idea_id'],
                'exist',
                'targetClass' => Idea::class,
                'targetAttribute' => ['idea_id' => 'id'],
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->due_date = $this->normalizeDate($this->due_date);

        return true;
    }

    private function normalizeDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);
        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : (string) $value;
    }
}
