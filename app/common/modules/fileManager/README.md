# File Manager Module

## Purpose
Provides a shared file storage abstraction for the application.

This module is responsible for:
- saving file content to local storage
- generating a deterministic relative storage path
- persisting file metadata in the database
- returning a reusable `StoredFile` record that other modules can reference

The module is infrastructure-oriented. It should not contain business-specific logic for Telegram, inbox, tasks, or any other domain.

## Main Concepts
- `StoredFile` is the canonical database record for a persisted file
- `FileStorageService` accepts file content plus metadata and stores both the file and its metadata
- the database stores relative file paths, not absolute host-specific paths

## Storage Layout
Default base path:
```text
@runtime/file-manager
```

Relative path format:
```text
<source>/<category>/YYYY/MM/DD/<random>.<ext>
```

Example:
```text
telegram/voice/2026/03/14/abc123def456.ogg
```

Absolute paths are resolved at runtime by `FileStorageService`.

## Database Model
Table: `stored_file`

Important fields:
- `storage`
- `path`
- `original_name`
- `extension`
- `mime_type`
- `size_bytes`
- `checksum_sha256`
- `source`
- `source_file_id`
- `source_unique_id`

## Core Files
- `Module.php`
- `models/StoredFile.php`
- `services/FileStorageService.php`

## Typical Usage
```php
$storedFile = $fileStorageService->store($content, [
    'originalName' => 'voice-note.ogg',
    'mimeType' => 'audio/ogg',
    'category' => 'voice',
    'source' => \common\modules\fileManager\models\StoredFile::SOURCE_TELEGRAM,
    'sourceFileId' => 'telegram-file-id',
    'sourceUniqueId' => 'telegram-unique-id',
]);
```

## Current Consumers
- `common/modules/inbox`
- `api/modules/telegramBot`

## Notes
- files are stored as relative paths in the database
- file content itself is stored on disk, not in the database
- this module can persist both original Telegram audio files and normalized audio files created for STT

## Tests
```bash
docker compose run --rm php sh -lc "cd common/modules/fileManager && php ../../../vendor/bin/codecept run unit --no-ansi"
```
