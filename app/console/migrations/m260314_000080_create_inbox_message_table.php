<?php

use yii\db\Migration;

class m260314_000080_create_inbox_message_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%inbox_message}}', [
            'id' => $this->primaryKey(),
            'source' => $this->string(32)->notNull(),
            'external_message_id' => $this->string(255)->null(),
            'external_chat_id' => $this->string(255)->null(),
            'external_user_id' => $this->string(255)->null(),
            'message_type' => $this->string(32)->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('pending'),
            'text_raw' => $this->text()->null(),
            'transcription_text' => $this->text()->null(),
            'resolved_command' => $this->text()->null(),
            'processing_error' => $this->text()->null(),
            'attempt_count' => $this->integer()->notNull()->defaultValue(0),
            'received_at' => $this->dateTime()->null(),
            'processed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-inbox_message-source', '{{%inbox_message}}', 'source');
        $this->createIndex('idx-inbox_message-message_type', '{{%inbox_message}}', 'message_type');
        $this->createIndex('idx-inbox_message-status', '{{%inbox_message}}', 'status');
        $this->createIndex('idx-inbox_message-external_message_id', '{{%inbox_message}}', 'external_message_id');
        $this->createIndex('idx-inbox_message-external_chat_id', '{{%inbox_message}}', 'external_chat_id');
        $this->createIndex('idx-inbox_message-external_user_id', '{{%inbox_message}}', 'external_user_id');
        $this->createIndex('idx-inbox_message-received_at', '{{%inbox_message}}', 'received_at');
        $this->createIndex('idx-inbox_message-processed_at', '{{%inbox_message}}', 'processed_at');
        $this->createIndex(
            'ux-inbox_message-source-external_message_id',
            '{{%inbox_message}}',
            ['source', 'external_message_id'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex('ux-inbox_message-source-external_message_id', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-processed_at', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-received_at', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-external_user_id', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-external_chat_id', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-external_message_id', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-status', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-message_type', '{{%inbox_message}}');
        $this->dropIndex('idx-inbox_message-source', '{{%inbox_message}}');

        $this->dropTable('{{%inbox_message}}');
    }
}
