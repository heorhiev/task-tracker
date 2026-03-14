<?php

namespace common\modules\inbox\controllers\console;

use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\services\InboxMessageService;
use common\modules\inbox\services\InboxVoiceProcessingService;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class InboxController extends Controller
{
    private InboxMessageService $inboxMessageService;
    private InboxVoiceProcessingService $inboxVoiceProcessingService;

    public function __construct(
        $id,
        $module,
        InboxMessageService $inboxMessageService = null,
        InboxVoiceProcessingService $inboxVoiceProcessingService = null,
        $config = []
    )
    {
        $this->inboxMessageService = $inboxMessageService ?? new InboxMessageService();
        $this->inboxVoiceProcessingService = $inboxVoiceProcessingService ?? new InboxVoiceProcessingService();
        parent::__construct($id, $module, $config);
    }

    public function actionProcessPendingVoice(int $limit = 20): int
    {
        $this->printVoiceProcessingDiagnostics();

        $messages = $this->inboxMessageService->getPendingMessages($limit, InboxMessage::TYPE_VOICE, InboxMessage::SOURCE_TELEGRAM);

        if ($messages === []) {
            $this->stdout("No pending voice messages found.\n");

            return ExitCode::OK;
        }

        $errors = 0;

        foreach ($messages as $message) {
            try {
                $result = $this->inboxVoiceProcessingService->process($message);
                $this->stdout(sprintf(
                    "Processed inbox message #%d: command=%s, transcription=%s\n",
                    $message->id,
                    $result['command'],
                    $result['transcriptionText']
                ));
            } catch (\Throwable $exception) {
                $errors++;
                $this->stderr(sprintf(
                    "Failed to process inbox message #%d: %s\n",
                    $message->id,
                    $exception->getMessage()
                ));
            }
        }

        return $errors === 0 ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    private function printVoiceProcessingDiagnostics(): void
    {
        $openAiApiKey = trim((string) getenv('OPENAI_API_KEY'));
        $ffmpegBinary = trim((string) (getenv('FFMPEG_BINARY') ?: 'ffmpeg'));
        $ffmpegAvailable = $this->isBinaryAvailable($ffmpegBinary);

        $this->stdout("Voice processing diagnostics:\n", Console::BOLD);

        if ($openAiApiKey !== '') {
            $this->stdout("  STT mode: OpenAI\n", Console::FG_GREEN);
            $this->stdout('  OPENAI_STT_MODEL: ' . (getenv('OPENAI_STT_MODEL') ?: 'gpt-4o-mini-transcribe') . "\n");
        } else {
            $this->stdout("  STT mode: Stub fallback\n", Console::FG_YELLOW);
            $this->stdout("  OPENAI_API_KEY is not configured. Real transcription is disabled.\n", Console::FG_YELLOW);
            $this->stdout("  Configure OPENAI_API_KEY to enable OpenAI speech-to-text.\n", Console::FG_YELLOW);
            $this->stdout("  Stub mode expects either <audio-file>.txt sidecar files or INBOX_STT_STUB_TEXT.\n", Console::FG_YELLOW);
        }

        if ($ffmpegAvailable) {
            $this->stdout('  ffmpeg: available (' . $ffmpegBinary . ")\n", Console::FG_GREEN);
        } else {
            $this->stdout('  ffmpeg: not available (' . $ffmpegBinary . ")\n", Console::FG_YELLOW);
            $this->stdout("  Voice files in unsupported formats may fail normalization before transcription.\n", Console::FG_YELLOW);
            $this->stdout("  Install ffmpeg in the PHP runtime or set FFMPEG_BINARY to the executable path.\n", Console::FG_YELLOW);
        }

        $this->stdout("\n");
    }

    private function isBinaryAvailable(string $binary): bool
    {
        if ($binary === '') {
            return false;
        }

        if (strpos($binary, DIRECTORY_SEPARATOR) !== false) {
            return is_file($binary) && is_executable($binary);
        }

        $output = [];
        $exitCode = 1;
        exec('command -v ' . escapeshellarg($binary) . ' 2>/dev/null', $output, $exitCode);

        return $exitCode === 0 && $output !== [];
    }
}
