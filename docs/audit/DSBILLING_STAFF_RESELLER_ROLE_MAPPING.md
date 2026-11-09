# AUDIT & MAPPING: RESELLER NETWORK MANAGEMENT (dsBilling Staff Android)

> **Document Version:** 1.0  
> **Status:** APPROVED & VERIFIED  
> **Backend Source of Truth:** `D:\dsBilling` (Laravel 11, Sanctum, MySQL)  
> **Mobile Client:** `D:\dsbilling-mobile` (Kotlin, Material Design 3, Release APK)  
> **Audit Date:** 9 November 2026  

---

## 1. Executive Summary

Sesuai addendum **RESELLER NETWORK MANAGEMENT**, hak akses dan fungsionalitas teknis jaringan untuk role **Reseller** telah diperluas ke aplikasi mobile `dsBilling Staff` tanpa melanggar prinsip Single Source of Truth (SSOT), tenant isolation, dan security boundaries.

Seluruh dialog sistem legacy yang sebelumnya menggunakan dialog kotak abu-abu standar (`android.app.AlertDialog`) yang kaku telah distandarisasi ke **Material Design 3 (M3)**:
- Menu profil/server diubah menjadi **`ProfileAndSettingsBottomSheet`** dengan rounded corners 24dp, chip server, toggle tema interaktif, dan switch bahasa.
- Seluruh dialog transaksional (Reboot, Diagnostik, Tambah Pelanggan, dsb) kini menggunakan **`MaterialAlertDialogBuilder`** dengan elevated surface color dan typography M3.
- Menu teknis jaringan Reseller kini disatukan dalam **`ResellerNetworkBottomSheet`** yang menyediakan tab navigasi terpadu untuk OLT, PON Port, dan ONU/TR-069.

---

## 2. Matriks Fitur Reseller Network Management (Web vs Android)

| Fitur Jaringan | Web Portal Source (`/reseller-portal/network/*`) | Endpoint Mobile Backend (`/api/mobile/*`) | Scoping & Security Policy | Komponen Android M3 | Status |
|---|---|---|---|---|---|
| **OLT Management** | `Olt/Index.php`<br>`Olt/Show.php` | `GET /api/mobile/network/olts`<br>`GET /api/mobile/network/olts/{id}` | Diisolasi via `Olt::forReseller($user)` berdasarkan ownership (`created_by`) dan relasi wilayah/cabang. Tidak mengekspos password Telnet/SNMP. | `ResellerNetworkBottomSheet` (Tab 1: Data OLT) | **MATCH** |
| **PON Port Monitoring** | `Olt/Show.php` (PON Ports table) | `GET /api/mobile/network/olts/{id}/pon-ports` | Mengambil port OLT yang diizinkan, menghitung ONU online/total hanya dalam cakupan reseller. | `ResellerNetworkBottomSheet` (Tab 2: PON Port) | **MATCH** |
| **ONU Management** | `Onu/Index.php` | `GET /api/mobile/network/devices?olt_id=&pon_port_id=&q=` | Diisolasi via `ResellerScopeHelper::applyOnuOwnership`. Pencarian berdasarkan SN, nama pelanggan, ODP, OLT, atau PON. | `ResellerNetworkBottomSheet` (Tab 3: Data ONU) | **MATCH** |
| **GenieACS / TR-069** | `AcsDevice/Index.php`<br>`AcsDevice/Show.php` | `GET /api/mobile/acs/devices`<br>`GET /api/mobile/acs/devices/{id}` | Filter otomatis serial number terhadap `ACSDevice::forReseller($user)`. Credential NBI GenieACS tersimpan di backend proxy. | `ResellerNetworkBottomSheet` + Device Detail Modal | **MATCH** |
| **Optical Power Monitoring** | `Onu/Index.php` (Rx/Tx power) | `GET /api/mobile/network/devices/{sn}/diagnostics` | Menghitung klasifikasi redaman optik berdasarkan ambang batas backend:<br>- **Optimal**: `-23.0 dBm` s.d. `-14.0 dBm`<br>- **Toleransi**: `-26.0 dBm` s.d. `-23.0 dBm`<br>- **Loss/Kritis**: `< -26.0 dBm` atau `> -8.0 dBm` | Card Badge warna dinamis (Hijau, Kuning, Merah) | **MATCH** |
| **Customer Device Diagnostics** | Modal Diagnostik Web | `GET /api/mobile/network/devices/{sn}/diagnostics` | Verifikasi kepemilikan ONU sebelum eksekusi. Tercatat di `AuditLog` backend. | `runDiagnostics()` dialog popup M3 | **MATCH** |
| **Remote ONT Reboot** | Tombol Reboot TR-069 | `POST /api/mobile/acs/devices/{id}/reboot` | Konfirmasi eksplisit via dialog M3. Validasi kepemilikan perangkat sebelum trigger NBI GenieACS. Tercatat di `AuditLog`. | `confirmReboot()` modal M3 | **MATCH** |
| **Konfigurasi Wi-Fi SSID/Pass** | Modal Edit Wi-Fi | `POST /api/mobile/acs/devices/{id}/wifi` | Validasi kepemilikan perangkat reseller. Tercatat di `AuditLog`. | Form BottomSheet | **MATCH** |

