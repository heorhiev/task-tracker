<?php

use common\models\Task;
use common\modules\tasks\models\Project;
use yii\base\Model;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var Model $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'status')->dropDownList(Task::statusOptions()) ?>

    <?= $form->field($model, 'priority')->dropDownList(Task::priorityOptions()) ?>

    <?= $form->field($model, 'project_id')->dropDownList(
        ArrayHelper::map(Project::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name')
    ) ?>

    <?= $form->field($model, 'due_date')->input('datetime-local') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
