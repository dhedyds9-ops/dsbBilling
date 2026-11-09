# Antigravity Workflow: Release Preflight
## Production Readiness, Cryptographic Build Verification & Rollout Preflight

**Workflow ID:** `release-preflight`  
**Purpose:** Reusable workflow to validate complete production readiness, verify Android APK/AAB signed bundles, audit ProGuard/R8 rules, verify web asset compilation, and generate the final deployment authorization manifest.

---

## 1. Prerequisites & Activation
- Completion of `regression-testing` workflow with 100% test pass rate.
- Clean git status without untracked debris.
- Release signing key configured in `D:\dsbilling-mobile\local.properties`.

---

## 2. Phased Execution Steps

### Phase 1: Security & Secrets Preflight Audit
- **Skill:** `07 dsbilling-diff` (`.agents/skills/07-dsbilling-diff/SKILL.md`)
- **Actions:**
  1. Scan entire codebase for accidental credentials, secrets, or API keys.
  2. Verify that Android code uses `KeyStoreManager.kt` (AES-256 GCM) for token persistence.
  3. Confirm `android:usesCleartextTraffic="false"` is set in `AndroidManifest.xml`.
  4. Ensure all third-party AI keys (Gemini) are strictly server-side.
- **Output:** Security preflight verification log.

### Phase 2: Web Production Asset Compilation
- **Skill:** `04 dsbilling-build` (`.agents/skills/04-dsbilling-build/SKILL.md`) & `10 dsbilling-launch` (`.agents/skills/10-dsbilling-launch/SKILL.md`)
- **Actions:**
  1. Run production Vite compilation:
     ```bash
     npm run build
     ```
  2. Check `public/build/manifest.json` for all required CSS and JS bundles.
  3. Validate Customer Portal and NOC layout rendering.
- **Output:** Production web assets and manifest.

### Phase 3: Android Release Packaging & Verification
- **Skill:** `10 dsbilling-launch` (`.agents/skills/10-dsbilling-launch/SKILL.md`)
- **Actions:**
  1. Audit `app/build.gradle.kts`: verify `compileSdk = 35`, `targetSdk = 35`, `minSdk = 24`.
  2. Verify ProGuard rules in `proguard-rules.pro` preserve all data models and serialization classes.
  3. Validate APK and AAB build artifacts and check sizes.
  4. Confirm cryptographic signature validity using APK Signature Scheme v1 & v2.
- **Output:** Verified signed release artifacts (`Genieacs.apk`, `Genieacs.aab`).

### Phase 4: Multi-Tenant Database Migration Preflight
- **Skill:** `02 dsbilling-architect` (`.agents/skills/02-dsbilling-architect/SKILL.md`) & `11 dsbilling-deploy` (`.agents/skills/11-dsbilling-deploy/SKILL.md`)
- **Actions:**
  1. Inspect pending database migrations. Confirm that all migrations are strictly additive.
  2. Verify rollback methods (`down()`) for each pending migration.
  3. Plan migration sequence across main database and tenant SQLite files in `database/tenants/`.
- **Output:** Multi-tenant migration rollout plan.

### Phase 5: Authorization Manifest & Handoff
- **Skill:** `11 dsbilling-deploy` (`.agents/skills/11-dsbilling-deploy/SKILL.md`)
- **Actions:**
  1. Compile preflight checklist into `docs/audit/RELEASE_PREFLIGHT_<VERSION>.md`.
  2. Present release summary, SHA-256 artifact checksums, and safety checklist to system administrator.
  3. Await explicit written approval before any server deployment.
- **Output:** Final Preflight Release Report.

---

## 3. Quality Gates & Acceptance Criteria
- [ ] Regression testing passed 100% prior to preflight.
- [ ] Android APK/AAB signed with verified v1 & v2 signatures.
- [ ] Cleartext HTTP blocked in production Android configuration.
- [ ] Multi-tenant migrations verified non-destructive.
- [ ] Explicit user sign-off required for deployment.
