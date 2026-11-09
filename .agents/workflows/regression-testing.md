# Antigravity Workflow: Regression Testing
## Comprehensive Automated Test Suite & Multi-Tenant Security Verification

**Workflow ID:** `regression-testing`  
**Purpose:** Reusable workflow to execute the full automated test suite, verify multi-tenant isolation, stress-test API security, and guarantee zero functional regressions before staging or deployment.

---

## 1. Prerequisites & Activation
- Full codebase available locally in `D:\dsBilling`.
- SQLite test database configured in `phpunit.xml`.
- Triggered after every completed module or before executing release packaging.

---

## 2. Phased Execution Steps

### Phase 1: Test Environment Preflight
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`)
- **Actions:**
  1. Inspect `phpunit.xml` configuration to ensure tests run against isolated test databases.
  2. Verify PHP CLI runtime: PHP 8.2+ required.
  3. Ensure cache files are cleared (`php artisan optimize:clear`).
- **Output:** Test environment readiness confirmation.

### Phase 2: Full Suite Automated Test Run
- **Skill:** `06 dsbilling-test` (`.agents/skills/06-dsbilling-test/SKILL.md`)
- **Actions:**
  1. Execute PHPUnit across all Feature and Unit test suites:
     - On Windows hosts:
       ```powershell
       .\vendor\bin\phpunit.bat
       ```
       Or via Artisan:
       ```powershell
       php artisan test
       ```
     - (Note: Run outside AppContainer token sandbox or with BypassSandbox if Windows ACL blocks `is_readable`).
  2. Record test metrics: total tests, total assertions, execution time.
  3. Verify that 100% of tests pass with zero failures (baseline: 63/63 minimum).
- **Output:** Test execution results.

### Phase 3: Multi-Tenant Boundary Assertion
- **Skill:** `06 dsbilling-test` (`.agents/skills/06-dsbilling-test/SKILL.md`)
- **Actions:**
  1. Execute tests specifically asserting `HasBranchScope` isolation.
  2. Execute tests asserting `HasResellerScope` isolation.
  3. Verify that unauthenticated requests to `/api/mobile/*` return `401 Unauthorized`.
  4. Verify that cross-branch data manipulation returns `403 Forbidden` or empty results.
- **Output:** Tenancy & Security assertion checklist.

### Phase 4: Mocked Hardware Integration Verification
- **Skill:** `06 dsbilling-test` (`.agents/skills/06-dsbilling-test/SKILL.md`)
- **Actions:**
  1. Verify MikroTik API error recovery when router sockets disconnect.
  2. Verify GenieACS TR-069 proxy error handling when NBI timeouts occur.
  3. Confirm that no live production router calls are made during automated tests.
- **Output:** Hardware Mocking Verification Report.

### Phase 5: Regression Verdict & Reporting
- **Skill:** `07 dsbilling-diff` (`.agents/skills/07-dsbilling-diff/SKILL.md`)
- **Actions:**
  1. Compile full test report into `docs/audit/REGRESSION_TEST_<TIMESTAMP>.md`.
  2. If any test fails, halt the workflow immediately and report the regression.
  3. Issue `PASS - READY FOR RELEASE` if 100% successful.
- **Output:** Final Regression Audit Artifact.

---

## 3. Quality Gates & Acceptance Criteria
- [ ] 100% tests pass (0 failures, 0 errors).
- [ ] Multi-tenant isolation verified via automated assertions.
- [ ] Zero unhandled hardware exceptions.
- [ ] Full test report archived in `docs/audit/`.

---

## 4. Failure Handling & Triage Protocol
If any test failure or runtime regression occurs:
1. **Immediate Workflow Halting:** Block any subsequent packaging (`10-dsbilling-launch`) or deployment (`11-dsbilling-deploy`).
2. **Defect Isolation:** Extract the failing test name, assertion diff, and exact file/line from PHPUnit output.
3. **Sandbox vs. Code Triage:** Verify whether the failure is an authentic code regression or an environment issue (e.g., Windows sandbox `is_readable` token restriction, stale SQLite database locks).
4. **Diagnostic Report:** Generate a triage artifact in `docs/audit/REGRESSION_FAIL_<TIMESTAMP>.md` outlining the failing assertions.
5. **Clean State Restoration:** Run `php artisan optimize:clear` and reset temporary test databases. Do not push unverified code.
