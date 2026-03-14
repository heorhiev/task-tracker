<?php

namespace common\modules\tasks\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property int|null $project_id
 * @property string $created_at
 * @property string $updated_at
 * @property-read Project|null $project
 */
class Idea extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_ARCHIVED = 'archived';

    public static function tableName(): string
    {
        return '{{%idea}}';
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
            [['title', 'status'], 'required'],
            [['description'], 'string'],
            [['project_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
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
            'project_id' => 'Project',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProject(): ActiveQuery
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_REVIEWING => 'Reviewing',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}
