<?php

namespace common\modules\tasks\services\command;

use common\models\forms\TaskCreateForm;
use common\modules\tasks\models\forms\IdeaCreateForm;
use common\modules\tasks\services\IdeaService;
use common\modules\tasks\services\ProjectService;
use common\modules\tasks\services\TaskService;

class TaskCommandExecutor
{
    public const COMMAND_UNKNOWN = 'unknown';
    public const COMMAND_CREATE_PROJECT = 'create_project';
    public const COMMAND_SET_DEFAULT_PROJECT = 'set_default_project';
    public const COMMAND_CREATE_TASK = 'create_task';
    public const COMMAND_CREATE_IDEA = 'create_idea';
    public const COMMAND_GET_DEFAULT_PROJECT = 'get_default_project';

    private TaskService $taskService;
    private IdeaService $ideaService;
    private ProjectService $projectService;

    public function __construct(
        TaskService $taskService = null,
        IdeaService $ideaService = null,
        ProjectService $projectService = null
    )
    {
        $this->taskService = $taskService ?? new TaskService();
        $this->ideaService = $ideaService ?? new IdeaService();
        $this->projectService = $projectService ?? new ProjectService();
    }

    /**
     * @param array{command:string,payload:array<string,string>} $commandData
     */
    public function execute(array $commandData, $chatId): string
    {
        $command = (string) ($commandData['command'] ?? self::COMMAND_UNKNOWN);
        $payload = (array) ($commandData['payload'] ?? []);

        if ($command === self::COMMAND_CREATE_PROJECT) {
            $projectName = trim((string) ($payload['name'] ?? ''));
            $project = $this->projectService->findOrCreateByName($projectName);

            if ($project === null) {
                return 'Project creation failed';
            }

            return 'Project created: #' . $project->id;
        }

        if ($command === self::COMMAND_SET_DEFAULT_PROJECT) {
            $projectName = trim((string) ($payload['name'] ?? ''));
            $project = $this->projectService->setDefaultProjectByName($projectName);

            if ($project === null) {
                return 'Project not found';
            }

            return 'Default project set: ' . $project->name;
        }

        if ($command === self::COMMAND_CREATE_TASK) {
            $title = trim((string) ($payload['title'] ?? ''));
            if ($title === '') {
                return 'Task creation failed';
            }

            $form = new TaskCreateForm([
                'title' => $title,
                'description' => $chatId !== null ? 'Created from Telegram chat ' . $chatId : 'Created from Telegram',
            ]);

            $task = $this->taskService->create($form);

            return $task !== null ? 'Task created: #' . $task->id : 'Task creation failed';
        }

        if ($command === self::COMMAND_CREATE_IDEA) {
            $title = trim((string) ($payload['title'] ?? ''));
            if ($title === '') {
                return 'Idea creation failed';
            }

            $form = new IdeaCreateForm([
                'title' => $title,
                'description' => $chatId !== null ? 'Created from Telegram chat ' . $chatId : 'Created from Telegram',
            ]);

            $idea = $this->ideaService->create($form);

            return $idea !== null ? 'Idea created: #' . $idea->id : 'Idea creation failed';
        }

        if ($command === self::COMMAND_GET_DEFAULT_PROJECT) {
            $project = $this->projectService->getDefaultProject();

            return 'Current default project: ' . $project->name . ' (#' . $project->id . ')';
        }

        return 'Unknown command. Use: create project \"name\", set default project \"name\", create task \"title\", create idea \"title\", default project';
    }
}
