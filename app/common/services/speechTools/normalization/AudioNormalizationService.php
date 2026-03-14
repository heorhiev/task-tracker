<?php

namespace common\services\speechTools\normalization;

use Yii;
use yii\base\Exception;

class AudioNormalizationService
{
    private const SUPPORTED_EXTENSIONS = ['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'];

    private string $ffmpegBinary;
    private string $tempPathAlias;

    public function __construct(?string $ffmpegBinary = null, string $tempPathAlias = '@runtime/inbox-normalization')
    {
        $this->ffmpegBinary = trim((string) ($ffmpegBinary ?? getenv('FFMPEG_BINARY') ?: 'ffmpeg'));
        $this->tempPathAlias = $tempPathAlias;
    }

    public function supportsExtension(?string $extension): bool
    {
        if ($extension === null || $extension === '') {
            return false;
        }

        return in_array(mb_strtolower($extension), self::SUPPORTED_EXTENSIONS, true);
    }

    public function normalizeToTranscriptionFormat(string $sourcePath): string
    {
        if (!is_file($sourcePath)) {
            throw new Exception('Audio file for normalization was not found: ' . $sourcePath);
        }

        $normalizedPath = $this->buildTempNormalizedPath();

        try {
            $this->runFfmpeg($sourcePath, $normalizedPath);

            return $normalizedPath;
        } catch (\Throwable $exception) {
            if (is_file($normalizedPath)) {
                @unlink($normalizedPath);
            }

            throw $exception;
        }
    }

    private function buildTempNormalizedPath(): string
    {
        $directory = Yii::getAlias($this->tempPathAlias);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new Exception('Unable to create normalization temp directory: ' . $directory);
        }

        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Yii::$app->security->generateRandomString(24) . '.wav';
    }

    private function runFfmpeg(string $sourcePath, string $normalizedPath): void
    {
        $command = sprintf(
            '%s -y -i %s -ar 16000 -ac 1 %s 2>&1',
            escapeshellcmd($this->ffmpegBinary),
            escapeshellarg($sourcePath),
            escapeshellarg($normalizedPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($normalizedPath)) {
            throw new Exception('ffmpeg normalization failed: ' . trim(implode("\n", $output)));
        }
    }
}
