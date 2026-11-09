---
name: dsbilling-test
description: >-
  Automated testing, regression verification, and security test gates for dsBilling.
  Use when writing or running PHPUnit 11 tests (Feature & Unit), Android unit tests,
  mocking network hardware (MikroTik/GenieACS), or asserting tenant isolation and RBAC.
---

# 06 dsbilling-test — Automated Verification & Regression Guard

## 1. Overview & Purpose
`dsbilling-test` is the quality gate keeper of dsBilling. It executes and expands the test suite to guarantee 100% pass rates, zero regressions, strict multi-tenant boundary compliance, and robust error handling across both backend APIs and mobile client operations.

---

## 2. Activation Conditions
Activate this skill when:
- Verifying code changes before commit or deployment.
- Writing new Feature and Unit test cases for backend controllers, models, or services.
- Writing test cases for Android networking, KeyStore encryption, or offline sync.
- Verifying tenant isolation, branch scoping, and RBAC authorization boundaries.
- Investigating regression bugs.

---

## 3. Step-by-Step Execution Instructions

### Step 1: PHPUnit Backend Test Execution
1. **Windows Host Execution Guidance:**
   - On Windows, always invoke the native Windows batch wrapper:
     ```powershell
     .\vendor\bin\phpunit.bat
     ```
     Or through Artisan:
     ```powershell
     php artisan test
     ```
   - **Antigravity Sandbox Token & `is_readable()` Nuance:**
     Inside the standard Windows AppContainer sandbox, PHP's C runtime `is_readable()` check may return `false` on file paths due to token ACL constraints, causing PHPUnit 11 to throw `Cannot open bootstrap script "vendor/autoload.php"`.
     When running test verification on Windows:
     - Run test executions with `BypassSandbox: true` if sandbox ACL token blocking occurs.
     - For targeted module verification, pass the specific test path:
       ```powershell
       .\vendor\bin\phpunit.bat tests/Feature/Api/Mobile/WorkforceTest.php
       ```
2. Verify that 100% of existing tests pass (baseline: 63/63 passing).
3. If failures occur, pinpoint the regression, inspect the stack trace, and resolve it immediately.

### Step 2: New Feature Test Authoring
1. Create new test classes in `tests/Feature/` extending `Tests\TestCase`.
2. Use `RefreshDatabase` cautiously with isolated test SQLite databases.
3. Test authentication: verify `401 Unauthorized` without token, and successful access with valid Sanctum Bearer token.
4. Test RBAC permissions: verify `403 Forbidden` when a Technician attempts Manager or Reseller actions, and vice-versa.
5. Test tenant scoping: verify that User A from Branch 1 cannot see or mutate resources belonging to Branch 2.

### Step 3: Mocking External Network Hardware
1. Mock MikroTik RouterOS API socket calls (`RouterOSApi` mock).
2. Mock GenieACS NBI HTTP responses (`Http::fake()` or mock services).
3. Ensure hardware downtime or socket failure tests assert graceful fallback responses.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Backend implementation files from `05-dsbilling-backend`.
- Frontend/mobile changes from `04-dsbilling-build`.

### Output Artifacts:
- Test files in `tests/Feature/` or `tests/Unit/`.
- Test execution report (`docs/audit/TEST_RUN_<TIMESTAMP>.md` or output log):
  - Total test count, assertion count, execution duration.
  - Verification of zero regressions.

---

## 5. Quality Gates
- [ ] `php vendor/bin/phpunit` reports 0 failures and 0 errors.
- [ ] Multi-tenant isolation verified with multi-user test assertions.
- [ ] All new API endpoints have corresponding test coverage.
- [ ] No test modifies or wipes production databases.

---

## 6. Safety Constraints
- Never configure tests to connect to live production databases or production MikroTik routers.
- Always use mocked responses for live network hardware during automated test runs.

---

## 7. Handoff Requirements
Handoff output to `07-dsbilling-diff` for code diff review and drift audit.
