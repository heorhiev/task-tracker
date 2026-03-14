<?php

namespace common\services\speechTools;

interface SpeechToTextServiceInterface
{
    /**
     * @param array<string,mixed> $context
     */
    public function transcribe(string $filePath, array $context = []): string;
}
