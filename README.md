# Task Tracker (Quick Start)

## What it is
Yii2 Advanced project with:
- `frontend` (UI)
- `backend`
- `api` (Telegram webhook)
- shared business logic in `common/modules/*`

Current domain modules:
- `common/modules/tasks` for tasks/projects business logic
- `common/modules/users` for shared user identity and API key auth
- `common/modules/fileManager` for file persistence and file metadata
- `common/modules/inbox` for incoming message storage and background processing

## Run
```bash
docker compose up -d --build --remove-orphans
docker compose run --rm php php yii migrate --interactive=0
```

## URLs
- Frontend: http://localhost:8080
- Backend: http://localhost:8081
- API: http://localhost:8082
- API health: http://localhost:8082/health/webhook

## Fast test commands
```bash
# File manager module
docker compose run --rm php sh -lc "cd common/modules/fileManager && php ../../../vendor/bin/codecept run unit --no-ansi"

# Inbox module
docker compose run --rm php sh -lc "cd common/modules/inbox && php ../../../vendor/bin/codecept run unit --no-ansi"

# Tasks module
docker compose run --rm php sh -lc "cd common/modules/tasks && php ../../../vendor/bin/codecept run unit --no-ansi"

# Telegram bot module
docker compose run --rm php sh -lc "cd api/modules/telegramBot && php ../../../vendor/bin/codecept run unit --no-ansi"
```

## Background processing
```bash
# Process pending Telegram voice messages from inbox
docker compose run --rm php php yii inbox/process-pending-voice 20
```

## Voice Processing Requirements
For real Telegram voice-to-command processing you need:
- `OPENAI_API_KEY`
- optional `OPENAI_STT_MODEL` (default: `gpt-4o-mini-transcribe`)
- optional `OPENAI_STT_TIMEOUT`
- optional `OPENAI_STT_PROMPT`
- `ffmpeg` available in the PHP container, or `FFMPEG_BINARY` pointing to the executable

Without `OPENAI_API_KEY`, the application falls back to the local STT stub for development/testing.

## Module docs
- File Manager: `app/common/modules/fileManager/README.md`
- Inbox: `app/common/modules/inbox/README.md`
- Tasks: `app/common/modules/tasks/README.md`
- Users: `app/common/modules/users/README.md`
- Frontend Auth: `app/frontend/modules/auth/README.md`
- API Telegram Bot: `app/api/modules/telegramBot/README.md`

## Shared Infrastructure
- Speech tools service layer: `app/common/services/speechTools/README.md`
- Yii STT wrapper component: `app/common/components/SpeechToTextComponent.php`

## Notes
- Telegram webhook URL must include `api-key` query param.
- Telegram webhook controller has CSRF disabled.
- Voice messages are stored first, then processed asynchronously from `inbox`.
- Some Telegram voice files may require normalization before transcription. The project handles this via `ffmpeg` and stores a normalized attachment when needed.
