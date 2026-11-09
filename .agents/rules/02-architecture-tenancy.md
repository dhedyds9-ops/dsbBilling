# Rule 02: Multi-Tenant Architecture & Data Scoping

## 1. Multi-Tenant SaaS Paradigm
- dsBilling implements a True Multi-Tenant SaaS architecture using dynamic SQLite tenant databases.
- Each ISP tenant operates with an isolated database file: `database/tenants/{tenant_slug}.sqlite`.
- Tenant storage and assets are isolated in `storage/app/public/tenants/{tenant_slug}/`.
- Tenant context is dynamically resolved via subdomain routing (`{tenant}.mstore.id`) through Nginx with `Host` header propagation.

## 2. In-Tenant Data Scoping & Branch Isolation
- Inside each tenant database, organizational units are strictly isolated using global scopes and traits:
- **Tenant Boundary (Primary):** The dynamic SQLite database file (`database/tenants/{tenant_slug}.sqlite`) provides physical database-level isolation between different ISP clients.
- **`HasBranchScope` (`app/Traits/HasBranchScope.php`):**
  - All models representing physical operational entities (routers, OLTs, ODPs, work orders, attendance) must use `HasBranchScope`.
  - Global scope `branch_isolation` automatically restricts queries for non-superadmin users based on `auth()->user()->branch_id`.
  - In `creating` lifecycle hooks, `branch_id` is automatically populated from the authenticated user.
- **`HasResellerScope` (`app/Traits/HasResellerScope.php`):**
  - Reseller accounts (`role: reseller`) are confined strictly to customers and inventory tagged with `reseller_id`.
  - Resellers must never access core router configurations, global warehouse inventory, or other resellers' data.
- **`HasUserAreaScope` (`app/Traits/HasUserAreaScope.php`):**
  - Confines field staff to designated geographic coverage areas.
- **Company Scoping Status:**
  - `company_id` on operational models (such as `work_orders`) must be checked explicitly in domain policies/requests. Never fallback to a default company ID (e.g. `1`) if unassigned. Intra-tenant multi-company isolation will be formalized in Phase 2C.

## 3. Web vs. Mobile Domain Boundaries
- **Web-Oriented Modules:**
  - Backoffice Administrator operations (`/dashboard`).
  - Warehouse & Logistics Management (`/inventory/*`).
  - NOC Router & OLT Management (`/noc/*`).
  - Financial General Ledger and Accounting (`/accounting/*`).
- **Mobile-Prioritized Modules:**
  - Field Technician operations (Work Orders, ONT Provisioning/Swap/Dismantle, GPS Attendance, Hand-held Custody stock).
  - Reseller operations (Customer CRM, Onboarding, Invoicing, Reseller ONT monitoring).
  - Customer self-service (Invoice review, speed test, ticket logging).
