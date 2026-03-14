<?php

namespace common\modules\tasks\commands;

use common\modules\tasks\services\ProjectService;
use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;

class SetDefaultProjectCommand implements CommandInterface
{
    use TextArgumentCommandTrait;

    private ProjectService $projectService;

    public function __construct(ProjectService $projectService = null)
    {
        $this->projectService = $projectService ?? new ProjectService();
    }

    public function name(): string
    {
        return 'set_default_project';
    }

    public function supports(string $text): bool
    {
        return $this->matchTextArgument($text, 'set\s+default\s+project') !== null;
    }

    public function parse(string $text): array
    {
        return ['name' => $this->matchTextArgument($text, 'set\s+default\s+project') ?? ''];
    }

    public function execute(array $payload, array $context = []): CommandExecutionResult
    {
        $projectName = trim((string) ($payload['name'] ?? ''));
        $project = $this->projectService->setDefaultProjectByName($projectName);

        if ($project === null) {
            return new CommandExecutionResult(true, $this->name(), 'Project not found', $payload);
        }

        return new CommandExecutionResult(true, $this->name(), 'Default project set: ' . $project->name, $payload);
    }
}
