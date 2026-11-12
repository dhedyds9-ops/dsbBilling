# ARSITEKTUR TEKNIS APLIKASI ANDROID CUSTOMER DSBILLING
## Spesifikasi Desain & Standar Rekayasa Perangkat Lunak (Fase 1 & 2)

> **Nama Aplikasi:** dsBilling Customer (BillingHub)  
> **Package Name:** `com.dsbilling.customer`  
> **Direktori Proyek:** `D:\dsbilling-customer` (Terisolasi dari `D:\dsbilling-mobile`)  
> **Target SDK:** Android 15 (API 35) • **Min SDK:** Android 8.0 (API 26)  
> **Bahasa & Framework:** Kotlin 2.1+, Jetpack Compose, Material Design 3  

---

## 1. Prinsip & Pola Arsitektur

Aplikasi Android Customer dibangun dengan menerapkan pola **Clean Architecture + MVVM (Model-View-ViewModel)** dengan aliran data satu arah (*Unidirectional Data Flow / UDF*):

```text
┌────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                   │
│   Jetpack Compose UI  ◄──  UI State (StateFlow)        │
│   Screens, BottomSheets, Components (M3)               │
│                            │                           │
│                            ▼ Events / User Actions     │
│                    VIEWMODEL LAYER                     │
│   Exposes StateFlow<UiState> & Handles CoroutineScope │
└────────────────────────────┬───────────────────────────┘
                             │
┌────────────────────────────▼───────────────────────────┐
│                      DOMAIN LAYER                      │
│   UseCases / Domain Contracts (Pure Kotlin)           │
│   Business Boundary & Model Mappings                   │
└────────────────────────────┬───────────────────────────┘
                             │
┌────────────────────────────▼───────────────────────────┐
│                       DATA LAYER                       │
│   Repository Implementation                            │
│   ├── Remote DataSource (Retrofit2 + OkHttp3)          │
│   └── Local DataSource (EncryptedSharedPrefs + Cache)  │
└────────────────────────────┬───────────────────────────┘
                             │ HTTPS (JSON API)
┌────────────────────────────▼───────────────────────────┐
│                 LARAVEL 12 BACKEND                     │
│   /api/customer/*  •  Sanctum  •  CustomerServices     │
└────────────────────────────────────────────────────────┘
```

---

## 2. Struktur Modul & Paket (`com.dsbilling.customer`)

```text
com.dsbilling.customer/
├── core/
│   ├── network/             # Retrofit Client, AuthInterceptor, ErrorHandler
│   ├── security/            # KeyStoreManager, EncryptedPrefs (AES-256 GCM)
│   ├── ui/                  # Theme (Color, Typography, Shape), Components
│   └── util/                # Formatters (Rupiah, DateTime, ByteUnits)
├── data/
│   ├── model/               # DTO API (Request/Response JSON)
│   ├── remote/              # Retrofit API Interfaces (/api/customer/*)
│   └── repository/          # Repository Implementations
├── domain/
│   ├── model/               # Domain Entities (Customer, Invoice, OnuWifi, Ticket)
│   └── repository/          # Repository Contracts (Interfaces)
└── presentation/
    ├── auth/                # LoginScreen, PasswordChangeBottomSheet
    ├── dashboard/           # DashboardScreen, QuickActionGrid, BannerCarousel
    ├── billing/             # InvoiceListScreen, InvoiceDetailBottomSheet, QrisModal
    ├── wifi/                # WifiSettingsScreen, ConnectedDevicesBottomSheet
    ├── speedtest/           # SpeedTestScreen, SpeedometerGauge
    ├── support/             # TicketListScreen, CreateTicketScreen, ContactBottomSheet
    ├── profile/             # ProfileScreen, EditProfileDialog
    └── navigation/          # AppNavigation, Screen Routes, BottomNavBar
```

---

## 3. Komponen Teknologi & Dependensi Inti

1. **Antarmuka Pengguna (UI):**
   * **Jetpack Compose + Material Design 3:** Komponen modern deklaratif (`Scaffold`, `NavigationBar`, `ModalBottomSheet`, `Card`, `Button`).
   * **Dukungan Dark Mode & Light Mode:** Menggunakan `MaterialTheme` dengan token warna yang selaras dengan tema Web Tailwind (`teal-600`, `slate-900`, `emerald-500`, `rose-500`).
2. **Jaringan & Komunikasi Data:**
   * **Retrofit 2.11 + OkHttp 4.12:**
     * `AuthInterceptor`: Menyematkan header `Authorization: Bearer <token>` secara otomatis pada setiap request terproteksi.
     * `TokenExpirationInterceptor`: Mendeteksi respons HTTP 401 dan secara otomatis mengarahkan pelanggan kembali ke layar login dengan notifikasi sesi habis.
   * **KotlinX Serialization / Gson:** Deserialisasi JSON yang cepat dan aman.
3. **Konkurensi & Asinkronitas:**
   * **Kotlin Coroutines + Flow / StateFlow:** Manajemen state reaktif tanpa blocking pada main thread.
4. **Penyimpanan Aman (Security & Cryptography):**
   * **AndroidX Security Crypto / Android KeyStore:**
     * Token autentikasi disimpan menggunakan `EncryptedSharedPreferences` dengan algoritma enkripsi `AES256_GCM` untuk nilai data dan `AES256_SIV` untuk kunci.
     * Tidak ada penyimpanan token, kredensial WiFi, atau nomor kartu dalam format plaintext.

---

## 4. Protokol Keamanan & Isolasi Data

1. **Zero Direct Network Access:**
   * Aplikasi Android Customer **dilarang keras** melakukan koneksi socket atau HTTP langsung ke:
     * Port GenieACS NBI (7557).
     * Port MikroTik RouterOS API (8728/8729).
     * Database MySQL/SQLite backend.
   * Seluruh pertukaran data perangkat dan status router wajib melalui endpoint aman `/api/customer/*` yang divalidasi oleh backend Laravel.
2. **Anti-IDOR Enforcement:**
   * Aplikasi tidak pernah mengirim `customer_id` yang dapat dimanipulasi untuk mengambil data pelanggan lain.
   * Backend mengidentifikasi kepemilikan data secara eksklusif melalui `auth()->user()->customer->id` dari Sanctum Token.
3. **In-App Payment Safety:**
   * Transaksi pembayaran tidak diverifikasi di client.
   * Konfirmasi lunas tagihan semata-mata menunggu respons backend yang telah tervalidasi oleh webhook payment gateway resmi.
