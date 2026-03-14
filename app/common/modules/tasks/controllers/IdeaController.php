<?php

namespace common\modules\tasks\controllers;

use common\modules\tasks\models\forms\IdeaCreateForm;
use common\modules\tasks\models\forms\IdeaDeleteForm;
use common\modules\tasks\models\forms\IdeaUpdateForm;
use common\modules\tasks\models\Idea;
use common\modules\tasks\models\IdeaSearch;
use common\modules\tasks\services\IdeaService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class IdeaController extends Controller
{
    private IdeaService $ideaService;

    public function __construct($id, $module, IdeaService $ideaService = null, $config = [])
    {
        $this->ideaService = $ideaService ?? new IdeaService();
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
        $searchModel = new IdeaSearch();
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
        $form = new IdeaCreateForm();

        if ($form->load(Yii::$app->request->post()) && $this->ideaService->create($form) !== null) {
            Yii::$app->session->setFlash('success', 'Idea created.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $idea = $this->findModel($id);
        $form = IdeaUpdateForm::fromIdea($idea);

        if ($form->load(Yii::$app->request->post()) && $this->ideaService->update($id, $form) !== null) {
            Yii::$app->session->setFlash('success', 'Idea updated.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $form,
            'idea' => $idea,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $form = new IdeaDeleteForm(['id' => $id]);
        $deleted = $this->ideaService->delete($form);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->asJson(['success' => $deleted]);
        }

        Yii::$app->session->setFlash($deleted ? 'success' : 'error', $deleted ? 'Idea deleted.' : 'Idea not found.');
        return $this->redirect(['index']);
    }

    public function actionChangeStatus(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $status = Yii::$app->request->post('status');

        if (!array_key_exists((string) $status, Idea::statusOptions())) {
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

    protected function findModel(int $id): Idea
    {
        if (($model = Idea::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested idea does not exist.');
    }
}
