<?php

use common\modules\tasks\models\Idea;
use common\modules\tasks\models\Project;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\modules\tasks\models\IdeaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ideas';
$this->params['breadcrumbs'][] = $this->title;

$changeStatusUrl = Url::to(['change-status']);
$deleteUrl = Url::to(['delete']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$(document).on('change', '.js-idea-status-select', function () {
    const id = $(this).data('id');
    const status = $(this).val();
    const payload = {status: status};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$changeStatusUrl}?id=' + id, payload)
      .done(function (res) {
        if (!res.success) {
          alert(res.message || 'Failed to update status');
        }
        $.pjax.reload({container: '#idea-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to update status');
      });
});

$(document).on('click', '.js-delete-idea', function (e) {
    e.preventDefault();
    if (!confirm('Delete this idea?')) {
      return;
    }

    const id = $(this).data('id');
    const payload = {};
    payload['{$csrfParam}'] = '{$csrfToken}';

    $.post('{$deleteUrl}?id=' + id, payload)
      .done(function () {
        $.pjax.reload({container: '#idea-grid-pjax'});
      })
      .fail(function () {
        alert('Failed to delete idea');
      });
});
JS;

$this->registerJs($js);
?>

<div class="idea-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Idea', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Tasks', ['/tasks/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Manage Projects', ['/tasks/project/index'], ['class' => 'btn btn-outline-secondary']) ?>
    </p>

    <?php Pjax::begin(['id' => 'idea-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            [
                'attribute' => 'status',
                'filter' => Idea::statusOptions(),
                'format' => 'raw',
                'value' => static function (Idea $model): string {
                    return Html::dropDownList(
                        'status',
                        $model->status,
                        Idea::statusOptions(),
                        [
                            'class' => 'form-select form-select-sm js-idea-status-select',
                            'data-id' => $model->id,
                        ]
                    );
                },
            ],
            [
                'attribute' => 'project_id',
                'label' => 'Project',
                'filter' => ArrayHelper::map(Project::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                'value' => static fn(Idea $model): string => $model->project !== null ? $model->project->name : '-',
            ],
            'created_at:datetime',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'delete' => static function ($url, Idea $model): string {
                        return Html::a('Delete', '#', [
                            'class' => 'btn btn-sm btn-outline-danger js-delete-idea',
                            'data-id' => $model->id,
                        ]);
                    },
                    'view' => static function ($url, Idea $model): string {
                        return Html::a('View', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']);
                    },
                    'update' => static function ($url, Idea $model): string {
                        return Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
