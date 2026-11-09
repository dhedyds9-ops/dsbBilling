# Rule 03: RBAC & Mobile Security Guardrails

## 1. Role-Based Access Control Principles
- **ROLE ≠ PERMISSION:**
  - `UserRole` (`app/Enums/UserRole.php`) defines *who* the user is and determines default portal routing.
  - `UserPermission` (`app/Enums/UserPermission.php`) defines *what* specific actions the user is authorized to perform.
- **No Ad-Hoc Roles:** Do not create new roles for branches, job titles, or temporary features. Use existing permissions or user metadata (`job_title`, `job_function`).
- **SuperAdmin Override:** Only accounts verified via `isSuperAdmin()` may bypass global tenant and branch scopes.
- **Supervisor Role Merging:** If a Manager user has `job_function: TECHNICIAN`, the backend seamlessly provisions both field technician capabilities and supervisor approval permissions (`workforce.approve`, `inventory.manage`).

## 2. API Security & Sanctum Token Handling
- All mobile endpoints (`/api/mobile/*`) require Laravel Sanctum token authentication (`auth:sanctum`), with the sole exception of the login and version check endpoints.
- Unauthenticated or unauthorized API responses must return uniform JSON errors (`401 Unauthorized` or `403 Forbidden`).
- Route authorization must use registered middleware (`role`, `job_function`, `workforce.checked_in`) or native Laravel `can:` gates. Never declare an unregistered `permission:...` middleware alias.
- Backend FormRequests and controllers must explicitly assert `$user->hasPermission(...)` and validate ownership/tenant boundaries to prevent Insecure Direct Object References (IDOR).

## 3. Android Mobile Cryptography & Transport Security
- **Android Keystore AES-256 GCM:**
  - Sensitive authentication tokens and user credentials must never be stored in plaintext XML or SharedPreferences.
  - All token persistence on Android must utilize `KeyStoreManager.kt` hardware-backed encryption.
- **Strict HTTPS / No Cleartext:**
  - Cleartext HTTP traffic is strictly prohibited in production (`android:usesCleartextTraffic="false"`).
  - Production builds must communicate over HTTPS with valid SSL/TLS certificates.
- **Zero Client-Side API Keys:**
  - Third-party API keys (e.g., Gemini AI, Telegram bots, SMS gateways) must **never** be compiled into Android APK/AAB bytecode.
  - All AI voice and external services must be proxied via backend endpoints (`POST /api/mobile/ai/ask`).
