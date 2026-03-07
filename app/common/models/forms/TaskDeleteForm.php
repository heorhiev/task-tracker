<?php

namespace common\models\forms;

use yii\base\Model;

class TaskDeleteForm extends Model
{
    public $id;

    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer', 'min' => 1],
        ];
    }
}
