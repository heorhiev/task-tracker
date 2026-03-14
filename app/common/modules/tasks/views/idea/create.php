<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var common\modules\tasks\models\forms\IdeaCreateForm $model */

$this->title = 'Create Idea';
$this->params['breadcrumbs'][] = ['label' => 'Ideas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="idea-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
