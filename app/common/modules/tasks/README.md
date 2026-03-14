# Tasks Module (Quick)

## Purpose
Task, idea, and project domain logic shared between frontend and API.

## Core files
- Module: `Module.php`
- Task service: `services/TaskService.php`
- Idea service: `services/IdeaService.php`
- Project service: `services/ProjectService.php`
- UI controllers: `controllers/DefaultController.php`, `controllers/IdeaController.php`, `controllers/ProjectController.php`

## Key rules
- Task belongs to project (`task.project_id`).
- Idea may optionally belong to project (`idea.project_id`).
- Exactly one default project (`project.is_default = true`).
- Task without `project_id` gets current default project.

## Main actions
- Task: create/update/delete + set task to default project
- Idea: create/update/delete + change status
- Project: create/update/status + set default + protect default from deletion

## DB migrations (important)
- `m260307_000001_create_task_table`
- `m260308_000030_create_project_table`
- `m260308_000040_add_project_id_to_task_table`
- `m260308_000050_seed_default_project`
- `m260308_000060_add_is_default_to_project_table`
- `m260314_000100_create_idea_table`

## Tests
```bash
docker compose run --rm php sh -lc "cd common/modules/tasks && php ../../../vendor/bin/codecept run unit --no-ansi"
```