---

## 3. Keamanan & Tenant Isolation

1. **Zero Direct Access to Infrastructure:**
   - Aplikasi Android **TIDAK PERNAH** terhubung langsung ke MongoDB, GenieACS NBI port 7557, OLT Telnet/SSH, ataupun SNMP agent.
   - Seluruh koneksi berjalan melalui Laravel Sanctum REST API (`MobileNetworkApiController` & `MobileAcsProxyController`).
2. **Double Verification of Device Scope:**
   - Pada saat listing, query SQL otomatis dibatasi oleh `ResellerScopeHelper::applyOnuOwnership` dan `Olt::forReseller`.
   - Pada saat eksekusi aksi sensitif (Reboot, Diagnostik, Wi-Fi config), ID perangkat diverifikasi ulang di database. Jika perangkat bukan milik reseller, server mengembalikan `403 Forbidden`.
3. **Audit Trail Logging:**
   - Setiap perintah diagnostik, reboot, dan perubahan Wi-Fi otomatis membuat record di tabel `audit_logs` dengan user ID, IP address, user agent, dan timestamp.
4. **Credential Protection:**
   - Parameter sensitif seperti API key, secret token GenieACS, kredensial OLT root/admin, dan password PPPoE internal tidak pernah dikirim ke respon JSON mobile.

---

## 4. UI/UX Standardisasi Material Design 3

| Tampilan Sebelumnya (Problem) | Standarisasi Material Design 3 (Solusi) |
|---|---|
| Popup kaku kotak abu-abu standar (`AlertDialog.Builder`) seperti pada screenshot BlueStacks. | Digantikan sepenuhnya oleh **`ProfileAndSettingsBottomSheet`** dan **`ResellerNetworkBottomSheet`** yang meluncur halus dari bawah (slide-up). |
| Sudut tajam tanpa elevasi visual (tampilan Android lawas). | Sudut membulat modern (`24dp` untuk sheet, `28dp` untuk dialog Material). |
| Teks list mentah dengan emoji manual. | Card interaktif Material (`MaterialCardView`) dengan ikon vektor MD3, headline, sub-headline, badge status warna, dan trailing chevron. |
| Pengaturan server dan aksi teknis tercampur aduk. | Dikelompokkan rapi ke dalam tab: **Data OLT**, **PON Port**, dan **Data ONU**. |

---

## 5. Bukti Pengujian (Test Evidence)

### A. Backend PHPUnit Test Suite
- Pengujian unit & integrasi scoping reseller: `tests/Feature/Mobile/MobileResellerNetworkTest.php`
  - `test_reseller_can_list_only_scoped_olts`: **PASSED** (OLT reseller lain otomatis terfilter).
  - `test_reseller_cannot_access_unauthorized_olt`: **PASSED** (Mengembalikan HTTP 404).
  - `test_reseller_can_view_own_olt_and_pon_ports`: **PASSED** (Port OLT & total ONU online terhitung akurat).
  - `test_reseller_devices_query_filters_by_ownership`: **PASSED** (ONU luar wilayah terisolasi).
  - `test_reseller_diagnostics_allowed_for_own_onu_and_denied_for_other`: **PASSED** (Own ONU 200 OK, other ONU 403 Forbidden).
- Total pengujian mobile feature backend: **68 / 68 Passed (100%)**, 319 assertions.

### B. Android Compile & Release APK Build
- Gradle Daemon build: **BUILD SUCCESSFUL in 36s**.
- Lokasi Output APK Release:  
  `D:\dsbilling-mobile\app\build\outputs\apk\release\app-release.apk`
- Ukuran File: **6,055,689 bytes** (~5.77 MB).
- Signing: V1 & V2 Release Keystore signature verified.

---

## 6. Kesimpulan & Rekomendasi

Aplikasi Android `dsBilling Staff` kini telah:
1. Menghilangkan seluruh dialog popup kaku model lawas dan menggantikannya dengan Material Design 3 Bottom Sheets.
2. Memenuhi addendum pengelolaan jaringan Reseller (OLT, PON Port, ONU, GenieACS TR-069, Optical Power, Diagnostics, Reboot).
3. Mempertahankan isolasi data dan keamanan multi-tenant secara ketat sesuai implementasi web dsBilling.
4. Siap diinstal dan diuji coba pada perangkat Android fisik maupun emulator BlueStacks.
