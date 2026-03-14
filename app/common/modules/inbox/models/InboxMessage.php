<?php

namespace common\modules\inbox\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $source
 * @property string|null $external_message_id
 * @property string|null $external_chat_id
 * @property string|null $external_user_id
 * @property string $message_type
 * @property string $status
 * @property string|null $text_raw
 * @property string|null $transcription_text
 * @property string|null $resolved_command
 * @property string|null $processing_error
 * @property int $attempt_count
 * @property string|null $received_at
 * @property string|null $processed_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property InboxMessageAttachment[] $attachments
 */
class InboxMessage extends ActiveRecord
{
    public const SOURCE_TELEGRAM = 'telegram';

    public const TYPE_TEXT = 'text';
    public const TYPE_VOICE = 'voice';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_DOCUMENT = 'document';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_TRANSCRIBED = 'transcribed';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    public static function tableName(): string
    {
        return '{{%inbox_message}}';
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
            [['source', 'message_type', 'status'], 'required'],
            [['text_raw', 'transcription_text', 'resolved_command', 'processing_error'], 'string'],
            [['attempt_count'], 'integer', 'min' => 0],
            [['attempt_count'], 'default', 'value' => 0],
            [['source'], 'string', 'max' => 32],
            [['message_type', 'status'], 'string', 'max' => 32],
            [['external_message_id', 'external_chat_id', 'external_user_id'], 'string', 'max' => 255],
            [['received_at', 'processed_at'], 'safe'],
            [['source'], 'in', 'range' => array_keys(self::sourceOptions())],
            [['message_type'], 'in', 'range' => array_keys(self::messageTypeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'source' => 'Source',
            'external_message_id' => 'External Message ID',
            'external_chat_id' => 'External Chat ID',
            'external_user_id' => 'External User ID',
            'message_type' => 'Message Type',
            'status' => 'Status',
            'text_raw' => 'Text Raw',
            'transcription_text' => 'Transcription Text',
            'resolved_command' => 'Resolved Command',
            'processing_error' => 'Processing Error',
            'attempt_count' => 'Attempt Count',
            'received_at' => 'Received At',
            'processed_at' => 'Processed At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->hasMany(InboxMessageAttachment::class, ['inbox_message_id' => 'id']);
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_TELEGRAM => 'Telegram',
        ];
    }

    public static function messageTypeOptions(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_VOICE => 'Voice',
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_DOCUMENT => 'Document',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_TRANSCRIBED => 'Transcribed',
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_IGNORED => 'Ignored',
        ];
    }
}
