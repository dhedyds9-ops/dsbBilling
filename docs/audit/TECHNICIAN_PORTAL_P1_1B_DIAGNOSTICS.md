# DOKUMENTASI IMPLEMENTASI P1-1B: LIVE ONT DIAGNOSTICS
## dsBilling Enterprise × GenieACS NBI REST API

**Tanggal**: 06 November 2026  
**Status**: 100% SELESAI & TERVERIFIKASI (16/16 Test Cases Passed)  
**Portal**: Technician Portal (`/technician-portal/my-jobs/maintenance`)  

---

## 1. Ringkasan Eksekutif

Implementasi **P1-1B (Live ONT Diagnostics – dsBilling Enterprise × GenieACS)** telah selesai dikerjakan secara komprehensif. Teknisi lapangan kini dapat melakukan pemantauan kondisi fisik dan sinyal optik ONT pelanggan secara live, memeriksa status koneksi, melihat perangkat klien Wi-Fi yang sedang aktif, me-reboot ONT, me-refresh parameter via NBI task (Summon), dan mengubah SSID & Password Wi-Fi secara aman langsung dari **Portal Teknisi** tanpa perlu membuka dashboard GenieACS.

Seluruh komunikasi backend mematuhi arsitektur wajib:
```
Technician Portal
  └── Maintenance Component (Livewire 4)
        └── GenieAcsDiagnosticService
              └── GenieACSDriver (Adapter)
                    └── GenieACS NBI REST API (HTTP Basic Auth)
                          └── ONT (TR-069 CWMP Protocol)
```
**Catatan Keamanan Penting**: Tidak ada akses langsung ke database MongoDB GenieACS. Password Wi-Fi disanitasi dari log aplikasi dan tidak pernah dikirimkan dalam payload pembacaan ke browser teknisi.

---

## 2. Audit Sebelum Implementasi & Temuan

1. **`GenieACSDriver.php`**:
   - Telah menyediakan fungsi dasar `getDeviceParameters`, `getDeviceSignal`, `getConnectedDevices`, `summonDevice`, `rebootDevice`, dan `updateWifiSsidAndPassword`.
   - Menggunakan kalkulasi status `calculateStatus` berbasis `abs(diffInSeconds)` dan toleransi `PeriodicInformInterval`.
2. **`MyJobs/Maintenance.php`**:
   - Komponen sebelumnya hanya menangani daftar tiket maintenance pelanggan dan jadwal maintenance preventif infrastruktur.
   - Belum memiliki integrasi diagnostik live ke perangkat ONT pelanggan yang bersangkutan.
3. **Multi-Tenant & Cabang**:
   - Validasi `CompanyScope` dan `BranchScope` diterapkan secara ketat. Teknisi dari cabang atau tenant lain langsung diblokir (`AuthorizationException` / HTTP 403) jika mencoba membaca atau mengeksekusi tindakan pada ONT di luar wewenangnya.
4. **Caching & Rate Limiting**:
   - Mencegah polling berlebih ke NBI server dengan caching hasil diagnostik selama 30 detik (`ont_live_diagnostics_{id}`).
   - Menerapkan cooldown kunci rate limiting: 15 detik untuk refresh parameter, 30 detik untuk reboot ONT, dan 20 detik untuk pembaruan Wi-Fi.

---

## 3. Daftar File yang Dibuat dan Diubah

| Status | File | Deskripsi |
| :--- | :--- | :--- |
| **Dibuat** | `app/Services/ISP/GenieAcsDiagnosticService.php` | Service utama pengelola diagnostik ONT, isolasi tenant/cabang, rate-limiting, audit log, dan eksekusi field action. |
| **Diubah** | `app/Livewire/Isp/Technician/MyJobs/Maintenance.php` | Penambahan handler livewire untuk modal diagnostik, pemanggilan service, state reboot, dan ubah Wi-Fi. |
| **Diubah** | `resources/views/livewire/isp/technician/my-jobs/maintenance.blade.php` | Antarmuka mobile-first 7 seksi diagnostik (Identitas, Status, Optik, Hardware, Wi-Fi, Klien, Action Panel) dan modal interaktif. |
| **Dibuat** | `scratch/test_genieacs_p1_1b.php` | Automated test suite 16 skenario pengujian komprehensif mock NBI. |
| **Dibuat** | `docs/audit/TECHNICIAN_PORTAL_P1_1B_DIAGNOSTICS.md` | Laporan audit dan dokumentasi teknis implementasi P1-1B. |

---

## 4. Arsitektur Service Diagnostik

Service `App\Services\ISP\GenieAcsDiagnosticService` memisahkan tanggung jawab secara jelas:
1. **Otorisasi & Keamanan**:
   - Memeriksa `$user->branch_id === $onu->branch_id` dan `$userTenant === $onuTenant`.
   - Memastikan role administrator/superadmin memiliki hak oversight, sedangkan teknisi dibatasi ketat pada cabangnya.
