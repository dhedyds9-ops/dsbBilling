# DSBILLING ENTERPRISE ARCHITECTURE: SINGLE SOURCE OF TRUTH (SSOT)
## Unified System Architecture, Tenancy, RBAC, Integrations & Parity Rules

**Document Version:** 1.0.0 (Authoritative SSOT)  
**Target Systems:**  
- **Web:** `D:\dsBilling` (Laravel 12, PHP 8.2+, Livewire, Tailwind CSS, Alpine.js)  
- **Mobile:** `D:\dsbilling-mobile` (Android Native, Kotlin, Material Design 3, Jetpack)  
**Governance Level:** MANDATORY / ZERO TOLERANCE FOR ARCHITECTURAL DRIFT  

---

## 1. Executive Mission & System Authority

1. **Definitive Architectural Authority:** This document (`docs/SSOT.md`) is the Single Source of Truth for all architectural, infrastructural, domain, and data decisions across the dsBilling platform.
2. **Brownfield / Existing Production Integrity:** dsBilling is an existing, active production system. **No scratch rebuilds.** Any addition or refactor must strictly extend existing patterns and maintain backward compatibility.
3. **No Duplicate Components:** Creating duplicate models, service layers, repository classes, or database migrations with overlapping responsibilities is strictly prohibited.
4. **Incremental Delivery:** Engineering is executed strictly in verified, isolated modules one step at a time.

---

## 2. Multi-Tenant Architecture & Data Scoping

### 2.1 Multi-Tenant SaaS Paradigm
dsBilling utilizes a **Dynamic Multi-Tenant SQLite Database Architecture** for physical tenant segregation:
- **Tenant Database Isolation:** Each subscriber ISP is provisioned an independent database instance located at `database/tenants/{tenant_slug}.sqlite`.
- **Tenant Storage Isolation:** Tenant media, logo, and file attachments are siloed in `storage/app/public/tenants/{tenant_slug}/`.
- **Tenant Routing & Identification:** Tenancy is dynamically resolved via subdomain routing (`{tenant}.domain.com`) through Nginx with the `Host` header passed upstream.

### 2.2 In-Tenant Data Scoping & Isolation
Within any tenant instance, logical isolation is enforced via Eloquent Global Scopes and Traits:
1. **`HasBranchScope` (`app/Traits/HasBranchScope.php`):**
   - Injects the `branch_isolation` global scope.
   - Restricts Managers, Technicians, and NOC personnel to their assigned `branch_id`.
   - Automatically assigns `branch_id` upon entity creation (`static::creating`).
2. **`HasResellerScope` (`app/Traits/HasResellerScope.php`):**
   - Enforces strict data partition for Resellers (`reseller_id`).
   - Resellers can only query and mutate customers, ONT profiles, and inventory materials assigned to their partnership hierarchy.
3. **`HasUserAreaScope` (`app/Traits/HasUserAreaScope.php`):**
   - Restricts field personnel to designated geographic distribution service areas.
4. **Company Scoping Status:**
   - Multi-tenancy operates primarily at the SQLite database layer (one database per ISP). Where `company_id` exists on operational models (such as `work_orders`), it must be verified explicitly via domain policies without relying on fallback values (`company_id ?? 1`). Intra-tenant company scoping will be formally unified in Phase 2C.

---

## 3. RBAC & Role Boundaries (Strict Web-to-Mobile Alignment)

### 3.1 Fundamental Governance Principles
- **ROLE ≠ PERMISSION:** A Role denotes *who* is authenticated (User persona and default portal). A Permission (`UserPermission` enum) explicitly governs *what* actions are permitted.
- **ROLE ≠ BRANCH:** Branches represent physical geographic divisions, not user roles.
- **ROLE ≠ RESELLER:** Resellers are commercial business entities with financial ledgers.
- **Backend Enforces Authority:** Mobile applications and web views never make unilateral permission decisions; all permissions are asserted by backend Sanctum middleware (`auth:sanctum`), role/job_function middleware (`role`, `job_function`), FormRequest validation, and `$user->hasPermission()` checks. Never declare an unmapped `permission:...` route middleware.

