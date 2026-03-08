<?php

use yii\db\Migration;

class m260308_000050_seed_default_project extends Migration
{
    private const DEFAULT_PROJECT_ID = 1;

    public function safeUp()
    {
        $project = (new \yii\db\Query())
            ->from('{{%project}}')
            ->where(['id' => self::DEFAULT_PROJECT_ID])
            ->one();

        if ($project === false || $project === null) {
            $this->insert('{{%project}}', [
                'id' => self::DEFAULT_PROJECT_ID,
                'name' => 'No Project',
                'status' => 'active',
                'created_at' => new \yii\db\Expression('NOW()'),
                'updated_at' => new \yii\db\Expression('NOW()'),
            ]);
        }

        $this->update('{{%task}}', ['project_id' => self::DEFAULT_PROJECT_ID], ['project_id' => null]);
    }

    public function safeDown()
    {
        if ((new \yii\db\Query())->from('{{%project}}')->where(['id' => self::DEFAULT_PROJECT_ID])->exists()) {
            $this->update('{{%task}}', ['project_id' => null], ['project_id' => self::DEFAULT_PROJECT_ID]);
            $this->delete('{{%project}}', ['id' => self::DEFAULT_PROJECT_ID]);
        }
    }
}
