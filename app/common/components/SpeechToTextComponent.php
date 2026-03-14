<?php

namespace common\components;

use common\services\speechTools\OpenAiSpeechToTextService;
use common\services\speechTools\SpeechToTextServiceInterface;
use common\services\speechTools\StubSpeechToTextService;
use yii\base\Component;

class SpeechToTextComponent extends Component implements SpeechToTextServiceInterface
{
    public string $provider = 'openai';
    public string $apiKey = '';
    public string $baseUrl = 'https://api.openai.com/v1';
    public string $model = 'gpt-4o-mini-transcribe';
    public int $timeout = 120;
    public ?string $prompt = null;

    private ?SpeechToTextServiceInterface $service = null;

    public function init(): void
    {
        parent::init();
        $this->service = $this->buildService();
    }

    public function transcribe(string $filePath, array $context = []): string
    {
        return $this->service()->transcribe($filePath, $context);
    }

    public function isConfigured(): bool
    {
        return $this->service instanceof OpenAiSpeechToTextService && $this->service->isConfigured();
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    private function service(): SpeechToTextServiceInterface
    {
        if ($this->service === null) {
            $this->service = $this->buildService();
        }

        return $this->service;
    }

    private function buildService(): SpeechToTextServiceInterface
    {
        if ($this->provider === 'openai') {
            return new OpenAiSpeechToTextService(
                apiKey: $this->apiKey,
                model: $this->model,
                timeout: $this->timeout,
                prompt: $this->prompt,
                baseUrl: $this->baseUrl
            );
        }

        return new StubSpeechToTextService();
    }
}
