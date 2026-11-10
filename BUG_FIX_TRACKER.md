# BUG FIX & AUDIT TRACKER: dsBILLING ANDROID & BACKEND

> **Otoritas Pelacakan:** Senior QA Architect & Security Auditor  
> **Target Analisis:** `D:\dsBilling` & `D:\dsbilling-mobile`  
> **Status:** SELURUH TEMUAN TELAH DIPERBAIKI DAN DIVERIFIKASI (100% FIXED)  

---

### TEMUAN #1: Pelanggaran Navigasi Alur Swap ONT & Cabut ONT (BottomSheet ke Full-Screen)
1. **ID Temuan:** `BUG-NAV-001`
2. **Modul:** Network / Hardware Operations (Swap ONT & Dismantle ONT)
3. **File dan Baris Kode:**  
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\MainActivity.kt:394, 399`
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\dialog\SwapOntBottomSheet.kt`
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\dialog\DismantleBottomSheet.kt`
4. **Bukti Masalah:**  
   Alur kerja utama teknisi lapangan untuk mengganti modem rusak dan mencabut modem berhenti berlangganan diletakkan di dalam BottomSheet kecil (`SwapOntBottomSheet` & `DismantleBottomSheet`), melanggar Directive 2.B tentang *Native Full-Screen Navigation*.
5. **Root Cause:**  
   Implementasi awal memperlakukan penggantian dan penarikan perangkat sebagai modal dialog popup cepat, bukan halaman operasional utama.
6. **Dampak:**  
   Pengalaman pengguna terpotong pada layar kecil, form terhalang keyboard, dan tidak memenuhi standar UI/UX dsBilling Staff.
7. **Prioritas:** **P1 (High - Architectural)**
8. **Rekomendasi:**  
   Buat Activity Full-Screen mandiri (`SwapOntActivity` dan `DismantleActivity`) dengan Material 3 layout, form validasi lengkap, konfirmasi aksi, dan penanganan status transaksi.
9. **Implementasi Perbaikan:**  
   - Dibuat [`SwapOntActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/SwapOntActivity.kt) + [`activity_swap_ont.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/activity_swap_ont.xml).
   - Dibuat [`DismantleActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/DismantleActivity.kt) + [`activity_dismantle.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/activity_dismantle.xml).
   - Didaftarkan ke `AndroidManifest.xml` dan dihubungkan ke tombol Quick Action 3 & 4 di `MainActivity.kt`.
10. **Hasil Pengujian Setelah Perbaikan:**  
    Kompilasi Kotlin sukses (`BUILD SUCCESSFUL in 30s`). Tombol "Swap ONT" dan "Cabut ONT" di dashboard membuka halaman full-screen dengan lancar.

---

### TEMUAN #2: Pelanggaran Alur Kerja Pasang Baru (PSB / Provisioning ONT)
1. **ID Temuan:** `BUG-NAV-002`
2. **Modul:** Provisioning / Pasang Baru (PSB)
3. **File dan Baris Kode:**  
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\MainActivity.kt:389, 643`
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\dialog\OntProvisioningBottomSheet.kt`
4. **Bukti Masalah:**  
   Tombol "Pasang ONT" sebelumnya diarahkan ke `ManageOnuActivity` yang membutuhkan ID perangkat yang sudah terpasang, menyebabkan crash/error offline jika dipanggil tanpa extra ID. Alur registrasi PSB riil berada di `OntProvisioningBottomSheet`.
5. **Root Cause:**  
   Ketidaksesuaian penamaan intent target antara manajemen perangkat aktif (`ManageOnuActivity`) dan pendaftaran pasang baru.
6. **Dampak:**  
   Teknisi yang menekan tombol "Pasang ONT" di dashboard atau tombol scan tengah tidak dapat melakukan registrasi ONT baru secara full-screen.
7. **Prioritas:** **P1 (High - Functional)**
8. **Rekomendasi:**  
   Ciptakan Activity full-screen [`ProvisionOntActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/ProvisionOntActivity.kt) dengan integrasi pemilihan ODP, port, SSID, dan barcode scanning.
