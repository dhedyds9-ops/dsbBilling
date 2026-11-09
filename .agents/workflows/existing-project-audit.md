# Antigravity Workflow: Existing Project Audit
## Brownfield Codebase & Mobile-Web Parity Audit

**Workflow ID:** `existing-project-audit`  
**Purpose:** Reusable, phased procedure to execute a complete non-destructive audit of dsBilling Web and Android codebases, validating architectural integrity, database schemas, RBAC alignment, and external integrations against `docs/SSOT.md`.

---

## 1. Prerequisites & Activation
- Workspace root: `D:\dsBilling` and mobile directory: `D:\dsbilling-mobile`.
- Triggered when starting a new engagement, verifying system health, or reviewing code changes after major milestones.
- **Safety Directive:** READ-ONLY inspection. Absolutely no modifications to application code or live database schemas during this workflow.

---

## 2. Phased Execution Steps

### Phase 1: Architectural & Documentation Reconnaissance
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`)
- **Actions:**
  1. Inspect `docs/SSOT.md` for current architectural directives and boundaries.
  2. Inspect previous audit documents in `docs/audit/`.
  3. Scan web routes in `routes/web.php` and `routes/api.php` to map public, authenticated, and mobile endpoints.
  4. Catalog all Eloquent models in `app/Models/` and domain classes in `src/Domain/`.
- **Output:** Initial asset inventory and route registry.

### Phase 2: Schema & Tenancy Scoping Inspection
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`) & `02 dsbilling-architect` (`.agents/skills/02-dsbilling-architect/SKILL.md`)
- **Actions:**
  1. Audit `database/migrations/` to identify all tables, foreign keys, and indexes.
  2. Verify that tenant-bound models implement `HasBranchScope` and `HasResellerScope`.
  3. Check for any duplicate models or migration anomalies.
  4. Inspect multi-tenant SQLite database management in `SAAS_GUIDE.md` and `app/Console/Commands/`.
- **Output:** Schema & Tenancy Audit section in report.

### Phase 3: Mobile Client & Parity Audit
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`) & `03 dsbilling-design` (`.agents/skills/03-dsbilling-design/SKILL.md`)
- **Actions:**
  1. Audit Android codebase at `D:\dsbilling-mobile`: activities, bottom sheets, networking services, preferences.
  2. Evaluate Material Design 3 (M3) compliance and verify zero core WebViews.
  3. Validate hardware-backed encryption via `KeyStoreManager.kt` and confirm zero plaintext tokens in SharedPreferences.
  4. Check that GenieACS NBI calls are strictly routed through backend proxies, not direct to port 7557.
- **Output:** Android Parity & Mobile Security Assessment.

### Phase 4: Integration & Network Hardware Audit
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`) & `05 dsbilling-backend` (`.agents/skills/05-dsbilling-backend/SKILL.md`)
- **Actions:**
  1. Review MikroTik integration drivers in `app/Integration/MikroTik/`.
  2. Review FreeRADIUS REST accounting and CoA dispatch methods.
  3. Review GenieACS proxy handlers in `app/Services/ACS/` and `MobileAcsProxyController.php`.
  4. Review OLT SNMP drivers and ONU signal monitoring routines.
- **Output:** Network Integration Health Matrix.

### Phase 5: Synthesis & Reporting
- **Skill:** `07 dsbilling-diff` (`.agents/skills/07-dsbilling-diff/SKILL.md`)
- **Actions:**
  1. Verify working directory is 100% clean (`git status -s`).
  2. Compile comprehensive audit report into `docs/audit/PROJECT_AUDIT_<DATE>.md`.
  3. Present executive findings, risk scores, and recommended action items.

---

## 3. Quality Gates & Acceptance Criteria
- [ ] No application source files or database records modified.
- [ ] Working tree verified clean via `git status`.
- [ ] Discrepancies between Web and Android cataloged with severity levels.
- [ ] Final audit report saved to `docs/audit/`.

---

## 4. Failure Handling & Audit Integrity Safeguard
1. **Unintended Modification Trap:** If `git status -s` detects modifications to existing application source code (`app/`, `src/`, `resources/`, `routes/`, `database/`), halt the workflow immediately.
2. **Reversion Protocol:** Immediately discard accidental edits with `git checkout -- <file>` to restore the brownfield baseline.
3. **Audit Log Flagging:** Record the violation in the audit report under "Audit Integrity Deviations" before concluding the audit.
