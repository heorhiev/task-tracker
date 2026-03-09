# Task Tracker (Quick Start)

## What it is
Yii2 Advanced project with:
- `frontend` (UI)
- `backend`
- `api` (Telegram webhook)
- shared business logic in `common/modules/*`

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
# Tasks module
docker compose run --rm php sh -lc "cd common/modules/tasks && php ../../../vendor/bin/codecept run unit --no-ansi"

# Telegram bot module
docker compose run --rm php sh -lc "cd api/modules/telegramBot && php ../../../vendor/bin/codecept run unit --no-ansi"
```

## Module docs
- Tasks: `app/common/modules/tasks/README.md`
- Users: `app/common/modules/users/README.md`
- Frontend Auth: `app/frontend/modules/auth/README.md`
- API Telegram Bot: `app/api/modules/telegramBot/README.md`

## Notes
- Telegram webhook URL must include `api-key` query param.
- Telegram webhook controller has CSRF disabled.
