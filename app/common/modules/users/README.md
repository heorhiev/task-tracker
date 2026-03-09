# Users Module (Quick)

## Purpose
Shared user identity model for frontend auth and API key auth.

## Core files
- Module: `Module.php`
- Model: `models/User.php`

## Model highlights
- Table: `users`
- Fields: `email`, `password` (hashed), `role`, `api_key`
- Roles: `admin`, `user`
- Helpers: `findByEmail()`, `findByApiKey()`, `setPassword()`, `validatePassword()`

## Migrations
- `m260307_000010_create_users_module_table` (creates table + admin seed)
- `m260307_000020_add_api_key_to_users` (adds unique `api_key`)

## Used by
- Frontend auth module (`identityClass`)
- API auth service (`api-key` query auth)
