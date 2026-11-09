---
name: dsbilling-deploy
description: >-
  Production deployment, multi-tenant migrations, Nginx reverse proxy, and operational health checks for dsBilling.
  Use when deploying updates to VPS/servers, running safe tenant migrations, configuring
  Supervisor queue workers, tuning Nginx/PHP-FPM, or executing rollback procedures.
---

# 11 dsbilling-deploy — Production Deployment & Operations Engine

## 1. Overview & Purpose
`dsbilling-deploy` orchestrates the zero-downtime deployment, server provisioning, web server configuration, and tenant database synchronization for the dsBilling enterprise platform. It guarantees that live ISP billing and network management services remain uninterrupted during upgrades.

---

## 2. Activation Conditions
Activate this skill when:
- Deploying verified code changes to staging or production Ubuntu servers.
- Running database migrations across the main database and all tenant SQLite databases.
- Configuring or testing Nginx reverse proxy sites and SSL/TLS certificates (Let's Encrypt / Certbot).
- Managing Supervisor queue workers, Redis, and cron schedules (`php artisan schedule:run`).
- Executing emergency rollback procedures.

---

## 3. Step-by-Step Execution Instructions

### Stage A: Local Staging & Preflight (Windows PowerShell Host)
1. **Explicit Approval Gate:** Verify written user/admin authorization. Never deploy autonomously.
2. **Working Tree Verification:** Confirm git working directory is 100% clean (`git status -s`).
3. **Local Asset Bundling:**
   ```powershell
   npm run build
   ```
4. **Preflight Checklist:** Ensure tests passed (`06-dsbilling-test`), diff was approved (`07-dsbilling-diff`), and release artifacts verified (`10-dsbilling-launch`).

---

### Stage B: Remote Production Server Rollout (Ubuntu Linux via SSH)
*All commands in this stage execute on the remote Ubuntu production server over SSH, NEVER on the local Windows machine.*

1. **Maintenance Mode Activation:**
   ```bash
   php artisan down --secret="dsbilling-deploy-secret"
   ```
2. **Code Pull & Remote Dependency Sync:**
   ```bash
   git pull origin main
   composer install --no-dev --optimize-autoloader --no-interaction
   ```
3. **Safe Additive Migrations:**
   ```bash
   php artisan migrate --force
   ```
4. **Multi-Tenant SQLite Database Sync:**
   - Iterate over all tenant databases in `database/tenants/` and apply additive migrations.
   - Restore Linux web server ownership and permissions:
     ```bash
     sudo chown -R www-data:www-data storage bootstrap/cache database/tenants
     sudo chmod -R 775 storage bootstrap/cache database/tenants
     ```
5. **Cache Optimization & Service Restarts:**
   ```bash
   php artisan optimize:clear
   php artisan optimize
   php artisan queue:restart
   sudo supervisorctl restart dsbilling-worker:*
   sudo nginx -t && sudo systemctl reload nginx
   sudo systemctl reload php8.2-fpm
   ```
6. **Bring Production Online & Smoke Test:**
   ```bash
   php artisan up
   curl -s -o /dev/null -w "%{http_code}" https://majujaya.mstore.id/api/mobile/version/check
   ```

---

### Stage C: Emergency Rollback Protocol
If any failure occurs during Stage B:
1. Revert Git commit: `git reset --hard HEAD@{1}`.
2. Revert pending additive migration (if safe): `php artisan migrate:rollback --step=1`.
3. Clear corrupted caches: `php artisan optimize:clear`.
4. Restart queue workers: `sudo supervisorctl restart all`.
5. Bring site back online: `php artisan up`.
6. Notify administrator immediately with error stack trace.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Release preflight verification from `10-dsbilling-launch`.
- Verified server credentials and SSH connection.
- Deployment parameters (subdomains, tenant list).

### Output Artifacts:
- **Deployment Log & Health Report** (`docs/audit/DEPLOY_LOG_<DATE>.md`):
  - Deployment timestamp and Git commit hash.
  - Migration output across main and tenant databases.
  - Service status checks (Nginx, PHP-FPM, Supervisor, Redis, MariaDB).
  - Smoke test verification results.

---

## 5. Quality Gates
- [ ] Explicit written user sign-off verified before execution.
- [ ] Database migrations executed without dropping tables or corrupting records.
- [ ] File permissions properly restored to `www-data:www-data`.
- [ ] HTTP health check returns 200 OK.
- [ ] Supervisor queue workers running without errors.

---

## 6. Safety Constraints
- **ZERO DEPLOYMENT WITHOUT APPROVAL:** Never deploy code to production servers autonomously.
- Never run `migrate:fresh` or destructive database commands in production.
- Keep automatic database backups prior to executing any production schema migrations.

---

## 7. Handoff Requirements
Handoff output to system administrator and provide final health summary in chat.
