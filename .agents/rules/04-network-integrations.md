# Rule 04: Network & Hardware Integration Standards

## 1. MikroTik RouterOS Architecture
- **Driver Integration:** Use `app/Integration/MikroTik` wrappers backed by `RouterOS API` and SSH.
- **Connection Isolation:** Never block web requests with long-running synchronous RouterOS commands. Use asynchronous Laravel Queues (`jobs`) for batch provisioning and synchronization.
- **Error Handling:** Gracefully handle router connection timeouts or network unreachability without crashing the user interface or returning raw socket stack traces.

## 2. GenieACS TR-069 Protocol & Server Security
- **Strict Port Segregation:**
  - Port `3000`: Web UI (Human administration only).
  - Port `7547`: CWMP protocol (Modem informs and CPE connection requests).
  - Port `7557`: NBI API (Northbound Interface) — **strictly restricted to localhost / internal server network**.
- **Backend Proxy Mandatory:**
  - The Android app or public clients must **never** make direct HTTP calls to Port 7557.
  - All CPE interactions (reboot, Wi-Fi SSID change, WAN provisioning, optical power query) must pass through `MobileAcsProxyController` or backend services.
  - Backend proxy logs each operation with timestamp, authenticated user ID, and IP in `audit_logs`.

## 3. FreeRADIUS AAA & Hotspot
- FreeRADIUS operates via REST API integration for authentication and accounting.
- Disconnections and bandwidth changes are dispatched using CoA (Change of Authorization) or MikroTik API `/ip/hotspot/active/remove` and `/ppp/active/remove`.
- Ensure radius accounting sync respects tenant database context.

## 4. OLT, PON & Optical Distribution Standards
- Support multi-vendor OLTs (ZTE C300/C320, Huawei MA5608T, Fiberhome, VSOL, HSGQ) via standardized driver abstractions.
- Optical signal thresholds:
  - Optimal: -18.0 dBm to -23.0 dBm.
  - Warning: -23.1 dBm to -26.0 dBm.
  - Critical: Worse than -26.0 dBm.
- Barcode scanning on mobile must normalize ONT serial numbers (ZTEG, HWTC, FHTT, VSOL) before querying backend inventory.
