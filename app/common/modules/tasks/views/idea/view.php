<?php

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\modules\tasks\models\Idea $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Ideas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="idea-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'description:ntext',
            'status',
            [
                'attribute' => 'project_id',
                'label' => 'Project',
                'value' => $model->project !== null ? $model->project->name : '-',
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