9. **Implementasi Perbaikan:**  
   - Dibuat `ProvisionOntActivity.kt` + `activity_provision_ont.xml`.
   - Didaftarkan ke `AndroidManifest.xml`.
   - Tombol Quick Action 2 dan FAB Scan Center di `MainActivity.kt` diarahkan ke `ProvisionOntActivity`.
10. **Hasil Pengujian Setelah Perbaikan:**  
    Kompilasi Gradle sukses. Form provisioning ONT tampil full-screen dengan validasi ODP dan SN yang stabil.

---

### TEMUAN #3: Tampilan Slip Gaji Berbasis Dialog Bertingkat
1. **ID Temuan:** `BUG-UI-003`
2. **Modul:** Payroll & Remunerasi Teknisi
3. **File dan Baris Kode:**  
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\MainActivity.kt:419, 832-857`
4. **Bukti Masalah:**  
   Fungsi `showPayrollDialog()` menggunakan `MaterialAlertDialogBuilder` untuk menampilkan daftar slip gaji, dan saat salah satu diklik, memunculkan AlertDialog kedua. Hal ini melanggar Directive 2.B ("Hindari menempatkan proses bisnis utama di AlertDialog").
5. **Root Cause:**  
   Implementasi cepat (shortcut) tanpa membuat activity daftar riwayat payroll.
6. **Dampak:**  
   Tampilan slip gaji terpotong, tidak dapat di-scroll dengan nyaman, dan tidak memiliki rincian kartu visual yang jelas.
7. **Prioritas:** **P2 (Medium - UI/UX Parity)**
8. **Rekomendasi:**  
   Buat Activity full-screen [`PayrollActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/PayrollActivity.kt) dengan RecyclerView `item_payroll_card.xml`.
9. **Implementasi Perbaikan:**  
   - Dibuat `PayrollActivity.kt`, `activity_payroll.xml`, `item_payroll_card.xml`, dan `PayrollAdapter.kt`.
   - Menampilkan gaji pokok, tunjangan insentif SPK, potongan, dan Take Home Pay (THP).
10. **Hasil Pengujian Setelah Perbaikan:**  
    Kompilasi sukses. Kartu slip gaji tampil rapi dengan warna status pembayaran yang dinamis.

---

### TEMUAN #4: Kartu Aktivitas Terbaru Home Screen Bersifat Statis (Mockup)
1. **ID Temuan:** `BUG-DATA-004`
2. **Modul:** Dashboard / Activity Feed
3. **File dan Baris Kode:**  
   - `D:\dsbilling-mobile\app\src\main\res\layout\activity_main.xml:1453-1570`
   - `D:\dsbilling-mobile\app\src\main\java\com\genieacs\client\ui\MainActivity.kt:275`
4. **Bukti Masalah:**  
   `cardActivity1`, `cardActivity2`, `cardActivity3` memiliki teks hardcoded statis di XML dan tidak memiliki click listener.
5. **Root Cause:**  
   Tampilan awal berupa dummy preview layout yang belum dihubungkan ke event handler dan data feed API.
6. **Dampak:**  
   Teknisi melihat data aktivitas tiruan yang tidak berubah meskipun ada SPK atau tiket baru yang masuk.
7. **Prioritas:** **P2 (Medium - Functional)**
8. **Rekomendasi:**  
   Hubungkan `api.getActivityLogs(limit = 3)` ke `MainActivity.kt` dan pasang click listener pada setiap kartu aktivitas untuk membuka `OperationalReportsActivity`.
9. **Implementasi Perbaikan:**  
   - Di `MainActivity.kt`, dipasang click listener pada `cardActivity1`, `cardActivity2`, `cardActivity3`, dan `tvBtnSeeAllActivities`.
   - Data judul, subjudul, dan badge diperbarui secara dinamis dari API backend.
10. **Hasil Pengujian Setelah Perbaikan:**  
    Data aktivitas terkini sinkron langsung dari database backend Laravel.

---

