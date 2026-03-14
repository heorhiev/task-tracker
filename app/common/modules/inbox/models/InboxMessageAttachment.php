<?php

namespace common\modules\inbox\models;

use common\modules\fileManager\models\StoredFile;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $inbox_message_id
 * @property int $stored_file_id
 * @property string $role
 * @property string $created_at
 * @property string $updated_at
 *
 * @property InboxMessage $inboxMessage
 * @property StoredFile $storedFile
 */
class InboxMessageAttachment extends ActiveRecord
{
    public const ROLE_ORIGINAL = 'original';
    public const ROLE_NORMALIZED = 'normalized';
    public const ROLE_TRANSCRIPT_ATTACHMENT = 'transcript_attachment';

    public static function tableName(): string
    {
        return '{{%inbox_message_attachment}}';
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
            [['inbox_message_id', 'stored_file_id', 'role'], 'required'],
            [['inbox_message_id', 'stored_file_id'], 'integer'],
            [['role'], 'string', 'max' => 64],
            [['role'], 'in', 'range' => array_keys(self::roleOptions())],
            [['inbox_message_id', 'stored_file_id', 'role'], 'unique', 'targetAttribute' => ['inbox_message_id', 'stored_file_id', 'role']],
            [['inbox_message_id'], 'exist', 'skipOnError' => true, 'targetClass' => InboxMessage::class, 'targetAttribute' => ['inbox_message_id' => 'id']],
            [['stored_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoredFile::class, 'targetAttribute' => ['stored_file_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'inbox_message_id' => 'Inbox Message ID',
            'stored_file_id' => 'Stored File ID',
            'role' => 'Role',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getInboxMessage(): ActiveQuery
    {
        return $this->hasOne(InboxMessage::class, ['id' => 'inbox_message_id']);
    }

    public function getStoredFile(): ActiveQuery
    {
        return $this->hasOne(StoredFile::class, ['id' => 'stored_file_id']);
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_ORIGINAL => 'Original',
            self::ROLE_NORMALIZED => 'Normalized',
            self::ROLE_TRANSCRIPT_ATTACHMENT => 'Transcript Attachment',
        ];
    }
}
