<?php

namespace common\modules\tasks\models\forms;

use yii\base\Model;

class IdeaDeleteForm extends Model
{
    public $id;

    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }
}
