<?php

use yii\db\Migration;

class m260314_000090_create_inbox_message_attachment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%inbox_message_attachment}}', [
            'id' => $this->primaryKey(),
            'inbox_message_id' => $this->integer()->notNull(),
            'stored_file_id' => $this->integer()->notNull(),
            'role' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-inbox_message_attachment-inbox_message_id', '{{%inbox_message_attachment}}', 'inbox_message_id');
        $this->createIndex('idx-inbox_message_attachment-stored_file_id', '{{%inbox_message_attachment}}', 'stored_file_id');
        $this->createIndex('idx-inbox_message_attachment-role', '{{%inbox_message_attachment}}', 'role');
        $this->createIndex(
            'ux-inbox_message_attachment-message-file-role',
            '{{%inbox_message_attachment}}',
            ['inbox_message_id', 'stored_file_id', 'role'],
            true
        );

        $this->addForeignKey(
            'fk-inbox_message_attachment-inbox_message_id',
            '{{%inbox_message_attachment}}',
            'inbox_message_id',
            '{{%inbox_message}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-inbox_message_attachment-stored_file_id',
            '{{%inbox_message_attachment}}',
            'stored_file_id',
            '{{%stored_file}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-inbox_message_attachment-stored_file_id', '{{%inbox_message_attachment}}');
        $this->dropForeignKey('fk-inbox_message_attachment-inbox_message_id', '{{%inbox_message_attachment}}');

        $this->dropIndex('ux-inbox_message_attachment-message-file-role', '{{%inbox_message_attachment}}');
        $this->dropIndex('idx-inbox_message_attachment-role', '{{%inbox_message_attachment}}');
        $this->dropIndex('idx-inbox_message_attachment-stored_file_id', '{{%inbox_message_attachment}}');
        $this->dropIndex('idx-inbox_message_attachment-inbox_message_id', '{{%inbox_message_attachment}}');

        $this->dropTable('{{%inbox_message_attachment}}');
    }
}
