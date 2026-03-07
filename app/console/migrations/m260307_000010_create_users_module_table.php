<?php

use yii\db\Migration;

class m260307_000010_create_users_module_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string(255)->notNull(),
            'password' => $this->string(255)->notNull(),
            'role' => $this->string(32)->notNull()->defaultValue('user'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uq-users-email', '{{%users}}', 'email', true);
        $this->createIndex('idx-users-role', '{{%users}}', 'role');

        $hashedPassword = Yii::$app->security->generatePasswordHash('Admin12345!');

        $this->insert('{{%users}}', [
            'email' => 'admin@example.com',
            'password' => $hashedPassword,
            'role' => 'admin',
            'created_at' => new \yii\db\Expression('NOW()'),
            'updated_at' => new \yii\db\Expression('NOW()'),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%users}}');
    }
}
