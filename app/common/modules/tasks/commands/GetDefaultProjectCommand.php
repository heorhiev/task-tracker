<?php

namespace common\modules\tasks\commands;

use common\modules\tasks\services\ProjectService;
use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;

class GetDefaultProjectCommand implements CommandInterface
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService = null)
    {
        $this->projectService = $projectService ?? new ProjectService();
    }

    public function name(): string
    {
        return 'get_default_project';
    }

    public function supports(string $text): bool
    {
        return preg_match('/^default\s+project$/i', trim($text)) === 1;
    }

    public function parse(string $text): array
    {
        return [];
    }

    public function execute(array $payload, array $context = []): CommandExecutionResult
    {
        $project = $this->projectService->getDefaultProject();

        return new CommandExecutionResult(
            true,
            $this->name(),
            'Current default project: ' . $project->name . ' (#' . $project->id . ')',
            $payload
        );
    }
}
