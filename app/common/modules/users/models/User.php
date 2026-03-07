<?php

namespace common\modules\users\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    public static function tableName(): string
    {
        return '{{%users}}';
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
            [['email', 'password', 'role'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['password'], 'string', 'max' => 255],
            [['api_key'], 'string', 'max' => 255],
            [['role'], 'in', 'range' => array_keys(self::roleOptions())],
            [['email'], 'unique'],
            [['api_key'], 'unique'],
        ];
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_USER => 'User',
        ];
    }

    public function setPassword(string $plainPassword): void
    {
        $this->password = Yii::$app->security->generatePasswordHash($plainPassword);
    }

    public function validatePassword(string $plainPassword): bool
    {
        return Yii::$app->security->validatePassword($plainPassword, $this->password);
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne((int) $id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public static function findByEmail(string $email): ?self
    {
        return static::find()->where(['email' => $email])->one();
    }

    public static function findByApiKey(string $apiKey): ?self
    {
        return static::find()->where(['api_key' => $apiKey])->one();
    }
}
