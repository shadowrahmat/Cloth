# Commands and Testing Rules — Cloth

## 1. Running commands
Run project commands directly on the host with `composer`.

## 2. Tests
Run `php artisan test` before marking work complete.

```bash
php artisan test
```

Report exactly which commands ran and their results. Do not weaken, skip or delete a failing test to get a green run.

## 3. Database
- Never run destructive database commands against shared or production data.
- Never edit an already-deployed migration; add a new one.
