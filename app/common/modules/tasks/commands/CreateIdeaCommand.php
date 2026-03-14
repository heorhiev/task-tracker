<?php

namespace common\modules\tasks\commands;

use common\modules\tasks\models\forms\IdeaCreateForm;
use common\modules\tasks\services\IdeaService;
use common\services\commands\CommandExecutionResult;
use common\services\commands\CommandInterface;

class CreateIdeaCommand implements CommandInterface
{
    use TextArgumentCommandTrait;

    private IdeaService $ideaService;

    public function __construct(IdeaService $ideaService = null)
    {
        $this->ideaService = $ideaService ?? new IdeaService();
    }

    public function name(): string
    {
        return 'create_idea';
    }

    public function supports(string $text): bool
    {
        return $this->matchTextArgument($text, 'create\s+idea') !== null;
    }

    public function parse(string $text): array
    {
        return ['title' => $this->matchTextArgument($text, 'create\s+idea') ?? ''];
    }

    public function execute(array $payload, array $context = []): CommandExecutionResult
    {
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return new CommandExecutionResult(true, $this->name(), 'Idea creation failed', $payload);
        }

        $chatId = $context['chatId'] ?? null;
        $form = new IdeaCreateForm([
            'title' => $title,
            'description' => $chatId !== null ? 'Created from Telegram chat ' . $chatId : 'Created from Telegram',
        ]);

        $idea = $this->ideaService->create($form);

        return new CommandExecutionResult(
            true,
            $this->name(),
            $idea !== null ? 'Idea created: #' . $idea->id : 'Idea creation failed',
            $payload
        );
    }
}
