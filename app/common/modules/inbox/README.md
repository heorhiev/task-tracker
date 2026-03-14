# Inbox Module

## Purpose
Stores incoming external messages and drives asynchronous processing workflows.

Right now the primary use case is Telegram voice messages:
- webhook receives a voice message
- audio file is saved through `fileManager`
- an `InboxMessage` is created with status `pending`
- a background console command later normalizes audio if needed, performs speech-to-text, resolves commands, and executes them

This module exists to keep webhook ingestion fast and make message processing reliable and retryable.

## Main Responsibilities
- persist normalized incoming message records
- link messages to one or more stored files
- expose pending messages for background workers
- track processing state and failures
- orchestrate voice message processing through speech-to-text and command execution

## Main Entities
### `InboxMessage`
Represents a normalized incoming message from an external source.

Important fields:
- `source`
- `external_message_id`
- `external_chat_id`
- `external_user_id`
- `message_type`
- `status`
- `text_raw`
- `transcription_text`
- `resolved_command`
- `processing_error`
- `attempt_count`
- `received_at`
- `processed_at`

### `InboxMessageAttachment`
Links an `InboxMessage` to a `StoredFile`.

Attachment roles currently used:
- `original`
- `normalized`
- `transcript_attachment`

## Processing Statuses
- `pending`
- `processing`
- `transcribed`
- `processed`
- `failed`
- `ignored`

## Processing Flow For Voice Messages
1. Telegram webhook stores the downloaded audio file in `fileManager`
2. Telegram webhook creates `InboxMessage(status=pending, message_type=voice)`
3. Telegram webhook creates `InboxMessageAttachment(role=original)`
4. console command reads pending voice messages
5. `InboxVoiceProcessingService` marks a message as `processing`
6. `AudioNormalizationService` from `common/services/speechTools/normalization` checks whether the original audio format is acceptable for transcription
7. if needed, audio is converted to a normalized `wav` file and linked as `InboxMessageAttachment(role=normalized)`
8. STT service produces text
9. the text is sent to the shared `commandRegistry`
10. the registry resolves and executes the matched task command
11. the message is marked `processed` or `failed`

## Services
- `services/InboxMessageService.php`
  - create messages
  - create messages with attachments
  - fetch pending messages
- `services/InboxMessageStatusService.php`
  - move messages through lifecycle states
- `services/InboxVoiceProcessingService.php`
  - process a pending voice message end-to-end
## Current Integration Points
- `api/modules/telegramBot` writes pending voice messages here
- `common/modules/fileManager` provides file persistence
- `common/components/SpeechToTextComponent.php` provides the Yii application wrapper for STT
- `common/services/speechTools` provides real and stub STT implementations
- `common/components/CommandRegistryComponent.php` routes transcribed text to registered commands
- `common/modules/tasks/commands` provide task/project command implementations
- `common/modules/inbox/controllers/console/InboxController.php` runs the background worker command

## Console Command
```bash
docker compose run --rm php php yii inbox/process-pending-voice 20
```

## Speech-To-Text Modes
### Real OpenAI STT
When `OPENAI_API_KEY` is configured, the inbox pipeline uses the `speechToText` component backed by OpenAI Audio Transcriptions.

Supported configuration:
- `OPENAI_API_KEY`
- `OPENAI_STT_MODEL` default: `gpt-4o-mini-transcribe`
- `OPENAI_STT_TIMEOUT`
- `OPENAI_STT_PROMPT`
- `OPENAI_STT_BASE_URL`
- `FFMPEG_BINARY` optional, default: `ffmpeg`

### Local Stub STT
The stub STT service reads transcription text from a sidecar file:
```text
<absolute_audio_path>.txt
```

Fallback:
- environment variable `INBOX_STT_STUB_TEXT`

If neither exists, processing fails and the message is marked `failed`.

## Operational Notes
- OpenAI transcription currently uses the `/v1/audio/transcriptions` API
- files larger than 25 MB will fail in the current implementation
- audio normalization relies on `ffmpeg`
- if the original Telegram audio format is already acceptable for STT, the original file is used directly

## Core Files
- `Module.php`
- `models/InboxMessage.php`
- `models/InboxMessageAttachment.php`
- `services/InboxMessageService.php`
- `services/InboxMessageStatusService.php`
- `services/InboxVoiceProcessingService.php`

## Tests
```bash
docker compose run --rm php sh -lc "cd common/modules/inbox && php ../../../vendor/bin/codecept run unit --no-ansi"
```
