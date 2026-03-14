<?php

use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use yii\base\Model;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var Model $model */
?>

<div class="idea-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>

    <?= $form->field($model, 'status')->dropDownList(Idea::statusOptions()) ?>

    <?= $form->field($model, 'project_id')->dropDownList(
        ArrayHelper::map(Project::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
        ['prompt' => 'No project']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
