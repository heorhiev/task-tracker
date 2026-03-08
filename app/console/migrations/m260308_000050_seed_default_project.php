<?php

use common\modules\tasks\models\Project;
use yii\db\Migration;

class m260308_000050_seed_default_project extends Migration
{
    public function safeUp()
    {
        $project = (new \yii\db\Query())
            ->from('{{%project}}')
            ->where(['id' => Project::DEFAULT_PROJECT_ID])
            ->one();

        if ($project === false || $project === null) {
            $this->insert('{{%project}}', [
                'id' => Project::DEFAULT_PROJECT_ID,
                'name' => 'No name',
                'status' => Project::STATUS_ACTIVE,
                'created_at' => new \yii\db\Expression('NOW()'),
                'updated_at' => new \yii\db\Expression('NOW()'),
            ]);
        }

        $this->update('{{%task}}', ['project_id' => Project::DEFAULT_PROJECT_ID], ['project_id' => null]);
    }

    public function safeDown()
    {
        if (Project::findOne(Project::DEFAULT_PROJECT_ID) !== null) {
            $this->update('{{%task}}', ['project_id' => null], ['project_id' => Project::DEFAULT_PROJECT_ID]);
            $this->delete('{{%project}}', ['id' => Project::DEFAULT_PROJECT_ID]);
        }
    }
}
