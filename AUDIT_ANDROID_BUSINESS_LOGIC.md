# AUDIT ANDROID BUSINESS LOGIC & CLIENT IMPLEMENTATION
**Aplikasi Android dsBilling Staff (`D:\dsbilling-mobile`)**  
**Dokumen Audit Teknis Arsitektur Klien Mobile**  
**Tanggal Audit:** 11 November 2026  
**Status:** READ-ONLY COMPREHENSIVE CLIENT AUDIT  

---

## 1. PENDAHULUAN & ARSITEKTUR KLIEN ANDROID

Aplikasi Android dsBilling Staff (`com.genieacs.client`) merupakan aplikasi native Kotlin yang ditujukan untuk staf lapangan (teknisi, staf gudang, sales, dan mitra reseller).  
Aplikasi ini berinteraksi dengan Laravel Backend melalui `DsBillingApiService` dan `GenieAcsNbiService` (via backend proxy `/api/mobile/*`).

### Karakteristik Arsitektur Aktual:
- **Pola Navigasi:** Hybrid Activity-Fragment dengan transisi ke **Native Full-Screen Navigation** untuk modul operasional utama (sesuai standar UI/UX).
- **Keamanan Kredensial:** Menggunakan `KeyStoreManager` (AES-256 GCM) untuk enkripsi Sanctum Bearer Token di `AppPreferences`. Tidak menyimpan password dalam teks polos.
- **Komunikasi Jaringan:** Seluruh permintaan ke perangkat keras jaringan (MikroTik, OLT, GenieACS TR-069) dialihkan melalui Laravel Backend Proxy endpoints.

---

## 2. AUDIT LOGIKA BISNIS & VALIDASI DI ANDROID

### 2.1. Pemeriksaan Role di Klien Android
Pada `MainActivity.kt`, Android melakukan percabangan tampilan dan fitur berdasarkan string role yang disimpan di `prefs.userRole`:
```kotlin
val role = prefs.userRole.uppercase()
when (role) {
    "RESELLER" -> setupResellerDashboard()
    "WAREHOUSE", "ADMIN_GUDANG" -> setupWarehouseMenu()
    "NOC" -> setupNocMenu()
    else -> setupTechnicianDashboard()
}
```
**Temuan Audit:**
- Android menggunakan string uppercase (`"RESELLER"`, `"TECHNICIAN"`, `"ADMIN_GUDANG"`), sementara backend mendefinisikan enum lowercase (`'teknisi'`, `'admin_gudang'`). Normalisasi dilakukan pada saat login di `prefs.userRole = loginRes.user.role.uppercase()`.
- Sebagian aksi tombol cepat di `MainActivity.kt` melakukan pemeriksaan role lokal (contoh: bypass presensi jika role adalah `"RESELLER"`, `"ADMINISTRATOR"`, `"SUPERADMIN"`).

### 2.2. Penanganan Otorisasi Presensi (Attendance Gating)
Pemeriksaan pada `MainActivity.kt`:
```kotlin
private fun requireActiveAttendance(actionName: String, onAllowed: () -> Unit) {
    val role = prefs.userRole.uppercase()
    if (role == "RESELLER" || role == "ADMINISTRATOR" || role == "SUPERADMIN") {
        onAllowed()
        return
    }
    if (isShiftActive) {
        onAllowed()
    } else {
        MaterialAlertDialogBuilder(this)
            .setTitle("Absen Masuk Diperlukan")
            .setMessage("Akses operasional '$actionName' memerlukan absensi masuk yang aktif...")
            .show()
    }
}
```
**Temuan Audit:**
- Logika ini selaras dengan kebijakan backend (`AttendanceAuthorizationService`).
- Pada level HTTP request, `DsBillingApiService` menangani error HTTP 403 dengan kode `ATTENDANCE_REQUIRED` dan melempar `AttendanceRequiredException`, yang secara otomatis memicu dialog absensi masuk.

### 2.3. Logika Cabang (`branch_id`) dan Reseller (`reseller_id`)
Pada Android:
- `prefs.branchId` disimpan dari respon login.
- Pada `MainActivity.kt`, teks header menampilkan `"Cabang #$branchId • dsBilling Staff Operasional"`.
- **Temuan Kritis:** Sebelumnya, beberapa panggilan API membatasi pengambilan data berdasarkan `branchId` lokal klien. Setelah sinkronisasi dengan backend, pengambilan SPK dan tiket menggunakan query penugasan global (`/api/mobile/workforce/work-orders`), sehingga teknisi dapat melihat SPK lintas cabang yang ditugaskan kepadanya.

---

## 3. AUDIT MODUL-MODUL OPERASIONAL ANDROID

