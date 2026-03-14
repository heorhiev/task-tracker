<?php

namespace common\modules\tasks;

use common\components\CommandRegistryComponent;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        if (!$app instanceof Application || !$app->has('commandRegistry')) {
            return;
        }

        $commandRegistry = $app->get('commandRegistry');
        if (!$commandRegistry instanceof CommandRegistryComponent) {
            return;
        }

        $commandRegistry->register(commands\CreateProjectCommand::class);
        $commandRegistry->register(commands\SetDefaultProjectCommand::class);
        $commandRegistry->register(commands\CreateTaskCommand::class);
        $commandRegistry->register(commands\CreateIdeaCommand::class);
        $commandRegistry->register(commands\GetDefaultProjectCommand::class);
    }
}
