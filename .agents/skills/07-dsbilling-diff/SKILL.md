---
name: dsbilling-diff
description: >-
  Git diff inspection, drift analysis, code review, and regression audit for dsBilling.
  Use when analyzing uncommitted git changes, verifying that only intended files were modified,
  checking for architectural drift against SSOT, and preparing clean commit reviews.
---

# 07 dsbilling-diff — Drift Analysis & Code Review Engine

## 1. Overview & Purpose
`dsbilling-diff` is the forensic inspection tool of dsBilling. It performs granular git diff analysis, detects unintended file modifications or formatting noise, flags breaking API changes, and ensures that code changes strictly adhere to `docs/SSOT.md` without introducing architectural drift.

---

## 2. Activation Conditions
Activate this skill when:
- Reviewing staged or unstaged changes before committing.
- Validating that no existing application source code was unintentionally modified during Phase 0 discovery.
- Checking for accidental secrets, dump statements (`dd()`, `dump()`, `Log.d`), or test credentials.
- Auditing schema drift between database migrations and Eloquent models.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Git Status & File Boundary Audit
1. Run `git status -s` to list all modified, added, and deleted files.
2. Verify that modified files fall strictly within the scope of the assigned module.
3. Reject changes to unrelated files, lockfiles, or production environment files (`.env`).

### Step 2: Line-by-Line Diff Inspection
1. Run `git diff` for modified tracked files.
2. Check for leftover debugging code (`dd()`, `ray()`, `var_dump()`, `print_r()`, `console.log`).
3. Check for plaintext tokens, API keys, or database credentials.
4. Verify that existing comments and documentation integrity are preserved.

### Step 3: SSOT Conformance Check
1. Cross-reference modified routes, permissions, and models against `docs/SSOT.md`.
2. Confirm that no duplicate classes were added.
3. Confirm that no breaking contract changes were introduced to mobile APIs.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Git working tree changes (`git status`, `git diff`).
- Architectural specification: `docs/SSOT.md`.

### Output Artifacts:
- **Diff & Drift Audit Report** (`docs/audit/DIFF_AUDIT_<MODULE>.md` or output):
  - Summary of changed files (classified into Models, Controllers, Views, Migrations, Tests).
  - List of detected risks, leftover debug code, or deviations.
  - Final verdict: `APPROVED FOR COMMIT` or `REJECTED (REQUIRES CLEANUP)`.

---

## 5. Quality Gates
- [ ] Zero unintended files touched.
- [ ] Zero debugging statements (`dd()`, `dump()`) remaining.
- [ ] Zero plaintext secrets or sensitive environment values introduced.
- [ ] Full compliance with `docs/SSOT.md`.

---

## 6. Safety Constraints
- Do not run `git reset --hard` or `git clean -fd` without explicit user instruction.
- Do not overwrite git history or commit with `--force`.

---

## 7. Handoff Requirements
Handoff output to:
- `10-dsbilling-launch` or `11-dsbilling-deploy` if preparing a release.
- Developer for git commit upon approval.
