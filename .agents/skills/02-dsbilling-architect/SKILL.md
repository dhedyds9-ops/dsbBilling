---
name: dsbilling-architect
description: >-
  System architecture, SSOT enforcement, domain modeling, and technical specification for dsBilling.
  Use when designing database migrations, planning new modules, structuring API contracts,
  or establishing tenant isolation, role boundaries, and integration flows.
---

# 02 dsbilling-architect — System Architecture & SSOT Modeling

## 1. Overview & Purpose
`dsbilling-architect` translates business requirements and reconnaissance findings into strict, production-grade technical designs adhering to `docs/SSOT.md`. It defines database schemas, DDD boundaries, API contracts, RBAC mappings, and tenant isolation policies before any code is generated.

---

## 2. Activation Conditions
Activate this skill when:
- Designing a new feature, table, API endpoint, or operational workflow.
- Resolving architectural conflicts or discrepancies between Web and Android layers.
- Defining integration contracts for MikroTik, GenieACS, FreeRADIUS, or OLT hardware.
- Planning multi-tenant data structures or Eloquent global scopes.

---

## 3. Step-by-Step Execution Instructions

### Step 1: SSOT Alignment Review
1. Cross-reference proposed features against `docs/SSOT.md`.
2. Check existing roles (`app/Enums/UserRole.php`) and permissions (`app/Enums/UserPermission.php`). Never introduce ad-hoc roles.
3. Validate tenant isolation: specify whether entities require `branch_id`, `reseller_id`, or `customer_id`.

### Step 2: Database Schema & Migration Planning
1. Design additive schema changes only (new nullable columns, new indices, new tables).
2. Explicitly forbid dropping tables or removing active columns.
3. Define index strategies for multi-tenant queries (`branch_id`, `status`, `created_at`).
4. Ensure reversible `down()` migrations.

### Step 3: API & Mobile Contract Definition
1. Design RESTful endpoint specifications under `/api/mobile/*` or web routes.
2. Formulate request validation rules (`FormRequest`) and authorization policies (`Policy`).
3. Formulate JSON response schemas ensuring standard envelopes: `{ "success": true, "data": {...}, "message": "..." }`.
4. Ensure hardware operations (reboot, Wi-Fi configuration, PPPoE resets) use asynchronous queuing where necessary.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Reconnaissance report from `01-dsbilling-recon`.
- Architectural baseline: `docs/SSOT.md`.
- User feature request or operational requirement.

### Output Artifacts:
- **Architectural Specification Document** (`docs/plans/ARCH_<FEATURE_NAME>.md`):
  - Domain entity design and table schemas.
  - Endpoints contract (URI, HTTP method, request payload, response schema).
  - RBAC & Scope assertion rules (`branch_id`, `reseller_id`).
  - Hardware integration topology diagram or sequence flow.

---

## 5. Quality Gates
- [ ] Schema design is 100% additive with no destructive drop operations.
- [ ] Role assignments align strictly with `UserRole` enum.
- [ ] Scoping traits (`HasBranchScope`, `HasResellerScope`) assigned to all tenant-bound models.
- [ ] GenieACS NBI port 7557 access remains isolated behind backend proxy endpoints.

---

## 6. Safety Constraints
- Do not execute database migrations or mutate live database tables.
- Do not bypass Laravel Sanctum authentication for mobile APIs.
- Do not design synchronous hardware calls on high-traffic web routes.

---

## 7. Handoff Requirements
Handoff output to:
- `03-dsbilling-design` for UI/UX wireframing and component design.
- `05-dsbilling-backend` for server-side domain and API implementation.