2. **Pembacaan Parameter (Read Engine)**:
   - Resolusi GenieACS Device ID melalui `GenieAcsService::resolveGenieAcsDeviceId` (ACSDevice link, serial number, atau MAC address).
   - Penarikan parameter via `$driver->getDeviceParameters($deviceId)`.
   - Normalisasi unit optik: mengonversi micro-dBm (faktor 1000) dan desimal dBm ke float baku 2 desimal.
   - Pengecekan status optik:
     - Normal / Optimal: `-15.0 dBm` s/d `-24.0 dBm` (Badge Hijau)
     - Marginal / Warning: `-24.0 dBm` s/d `-27.0 dBm` (Badge Kuning)
     - Kritis: `< -27.0 dBm` atau `> -10.0 dBm` (Badge Merah)
     - Tidak Didukung: Sensor tidak merespons atau bernilai 0 (Badge Abu-abu "Tidak Didukung", **tidak pernah menampilkan 0 atau nilai dummy**).
3. **Pengiriman Task (Dispatch Engine)**:
   - **Summon / Refresh**: Mengirimkan refresh task untuk `WANDevice` dan `Optical` via REST API dengan `connection_request`.
   - **Reboot**: Mengambil baseline inform & uptime, mengirimkan task `reboot`, dan mengaktifkan cooldown 30 detik.
   - **Update Wi-Fi**: Validasi panjang SSID (1-32 karakter) dan password (minimal 8 karakter standar WPA2-PSK), mapping parameter vendor, dan eksekusi task via NBI.
4. **Verifikasi Hasil (Verification Engine)**:
   - `verifyReboot`: Memeriksa apakah uptime ter-reset (< baseline atau < 180 detik) atau last inform baru telah diterima dari ONT.
   - Menghindari anggapan bahwa HTTP 200 saat pengiriman task berarti reboot sudah berhasil selesai secara fisik di ONT.

---

## 5. Parameter Mapping Multi-Vendor TR-069

| Vendor / Model | Parameter RX Optical Power | Parameter TX Optical Power | Parameter Suhu Optik / ONT | Parameter Wi-Fi SSID | Parameter Wi-Fi Passphrase |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **ZTE** (F609, F670L, dll.) | `InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower` | `...X_ZTE-COM_TxPower` | `InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature` | `InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID` | `...WLANConfiguration.1.PreSharedKey.1.PreSharedKey` |
| **Huawei** (HG8245, EG8145) | `InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower` | `...X_HW_TxPower` | `InternetGatewayDevice.DeviceInfo.X_HW_Temperature` | `InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID` | `...WLANConfiguration.1.PreSharedKey.1.PreSharedKey` |
| **FiberHome** (AN5506) | `InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_RxPower` | `...X_FH_TxPower` | `InternetGatewayDevice.DeviceInfo.X_FH_Temperature` | `InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID` | `...WLANConfiguration.1.PreSharedKey.1.PreSharedKey` |
| **Generic / TR-181** (VSOL, C-Data) | `Device.Optical.1.Transceiver.RxPower` | `Device.Optical.1.Transceiver.TxPower` | `Device.Optical.1.Transceiver.Temperature` | `Device.WiFi.SSID.1.SSID` | `Device.WiFi.AccessPoint.1.Security.KeyPassphrase` |

---

## 6. Endpoint GenieACS NBI REST yang Digunakan

1. `GET /devices/?query={query}&projection={projection}`:
   - Mengambil data inventaris dan parameter TR-069 terkini perangkat berdasarkan `_id`, `_deviceId._SerialNumber`, atau `_deviceId._MACAddress`.
2. `POST /devices/{deviceId}/tasks?connection_request`:
   - Mengirimkan task asynchronous (`refreshObject`, `setParameterValues`, `reboot`) langsung ke ONT melalui pemicu Connection Request UDP/HTTP.
3. `GET /devices/{deviceId}/tasks`:
   - Memeriksa antrian task perangkat dan memverifikasi penyelesaian task.

---

## 7. Hasil Pengujian Otomatis (Automated Test Suite)

Pengujian dijalankan melalui script `scratch/test_genieacs_p1_1b.php`:

