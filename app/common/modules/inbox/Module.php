<?php

namespace common\modules\inbox;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'common\\modules\\inbox\\controllers';

    public function init(): void
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'common\\modules\\inbox\\controllers\\console';
        }
    }
}