### 3.1. SPK Lapangan (`WorkOrderActivity.kt` & `WorkOrderDetailActivity.kt`)
- **Implementasi:** Halaman Native Full-Screen menampilkan daftar tiket dan SPK aktivasi (PSB) serta perbaikan.
- **Alur Kerja:**
  - Status pekerjaan: `pending` -> `assigned` -> `in_progress` -> `completed` / `cancelled`.
  - Pada saat teknisi menekan tombol "Mulai Kerjakan", Android mengirimkan permintaan ke `/api/mobile/workforce/work-orders/{id}/start`.
  - Pada saat teknisi menyelesaikan pekerjaan, Android membuka `WorkOrderDetailActivity` yang mewajibkan input catatan teknis, bukti foto instalasi/redaman optik, dan tanda tangan pelanggan (via `SignatureView`).
- **Status Paritas:** **MATCH** dengan alur Web Portal Teknisi (`MyJobs/Show.php`).

### 3.2. Pasang ONT / Aktivasi Baru (`ProvisionOntActivity.kt`)
- **Implementasi:** Full-Screen wizard 4 langkah (Pilih Pelanggan -> Scan Barcode ONU -> Konfigurasi Layanan -> Aktivasi OLT/GenieACS).
- **Backend Endpoint:** `/api/mobile/network/provision-ont`.
- **Status Paritas:** **MATCH** dengan alur Web Wizard (`Installation/Wizard.php`).

### 3.3. Swap ONT (`SwapOntActivity.kt`)
- **Implementasi:** Full-Screen Activity untuk penggantian modem rusak.
- **Alur:** Teknisi memasukkan atau memilih serial number modem lama yang rusak, kemudian memilih serial number modem baru dari stok tangan (custody) teknisi, dan memberikan alasan penggantian.
- **Backend Endpoint:** `/api/mobile/network/swap-ont`.
- **Status Paritas:** **MATCH** dengan alur Web Swap (`Swap/Index.php`).

### 3.4. Cabut ONT (`DismantleActivity.kt`)
- **Implementasi:** Full-Screen Activity untuk pembongkaran perangkat pelanggan terputus/berhenti berlangganan.
- **Alur:** Teknisi memilih pelanggan atau memindai SN modem, mengkonfirmasi penarikan perangkat, memilih kondisi modem (Baik/Rusak), dan memasukkan modem kembali ke status custody teknisi.
- **Backend Endpoint:** `/api/mobile/network/dismantle-ont`.
- **Status Paritas:** **MATCH** dengan alur Web Dismantle (`Dismantle/Index.php`).

### 3.5. Diagnosa ONT & Pengaturan WiFi (`DiagnosticActivity.kt` & `DeviceDetailActivity.kt`)
- **Implementasi:**
  - `DiagnosticActivity.kt`: Pencarian perangkat berdasarkan nama pelanggan, kode pelanggan, atau serial number ONT. Menampilkan status online/offline, RX Power (dBm), TX Power, uptime, dan link LAN.
  - `DeviceDetailActivity.kt`: Informasi detail TR-069, termasuk tab WiFi untuk ganti nama WiFi (SSID) dan ganti password WiFi (WPA Key).
- **Temuan Kritis & Bug:**
  - **Akses Ditolak Operasi WiFi:** Saat teknisi menekan "Simpan Nama SSID" atau "Simpan Keamanan & Password", muncul toast error merah:  
    `"Gagal: Akses ditolak: Anda tidak memiliki permission untuk operasi 'wifi'."`
    - **Penyebab:** Pada backend `MobileAcsProxyController::validateDeviceAccess`, operasi `'wifi'` dicek menggunakan `$user->hasPermission('onu.configure.wifi')`. Di model `User.php`, method `hasPermission()` memeriksa `job_title` dan `hasRole('teknisi')`, tetapi **tidak memeriksa `job_function = 'TECHNICIAN'`**. Akibatnya, teknisi yang di-assign via `job_function` tanpa role tersambung langsung di tabel pivot `role_user` ditolak oleh backend!
- **Status Paritas:** **BROKEN (Wifi Configuration Gate)**.

### 3.6. Stok Material / Custody (`InventoryActivity.kt`)
- **Implementasi:** Full-Screen Activity dengan dua tab: "Stok di Tangan" (Material Custody) dan "Riwayat Permintaan".
- **Backend Endpoint:** `/api/mobile/inventory/my-stock`.
- **Status Paritas:** **MATCH** dengan Web (`Inventory/Custody/Index.php`).

### 3.7. Slip Gaji / Payroll (`PayrollActivity.kt`)
- **Implementasi:** Full-Screen Activity menampilkan ringkasan take-home pay, tunjangan, insentif per SPK, dan riwayat slip gaji per periode.
- **Backend Endpoint:** `/api/mobile/payroll` & `/api/mobile/payroll/{id}`.
- **Status Paritas:** **MATCH** dengan Web (`Technician/Payroll/Index.php`).

