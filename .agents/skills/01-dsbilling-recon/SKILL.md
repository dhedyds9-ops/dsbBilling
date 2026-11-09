---
name: dsbilling-recon
description: >-
  Brownfield discovery, code inspection, and architectural reconnaissance for dsBilling.
  Use when exploring codebase structure, identifying existing models, routes, migrations,
  controllers, or auditing Android mobile and Laravel backend parity before making any changes.
---

# 01 dsbilling-recon — Reconnaissance & Discovery Engine

## 1. Overview & Purpose
`dsbilling-recon` is the non-destructive discovery and auditing engine for the dsBilling ecosystem. It scans both the Laravel 12 backend (`D:\dsBilling`) and Android Native client (`D:\dsbilling-mobile`), constructing an accurate inventory of assets, dependencies, schemas, and endpoint contracts without modifying any source files.

---

## 2. Activation Conditions
Activate this skill when:
- Beginning any new feature, bug fix, or investigation.
- Verifying whether a model, service, migration, or route already exists.
- Auditing alignment between Web backend endpoints and Android mobile clients.
- Investigating tenant isolation, branch scoping, or role assignments.
- Preparing inputs for `02-dsbilling-architect`.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Web Backend Inventory
1. Scan `routes/web.php` and `routes/api.php` for target endpoints and prefix groupings (`/technician-portal`, `/reseller-portal`, `/noc`, `/api/mobile`).
2. Inspect models in `app/Models/` and domain entities in `src/Domain/` to ensure no duplicates exist.
3. Inspect migration history in `database/migrations/` to understand table structures, foreign keys, and indexes.
4. Verify tenant traits (`HasBranchScope`, `HasResellerScope`, `HasUserAreaScope`) on relevant models.

### Step 2: Mobile Client Inventory
1. Inspect Android activities, dialogs, and adapters in `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\`.
2. Review networking services (`DsBillingApiService.kt`, `GenieAcsNbiService.kt`).
3. Check `KeyStoreManager.kt` and `AppPreferences.kt` for secure token handling.
4. Verify layout files in `app/src/main/res/layout/` to determine UI component maturity.

### Step 3: Network & External Services Inspection
1. Review MikroTik integration drivers in `app/Integration/MikroTik/`.
2. Inspect GenieACS proxy handlers in `app/Services/ACS/` and `app/Http/Controllers/Api/Mobile/MobileAcsProxyController.php`.
3. Check FreeRADIUS REST endpoints and queue worker configurations.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Target feature description or bug report.
- Reference architectural document: `docs/SSOT.md`.
- Existing audit documents in `docs/audit/`.

### Output Artifacts:
- **Reconnaissance Report** (written to `docs/audit/RECON_<MODULE_NAME>.md` or conversation output):
  - File inventory (relevant models, controllers, views, mobile classes).
  - Schema mapping (tables, columns, foreign keys).
  - Route & Permission mapping (roles required, middleware).
  - Risk & compatibility assessment.

---

## 5. Quality Gates
- [ ] No application code was altered during reconnaissance.
- [ ] Existing duplicate classes or migrations were flagged.
- [ ] Scoping traits (`HasBranchScope`, `HasResellerScope`) verified on related data entities.
- [ ] Security concerns (e.g., exposed ports, plaintext secrets) cataloged.

---

## 6. Safety Constraints
- **READ-ONLY EXECUTION:** Do not write or edit application source code, configuration files, or database entries during reconnaissance.
- Do not run `composer update`, `npm install`, or gradle sync tasks that alter lockfiles.
- Do not make live destructive API calls to production routers or GenieACS servers.

---

## 7. Handoff Requirements
Handoff output directly to `02-dsbilling-architect` with:
1. Path list of all files inspected.
2. Verified schema definitions and scoping constraints.
3. Identified integration touchpoints (MikroTik / GenieACS / OLT).
