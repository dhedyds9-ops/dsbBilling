# LAPORAN AUDIT & EVALUASI SOURCE CODE
## Evolusi GenieACS Mobile Menjadi dsBilling Staff (PHASE 0)

> **Dokumen:** Audit Teknis & Keamanan Existing Source Code  
> **Aplikasi Asal:** GenieACS Mobile Client (`com.genieacs.client`)  
> **Aplikasi Target:** dsBilling Staff — ISP Field Operations & Network Management  
> **Direktori Proyek:** `D:\dsbilling-mobile`  
> **Backend Pendukung:** `D:\dsBilling` (Laravel 11, Livewire 3, Sanctum)  
> **Tanggal Audit:** 8 November 2026  
> **Status:** **PHASE 0 COMPLETED — Menunggu Persetujuan Implementasi**

---

## 1. Identitas & Status Existing Aplikasi

### 1.1 Spesifikasi Teknis Proyek
- **Nama Aplikasi Existing:** GenieACS Mobile
- **Application ID / Package Name:** `com.genieacs.client`
- **Compile SDK:** 35 (Android 15)
- **Target SDK:** 35 (Android 15)
- **Min SDK:** 24 (Android 7.0 Nougat)
- **Versi Rilis Saat Ini:** `versionCode = 1`, `versionName = "1.0.0"`
- **Bahasa Pemrograman:** Kotlin 1.9+ / Java 17
- **Build System:** Gradle 9.3.1 (Kotlin DSL `build.gradle.kts`)
- **View Layer:** Android ViewBinding, Material Components 1.12.0
- **Networking & Async:** OkHttp 4.12.0, Gson 2.11.0, Kotlin Coroutines 1.8.1
- **Ukuran Build Terakhir:**
  - `Genieacs.apk` (Release APK): **~5.7 MB**
  - `Genieacs.aab` (Android App Bundle): **~5.1 MB**

---

## 2. Inventarisasi & Pemetaan Komponen Existing

### 2.1 Activities & Flow Navigasi
| Komponen | Path Berkas | Fungsi & Alur Saat Ini |
| :--- | :--- | :--- |
| **`LoginActivity`** | `ui/LoginActivity.kt` | **Launcher utama.** Meminta URL GenieACS NBI (port 7557), username, password, dan Gemini API Key. Mengautentikasi langsung ke GenieACS NBI port 7557. |
| **`MainActivity`** | `ui/MainActivity.kt` | Menampilkan daftar seluruh ONT/Modem dari GenieACS NBI (`/devices`), status online/offline, daya redaman Rx/Tx, filter tag, search, dan tombol aksi cepat. |
| **`DeviceDetailActivity`** | `ui/DeviceDetailActivity.kt` | Detail satu modem: status optik logaritmik, akun SuperAdmin, daftar perangkat terhubung (LAN/WLAN), manajemen Wi-Fi dual-band, summon live, reboot, ping diagnostic, dan hapus antrean task. |
| **`NetworkMapActivity`** | `ui/NetworkMapActivity.kt` | WebView hardware-accelerated yang memuat `assets/map/peta.html` (Leaflet.js + OSM) untuk visualisasi titik ODP, jalur FO, dan modem pelanggan beserta lokasi GPS teknisi. |

### 2.2 BottomSheets & Dialogs
| Komponen | Path Berkas | Fungsi Saat Ini |
| :--- | :--- | :--- |
| **`WorkOrdersBottomSheet`** | `ui/dialog/WorkOrdersBottomSheet.kt` | Dialog sekunder untuk mengakses dsBilling SPK (`/api/mobile/login` dan `/api/mobile/workforce/work-orders`). Memulai SPK, menyelesaikan SPK, input material. |
| **`AddWanBottomSheet`** | `ui/dialog/AddWanBottomSheet.kt` | Menambah atau menimpa interface WAN pada ONT (PPPoE/IPoE/Bridge, VLAN, Service Type, Port Binding). |
| **`BulkAdminBottomSheet`** | `ui/dialog/BulkAdminBottomSheet.kt` | Eksekusi massal perubahan akun SuperAdmin web GUI modem pada banyak perangkat terpilih. |
| **`VoiceAssistBottomSheet`** | `ui/dialog/VoiceAssistBottomSheet.kt` | Perintah suara hands-free berbasis Android SpeechRecognizer, Google Gemini AI (`GeminiAiService`), dan Text-to-Speech (TTS). |
| **`ServerConfigDialog`** | `ui/dialog/ServerConfigDialog.kt` | Pengaturan cepat URL GenieACS, kredensial NBI, dan API Key Gemini. |