### 3.2 Standard Role Registry (`app/Enums/UserRole.php`)
1. **`administrator`:** Complete backoffice governance, audit trail inspection, system-wide configuration. Web-focused.
2. **`manager`:** Operational branch supervisor. Handles approval of Work Orders (SPK), material requisitions, and attendance overviews. If assigned `job_function: TECHNICIAN`, inherits full field technician toolsets with supervisor override authority.
3. **`teknisi` (`technician`):** Field operations hero. Android & Web Technician Portal. Handles Work Orders (SPK), PSB installation wizards, barcode scanning, ONT swap/dismantle, ODP GIS routing, custody stock on hand, and GPS shift clock-in/out.
4. **`reseller`:** Commercial distribution partner. Mobile & Web Reseller Portal. Manages customer CRM, new onboarding in reseller areas, invoice collection, wallet topups, and monitoring of reseller-assigned ONTs. Dllowed direct core network or global inventory access.
5. **`admin_gudang`:** Inventory, warehouse, and logistics management. Web-oriented. Inbound receiving, requisition dispatch approvals, custody transfers to technicians, return processing, and physical stocktaking (Opname).
6. **`noc`:** Network Operations Center. Web NOC Portal & Android Diagnostic tools. Core MikroTik monitoring, OLT optical power levels, LOS/alarm monitoring, topology graphs, and remote TR-069 diagnostics.
7. **`customer_service`:** CRM, lead tickets, customer complaints, and service provisioning requests.
8. **`kasir`:** Cash desk, invoice settlement, physical and digital payment reconciliation.
9. **`marketing`:** Sales pipeline, lead generation, voucher hotspots.
10. **`customer`:** End-user portal (`/customer-portal`). Read-only invoice viewing, payment checkout, speed test, and ticket submission.

---

## 4. Network & Hardware Integration Architecture

### 4.1 MikroTik Integration (`app/Integration/MikroTik`)
- **Transport:** Native RouterOS API (`evilfreelancer/routeros-api-php`) and SSH fallback (`phpseclib3`).
- **Core Responsibilities:**
  - Dynamic PPPoE Secret creation, activation, disconnection, and pool binding.
  - Hotspot User Profiles, active sessions, and byte-quota limits.
  - Queue Tree and Simple Queue rate-shaping.
  - Real-time traffic monitoring and accounting.

### 4.2 FreeRADIUS Integration
- **Transport:** REST API Integration Mode.
- **Core Responsibilities:** Centralized AAA authentication, interim accounting packet processing, framed-IP allocation, and CoA (Change of Authorization) disconnection.

### 4.3 GenieACS TR-069 Auto-Configuration Server (`app/Services/ACS`)
- **Port Strategy:**
  - `3000`: Web UI (Human administration only).
  - `7547`: CWMP (Modem TR-069 protocol endpoint).
  - `7557`: NBI API (Northbound Interface) — **strictly internal localhost/internal network.**
- **CRITICAL SECURITY LAW:** Mobile devices **never** communicate directly with Port 7557. All TR-069 operations (diagnostics, reboot, Wi-Fi configuration, WAN binding) must route through backend proxy endpoints (`/api/mobile/network/...` or `/api/mobile/acs/...`).

### 4.4 OLT, PON & Optical Distribution (ODC/ODP)
- **Vendors Supported:** ZTE, Huawei, Fiberhome, VSOL, HSGQ, C-Data.
- **Protocol:** SNMP v2c/v3 and CLI Telnet/SSH.
- **Topology:** Central Office OLT -> Distribution ODC -> Access ODP -> Customer Dropcore -> Optical Network Unit (ONU/ONT).
- **Signal Thresholds:**
  - Ideal: -18.0 dBm to -23.0 dBm
  - Acceptable: -23.1 dBm to -26.0 dBm
  - Critical/Loss: < -27.0 dBm

---

## 5. Mobile Application Parity & Architecture (`D:\dsbilling-mobile`)

1. **Native Android Platform:** Built with Kotlin, Gradle 9+, compileSdk 35, minSdk 24.
2. **Material Design 3 (M3):** Pure native components (`BottomNavigationView`, `BottomSheetDialogFragment`, `MaterialCardView`, `CoordinatorLayout`). Absolutely no hybrid WebViews for core transactional workflows.
3. **Security Standards:**
   - Sanctum Bearer Tokens encrypted at rest using **Android Keystore (AES-256 GCM)** via `KeyStoreManager.kt`.
   - `android:usesCleartextTraffic="false"` strictly enforced in production with HTTPS certificate pinning.
   - Zero hardcoded API keys or plaintext credentials.
4. **Primary Persona Focus:**
   - **Technician:** Field installation, diagnostics, offline queue sync.
   - **Reseller:** CRM, customer onboarding, invoice payment collection.
   - **Customer:** Billing transparency and service status.

---

## 6. Safety & Engineering Constraints

1. **Zero Destructive Migrations:** Never drop tables, truncate records, or delete production columns without explicit architectural sign-off.
2. **Preserve Customer Portal & NOC Styling:** Distinctive, high-contrast, polished UI themes for Customer Portal and NOC monitoring must be preserved.
3. **Comprehensive Test Gates:** Any change must pass the full PHPUnit suite (target 100% pass rate) without regressions.
4. **No Premature Deployment:** Production deployments require preflight verification, cache optimization, and explicit supervisor approval.
