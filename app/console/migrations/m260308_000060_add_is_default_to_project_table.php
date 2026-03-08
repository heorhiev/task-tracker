<?php

use yii\db\Migration;

class m260308_000060_add_is_default_to_project_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'is_default', $this->boolean()->notNull()->defaultValue(false));

        $this->execute('CREATE UNIQUE INDEX "uq-project-is-default-true" ON "project" ("is_default") WHERE "is_default" = true');

        $defaultProjectId = (new \yii\db\Query())
            ->from('{{%project}}')
            ->select('id')
            ->where(['name' => 'No Project'])
            ->scalar();

        if ($defaultProjectId === false || $defaultProjectId === null) {
            $defaultProjectId = (new \yii\db\Query())
                ->from('{{%project}}')
                ->select('id')
                ->orderBy(['id' => SORT_ASC])
                ->scalar();
        }

        if ($defaultProjectId !== false && $defaultProjectId !== null) {
            $this->update('{{%project}}', ['is_default' => true], ['id' => (int) $defaultProjectId]);
        }
    }

    public function safeDown()
    {
        $this->dropIndex('uq-project-is-default-true', '{{%project}}');
        $this->dropColumn('{{%project}}', 'is_default');
    }
}
