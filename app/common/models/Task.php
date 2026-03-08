<?php

namespace common\models;

use common\modules\tasks\models\Project;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property int|null $project_id
 * @property string|null $due_date
 * @property string $created_at
 * @property string $updated_at
 * @property-read Project|null $project
 */
class Task extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public static function tableName(): string
    {
        return '{{%task}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['title', 'status', 'priority'], 'required'],
            [['description'], 'string'],
            [['project_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['priority'], 'in', 'range' => array_keys(self::priorityOptions())],
            [['due_date'], 'safe'],
            [
                ['project_id'],
                'exist',
                'targetClass' => Project::class,
                'targetAttribute' => ['project_id' => 'id'],
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'status' => 'Status',
            'priority' => 'Priority',
            'project_id' => 'Project',
            'due_date' => 'Due Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProject(): ActiveQuery
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->due_date === '') {
            $this->due_date = null;
        }

        if ($this->due_date !== null && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) $this->due_date) === 1) {
            $this->due_date = date('Y-m-d H:i:s', strtotime((string) $this->due_date));
        }

        return true;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_DONE => 'Done',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
        ];
    }
}
