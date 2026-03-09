# Frontend Auth Module (Quick)

## Purpose
Login/logout module for frontend app.

## Routes
- Login: `/auth/default/login`
- Logout (POST): `/auth/default/logout`

## Core files
- Module: `Module.php`
- Controller: `controllers/DefaultController.php`
- Form: `forms/LoginForm.php`
- Service: `services/AuthService.php`
- View: `views/default/login.php`

## Flow
1. Validate login form
2. Find user by email
3. Validate password hash
4. Login with Yii user component

## Test
```bash
docker compose run --rm php sh -lc "cd frontend && php ../vendor/bin/codecept run unit modules/auth/services/AuthServiceTest --no-ansi"
```
