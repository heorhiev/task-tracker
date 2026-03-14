<?php

namespace common\modules\fileManager\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $storage
 * @property string $path
 * @property string|null $original_name
 * @property string|null $extension
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property string $checksum_sha256
 * @property string $source
 * @property string|null $source_file_id
 * @property string|null $source_unique_id
 * @property string $created_at
 * @property string $updated_at
 */
class StoredFile extends ActiveRecord
{
    public const STORAGE_LOCAL = 'local';

    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_TELEGRAM = 'telegram';
    public const SOURCE_SYSTEM = 'system';

    public static function tableName(): string
    {
        return '{{%stored_file}}';
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
            [['storage', 'path', 'size_bytes', 'checksum_sha256', 'source'], 'required'],
            [['size_bytes'], 'integer', 'min' => 0],
            [['storage', 'source'], 'string', 'max' => 32],
            [['path', 'original_name'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 32],
            [['mime_type'], 'string', 'max' => 128],
            [['checksum_sha256'], 'string', 'length' => 64],
            [['source_file_id', 'source_unique_id'], 'string', 'max' => 255],
            [['path'], 'unique'],
            [['checksum_sha256'], 'match', 'pattern' => '/^[a-f0-9]{64}$/'],
            [['storage'], 'in', 'range' => array_keys(self::storageOptions())],
            [['source'], 'in', 'range' => array_keys(self::sourceOptions())],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'storage' => 'Storage',
            'path' => 'Path',
            'original_name' => 'Original Name',
            'extension' => 'Extension',
            'mime_type' => 'Mime Type',
            'size_bytes' => 'Size Bytes',
            'checksum_sha256' => 'Checksum Sha256',
            'source' => 'Source',
            'source_file_id' => 'Source File ID',
            'source_unique_id' => 'Source Unique ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function storageOptions(): array
    {
        return [
            self::STORAGE_LOCAL => 'Local',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_UPLOAD => 'Upload',
            self::SOURCE_TELEGRAM => 'Telegram',
            self::SOURCE_SYSTEM => 'System',
        ];
    }
}
