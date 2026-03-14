<?php

namespace common\tests\unit\components;

use common\components\ModuleBootstrapComponent;
use Codeception\Test\Unit;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application;

class ModuleBootstrapComponentTest extends Unit
{
    private ?\yii\base\Application $previousApp = null;

    protected function _before(): void
    {
        parent::_before();
        $this->previousApp = Yii::$app;
        TestModuleBootstrap::$calls = [];
        NestedTestModuleBootstrap::$calls = [];
    }

    protected function _after(): void
    {
        if ($this->previousApp !== null) {
            Yii::$app = $this->previousApp;
        }

        parent::_after();
    }

    public function testBootstrapRunsModuleBootstrapClassesAndSanitizesDefinitions(): void
    {
        $app = new Application([
            'id' => 'module-bootstrap-test',
            'basePath' => YII_APP_BASE_PATH . '/common',
            'vendorPath' => YII_APP_BASE_PATH . '/vendor',
            'bootstrap' => [],
            'components' => [],
            'modules' => [
                'demo' => [
                    'class' => TestModule::class,
                    'bootstrapClass' => TestModuleBootstrap::class,
                    'modules' => [
                        'nested' => [
                            'class' => NestedTestModule::class,
                            'bootstrapClass' => NestedTestModuleBootstrap::class,
                        ],
                    ],
                ],
            ],
        ]);

        $component = new ModuleBootstrapComponent();
        $component->bootstrap($app);

        $modules = $app->getModules(false);
        $this->assertArrayHasKey('demo', $modules);
        $this->assertIsArray($modules['demo']);
        $this->assertArrayNotHasKey('bootstrapClass', $modules['demo']);
        $this->assertIsArray($modules['demo']['modules']);
        $this->assertArrayNotHasKey('bootstrapClass', $modules['demo']['modules']['nested']);

        $this->assertSame(['module-bootstrap-test'], TestModuleBootstrap::$calls);
        $this->assertSame(['module-bootstrap-test'], NestedTestModuleBootstrap::$calls);

        $this->assertInstanceOf(TestModule::class, $app->getModule('demo'));
        $this->assertInstanceOf(NestedTestModule::class, $app->getModule('demo')->getModule('nested'));
    }
}

class TestModule extends \yii\base\Module
{
}

class NestedTestModule extends \yii\base\Module
{
}

class TestModuleBootstrap implements BootstrapInterface
{
    /**
     * @var string[]
     */
    public static array $calls = [];

    public function bootstrap($app): void
    {
        self::$calls[] = $app->id;
    }
}

class NestedTestModuleBootstrap implements BootstrapInterface
{
    /**
     * @var string[]
     */
    public static array $calls = [];

    public function bootstrap($app): void
    {
        self::$calls[] = $app->id;
    }
}
