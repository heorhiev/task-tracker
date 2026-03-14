# Resolved Cases

## Telegram text commands returned `Unknown command` for valid input

### Symptom

Telegram webhook received a valid text command such as:

```text
create task "title"
```

but the bot replied with:

```text
Unknown command. Use: create project "name", set default project "name", create task "title", create idea "title", default project
```

### What was checked

- Raw Telegram text in the API log was correct:
  - `text => create task "title"`
  - `text_hex => 637265617465207461736b20227469746c6522`
- Command parsing worked in tests and in one-off container runs.
- The failure happened only in the live webhook request path.

### Root cause

`api/modules/telegramBot/controllers/DefaultController.php` accepted `CommandRegistryComponent` as a typed constructor argument:

```php
CommandRegistryComponent $commandRegistry = null
```

In the live controller instantiation path, Yii created a new empty `CommandRegistryComponent` instead of using the configured application component `Yii::$app->commandRegistry`.

As a result:

- the webhook controller received an empty registry
- `registry_commands` in logs was `[]`
- every valid command fell through to `Unknown command`

### Fix

Stop injecting `CommandRegistryComponent` into the controller constructor and always read the configured component from the application:

```php
$this->commandRegistry = Yii::$app->commandRegistry;
```

### How to diagnose quickly next time

If Telegram replies `Unknown command` for obviously valid text:

1. Log the raw incoming text and its hex representation.
2. Log the command registry contents in the webhook request.
3. If `registry_commands` is empty in the live request but commands work in tests/CLI, check for accidental constructor injection of application components.

### Relevant files

- [DefaultController.php](/Users/mac/dev/my/productivity/task-tracker/app/api/modules/telegramBot/controllers/DefaultController.php)
- [CommandRegistryComponent.php](/Users/mac/dev/my/productivity/task-tracker/app/common/components/CommandRegistryComponent.php)
- [README.md](/Users/mac/dev/my/productivity/task-tracker/README.md)
