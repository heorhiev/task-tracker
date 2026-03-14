<?php

namespace common\modules\tasks\commands;

use common\models\forms\TaskCreateForm;
use common\modules\tasks\services\TaskService;
use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;

class CreateTaskCommand implements CommandInterface
{
    use TextArgumentCommandTrait;

    private TaskService $taskService;

    public function __construct(TaskService $taskService = null)
    {
        $this->taskService = $taskService ?? new TaskService();
    }

    public function name(): string
    {
        return 'create_task';
    }

    public function supports(string $text): bool
    {
        return $this->matchTextArgument($text, 'create\s+task') !== null;
    }

    public function parse(string $text): array
    {
        return ['title' => $this->matchTextArgument($text, 'create\s+task') ?? ''];
    }

    public function execute(array $payload, array $context = []): CommandExecutionResult
    {
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return new CommandExecutionResult(true, $this->name(), 'Task creation failed', $payload);
        }

        $chatId = $context['chatId'] ?? null;
        $form = new TaskCreateForm([
            'title' => $title,
            'description' => $chatId !== null ? 'Created from Telegram chat ' . $chatId : 'Created from Telegram',
        ]);

        $task = $this->taskService->create($form);

        return new CommandExecutionResult(
            true,
            $this->name(),
            $task !== null ? 'Task created: #' . $task->id : 'Task creation failed',
            $payload
        );
    }
}
