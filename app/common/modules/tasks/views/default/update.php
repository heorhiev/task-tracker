<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var common\models\forms\TaskUpdateForm $model */
/** @var common\models\Task $task */

$this->title = 'Update Task: ' . $task->title;
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $task->title, 'url' => ['view', 'id' => $task->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="task-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
