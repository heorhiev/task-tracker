<?php

namespace common\modules\tasks\controllers;

use common\modules\tasks\models\Project;
use common\modules\tasks\models\ProjectSearch;
use common\modules\tasks\services\ProjectService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProjectController extends Controller
{
    private ProjectService $projectService;

    public function __construct($id, $module, ProjectService $projectService = null, $config = [])
    {
        $this->projectService = $projectService ?? new ProjectService();
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
                    'set-default' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new ProjectSearch();
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
        $model = new Project([
            'status' => Project::STATUS_ACTIVE,
        ]);

        if ($this->projectService->create($model, Yii::$app->request->post())) {
            Yii::$app->session->setFlash('success', 'Project created.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->projectService->update($model, Yii::$app->request->post())) {
            Yii::$app->session->setFlash('success', 'Project updated.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        if ((bool) $model->is_default) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $this->asJson(['success' => false, 'message' => 'Default project cannot be deleted.']);
            }

            Yii::$app->session->setFlash('error', 'Default project cannot be deleted.');
            return $this->redirect(['index']);
        }

        $deleted = $model->delete() !== false;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->asJson(['success' => $deleted]);
        }

        Yii::$app->session->setFlash($deleted ? 'success' : 'error', $deleted ? 'Project deleted.' : 'Project not found.');
        return $this->redirect(['index']);
    }

    public function actionChangeStatus(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $status = (string) Yii::$app->request->post('status');

        if (!$this->projectService->changeStatus($model, $status)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid status value or unable to update status.',
            ]);
        }

        return $this->asJson([
            'success' => true,
            'message' => 'Status updated.',
        ]);
    }

    public function actionSetDefault(int $id): Response
    {
        $project = $this->projectService->setDefaultProject($id);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $this->asJson([
                'success' => $project !== null,
                'message' => $project !== null ? 'Default project updated.' : 'Project not found.',
            ]);
        }

        Yii::$app->session->setFlash($project !== null ? 'success' : 'error', $project !== null ? 'Default project updated.' : 'Project not found.');

        return $this->redirect(['index']);
    }

    protected function findModel(int $id): Project
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested project does not exist.');
    }
}
