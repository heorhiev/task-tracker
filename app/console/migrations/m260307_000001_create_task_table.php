<?php

use yii\db\Migration;

class m260307_000001_create_task_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'status' => $this->string(20)->notNull()->defaultValue('new'),
            'priority' => $this->string(20)->notNull()->defaultValue('medium'),
            'due_date' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-task-status', '{{%task}}', 'status');
        $this->createIndex('idx-task-priority', '{{%task}}', 'priority');
        $this->createIndex('idx-task-due-date', '{{%task}}', 'due_date');
        $this->createIndex('idx-task-created-at', '{{%task}}', 'created_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%task}}');
    }
}