### 3.8. Asisten Suara AI (`VoiceAssistBottomSheet.kt` & `GeminiAiService.kt`)
- **Implementasi:** Menggunakan Android SpeechRecognizer (STT) untuk input suara teknisi dan Android Text-To-Speech (TTS) untuk respon suara. Pertanyaan teknis diteruskan ke `/api/mobile/ai/ask` (didukung Gemini AI).
- **Status Paritas:** **NOT APPLICABLE / MOBILE EXCLUSIVE**. Fitur ini dirancang khusus untuk kenyamanan teknisi di atas tiang/lapangan dan sengaja tidak memiliki padanan di Web Portal Teknisi.

---

## 4. AUDIT UI/UX, LAYOUT & EDGE-TO-EDGE INSETS

Berdasarkan inspeksi visual (lampiran tangkapan layar) dan audit file layout:

### 4.1. Masalah Tumpang Tindih Navigasi Bawah (Bottom Navigation Bar Overlap)
- **Tampilan Aktual:** Pada layar dengan sistem navigasi 3-tombol Android (Back, Home, Recent Apps `||| O <`), baris tombol navigasi bawah aplikasi dsBilling Staff (`Beranda`, `SPK`, `Perangkat`, `Lainnya`) tertimpa dan terhalang oleh tombol navigasi sistem Android.
- **Penyebab Teknis:**
  - Di `activity_main.xml`, `bottomNavigation` memiliki tinggi tetap `android:layout_height="64dp"`.
  - Di `MainActivity.kt`:
    ```kotlin
    binding.bottomNavigation.updatePadding(bottom = navBars.bottom)
    ```
    Karena tinggi `layout_height` tetap `64dp`, penambahan padding bawah menyebabkan item navigasi terdesak ke atas dan teks label terpotong atau tertekan di balik tombol sistem.
  - Seharusnya, tinggi total `BottomNavigationView` disesuaikan: `height = (64 * density) + navBars.bottom`, atau menggunakan `fitsSystemWindows="false"` dengan margin container yang tepat.

### 4.2. Tumpang Tindih Floating Action Button (FAB)
- **Tampilan Aktual:**
  - Tombol **Asisten Suara** (`fabVoiceAssist`) mengambang di atas bottom bar dan menghalangi teks kartu aktivitas.
  - Tombol tengah **Scan QR** (`fabCenterScan`) terpotong sebagian di bawah `BottomNavigationView`.
- **Penyebab Teknis:**
  - Di `activity_main.xml`, urutan deklarasi XML:
    1. `fabVoiceAssist`
    2. `fabCenterScan`
    3. `bottomNavigation`
  - Di Android CoordinatorLayout/FrameLayout, elemen yang dideklarasikan belakangan digambar **di atas** elemen sebelumnya. Akibatnya, `bottomNavigation` menutupi bagian bawah `fabCenterScan`.
  - Nilai `bottomMargin` pada insets listener:
    ```kotlin
    binding.fabCenterScan.updateLayoutParams<ViewGroup.MarginLayoutParams> {
        bottomMargin = (26 * density).toInt() + navBars.bottom
    }
    binding.fabVoiceAssist.updateLayoutParams<ViewGroup.MarginLayoutParams> {
        bottomMargin = (76 * density).toInt() + navBars.bottom
    }
    ```
    Nilai offset `76 * density` menempatkan tombol Asisten Suara tepat di area konten terbawah dan tumpang tindih dengan navigasi.

### 4.3. Masalah Tampilan Snackbar pada `DeviceDetailActivity.kt`
- **Tampilan Aktual:**
  - Kotak peringatan merah (*Snackbar*) menempel di bagian paling bawah layar dan tertutup oleh tombol navigasi sistem Android.
  - Informasi pada bagian terbawah scroll view (`Username PPPoE`, `IP Address WAN`) terpotong dan tidak dapat di-scroll sepenuhnya melewati navigasi bar.
- **Penyebab Teknis:**
  - Di `DeviceDetailActivity.kt`, `binding.root.setPadding(0, 0, 0, navBars.bottom)` belum diterapkan pada scrollable container (`nestedScrollView`), sehingga konten terbawah tertutup navigation bar.
  - `Snackbar.make` tidak diberikan anchor view (`setAnchorView`), sehingga jatuh ke dasar layar di balik bar navigasi.

---

## 5. KESIMPULAN AUDIT ANDROID
Aplikasi Android dsBilling Staff telah memiliki implementasi fitur native yang sangat lengkap dan fungsional. Namun, terdapat dua kendala mendasar yang perlu diselaraskan:
1. **Otorisasi Backend:** Normalisasi method `User::hasPermission` agar secara eksplisit mengakomodasi teknisi dengan `job_function = 'TECHNICIAN'`.
2. **Insetting & Z-Order XML:** Penataan ulang urutan z-index FAB dan penanganan insets sistem navigasi Android 3-tombol agar tidak ada lagi menu yang terhalang tombol handphone.
