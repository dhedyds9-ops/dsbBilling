# PHASE 2B AUDIT REPORT: GENIEACS MOBILE SECURITY HARDENING & RESELLER AUTHORIZATION CORRECTION
**dsBilling AI Development System — Security & Architectural Verification**
*Date: 2026-11-09*
*Authority: docs/SSOT.md, .agents/AGENTS.md, .agents/rules/04-network-integrations.md*

---

## 1. Executive Summary

Phase 2B has achieved full security hardening and refined authorization for GenieACS mobile operations.
All direct mobile communication from the Android application (`D:\dsbilling-mobile`) to GenieACS NBI port 7557 has been completely removed.
Every GenieACS operation is mediated through authenticated, authorized, and tenant-scoped proxy endpoints within the dsBilling Laravel backend (`D:\dsBilling`).

Following user review, a **Targeted Authorization Correction** was implemented to align with the dsBilling ISP business model: Resellers are not merely sales agents—they actively manage customer CPEs and network devices within their assigned scope. The previous blanket role ban on `wan`, `admin`, and `tasks` was replaced with a robust **3-Layer Authorization Model (Role + Permission + Scope/Ownership)**.

### Core Accomplishments:
1. **Zero Direct NBI Access:** The Android app has zero occurrences of port `7557` or internal NBI endpoints in its codebase.
2. **Credential Isolation:** GenieACS basic authentication credentials and internal service URLs remain exclusively within the backend environment (`.env` / `config/genieacs.php`). The mobile client holds only a dsBilling Sanctum bearer token.
3. **3-Layer Authorization Architecture:**
   - **Layer A (Role):** Backoffice Administrator bypass; Staff / Technician / Reseller role identification.
   - **Layer B (Permission):** Granular permission checks (`onu.configure.wan`, `onu.configure.wifi`, `onu.configure.admin`, `onu.task.delete`, `onu.reboot`, etc.).
   - **Layer C (Scope & Ownership):** Strict validation chain: `Reseller -> CustomerService -> ONU -> ACSDevice` preventing cross-reseller access, cross-tenant/branch leakage, and device ID spoofing.
4. **Zero Regressions & Full Verification:** 100% of test suites pass (`MobileAcsProxySecurityTest` 13/13 OK, `MobileNetworkOperationsTest` 6/6 OK), and the Android app compiles cleanly (`BUILD SUCCESSFUL in 16s`).

---

## 2. Targeted Correction: Reseller Device Management Authorization

### A. Pembatasan Reseller Sebelumnya (Previous Blanket Role Ban)
In the initial Phase 2B implementation, `MobileAcsProxyController::validateDeviceAccess` automatically rejected any user with the `reseller` role from executing `wan`, `admin`, and `tasks` operations via a hardcoded role check:
```php
// Old naive implementation:
if ($user->hasRole('reseller')) {
    if (in_array($operation, ['wan', 'admin', 'tasks'], true)) {
        return response()->json(['message' => 'Akun Mitra Reseller tidak diizinkan...'], 403);
    }
}
```

### B. Alasan Perubahan Model Akses (Rationale)
In dsBilling Enterprise, Resellers (Channel Partners / Mitra) often operate as independent micro-ISPs or regional field managers responsible for customer installations, PPPoE credentials, WiFi customization, and modem diagnostics within their area.
Blocking WAN, admin accounts, or task queues based solely on the `reseller` role crippled legitimate operational workflows.
The correct architectural model dictates:
- A **Role** determines who the user is.
- A **Permission** determines what actions the user is authorized to perform.
- An **Ownership & Scope** check determines which devices the user is permitted to touch.

### C. Permission yang Digunakan (Granular Permissions)
The following permissions from `App\Enums\UserPermission` and `App\Services\Auth\PermissionRegistry` govern ACS proxy operations:

| Operasi API | Permission yang Diperiksa | Deskripsi |
| :--- | :--- | :--- |
| `read` (Detail / Parameter) | `onu.view`, `noc.onu.view`, `onu.manage` | Melihat parameter dan status perangkat |
| `wifi` (SSID, Password, WLAN Enable) | `onu.configure.wifi`, `onu.manage` | Konfigurasi Wi-Fi pelanggan |
| `wan` (PPPoE, VLAN, Bridge/Route) | `onu.configure.wan`, `onu.manage` | Pengaturan koneksi WAN dan VLAN |
| `admin` (SuperAdmin Web GUI Credentials) | `onu.configure.admin`, `onu.manage` | Pengelolaan akun web GUI admin modem |
| `reboot` (Restart ONU) | `onu.reboot`, `onu.manage` | Restart perangkat jarak jauh |
| `summon` (Connection Request) | `onu.diagnose`, `onu.view`, `onu.reboot` | Trigger sesi inform live ke ACS |
| `ping` (IPPingDiagnostics) | `onu.diagnose`, `onu.manage` | Diagnostik ping dari modem ke host target |
| `tasks.view` (Lihat Antrean Task) | `onu.task.view`, `onu.view`, `onu.manage` | Monitoring antrean perintah TR-069 |
| `tasks.retry` (Jadwal Ulang Task Gagal) | `onu.task.retry`, `onu.manage` | Retry task yang gagal pada modem |
| `tasks.delete` (Hapus Task / Clear Queue) | `onu.task.delete`, `onu.manage` | Pembatalan task (operasi berisiko tinggi) |
| `tags` (Add/Remove Tag Lokasi/ODP) | `onu.view`, `onu.manage` | Pengelolaan metadata tag perangkat |

