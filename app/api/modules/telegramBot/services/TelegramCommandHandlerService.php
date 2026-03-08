<?php

namespace api\modules\telegramBot\services;

use common\models\forms\TaskCreateForm;
use common\modules\tasks\services\ProjectService;
use common\modules\tasks\services\TaskService;

class TelegramCommandHandlerService
{
    public const COMMAND_UNKNOWN = 'unknown';
    public const COMMAND_CREATE_PROJECT = 'create_project';
    public const COMMAND_MARK_PROJECT_DEFAULT = 'mark_project_default';
    public const COMMAND_CREATE_TASK = 'create_task';

    private TaskService $taskService;
    private ProjectService $projectService;

    public function __construct(TaskService $taskService = null, ProjectService $projectService = null)
    {
        $this->taskService = $taskService ?? new TaskService();
        $this->projectService = $projectService ?? new ProjectService();
    }

    /**
     * @param array{command:string,payload:array<string,string>} $commandData
     */
    public function handle(array $commandData, $chatId): string
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

        if ($command === self::COMMAND_MARK_PROJECT_DEFAULT) {
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

        return 'Unknown command. Use: create project \"name\", mark as default \"name\", create task \"title\"';
    }
}
