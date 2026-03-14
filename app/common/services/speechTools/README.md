# Speech Tools Service Layer

## Purpose
Provides reusable speech-to-text services that are independent from Yii modules and application domains.

This layer is intentionally framework-light so it can later be moved into a separate Composer package with minimal changes.

## Contents
- `SpeechToTextServiceInterface.php`
- `OpenAiSpeechToTextService.php`
- `StubSpeechToTextService.php`
- `normalization/AudioNormalizationService.php`

## Design Rules
- no dependency on `InboxMessage`
- no dependency on Telegram-specific classes
- no dependency on Yii modules or controllers
- input is a file path plus optional generic `context` array

## Service Contract
```php
transcribe(string $filePath, array $context = []): string
```

The `context` array is intentionally generic. It can carry message metadata if needed, but the speech service layer does not know the application domain model.

## Current Implementations
### `OpenAiSpeechToTextService`
- calls OpenAI Audio Transcriptions API
- validates file existence and size
- uploads audio as multipart form data
- returns plain text transcription

### `StubSpeechToTextService`
- reads `<audio-file>.txt` sidecar files
- falls back to `INBOX_STT_STUB_TEXT`
- useful for tests and local development without real STT

### `normalization/AudioNormalizationService`
- checks whether the source extension is already acceptable for transcription
- converts unsupported audio formats to mono `wav` via `ffmpeg`
- returns a temporary normalized file path
- does not know anything about `InboxMessage`, attachments, or database persistence

## Intended Usage
- application/framework code should use a wrapper component such as `common/components/SpeechToTextComponent`
- domain modules like `inbox` should depend on the interface, not on provider-specific classes
