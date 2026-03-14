<?php

namespace common\services\speechTools;

use yii\base\Exception;

class StubSpeechToTextService implements SpeechToTextServiceInterface
{
    public function transcribe(string $filePath, array $context = []): string
    {
        $sidecarPath = $filePath . '.txt';
        if (is_file($sidecarPath)) {
            $contents = file_get_contents($sidecarPath);
            if ($contents === false) {
                throw new Exception('Unable to read STT stub sidecar file.');
            }

            $transcription = trim($contents);
            if ($transcription !== '') {
                return $transcription;
            }
        }

        $fallback = trim((string) getenv('INBOX_STT_STUB_TEXT'));
        if ($fallback !== '') {
            return $fallback;
        }

        throw new Exception(
            'STT stub is not configured. Create a sidecar file "' . $sidecarPath . '" or set INBOX_STT_STUB_TEXT.'
        );
    }
}