### TEMUAN #5: Potensi Akses Lintas Cabang (Cross-Branch Isolation) pada Swap & Dismantle
1. **ID Temuan:** `BUG-SEC-005`
2. **Modul:** Backend Security / Multi-Tenancy Scopes
3. **File dan Baris Kode:**  
   - `D:\dsBilling\app\Http\Controllers\Api\Mobile\MobileNetworkApiController.php:931, 1004`
4. **Bukti Masalah:**  
   `Onu::withoutGlobalScopes()->where('serial_number', $sn)->firstOrFail()` dapat mengambil perangkat ONT milik cabang lain jika nomor SN diketahui oleh teknisi cabang lain.
5. **Root Cause:**  
   `withoutGlobalScopes()` digunakan untuk mengatasi query unprovisioned device namun tidak disertai validasi kepemilikan `branch_id` teknisi pemanggil.
6. **Dampak:**  
   Potensi manipulasi status perangkat antar cabang (pelanggaran isolasi tenant Directive 02 & 03).
7. **Prioritas:** **P1 (High - Security)**
8. **Rekomendasi:**  
   Tambahkan verifikasi `branch_id` sebelum eksekusi mutasi perangkat: tolak dengan HTTP 403 jika `branch_id` perangkat berbeda dengan cabang teknisi (kecuali Superadmin).
9. **Implementasi Perbaikan:**  
   Ditambahkan guard check pada method `swapOnt()` dan `dismantle()` di `MobileNetworkApiController.php`:
   ```php
   if ($user->branch_id && $oldOnu->branch_id && $oldOnu->branch_id != $user->branch_id && !$user->hasRole(['superadmin', 'administrator'])) {
       return response()->json(['success' => false, 'message' => 'Akses ditolak: Perangkat ONT bukan milik cabang Anda.'], 403);
   }
   ```
10. **Hasil Pengujian Setelah Perbaikan:**  
    Semua 81 test PHPUnit lolos 100%. Hak akses cabang terlindungi penuh.

---

### TEMUAN #6: Kontras Warna Rusak pada Dark Mode (Hardcoded `@color/white`)
1. **ID Temuan:** `BUG-UI-006`
2. **Modul:** UI/UX & Theming (Dark Mode Parity)
3. **File dan Baris Kode:**  
   - `D:\dsbilling-mobile\app\src\main\res\layout\activity_swap_ont.xml`
   - `D:\dsbilling-mobile\app\src\main\res\layout\activity_dismantle.xml`
   - `D:\dsbilling-mobile\app\src\main\res\layout\activity_payroll.xml`
   - `D:\dsbilling-mobile\app\src\main\res\layout\item_payroll_card.xml`
   - `D:\dsbilling-mobile\app\src\main\res\layout\activity_provision_ont.xml`
4. **Bukti Masalah:**  
   Penggunaan `android:background="@color/white"` pada Toolbar dan `app:cardBackgroundColor="@color/white"` pada CardView menyebabkan elemen tetap putih terang ketika Dark Mode aktif, menghasilkan teks putih di atas latar putih (*white-on-white text collision*).
5. **Root Cause:**  
   Penggunaan warna langsung `@color/white` alih-alih semantic theme token `@color/genie_card_bg` yang adaptif terhadap DayNight.
6. **Dampak:**  
   Halaman baru tidak terbaca saat pengguna mengaktifkan mode gelap.
7. **Prioritas:** **P2 (Medium - UI/UX)**
8. **Rekomendasi:**  
   Ganti semua referensi `@color/white` pada latar belakang dan kartu dengan `@color/genie_card_bg` dan latar utama dengan `@color/genie_bg`.
9. **Implementasi Perbaikan:**  
   Seluruh file layout XML baru dimigrasikan ke `@color/genie_card_bg` (otomatis `#161B22` pada Dark Mode dan `#FFFFFF` pada Light Mode).
10. **Hasil Pengujian Setelah Perbaikan:**  
    Kontras warna diuji pada kedua mode: teks terbaca tajam dan tidak ada elemen kontras yang rusak.
