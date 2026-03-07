<?php

namespace common\modules\tasks\controllers;

use common\models\forms\TaskCreateForm;
use common\models\forms\TaskDeleteForm;
use common\models\forms\TaskUpdateForm;
use common\models\Task;
use common\models\TaskSearch;
use common\modules\tasks\services\TaskService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    private TaskService $taskService;

    public function __construct($id, $module, TaskService $taskService = null, $config = [])
    {
        $this->taskService = $taskService ?? new TaskService();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $form = new TaskCreateForm();

        if ($form->load(Yii::$app->request->post()) && $this->taskService->create($form) !== null) {
            Yii::$app->session->setFlash('success', 'Task created.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $task = $this->findModel($id);
        $form = TaskUpdateForm::fromTask($task);

        if ($form->load(Yii::$app->request->post()) && $this->taskService->update($id, $form) !== null) {
            Yii::$app->session->setFlash('success', 'Task updated.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $form,
            'task' => $task,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $form = new TaskDeleteForm(['id' => $id]);
        $deleted = $this->taskService->delete($form);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->asJson(['success' => $deleted]);
        }

        Yii::$app->session->setFlash($deleted ? 'success' : 'error', $deleted ? 'Task deleted.' : 'Task not found.');
        return $this->redirect(['index']);
    }

    public function actionChangeStatus(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $status = Yii::$app->request->post('status');

        if (!array_key_exists((string) $status, Task::statusOptions())) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid status value.',
            ]);
        }

        $model->status = (string) $status;

        if (!$model->save(true, ['status', 'updated_at'])) {
            return $this->asJson([
                'success' => false,
                'message' => 'Unable to update status.',
            ]);
        }

        return $this->asJson([
            'success' => true,
            'message' => 'Status updated.',
        ]);
    }

    protected function findModel(int $id): Task
    {
        if (($model = Task::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested task does not exist.');
    }
}