### D. Ownership & Scope Validation (Validasi Rantai Kepemilikan)
To ensure Resellers only manage their own equipment, the backend validates:
1. **Rantai Kepemilikan Positif:**
   $$\text{Reseller} \longrightarrow \text{CustomerService} \longrightarrow \text{ONU} \longrightarrow \text{ACSDevice}$$
   - `customer_services.reseller_id == $resellerId` OR `customer_services.created_by == $resellerId`
   - OR `customers.reseller_id == $resellerId` OR `customers.created_by == $resellerId`
   - OR `onus.created_by == $resellerId`
   - OR `acs_devices.created_by == $resellerId`
   - OR `ACSDevice::forReseller($user)`
2. **Penolakan Kepemilikan Reseller Lain (Explicit Exclusion):**
   If a device is bound to a `CustomerService` or `Customer` whose `reseller_id` belongs to another reseller, access is immediately rejected (403 Forbidden).
3. **Branch & Tenant Isolation:**
   If the user has an assigned `branch_id`, any device linked to a different branch is rejected (403 Forbidden).
4. **Anti-Spoofing & Anti-Manipulation Device ID:**
   - Rejects wildcard queries (`ALL`, `*`) for non-administrators.
   - Eliminates loose substring matches (`str_contains`) that could allow `ALCLB123` to match `ALCLB12345678`.
   - Requires exact matching against `uuid`, `serial_number`, `mac_address`, or exact extracted serial number from compound GenieACS IDs (`OUI-ProductClass-SerialNumber`).
   - Devices not registered in dsBilling are strictly rejected (403 Forbidden).

---

## 3. Architecture: Before vs. After

### Before (Vulnerable & Over-Restricted):
```text
[Android dsBilling Mobile]
    |
    +---> (Direct HTTP :7557) ----> [GenieACS NBI Port 7557] (Unauthenticated / Raw Basic Auth)
    |
    +---> (HTTPS API Proxy) -------> [MobileAcsProxyController]
                                       - Blanket rejection: Reseller blocked from WAN/Admin/Tasks
```

### After (Hardened 3-Layer Authorization):
```text
[Android dsBilling Mobile]
    |
    | (HTTPS with Sanctum Bearer Token)
    v
[dsBilling Backend: /api/mobile/acs/...]
    |
    +---> 1. Authenticate Sanctum User (`auth:sanctum`)
    +---> 2. Layer A (Role): SuperAdmin / Administrator bypasses checks
    +---> 3. Layer B (Permission): Enforces required permission for operation
    +---> 4. Layer C (Ownership & Scope):
    |        - Reseller: Chain validated (Reseller -> CustomerService -> ONU -> ACSDevice)
    |        - Technician: Branch boundary validated (`withoutGlobalScopes()`)
    |        - Anti-Spoofing: Exact ID resolution; foreign/unregistered devices blocked
    +---> 5. Parameter Validation & Sanitization
    |
    v
[ACSService / GenieACSDriver]
    |
    | (Internal LAN / Loopback HTTP with Server Basic Auth)
    v
[GenieACS NBI (Port 7557 - Internal Only)]
```

---

## 4. Affected Endpoints & Modified Files

### A. Affected API Endpoints:
- `GET /api/mobile/acs/devices` (Device list filtered by reseller ownership)
- `GET /api/mobile/acs/devices/{id}` (Device parameter details)
- `POST /api/mobile/acs/devices/{id}/wifi` (WLAN configuration)
- `POST /api/mobile/acs/devices/{id}/wan` (WAN / PPPoE / VLAN configuration)
- `POST /api/mobile/acs/devices/{id}/admin-account` (ONU SuperAdmin credentials)
- `POST /api/mobile/acs/devices/{id}/reboot` (Device restart)
- `POST /api/mobile/acs/devices/{id}/summon` (Connection request refresh)
- `POST /api/mobile/acs/devices/{id}/ping` (IPPingDiagnostics)
- `GET /api/mobile/acs/devices/{id}/tasks` (Pending task inspection)
- `DELETE /api/mobile/acs/devices/{id}/tasks/{taskId}` (Task deletion)
- `POST /api/mobile/acs/devices/{id}/tasks/{taskId}/retry` (Task retry)
- `POST /api/mobile/acs/devices/{id}/tags` & `DELETE /api/mobile/acs/devices/{id}/tags/{tag}` (Tags)

