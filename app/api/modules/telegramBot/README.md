# Telegram Bot API Module (Quick)

## Purpose
Processes Telegram webhook updates and executes task/project commands.

## Endpoint
- Controller: `telegramBot/default/index`
- Webhook URL format:
```text
https://<domain>/?api-key=<API_KEY>
```

## Commands
- `create project "name"`
- `set default project "name"`
- `default project`
- `create task "title"`
- `/start`

## Core files
- Controller: `controllers/DefaultController.php`
- Resolver: `services/TelegramTaskResolverService.php`
- Command handler: `services/TelegramCommandHandlerService.php`

## Notes
- CSRF disabled for webhook controller.
- API key is mandatory.
- Health check: `GET /health/webhook`

## Tests
```bash
docker compose run --rm php sh -lc "cd api/modules/telegramBot && php ../../../vendor/bin/codecept run unit --no-ansi"
```
