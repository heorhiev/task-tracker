<?php

use yii\db\Migration;

class m260314_000100_create_idea_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%idea}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'status' => $this->string(20)->notNull()->defaultValue('new'),
            'project_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-idea-status', '{{%idea}}', 'status');
        $this->createIndex('idx-idea-project-id', '{{%idea}}', 'project_id');
        $this->createIndex('idx-idea-created-at', '{{%idea}}', 'created_at');
        $this->addForeignKey(
            'fk-idea-project-id',
            '{{%idea}}',
            'project_id',
            '{{%project}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-idea-project-id', '{{%idea}}');
        $this->dropTable('{{%idea}}');
    }
}
