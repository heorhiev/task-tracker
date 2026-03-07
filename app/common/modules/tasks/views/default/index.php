<?php

use common\models\Task;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\models\TaskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Tasks';
$this->params['breadcrumbs'][] = $this->title;

$changeStatusUrl = Url::to(['change-status']);
$deleteUrl = Url::to(['delete']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$(document).on('change', '.js-status-select', function () {
    const id = $(this).data('id');
    const status = $(this).val();
    const payload = {status: status};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$changeStatusUrl}?id=' + id, payload)
      .done(function (res) {
        if (!res.success) {
          alert(res.message || 'Failed to update status');
        }
        $.pjax.reload({container: '#task-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to update status');
      });
});

$(document).on('click', '.js-delete-task', function (e) {
    e.preventDefault();
    if (!confirm('Delete this task?')) {
      return;
    }

    const id = $(this).data('id');
    const payload = {};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$deleteUrl}?id=' + id, payload)
      .done(function () {
        $.pjax.reload({container: '#task-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to delete task');
      });
});
JS;

$this->registerJs($js);
?>

<div class="task-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Task', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(['id' => 'task-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            [
                'attribute' => 'status',
                'filter' => Task::statusOptions(),
                'format' => 'raw',
                'value' => static function (Task $model): string {
                    return Html::dropDownList(
                        'status',
                        $model->status,
                        Task::statusOptions(),
                        [
                            'class' => 'form-select form-select-sm js-status-select',
                            'data-id' => $model->id,
                        ]
                    );
                },
            ],
            [
                'attribute' => 'priority',
                'filter' => Task::priorityOptions(),
                'value' => static fn(Task $model): string => Task::priorityOptions()[$model->priority] ?? $model->priority,
            ],
            'due_date:datetime',
            'created_at:datetime',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'delete' => static function ($url, Task $model): string {
                        return Html::a('Delete', '#', [
                            'class' => 'btn btn-sm btn-outline-danger js-delete-task',
                            'data-id' => $model->id,
                        ]);
                    },
                    'view' => static function ($url, Task $model): string {
                        return Html::a('View', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']);
                    },
                    'update' => static function ($url, Task $model): string {
                        return Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
