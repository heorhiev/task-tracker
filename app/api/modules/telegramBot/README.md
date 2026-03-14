# Telegram Bot API Module

## Purpose
Receives Telegram webhook updates, authenticates requests with `api-key`, and routes user input into the application.

This module has two main responsibilities:
- process plain text Telegram commands immediately
- receive Telegram voice messages, download the audio file, and enqueue it into `inbox` for background processing

Business logic for tasks/projects is not implemented here. This module delegates command execution to `common/modules/tasks`.

## Endpoint
- Controller route: `telegramBot/default/index`
- Webhook URL format:
```text
https://<domain>/?api-key=<API_KEY>
```

## Input Types
- text message
- voice message
- unsupported message types are acknowledged but ignored

## Text Command Flow
1. `DefaultController` receives the webhook update
2. `TelegramIncomingMessageService` normalizes the incoming payload
3. `commandRegistry` receives the normalized text
4. the registry finds a matching command implementation from `common/modules/tasks/commands`
5. the matched command parses and executes the request
5. Telegram gets a text reply

## Voice Message Flow
1. `DefaultController` receives a voice update
2. `TelegramIncomingMessageService` extracts `file_id`, `file_unique_id`, `messageId`, `chatId`, and `userId`
3. `TelegramVoiceInboxService` downloads the Telegram file
4. the audio is stored via `common/modules/fileManager`
5. an `InboxMessage` with status `pending` is created via `common/modules/inbox`
6. the controller replies that the voice message was queued
7. later, a console command processes pending voice records from `inbox`
8. during background processing, audio may be normalized with `ffmpeg`
9. OpenAI transcription converts audio to text
10. the recognized text is mapped to the shared task command flow

## Supported Text Commands
- `create project "name"`
- `set default project "name"`
- `default project`
- `create task "title"`
- `/start`

## Core Files
- `controllers/DefaultController.php`
- `services/TelegramIncomingMessageService.php`
- `services/TelegramVoiceInboxService.php`
- `common/components/CommandRegistryComponent.php`
- `common/services/commands/CommandRegistry.php`
- `common/modules/tasks/commands/*`

## Integration Points
- `common/components/TelegramComponent.php` wraps Telegram SDK calls
- `common/modules/fileManager` stores downloaded Telegram audio files
- `common/modules/inbox` stores pending voice messages for background processing
- `common/modules/tasks` executes recognized task/project commands

## Operational Notes
- CSRF is disabled for the webhook controller
- API key is mandatory
- bot token must be configured in the Telegram component
- health check endpoint: `GET /health/webhook`
- voice messages are not executed synchronously in the webhook request
- voice command execution depends on the background inbox processor

## Tests
```bash
docker compose run --rm php sh -lc "cd api/modules/telegramBot && php ../../../vendor/bin/codecept run unit --no-ansi"
```
