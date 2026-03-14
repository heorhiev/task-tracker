<?php

use yii\db\Migration;

class m260314_000070_create_stored_file_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%stored_file}}', [
            'id' => $this->primaryKey(),
            'storage' => $this->string(32)->notNull(),
            'path' => $this->string(255)->notNull(),
            'original_name' => $this->string(255)->null(),
            'extension' => $this->string(32)->null(),
            'mime_type' => $this->string(128)->null(),
            'size_bytes' => $this->integer()->notNull()->defaultValue(0),
            'checksum_sha256' => $this->string(64)->notNull(),
            'source' => $this->string(32)->notNull(),
            'source_file_id' => $this->string(255)->null(),
            'source_unique_id' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('ux-stored_file-path', '{{%stored_file}}', 'path', true);
        $this->createIndex('idx-stored_file-storage', '{{%stored_file}}', 'storage');
        $this->createIndex('idx-stored_file-source', '{{%stored_file}}', 'source');
        $this->createIndex('idx-stored_file-source_file_id', '{{%stored_file}}', 'source_file_id');
        $this->createIndex('idx-stored_file-source_unique_id', '{{%stored_file}}', 'source_unique_id');
        $this->createIndex('idx-stored_file-checksum_sha256', '{{%stored_file}}', 'checksum_sha256');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-stored_file-checksum_sha256', '{{%stored_file}}');
        $this->dropIndex('idx-stored_file-source_unique_id', '{{%stored_file}}');
        $this->dropIndex('idx-stored_file-source_file_id', '{{%stored_file}}');
        $this->dropIndex('idx-stored_file-source', '{{%stored_file}}');
        $this->dropIndex('idx-stored_file-storage', '{{%stored_file}}');
        $this->dropIndex('ux-stored_file-path', '{{%stored_file}}');

        $this->dropTable('{{%stored_file}}');
    }
}
