<?php

use yii\db\Migration;

class m260314_000110_add_idea_id_to_task_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%task}}', 'idea_id', $this->integer()->null()->after('project_id'));
        $this->createIndex('idx-task-idea-id', '{{%task}}', 'idea_id');
        $this->addForeignKey(
            'fk-task-idea-id',
            '{{%task}}',
            'idea_id',
            '{{%idea}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-task-idea-id', '{{%task}}');
        $this->dropIndex('idx-task-idea-id', '{{%task}}');
        $this->dropColumn('{{%task}}', 'idea_id');
    }
}
