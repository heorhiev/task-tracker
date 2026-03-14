<?php

namespace common\services\speechTools;

use yii\base\Exception;

class OpenAiSpeechToTextService implements SpeechToTextServiceInterface
{
    private const DEFAULT_BASE_URL = 'https://api.openai.com/v1';
    private const DEFAULT_MODEL = 'gpt-4o-mini-transcribe';
    private const DEFAULT_TIMEOUT = 120;
    private const MAX_FILE_SIZE_BYTES = 26214400;

    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int $timeout;
    private ?string $prompt;

    public function __construct(
        string $apiKey = '',
        string $model = self::DEFAULT_MODEL,
        int $timeout = self::DEFAULT_TIMEOUT,
        ?string $prompt = null,
        string $baseUrl = self::DEFAULT_BASE_URL
    )
    {
        $this->apiKey = trim($apiKey);
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->model = trim($model) !== '' ? trim($model) : self::DEFAULT_MODEL;
        $this->timeout = $timeout > 0 ? $timeout : self::DEFAULT_TIMEOUT;
        $this->prompt = $prompt !== null ? trim($prompt) : null;
        $this->baseUrl = $this->baseUrl !== '' ? $this->baseUrl : self::DEFAULT_BASE_URL;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function transcribe(string $filePath, array $context = []): string
    {
        if (!$this->isConfigured()) {
            throw new Exception('OPENAI_API_KEY is not configured for speech-to-text.');
        }

        if (!is_file($filePath)) {
            throw new Exception('Audio file not found: ' . $filePath);
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            throw new Exception('Unable to read audio file size: ' . $filePath);
        }

        if ($fileSize > self::MAX_FILE_SIZE_BYTES) {
            throw new Exception('Audio file exceeds the 25 MB transcription limit.');
        }

        if (!function_exists('curl_init') || !function_exists('curl_file_create')) {
            throw new Exception('cURL extension is required for OpenAI speech-to-text requests.');
        }

        $mimeType = $this->detectMimeType($filePath);
        $postFields = [
            'model' => $this->model,
            'response_format' => 'text',
            'file' => curl_file_create($filePath, $mimeType, basename($filePath)),
        ];

        if ($this->prompt !== null && $this->prompt !== '') {
            $postFields['prompt'] = $this->prompt;
        }

        $response = $this->executeRequest($postFields);
        $text = trim($response);
        if ($text === '') {
            throw new Exception('OpenAI STT returned an empty transcription.');
        }

        return $text;
    }

    private function executeRequest(array $postFields): string
    {
        $ch = curl_init($this->baseUrl . '/audio/transcriptions');
        if ($ch === false) {
            throw new Exception('Unable to initialize cURL for OpenAI STT request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $postFields,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errorMessage = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('OpenAI STT request failed: ' . $errorMessage);
        }

        if ($httpCode >= 400) {
            throw new Exception('OpenAI STT returned HTTP ' . $httpCode . ': ' . $response);
        }

        return (string) $response;
    }

    private function detectMimeType(string $filePath): string
    {
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if (is_string($mimeType) && $mimeType !== '') {
                return $mimeType;
            }
        }

        return 'audio/wav';
    }
}
