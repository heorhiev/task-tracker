<?php

use common\modules\tasks\models\Project;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\modules\tasks\models\ProjectSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;

$changeStatusUrl = Url::to(['change-status']);
$deleteUrl = Url::to(['delete']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$(document).on('change', '.js-project-status-select', function () {
    const id = $(this).data('id');
    const status = $(this).val();
    const payload = {status: status};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$changeStatusUrl}?id=' + id, payload)
      .done(function (res) {
        if (!res.success) {
          alert(res.message || 'Failed to update status');
        }
        $.pjax.reload({container: '#project-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to update status');
      });
});

$(document).on('click', '.js-delete-project', function (e) {
    e.preventDefault();
    if (!confirm('Delete this project?')) {
      return;
    }

    const id = $(this).data('id');
    const payload = {};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$deleteUrl}?id=' + id, payload)
      .done(function () {
        $.pjax.reload({container: '#project-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to delete project');
      });
});
JS;

$this->registerJs($js);
?>

<div class="project-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(['id' => 'project-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\\grid\\SerialColumn'],
            'name',
            [
                'attribute' => 'status',
                'filter' => Project::statusOptions(),
                'format' => 'raw',
                'value' => static function (Project $model): string {
                    return Html::dropDownList(
                        'status',
                        $model->status,
                        Project::statusOptions(),
                        [
                            'class' => 'form-select form-select-sm js-project-status-select',
                            'data-id' => $model->id,
                        ]
                    );
                },
            ],
            'created_at:datetime',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'delete' => static function ($url, Project $model): string {
                        if ((int) $model->id === Project::DEFAULT_PROJECT_ID) {
                            return '';
                        }

                        return Html::a('Delete', '#', [
                            'class' => 'btn btn-sm btn-outline-danger js-delete-project',
                            'data-id' => $model->id,
                        ]);
                    },
                    'view' => static function ($url, Project $model): string {
                        return Html::a('View', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']);
                    },
                    'update' => static function ($url, Project $model): string {
                        return Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
