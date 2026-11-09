# Antigravity Workflow: Feature Planning
## Incremental Specification & Architectural Blueprinting

**Workflow ID:** `feature-planning`  
**Purpose:** Reusable procedure to transform raw feature requirements into an incremental technical specification, database migration blueprint, API contract, and UI layout wireframe adhering strictly to `docs/SSOT.md`.

---

## 1. Prerequisites & Activation
- User feature request or operational enhancement requirement.
- Authoritative architecture reference: `docs/SSOT.md`.
- Working tree is clean without uncommitted conflicts.

---

## 2. Phased Execution Steps

### Phase 1: Contextual Reconnaissance & Non-Duplication Check
- **Skill:** `01 dsbilling-recon` (`.agents/skills/01-dsbilling-recon/SKILL.md`)
- **Actions:**
  1. Inspect existing models, tables, and services related to the requested feature.
  2. Confirm that no existing class already solves the problem.
  3. Identify all models that will require relationship bindings or scope adjustments.
- **Output:** Reconnaissance summary of affected modules.

### Phase 2: Domain Architecture & Schema Design
- **Skill:** `02 dsbilling-architect` (`.agents/skills/02-dsbilling-architect/SKILL.md`)
- **Actions:**
  1. Design strictly additive database migrations (new nullable columns, new indices, new tables).
  2. Assign scoping traits (`HasBranchScope`, `HasResellerScope`, `HasUserAreaScope`).
  3. Define RBAC authorization rules mapping to `UserRole` and `UserPermission`.
  4. Specify API contracts (routes, HTTP methods, headers, payload schemas, error formats).
- **Output:** Domain Architecture & Database Blueprint.

### Phase 3: Commercial & Ledger Impact Analysis
- **Skill:** `08 dsbilling-entrepreneur` (`.agents/skills/08-dsbilling-entrepreneur/SKILL.md`)
- **Actions:**
  1. Evaluate if the feature impacts billing cycles, invoices, payments, or revenue sharing.
  2. If financial transactions are created, define balanced double-entry accounting records (`cash_transactions`, `journal_entries`).
  3. Ensure idempotency mechanisms are specified to prevent duplicate billing.
- **Output:** Financial Impact & Billing Verification Matrix.

### Phase 4: UI/UX & Native Component Specification
- **Skill:** `03 dsbilling-design` (`.agents/skills/03-dsbilling-design/SKILL.md`)
- **Actions:**
  1. For Web: Specify Blade view layouts, Livewire component properties, and Tailwind utility classes.
  2. For Android: Specify native XML layout structure, Material Design 3 (M3) components, and BottomSheets.
  3. Define loading, disabled, error, and empty states.
- **Output:** UI/UX Component Specifications.

### Phase 5: Implementation Plan & Handoff
- **Skill:** `02 dsbilling-architect` (`.agents/skills/02-dsbilling-architect/SKILL.md`)
- **Actions:**
  1. Assemble complete implementation plan document in `docs/plans/FEATURE_<FEATURE_NAME>.md`.
  2. Partition implementation into small, atomic, independently testable tasks.
  3. Present plan to user for formal review and approval.

---

## 3. Quality Gates & Acceptance Criteria
- [ ] Schema changes are 100% additive with no table drops or column deletions.
- [ ] Scoping and tenancy rules explicitly defined.
- [ ] RBAC boundaries strictly aligned with `docs/SSOT.md`.
- [ ] Feature plan approved by user prior to code implementation.
