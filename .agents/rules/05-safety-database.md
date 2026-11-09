# Rule 05: Database Safety, Testing Gates & Production Controls

## 1. Zero Destructive Database Operations
- **Strict Prohibition:** Never execute `migrate:fresh`, `migrate:reset`, `db:wipe`, or drop tables/columns in production or staging environments.
- **Additive Migrations:** All schema evolutions must be strictly additive. Adding nullable columns or new tables is permitted; removing or renaming existing active columns requires an explicit deprecation and verification cycle.
- **Rollback Safety:** Every migration's `down()` method must cleanly invert the operations performed in `up()` without data corruption.

## 2. Test Verification Gate
- Prior to completing any modification, the test suite must pass 100%:
  - Execute: `php vendor/bin/phpunit`
  - Zero test failures permitted (baseline: 63/63 passing).
  - All new endpoints or business logic must be accompanied by Feature test coverage in `tests/Feature/`.

## 3. Production Deployment Controls
- No automated deployments to live servers are permitted without explicit written user approval.
- Before production rollout:
  1. Complete preflight audit via `10-dsbilling-launch` and `11-dsbilling-deploy`.
  2. Verify Nginx configurations (`nginx -t`).
  3. Ensure supervisor queue workers are restarted gracefully (`php artisan queue:restart`).
  4. Clear application, route, and config caches (`php artisan optimize:clear` followed by `optimize`).
