# Antigravity Workflow: Single-Module Implementation
## Incremental Module Development, Integration & Verification Cycle

**Workflow ID:** `single-module-implementation`  
**Purpose:** Reusable workflow to implement an approved, isolated module or feature increment across Laravel backend and Android mobile without touching unrelated code.

---

## 1. Prerequisites & Activation
- Approved feature specification from `feature-planning` workflow.
- Clean git status.
- Zero existing regressions (`php vendor/bin/phpunit` passing).

---

## 2. Phased Execution Steps

### Phase 1: Database Migration & Model Layer
- **Skill:** `05 dsbilling-backend` (`.agents/skills/05-dsbilling-backend/SKILL.md`)
- **Actions:**
  1. Author additive migration file in `database/migrations/`.
  2. Implement Eloquent model in `app/Models/` or `src/Domain/`.
  3. Attach `HasBranchScope` or `HasResellerScope` traits.
  4. Define `$fillable` array and attribute casting.
- **Output:** Migration and Model source files.

### Phase 2: Service Layer & Integration Logic
- **Skill:** `05 dsbilling-backend` (`.agents/skills/05-dsbilling-backend/SKILL.md`)
- **Actions:**
  1. Encapsulate business logic in dedicated service classes (`app/Services/`).
  2. If hardware integration is involved, implement safe MikroTik or GenieACS proxy drivers.
  3. Wrap network or slow operations in queued Laravel Jobs.
  4. Implement `audit_logs` logging for operational accountability.
- **Output:** Service and Job classes.

### Phase 3: REST API & Web Controllers
- **Skill:** `05 dsbilling-backend` (`.agents/skills/05-dsbilling-backend/SKILL.md`)
- **Actions:**
  1. Register routes in `routes/api.php` or `routes/web.php`.
  2. Implement `FormRequest` validation classes.
  3. Author controller action methods returning standardized JSON responses.
  4. Enforce `auth:sanctum`, registered role/job_function middleware, and explicit permission authorization checks.
- **Output:** Controller and Request classes.

### Phase 4: UI & Mobile Implementation
- **Skill:** `04 dsbilling-build` (`.agents/skills/04-dsbilling-build/SKILL.md`)
- **Actions:**
  1. Implement Blade/Livewire components or Android Kotlin / XML views.
  2. For Android: bind views using ViewBinding; connect to backend via `DsBillingApiService.kt`.
  3. Verify responsive layout, dark/light themes, and outdoor readability.
  4. For Web: compile assets using `npm run build`.
- **Output:** UI templates and client code.

### Phase 5: Automated Testing & Verification Gate
- **Skill:** `06 dsbilling-test` (`.agents/skills/06-dsbilling-test/SKILL.md`)
- **Actions:**
  1. Write Feature tests in `tests/Feature/` covering authentication, permission gates, tenant scopes, and success/failure scenarios.
  2. Execute `php vendor/bin/phpunit`.
  3. Assert 100% pass rate with zero regressions.
- **Output:** Test results and verification logs.

### Phase 6: Code Diff & Drift Audit
- **Skill:** `07 dsbilling-diff` (`.agents/skills/07-dsbilling-diff/SKILL.md`)
- **Actions:**
  1. Run `git status -s` and inspect `git diff`.
  2. Verify that only files belonging to this module were changed.
  3. Remove any leftover debugging statements (`dd()`, `dump()`, `console.log`).
- **Output:** Diff review summary and clean working tree.

---

## 3. Quality Gates & Acceptance Criteria
- [ ] Feature tests pass 100% with zero regressions.
- [ ] Tenant scoping verified across all newly created endpoints.
- [ ] No unapproved packages or dependencies added.
- [ ] Git diff inspected and approved.

---

## 4. Failure Recovery & Rollback Protocol
If any error occurs during Phases 1–5 (compilation failure, unhandled exception, or test failure):
1. **Immediate Execution Halt:** Cease execution immediately. Do not attempt unapproved blind edits across other files.
2. **State Inspection:** Run `git status -s` to list all modified and newly created files for this increment.
3. **Database Rollback (if locally migrated):**
   ```powershell
   php artisan migrate:rollback --step=1
   ```
4. **File Restoration:** Revert modified files to the clean baseline state:
   ```powershell
   git checkout -- <modified_files>
   ```
   Remove any scratch files or unneeded test artifacts.
5. **Cache Reset:** Run `php artisan optimize:clear` to prevent stale caches.
6. **Incident Logging:** Log the failure details, error stack trace, and root cause in `docs/audit/INCIDENT_<MODULE>.md` before requesting developer guidance.
