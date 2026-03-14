<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var common\modules\tasks\models\forms\IdeaUpdateForm $model */
/** @var common\modules\tasks\models\Idea $idea */

$this->title = 'Update Idea: ' . $idea->title;
$this->params['breadcrumbs'][] = ['label' => 'Ideas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $idea->title, 'url' => ['view', 'id' => $idea->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="idea-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
