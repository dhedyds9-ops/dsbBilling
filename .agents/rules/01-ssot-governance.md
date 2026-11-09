# Rule 01: SSOT Supremacy & Brownfield Governance

## 1. Single Source of Truth (`docs/SSOT.md`)
- The documentation in `docs/SSOT.md` is the primary, non-negotiable architectural authority for dsBilling.
- When any discrepancy arises between code comments, legacy documentation, and `docs/SSOT.md`, the specifications in `docs/SSOT.md` prevail.
- All modifications to models, database schemas, RBAC logic, or API contracts must be cross-referenced with `docs/SSOT.md` prior to code generation.

## 2. Brownfield Project Integrity
- dsBilling is an existing, active production application serving ISP operations.
- Rebuilding modules from scratch or introducing alternative frameworks/libraries is strictly prohibited.
- Developers and agents must understand existing code structures before proposing or writing modifications.
- Existing working features, routes, blade templates, and mobile screens must be preserved.

## 3. Strict Non-Duplication Policy
- **No Duplicate Models:** Before creating an Eloquent model, verify whether a model already exists in `app/Models/` or `src/Domain/`.
- **No Duplicate Services:** Business logic must reuse existing service classes located in `app/Services/` or `src/Application/Services/`.
- **No Duplicate Migrations:** Never create redundant migrations for columns or tables that already exist. Always check `database/migrations/` and database schemas.

## 4. Incremental Delivery Cycle
- Engineering tasks must be partitioned into single-module increments.
- Never refactor or touch unrelated modules within a feature implementation cycle.
- Each increment must satisfy its verification gate before the next increment commences.