### 2.3 Layer Jaringan, Service & Penyimpanan
| Komponen | Path Berkas | Analisa Kondisi |
| :--- | :--- | :--- |
| **`GenieAcsNbiService`** | `data/network/GenieAcsNbiService.kt` | Berisi ~2.100 baris logika TR-069 komprehensif (heuristik multi-vendor ZTE, Huawei, Fiberhome, VSOL, C-Data; parsing daya optik logaritmik; manajemen Wi-Fi & WAN; diagnostic ping). **Berkomunikasi langsung dari HP ke port 7557 NBI.** |
| **`DsBillingApiService`** | `data/network/DsBillingApiService.kt` | Klien HTTP ke backend dsBilling untuk auth teknisi, pengambilan SPK, ubah status SPK, input material usage, dan GPS logging. |
| **`GeminiAiService`** | `data/network/GeminiAiService.kt` | Klien REST ke Google Gemini API v1beta (`gemini-3-flash-preview`, `gemini-flash-lite-latest`) untuk memproses bahasa alami teknisi. |
| **`AppPreferences`** | `data/pref/AppPreferences.kt` | Wrapper `SharedPreferences` standar (`genieacs_prefs`). Menyimpan URL server, token, username, password, JSON cache modem, dan API key dalam bentuk plaintext. |

---

## 3. Temuan Audit Keamanan (Security Audit & Vulnerabilities)

Kami menemukan **6 kerentanan keamanan signifikan** pada arsitektur existing yang wajib diselesaikan pada evolusi menjadi dsBilling Staff:

