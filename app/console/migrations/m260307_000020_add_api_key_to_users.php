<?php

use yii\db\Migration;

class m260307_000020_add_api_key_to_users extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%users}}', 'api_key', $this->string(255)->null());
        $this->createIndex('uq-users-api-key', '{{%users}}', 'api_key', true);

        $apiKey = Yii::$app->security->generateRandomString(64);

        $this->update('{{%users}}', ['api_key' => $apiKey], ['id' => 1]);
    }

    public function safeDown(): void
    {
        $this->dropIndex('uq-users-api-key', '{{%users}}');
        $this->dropColumn('{{%users}}', 'api_key');
    }
}