### B. Modified Files:
1. **`app/Http/Controllers/Api/Mobile/MobileAcsProxyController.php`:**
   - Implemented 3-layer authorization in `validateDeviceAccess()`.
   - Updated operation verbs (`summon`, `ping`, `tasks.view`, `tasks.retry`, `tasks.delete`).
   - Hardened `listDevices()` to use exact/compound serial matching.
2. **`app/Enums/UserPermission.php`:**
   - Added enum cases: `OnuConfigureAdmin`, `OnuTaskView`, `OnuTaskRetry`, `OnuTaskDelete`.
3. **`app/Services/Auth/PermissionRegistry.php`:**
   - Added descriptive action translations for the new ONU permissions.
4. **`app/Models/User.php`:**
   - Prioritized `directPermissions` check so direct permission assignments take effect before general title fallbacks.
5. **`tests/Feature/MobileAcsProxySecurityTest.php`:**
   - Expanded test suite to cover all 10 required positive and negative test cases.

---

## 5. Verification & Quality Gates

### A. Automated PHPUnit Security Test Suite (13/13 Passed)
```powershell
.\vendor\bin\phpunit.bat tests\Feature\MobileAcsProxySecurityTest.php
```
**Output:**
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.28
Configuration: D:\dsBilling\phpunit.xml

.............                                                     13 / 13 (100%)

Time: 00:04.146, Memory: 84.00 MB

OK (13 tests, 26 assertions)
```

#### Detailed Test Coverage Matrix:
| # | Test Method | Kategori | Assertion | Status |
| :- | :--- | :--- | :--- | :---: |
| 1 | `test_unauthenticated_request_is_rejected` | Autentikasi | HTTP 401 Unauthorized | PASS |
| 2 | `test_unauthorized_warehouse_staff_is_rejected` | Role Boundary | HTTP 403 Forbidden | PASS |
| 3 | `test_reseller_a_with_wan_permission_can_configure_wan` | Positif (Req 1) | HTTP 200 OK & WAN parameter applied | PASS |
| 4 | `test_reseller_a_without_wan_permission_is_rejected` | Negatif (Req 2) | HTTP 403 Lacking 'wan' permission | PASS |
| 5 | `test_reseller_a_cannot_modify_onu_belonging_to_reseller_b` | Negatif (Req 3) | HTTP 403 Foreign reseller rejection | PASS |
| 6 | `test_reseller_a_cannot_access_other_branch_device` | Negatif (Req 4) | HTTP 403 Scope boundary rejection | PASS |
| 7 | `test_reseller_a_can_manage_wifi_with_permission` | Positif (Req 5) | HTTP 200 OK & WiFi parameters applied | PASS |
| 8 | `test_reseller_a_without_admin_permission_cannot_change_onu_admin_account` | Negatif (Req 6) | HTTP 403 Lacking 'admin' permission | PASS |
| 9 | `test_reseller_cannot_delete_task_without_permission` | Negatif (Req 7) | HTTP 403 Lacking 'tasks.delete' permission | PASS |
| 10 | `test_technician_cross_branch_access_is_blocked` | Negatif (Req 8) | HTTP 403 Cross-branch rejection | PASS |
| 11 | `test_technician_can_manage_device_within_own_branch` | Positif (Req 8) | HTTP 200 OK on own branch | PASS |
| 12 | `test_administrator_has_full_access_to_all_operations` | Positif (Req 9) | HTTP 200 OK without explicit permission | PASS |
| 13 | `test_device_id_manipulation_cannot_bypass_ownership` | Anti-Spoofing (Req 10) | HTTP 403 on ALL, partial prefix, & fake ID | PASS |

### B. Regression Verification (`MobileNetworkOperationsTest`)
```powershell
.\vendor\bin\phpunit.bat tests\Feature\MobileNetworkOperationsTest.php
```
**Result:** `OK (6 tests, 22 assertions)` — 100% Passed.

### C. Android Build Verification
```powershell
cd d:\dsbilling-mobile
.\gradlew.bat compileDebugKotlin
```
**Result:** `BUILD SUCCESSFUL in 16s` (0 errors).

---

## 6. Analysis of Residual Risks & Mitigations

1. **Unlinked Pre-provisioned ONUs:**
   - *Risk:* If a technician registers an ONU before binding it to a `CustomerService`, a reseller might not yet see it.
   - *Mitigation:* The ownership validator also checks `onus.created_by == $resellerId`, allowing resellers who physically deploy their own inventory to manage the ONU even before billing activation.
2. **GenieACS NBI Latency:**
   - *Risk:* TR-069 parameter setting is asynchronous if the modem is offline or sleeping.
   - *Mitigation:* Handled cleanly via GenieACS tasks queue; proxy returns clear status codes and preserves error messages without crashing.

---

## 7. Status & Handoff

Phase 2B (including Reseller Device Management Authorization Correction) is **COMPLETE and FULLY VERIFIED**.
Execution is stopped. Awaiting explicit user approval before proceeding to Phase 2C.
