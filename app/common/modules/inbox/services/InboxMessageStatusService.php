<?php

namespace common\modules\inbox\services;

use common\modules\inbox\models\InboxMessage;
use yii\db\Expression;

class InboxMessageStatusService
{
    public function markProcessing(InboxMessage $message): bool
    {
        $message->status = InboxMessage::STATUS_PROCESSING;
        $message->attempt_count = (int) $message->attempt_count + 1;
        $message->processing_error = null;

        return $message->save(true, ['status', 'attempt_count', 'processing_error', 'updated_at']);
    }

    public function markProcessed(
        InboxMessage $message,
        ?string $transcriptionText = null,
        ?string $resolvedCommand = null
    ): bool
    {
        $message->status = InboxMessage::STATUS_PROCESSED;
        $message->processing_error = null;
        $message->processed_at = new Expression('NOW()');

        if ($transcriptionText !== null) {
            $message->transcription_text = $transcriptionText;
        }

        if ($resolvedCommand !== null) {
            $message->resolved_command = $resolvedCommand;
        }

        return $message->save(true, [
            'status',
            'processing_error',
            'processed_at',
            'transcription_text',
            'resolved_command',
            'updated_at',
        ]);
    }

    public function markFailed(InboxMessage $message, ?string $errorMessage = null): bool
    {
        $message->status = InboxMessage::STATUS_FAILED;
        $message->processing_error = $errorMessage;
        $message->processed_at = new Expression('NOW()');

        return $message->save(true, ['status', 'processing_error', 'processed_at', 'updated_at']);
    }
}
