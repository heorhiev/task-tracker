<?php

use yii\db\Migration;

class m260308_000040_add_project_id_to_task_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%task}}', 'project_id', $this->integer()->null()->after('priority'));
        $this->createIndex('idx-task-project-id', '{{%task}}', 'project_id');
        $this->addForeignKey(
            'fk-task-project-id',
            '{{%task}}',
            'project_id',
            '{{%project}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-task-project-id', '{{%task}}');
        $this->dropIndex('idx-task-project-id', '{{%task}}');
        $this->dropColumn('{{%task}}', 'project_id');
    }
}
