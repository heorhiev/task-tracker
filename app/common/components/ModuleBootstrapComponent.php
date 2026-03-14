<?php

namespace common\components;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module;

class ModuleBootstrapComponent extends \yii\base\Component implements BootstrapInterface
{
    public string $bootstrapKey = 'bootstrapClass';

    public function bootstrap($app): void
    {
        if (!$app instanceof Application) {
            return;
        }

        $app->setModules($this->processModuleDefinitions($app->getModules(false), $app));
    }

    /**
     * @param array<string, array|Module> $definitions
     * @return array<string, array|Module>
     */
    private function processModuleDefinitions(array $definitions, Application $app): array
    {
        $processedDefinitions = [];

        foreach ($definitions as $id => $definition) {
            if ($definition instanceof Module) {
                $definition->setModules($this->processModuleDefinitions($definition->getModules(false), $app));
                $processedDefinitions[$id] = $definition;
                continue;
            }

            if (!is_array($definition)) {
                $processedDefinitions[$id] = $definition;
                continue;
            }

            $bootstrapDefinition = $definition[$this->bootstrapKey] ?? null;
            unset($definition[$this->bootstrapKey]);

            if (isset($definition['modules']) && is_array($definition['modules'])) {
                $definition['modules'] = $this->processModuleDefinitions($definition['modules'], $app);
            }

            if ($bootstrapDefinition !== null) {
                $this->runBootstrap($bootstrapDefinition, $app);
            }

            $processedDefinitions[$id] = $definition;
        }

        return $processedDefinitions;
    }

    private function runBootstrap(mixed $definition, Application $app): void
    {
        $bootstrapper = is_object($definition) ? $definition : Yii::createObject($definition);

        if ($bootstrapper instanceof BootstrapInterface) {
            $bootstrapper->bootstrap($app);

            return;
        }

        if (is_object($bootstrapper) && method_exists($bootstrapper, 'bootstrap')) {
            $bootstrapper->bootstrap($app);

            return;
        }

        throw new InvalidConfigException(sprintf(
            'Module bootstrap definition must implement %s or expose a bootstrap() method.',
            BootstrapInterface::class
        ));
    }
}
