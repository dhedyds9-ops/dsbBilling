---
name: dsbilling-backend
description: >-
  Backend domain development, REST API engineering, and network hardware integrations for dsBilling.
  Use when writing Laravel 12 controllers, models, migrations, service classes, policies,
  Sanctum mobile endpoints, or MikroTik and GenieACS TR-069 communication drivers.
---

# 05 dsbilling-backend — Backend Domain & API Engineering

## 1. Overview & Purpose
`dsbilling-backend` is the core engine for server-side business logic, database migrations, RESTful APIs, and hardware integration drivers within Laravel 12. It enforces multi-tenant scoping, strict RBAC authorization, and asynchronous job queuing.

---

## 2. Activation Conditions
Activate this skill when:
- Authoring database migrations, Eloquent models, or Repository queries.
- Building REST API endpoints under `/api/mobile/*` or web controllers.
- Implementing MikroTik RouterOS API interactions or FreeRADIUS sync routines.
- Constructing GenieACS TR-069 proxy services or OLT SNMP drivers.
- Handling asynchronous background tasks via Laravel Jobs and Queues.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Database Migration & Model Creation
1. Create additive migrations in `database/migrations/`.
2. Implement or update Eloquent models in `app/Models/` or `src/Domain/`.
3. Attach tenant scoping traits: `HasBranchScope`, `HasResellerScope`, `HasUserAreaScope`.
4. Configure mass-assignment `$fillable` arrays and strict attribute casts.

### Step 2: Service Layer & Integration Drivers
1. Encapsulate business logic in `app/Services/` or domain actions.
2. Implement network equipment drivers:
   - MikroTik: `app/Integration/MikroTik/` using `RouterOS API`.
   - GenieACS: `app/Services/ACS/` querying internal Port 7557 and parsing TR-069 parameters.
   - OLT: driver abstractions for ZTE, Huawei, and Fiberhome.
3. Dispatch slow or network-dependent operations via queued Jobs (`dispatch()`).

### Step 3: Controller & API Routing
1. Define route groups in `routes/api.php` or `routes/web.php`.
2. Apply registered authentication and role middleware:
   - Sanctum Token auth: `middleware('auth:sanctum')`.
   - Role gate: `middleware('role:administrator,manager')` (using `RoleMiddleware`).
   - Job function gate: `middleware('job_function:TECHNICIAN')` (using `JobFunctionMiddleware`).
   - Workforce shift gate: `middleware('workforce.checked_in')`.
   - DO NOT use an unmapped `permission:...` middleware alias.
3. Enforce granular permissions via actual Laravel authorization mechanisms:
   - User permission assertion: `$user->hasPermission(UserPermission::CustomerCreate->value)`.
   - Policy authorization: `$this->authorize('create', Customer::class)`.
   - Native route ability gate: `middleware('can:update,workOrder')`.
4. Implement `FormRequest` classes with robust validation rules and explicit `authorize()` methods.
5. Return standardized JSON envelopes with correct HTTP status codes (200, 201, 400, 401, 403, 404, 422).

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Architectural specifications from `02-dsbilling-architect`.
- Reconnaissance inventory from `01-dsbilling-recon`.

### Output Artifacts:
- Migration files in `database/migrations/`.
- Model classes in `app/Models/` or `src/Domain/`.
- Service classes in `app/Services/`.
- Controller and Request classes in `app/Http/`.
- Route definitions in `routes/api.php` or `routes/web.php`.

---

## 5. Quality Gates
- [ ] No duplicate models, migrations, or services created.
- [ ] All tenant models incorporate `HasBranchScope` or `HasResellerScope`.
- [ ] Mobile endpoints require `auth:sanctum`.
- [ ] All hardware calls are wrapped in exception handlers with logging to `audit_logs`.

---

## 6. Safety Constraints
- Never execute destructive migration commands (`migrate:fresh`, `db:wipe`).
- Never expose GenieACS Port 7557 directly to mobile clients.
- Never write hardcoded database credentials or API secrets.

---

## 7. Handoff Requirements
Handoff output to `06-dsbilling-test` for automated PHPUnit test verification.
