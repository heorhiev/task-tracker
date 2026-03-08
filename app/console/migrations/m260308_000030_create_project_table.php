<?php

use yii\db\Migration;

class m260308_000030_create_project_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('active'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-project-status', '{{%project}}', 'status');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-project-status', '{{%project}}');
        $this->dropTable('{{%project}}');
    }
}
