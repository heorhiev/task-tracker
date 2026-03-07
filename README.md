# Task Tracker - Yii2 Advanced

Stack:
- PHP 8.0 (Yii2 Advanced Template)
- PostgreSQL 16
- Nginx + PHP-FPM (Docker)
- Server-rendered UI + jQuery + AJAX + PJAX

## Project structure
- `app/frontend` - user-facing app (tasks UI)
- `app/backend` - admin app
- `app/common` - shared models/forms/services
- `app/console` - migrations/console commands

## Run
```bash
docker compose up -d --build --remove-orphans
```

## URLs
- Frontend: http://localhost:8080
- Backend: http://localhost:8081

## Migrations
```bash
docker compose run --rm php sh -lc 'cd /var/www/html && php yii migrate --interactive=0'
```

## Implemented Task module
- Frontend module: `frontend/modules/tasks`
- Routes: `/tasks/default/index`, `/tasks/default/create`, `/tasks/default/update`, `/tasks/default/view`
- PJAX list update for filters/sort/pagination
- AJAX status change: `POST /tasks/default/change-status?id=...`
- AJAX delete: `POST /tasks/default/delete?id=...`

### Service/Form layer
- `common/services/TaskService`
- `common/models/forms/TaskCreateForm`
- `common/models/forms/TaskUpdateForm`
- `common/models/forms/TaskDeleteForm`

### Unit tests
```bash
docker compose run --rm php sh -lc 'cd /var/www/html && vendor/bin/codecept run -c frontend unit services/TaskServiceTest.php'
```

Covers:
- task creation
- task update
- task deletion