### 🔴 VULN-01: Hardcoded Google Gemini API Key di Kode Sumber (Severity: HIGH)
- **Lokasi:** [`AppPreferences.kt` Baris 29](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/pref/AppPreferences.kt#L29)
- **Temuan:** `const val DEFAULT_GEMINI_API_KEY = "AQ.Ab8RN6JZq03oMKUYh9IvVJQMpuZYNkOzqtQsvmy8SlwfKpTOig"`
- **Dampak:** Kunci API Gemini tersimpan permanen di dalam bytecode APK dan dapat diekstraksi dengan mudah via dekompilasi (APKTool / Jadx), berisiko kuota disalahgunakan pihak ketiga.
- **Rekomendasi:** Hapus API Key dari klien Android. Layanan Voice AI harus diproxy melalui backend dsBilling (`POST /api/mobile/ai/ask`) menggunakan server-side secret key yang aman.

### 🔴 VULN-02: Akses Langsung HP Teknisi ke Port 7557 GenieACS NBI (Severity: CRITICAL)
- **Lokasi:** `GenieAcsNbiService.kt` & `LoginActivity.kt`
- **Temuan:** Aplikasi Android melakukan pemanggilan HTTP langsung ke port 7557 GenieACS NBI dengan kredensial Basic Auth `admin:password`.
- **Dampak:**
  1. Port NBI 7557 GenieACS harus dibuka ke jaringan publik / teknisi, mengekspos core server TR-069.
  2. Kredensial super-admin NBI tersimpan di HP teknisi.
  3. Tidak ada audit log siapa teknisi yang mereboot atau mengubah konfigurasi modem di backend dsBilling.
  4. Tidak ada isolasi multi-cabang (branch isolation) atau batas hak akses.
- **Rekomendasi:** Alihkan seluruh interaksi TR-069 melalui **dsBilling Backend Proxy API** (`/api/mobile/acs/...`). Backend dsBilling memvalidasi hak akses pengguna, mencatat audit log, lalu memanggil GenieACS driver secara aman dari jaringan internal server.

### 🔴 VULN-03: Kredensial & Token Disimpan dalam Plaintext SharedPreferences (Severity: HIGH)
- **Lokasi:** [`AppPreferences.kt` Baris 10](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/pref/AppPreferences.kt#L10)
- **Temuan:** Menggunakan `context.getSharedPreferences("genieacs_prefs", Context.MODE_PRIVATE)`.
- **Dampak:** Pada perangkat yang di-root atau melalui backup ADB, file XML plaintext berisi password server dan Bearer Token Sanctum dapat dibaca tanpa proteksi.
- **Rekomendasi:** Migrasikan penyimpanan kredensial sensitif ke **AndroidX Security Crypto (`EncryptedSharedPreferences`)** dengan enkripsi AES-256 GCM berbasis Android Keystore.

### 🟡 VULN-04: Cleartext Traffic Diizinkan Global (Severity: MEDIUM)
- **Lokasi:** [`AndroidManifest.xml` Baris 19](file:///D:/dsbilling-mobile/app/src/main/AndroidManifest.xml#L19)
- **Temuan:** `android:usesCleartextTraffic="true"`
- **Dampak:** Membuka celah *Man-in-the-Middle (MITM)* saat teknisi berada di jaringan Wi-Fi publik, membiarkan token dan data pelanggan tersadap jika server menggunakan HTTP biasa.
- **Rekomendasi:** Wajibkan HTTPS penuh (`android:usesCleartextTraffic="false"`) dengan konfigurasi `network_security_config.xml` untuk domain produksi (`https://mstore.mstore.id`).

### 🔴 VULN-05: Autentikasi Utama Masih Bertumpu pada GenieACS, Bukan dsBilling (Severity: CRITICAL)
- **Lokasi:** `LoginActivity.kt`
- **Temuan:** Gerbang login utama aplikasi adalah login server NBI GenieACS, sedangkan login dsBilling terisolasi di dalam `WorkOrdersBottomSheet`.
- **Dampak:** Pengguna non-teknisi (CS, Kasir, Gudang, Manager) tidak dapat menggunakan aplikasi karena dipaksa memiliki akses port 7557 GenieACS terlebih dahulu.
- **Rekomendasi:** Pindahkan gerbang login utama menjadi **dsBilling Single Sign-On** (`/api/mobile/login`). Setelah login berhasil, aplikasi menerima identitas role, cabang, dan permission backend, lalu menampilkan dashboard yang sesuai.

### 🟡 VULN-06: Potensi IDOR pada Endpoint Work Order & Material (Severity: HIGH)
- **Lokasi:** Backend `MobileWorkforceApiController.php` baris 177, 201, 231.
- **Temuan:** Method `startWorkOrder`, `completeWorkOrder`, dan `addMaterialUsage` hanya mengecek `WorkOrder::findOrFail($id)` tanpa memvalidasi apakah teknisi yang sedang login berhak atas SPK tersebut atau berada di cabang yang sama.
- **Rekomendasi:** Tambahkan verifikasi otorisasi kepemilikan SPK dan validasi branch scope di backend sebelum aksi dijalankan.

---

## 4. Evaluasi Fitur: Yang Dipertahankan vs Yang Perlu Dikembangkan

```
┌────────────────────────────────────────────────────────────────────────┐
│                   MATRIKS EVALUASI FITUR APLIKASI                      │
├────────────────────────────────────────────────────────────────────────┤
│  [DIPERTAHANKAN 100%]                                                  │
│   ✓ Multi-vendor TR-069 Heuristics (ZTE, Huawei, Fiberhome, VSOL)      │
│   ✓ Logarithmic Optical Calculation (uW -> dBm formula)                │
│   ✓ Dual-band Wi-Fi UI & Logic (2.4G & 5G SSID, Security Mode)        │
│   ✓ Add WAN BottomSheet UI & Parameter Mapping                         │
│   ✓ Connected Client RSSI Parser (LAN & WLAN Clients)                  │
│   ✓ Leaflet GIS / ODP Engine (peta.html & WebView bridge)              │
│   ✓ Voice Assistant Speech-to-Text & TTS UI Interaction                │
│                                                                        │
│  [DIREFAKTOR & DIROMBAK]                                               │
│   ⟳ Branding: "GenieACS Mobile" -> "dsBilling Staff"                   │
│   ⟳ Login Flow: NBI Login -> dsBilling Backend Sanctum Authentication  │
│   ⟳ Session & Storage: Plain SharedPreferences -> EncryptedSharedPrefs │
│   ⟳ TR-069 Routing: Direct Port 7557 -> dsBilling Backend Proxy API    │
│   ⟳ Voice AI: Direct Gemini API Key -> dsBilling AI Proxy Endpoint     │
│   ⟳ Main Screen: Hardcoded Modem List -> Role-Based Dynamic Dashboard │
│                                                                        │
│  [MODUL BARU YANG DIBANGUN]                                            │
│   ★ Role-Based Dashboard (Admin, Manager, NOC, Teknisi, Gudang, CS)    │
│   ★ Modul Inventory: Stok Teknisi (Custody), Permintaan & Mutasi       │
│   ★ QR / Barcode Scanner untuk Serial Number ONT & Barang Gudang       │
│   ★ Work Order: Checklist Interaktif, Redaman, dan Upload Foto Bukti   │
│   ★ Offline Mode Caching & Background Sync                             │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Kebutuhan Backend API dsBilling

Untuk mendukung evolusi aplikasi tanpa mengubah core database, backend dsBilling sudah memiliki model Eloquent yang sangat lengkap (`App\Models\Inventory`, `App\Models\Workforce`, `App\Models\ACS`, `App\Models\User`). Kita hanya perlu mengekspos endpoint RESTful API terpadu pada `routes/api.php` di bawah prefix `/api/mobile/`:

### 5.1 Kelompok Autentikasi & Profil Pengguna
1. `POST /api/mobile/auth/login`
   - Request: `email_or_username`, `password`, `device_info`
   - Response: `token`, `user` (id, name, email, role, job_title, branch_id), `permissions` (array), `app_config`
2. `GET /api/mobile/auth/me`
   - Mengambil profil terbaru, role, dan daftar permission aktif.
3. `POST /api/mobile/auth/logout`
   - Melakukan revocasi Sanctum Token saat ini.

### 5.2 Kelompok Modul Teknisi & SPK (Workforce)
1. `GET /api/mobile/workforce/work-orders` (Sudah ada, diperkaya filter status & pagination)
2. `GET /api/mobile/workforce/work-orders/{id}` (Detail lengkap dengan checklist, customer coords, material)
3. `POST /api/mobile/workforce/work-orders/{id}/start` (Sudah ada)
4. `POST /api/mobile/workforce/work-orders/{id}/complete` (Sudah ada)
5. `POST /api/mobile/workforce/work-orders/{id}/checklists/{checklist_id}/toggle` (Checklist dinamis)
6. `POST /api/mobile/workforce/work-orders/{id}/photos` (Upload foto bukti pekerjaan: redaman ODP, redaman ONT, kerapian kabel, foto rumah)
7. `POST /api/mobile/workforce/work-orders/{id}/optical-power` (Catat hasil ukur redaman OPM)
8. `POST /api/mobile/workforce/work-orders/{id}/signature` (Simpan tanda tangan digital pelanggan)
9. `POST /api/mobile/workforce/gps/log` (Sudah ada)

### 5.3 Kelompok Modul Inventory & Pergudangan
1. `GET /api/mobile/inventory/my-stock` (Daftar stok material yang dipegang teknisi / `InventoryCustody`)
2. `GET /api/mobile/inventory/items` (Pencarian master item material/alat, dukungan scan barcode)
3. `POST /api/mobile/inventory/requisitions` (Permintaan barang dari teknisi ke gudang)
4. `POST /api/mobile/inventory/custody/accept` (Teknisi mengonfirmasi serah terima barang dari gudang)
5. `POST /api/mobile/inventory/return` (Pengembalian sisa material atau barang rusak/RMA ke gudang)
6. `GET /api/mobile/inventory/mutations` (Riwayat serah terima dan mutasi teknisi)

### 5.4 Kelompok Modul NOC & ACS Proxy
1. `GET /api/mobile/acs/devices` (Daftar perangkat ONT berpaginasi dengan pencarian & filter tag)
2. `GET /api/mobile/acs/devices/{id}` (Detail lengkap perangkat, status optik, WAN, WLAN, client list)
3. `POST /api/mobile/acs/devices/{id}/summon` (Proxy eksekusi summon via backend `GenieACSDriver`)
4. `POST /api/mobile/acs/devices/{id}/reboot` (Proxy reboot via backend)
5. `POST /api/mobile/acs/devices/{id}/wifi` (Proxy update SSID & Password dual-band)
6. `POST /api/mobile/acs/devices/{id}/wan` (Proxy penambahan / konfigurasi WAN)
7. `POST /api/mobile/acs/devices/{id}/ping` (Proxy eksekusi ping diagnostics)

### 5.5 Kelompok Asisten AI & Voice Proxy
1. `POST /api/mobile/ai/ask`
   - Mengirim teks hasil pengenalan suara teknisi ke backend dsBilling. Backend memanggil Google Gemini menggunakan API key server yang aman, menyertakan konteks jaringan terkini, dan mengembalikan teks ringkas untuk dibacakan oleh TTS Android.

---

## 6. Roadmap Implementasi Komprehensif

```
┌────────────────────────────────────────────────────────────────────────┐
│               ROADMAP EVOLUSI DSBILLING STAFF (PHASE 1 - 6)            │
├────────────────────────────────────────────────────────────────────────┤
│ PHASE 0: [SELESAI] Audit Source Code, Keamanan & Kebutuhan API         │
│                                                                        │
│ PHASE 1: Branding, Login dsBilling & Role-Based Dashboard              │
│  • Ganti identitas aplikasi menjadi "dsBilling Staff"                  │
│  • Perbarui logo, palet warna, splash screen, dan styling Material 3   │
│  • Implementasi Login berbasis dsBilling Backend API (Sanctum)         │
│  • Implementasi EncryptedSharedPreferences untuk token & session       │
│  • Rancang Dynamic Dashboard menyesuaikan 7 Roles:                     │
│    (Admin, Manager, NOC, Teknisi, Gudang, CS, Kasir)                  │
│                                                                        │
│ PHASE 2: Modul Inventory & Mutasi Barang                               │
│  • Layar Stok Teknisi (Custody) real-time dari backend                 │
│  • Form Permintaan Barang (Requisition) ke gudang                      │
│  • Konfirmasi Serah Terima Barang (Dispatch & Custody Transfer)        │
│  • Integrasi Barcode / QR Scanner (ML Kit) untuk scan SN ONT & dropcore│
│  • Modul Retur Barang Rusak / Sisa Proyek                              │
│                                                                        │
│ PHASE 3: Penyempurnaan Modul Work Order (SPK Teknisi)                  │
│  • Tampilan modern daftar SPK & Filter status                          │
│  • Alur pengerjaan: Terima -> Mulai -> Selesai SPK                     │
│  • Checklist instalasi dinamis                                         │
│  • Pengambilan & Upload Foto Bukti Pekerjaan (Camera / Gallery)        │
│  • Input redaman ODP & ONT dengan validasi ambang batas normal         │
│  • Tanda Tangan Digital (Canvas Signature) pelanggan                   │
│                                                                        │
│ PHASE 4: Modul NOC & Penyempurnaan GenieACS via Backend Proxy          │
│  • Migrasikan seluruh panggilan NBI port 7557 ke dsBilling API Proxy   │
│  • Pemantauan status ONT, RX/TX power, uptime, last inform             │
│  • Fitur Summon & Reboot aman dengan audit log per teknisi             │
│  • Wi-Fi Management dual-band & Add WAN via backend proxy              │
│  • Integrasi Voice Assistant melalui secure AI proxy backend           │
│                                                                        │
│ PHASE 5: Offline Mode, Notifikasi & Sinkronisasi                       │
│  • Local Database (Room / SQLite) untuk caching SPK & master inventory │
│  • Background Sync saat koneksi internet pulih (WorkManager)           │
│  • Push Notification (FCM / WebSocket) untuk penugasan SPK baru        │
│                                                                        │
│ PHASE 6: Security Testing, Hardening & Release APK                     │
│  • Nonaktifkan cleartext traffic (enforce HTTPS)                       │
│  • Bersihkan seluruh hardcoded keys dari kode sumber                   │
│  • Pengujian regresi menyeluruh seluruh alur kerja lapangan            │
│  • Konfigurasi Proguard / R8 minification & obfuscation                │
│  • Build Release APK & AAB bertandatangan (Keystore)                   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 7. Kesimpulan & Status Akhir PHASE 0

1. **Kode dasar existing sangat solid:** Heuristik multi-vendor TR-069, perhitungan daya optik logaritmik, manajemen dual-band Wi-Fi, peta jaringan Leaflet, dan voice assistant sudah bekerja dengan baik dan **layak dipertahankan 100%**.
2. **Kelemahan utama ada pada tata kelola keamanan & arsitektur gerbang akses:** Komunikasi langsung dari HP ke port 7557 NBI dan penyimpanan API Key Gemini di klien merupakan risiko yang harus segera dialihkan ke backend dsBilling.
3. **Model data backend dsBilling sudah sangat siap:** Struktur database dsBilling untuk Inventory, Workforce, ACS, dan Roles sudah mencakup semua kebutuhan lapangan tanpa perlu merombak skema database yang sudah berjalan.

**STATUS: PHASE 0 TELAH SELESAI.**  
Tidak ada baris kode aplikasi Android yang diubah pada fase audit ini. Kami menunggu konfirmasi dan persetujuan Pengguna untuk melanjutkan ke **PHASE 1 (Branding, Login dsBilling & Role-Based Dashboard)**.