```
=================================================================
  P1-1B AUTOMATED TEST SUITE: LIVE ONT DIAGNOSTICS × GENIEACS    
=================================================================

 [PASS] 1. ONT Online
        -> Status: online, Badge: green
 [PASS] 2. ONT Offline
        -> Status: offline, Badge: red
 [PASS] 3. Last Inform Tidak Tersedia
        -> Last inform: Tidak Pernah, Online: false
 [PASS] 4. RX Power Tersedia
        -> RX: -19.45 dBm, Status: normal
 [PASS] 5. RX Power Tidak Didukung
        -> Display: 'Tidak Didukung' (Bukan 0/dummy)
 [PASS] 6. Wi-Fi Client Tersedia
        -> Jumlah client: 2, Client 1 MAC: AA:BB:CC:DD:EE:FF
 [PASS] 7. Refresh Parameters Berhasil
        -> Status: success, Refreshed At: 2026-11-06T00:04:05+07:00
 [PASS] 8. Reboot Berhasil Diverifikasi
        -> Dispatch: task_dispatched, Verify: Reboot ONT berhasil diverifikasi (Uptime ter-reset dari 99999s ke 45s.)
 [PASS] 9. Perubahan Wi-Fi Berhasil Diverifikasi
        -> Status: success, DB SSID: DS-OFFICE-WIFI, Password excluded: YES
 [PASS] 10. GenieACS NBI Error Handling
        -> Mendeteksi dan melempar exception NBI REST API secara tepat tanpa silent crash
 [PASS] 11. ONT Tidak Ditemukan (404/Empty)
        -> Status: Perangkat Tidak Ditemukan di GenieACS (404), Data state: unavailable
 [PASS] 12. Tenant Isolation
        -> Teknisi Company 2 ditolak mengakses ONT Company 1 via AuthorizationException
 [PASS] 13. Branch Isolation
        -> Teknisi Cabang 2 ditolak mengakses ONT Cabang 1 via AuthorizationException
 [PASS] 14. Unauthorized Action
        -> Aksi field reboot ditolak jika pengguna belum terautentikasi (403)
 [PASS] 15. Rate Limiting
        -> Reboot Cooldown: throttled, Refresh Cooldown: throttled
 [PASS] 16. Credential Tidak Bocor ke Log
        -> Password bocor di log: TIDAK (AMAN), Masking [REDACTED] tercatat: YA

=================================================================
  TEST SUMMARY: 16 PASSED, 0 FAILED (100% SUCCESS RATE)
=================================================================
```

Pengujian regresi P1-1A (`scratch/test_genieacs_p1_1a.php`) juga dijalankan ulang dan menghasilkan **10/10 PASS (100%)**, membuktikan fitur sebelumnya tetap utuh tanpa degradasi.

---

## 8. Panduan Pengujian ONT Fisik di Lapangan

Untuk teknisi yang melakukan pengujian langsung dengan perangkat ONT riil:
1. **Pemeriksaan Koneksi Optik**:
   - Pastikan kabel Drop Core tercolok ke port PON ONT.
   - Buka `/technician-portal/my-jobs/maintenance`.
   - Klik tombol **Diagnostik ONT** pada kartu tiket terkait.
   - Amati RX Optical Power. Pastikan nilai redaman sesuai dengan hasil ukur OPM (Optical Power Meter), contoh `-18.50 dBm` (Indikator Hijau).
2. **Pemeriksaan Klien Terhubung**:
   - Sambungkan smartphone ke Wi-Fi ONT.
   - Klik **Refresh Data (Summon)**.
   - Pastikan nama perangkat, IP, dan MAC smartphone muncul pada tabel Klien Terhubung.
3. **Uji Reboot**:
   - Klik tombol **Reboot ONT**, konfirmasi pada modal.
   - Amati lampu indikator PON/Power pada ONT padam dan menyala kembali.
   - Klik **Cek Status Reboot** pada modal untuk melihat konfirmasi reset uptime.
4. **Uji Ubah Sandi Wi-Fi**:
   - Klik **Ubah Wi-Fi ONT**, masukkan SSID dan kata sandi baru.
   - Klik **Simpan Wi-Fi**.
   - Hubungkan kembali smartphone dengan kata sandi baru tersebut.

---

## 9. Daftar Parameter yang Belum Didukung Secara Universal

Beberapa pabrikan/firmware ONT murah atau custom OEM memiliki batasan berikut pada TR-069:
1. **RSSI Sinyal Klien Wi-Fi**: Sebagian ONT lama tidak melaporkan tabel `AssociatedDevice.SignalStrength` ke data model TR-069 (ditampilkan sebagai "Tidak Didukung").
2. **Band Steering / SSID 5 GHz**: Pada model single-band (2.4 GHz saja seperti ZTE F609v1/v2), parameter SSID 5 GHz otomatis ditampilkan sebagai "Tidak Didukung".
3. **LOS Alarm Timestamp**: Sebagian vendor hanya melaporkan boolean flag LOS tanpa timestamp kejadian awal putus serat optik.

---

## 10. Kesimpulan & Status Deliverable

Implementasi P1-1B telah berhasil diselesaikan secara sempurna:
- Seluruh 16 skenario pengujian diverifikasi 100% PASS.
- Sesuai instruksi batasan: Tidak menyentuh Customer Portal, tidak menyentuh NOC Portal, tidak memulai P1-1C (Webhooks) atau P1-2 (Workflow Tiket), dan seluruh fitur P0 Hardening & P1-1A tetap terjaga.
