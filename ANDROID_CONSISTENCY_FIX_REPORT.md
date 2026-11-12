# LAPORAN RENCANA PERBAIKAN DAN PENYERAGAMAN ANDROID STAFF DENGAN WEB DSBILLING
**dsBilling Staff Android App (`D:\dsbilling-mobile`) & Laravel Backend (`D:\dsBilling`)**  
**Dokumen Rencana Tindakan & Rekomendasi Solusi Teknis**  
**Tanggal:** 11 November 2026  
**Status:** MENUNGGU PERSETUJUAN (PENDING USER APPROVAL)  

---

## 1. RINGKASAN EKSEKUTIF AUDIT

Audit mendalam terhadap aplikasi Web dsBilling, Web Portal Teknisi, dan Android Native Staff membuktikan bahwa kedua platform telah berbagi fondasi aturan bisnis yang kuat.  
Namun, audit menemukan **2 akar masalah teknis utama** yang saat ini dialami langsung oleh teknisi di lapangan (seperti terlihat pada screenshot):

1. **Masalah Otorisasi TR-069 WiFi Backend (P0 - Critical):**
   - **Gejala:** Muncul error merah `"Gagal: Akses ditolak: Anda tidak memiliki permission untuk operasi 'wifi'."` saat teknisi mencoba mengganti nama SSID atau password WiFi di aplikasi Android.
   - **Akar Masalah:** Evaluasi izin di `app/Models/User.php` (`hasPermission()`) hanya memeriksa `job_title` dan relasi role di database, tetapi tidak memeriksa atribut `job_function = 'TECHNICIAN'`. Akibatnya, teknisi yang diset dengan `job_function = 'TECHNICIAN'` ditolak hak aksesnya oleh backend proxy `/api/mobile/acs/devices/{id}/wifi`.

2. **Masalah Tampilan & Insets Navigasi Android (P1 - High):**
   - **Gejala:** Baris menu navigasi bawah terhalang oleh tombol navigasi sistem Android 3-tombol (`||| O <`), tombol tengah Scan QR terpotong setengah, tombol Asisten Suara menutupi kartu aktivitas, dan Snackbar detail perangkat menutupi baris navigasi sistem.
   - **Akar Masalah:** Urutan deklarasi Z-index di `activity_main.xml` menempatkan `BottomNavigationView` di atas FAB, serta perhitungan insets di `MainActivity.kt` hanya menambahkan padding bawah tanpa memperbesar tinggi total `layout_height` bar navigasi.

---

## 2. RENCANA TINDAKAN PERBAIKAN TEKNIS (ACTION PLAN)

### TAHAP 1: PERBAIKAN OTORISASI PERMISSION BACKEND (`D:\dsBilling`)

#### 1.1. Normalisasi Otorisasi Teknisi di `app/Models/User.php`
Mengubah metode `hasPermission()` agar mengevaluasi `$this->isTechnician()` secara menyeluruh:
```php
// File: app/Models/User.php
public function hasPermission(string $permission): bool
{
    // 1. Bypass untuk administrator
    if ($this->hasRole(\App\Enums\UserRole::Administrator->value)) {
        return true;
    }

    // 2. Direct permissions spesifik diutamakan jika ada
    if ($this->directPermissions->isNotEmpty() && $this->directPermissions->contains('name', $permission)) {
        return true;
    }

    // 3. Evaluasi Teknisi berbasis isTechnician() (Mencakup role, job_title, dan job_function)
    if ($this->isTechnician()) {
        return str_starts_with($permission, 'technician.') 
            || str_starts_with($permission, 'workforce.') 
            || in_array($permission, \App\Enums\UserPermission::defaultTeknisiPermissions())
            || in_array($permission, [
                'onu.view', 'onu.diagnose', 'onu.configure.wifi', 'onu.configure.wan',
                'onu.reboot', 'inventory.custody', 'inventory.usage', 'inventory.return',
                'inventory.requisition', 'technician.portal'
            ]);
    }

    // 4. Evaluasi Staf Gudang, Kasir, CS, Marketing
    // ...
}
```
**Hasil yang Diharapkan:** Setiap teknisi yang terdaftar di sistem dapat langsung melakukan konfigurasi WiFi (SSID dan Password) tanpa penolakan otorisasi.

---

### TAHAP 2: PERBAIKAN INSETS DAN Z-INDEX UI ANDROID (`D:\dsbilling-mobile`)

#### 2.1. Penyesuaian Insets Navigasi Bawah di `MainActivity.kt`
Mengubah penanganan window insets agar secara dinamis menambah tinggi total `BottomNavigationView`:
```kotlin
// File: MainActivity.kt
ViewCompat.setOnApplyWindowInsetsListener(binding.root) { _, insets ->
    val navBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
    val density = resources.displayMetrics.density
    val baseNavHeight = (64 * density).toInt()

    // 1. Sesuaikan tinggi BottomNavigationView agar mengakomodasi navigation bar sistem
    binding.bottomNavigation.updateLayoutParams<ViewGroup.LayoutParams> {
        height = baseNavHeight + navBars.bottom
    }
    binding.bottomNavigation.updatePadding(bottom = navBars.bottom)

    // 2. Sesuaikan posisi Floating Action Buttons agar melayang tepat di atas BottomNavigationView
    binding.fabCenterScan.updateLayoutParams<ViewGroup.MarginLayoutParams> {
        bottomMargin = (baseNavHeight / 2) + navBars.bottom - (14 * density).toInt()
    }
    binding.fabVoiceAssist.updateLayoutParams<ViewGroup.MarginLayoutParams> {
        bottomMargin = baseNavHeight + navBars.bottom + (16 * density).toInt()
    }

    insets
}
```

#### 2.2. Perbaikan Urutan Z-Index di `activity_main.xml`
Memindahkan deklarasi `fabCenterScan` dan `fabVoiceAssist` setelah deklarasi `bottomNavigation` serta menyematkan `android:elevation="12dp"` agar digambar di lapisan teratas:
```xml
<!-- 1. Bottom Navigation Bar -->
<com.google.android.material.bottomnavigation.BottomNavigationView
    android:id="@+id/bottomNavigation"
    android:layout_width="match_parent"
    android:layout_height="64dp"
    android:layout_gravity="bottom"
    app:elevation="4dp" />

<!-- 2. Center Scan FAB (Layer di atas BottomBar) -->
<com.google.android.material.floatingactionbutton.FloatingActionButton
    android:id="@+id/fabCenterScan"
    android:layout_width="wrap_content"
    android:layout_height="wrap_content"
    android:layout_gravity="bottom|center_horizontal"
    app:elevation="10dp" />

<!-- 3. Voice Assist FAB (Layer di atas Konten) -->
<com.google.android.material.floatingactionbutton.ExtendedFloatingActionButton
    android:id="@+id/fabVoiceAssist"
    android:layout_width="wrap_content"
    android:layout_height="wrap_content"
    android:layout_gravity="bottom|end"
    android:layout_marginEnd="16dp"
    app:elevation="10dp" />
```

#### 2.3. Perbaikan Insets pada `DeviceDetailActivity.kt`
- Menambahkan bottom padding pada `nestedScrollView` agar informasi paling bawah (PPPoE & WAN IP) dapat di-scroll sepenuhnya di atas bilah navigasi handphone.
- Menambahkan margin/anchor pada Snackbar error dan sukses sehingga tampil melayang di atas bilah navigasi tanpa terpotong.

---

### TAHAP 3: VERIFIKASI PENGUJIAN DAN PACKAGING RELEASE

1. **Uji Otomatis PHPUnit Backend:**
   - Menjalankan seluruh test suite otorisasi:
     `php vendor/bin/phpunit tests/Feature/MobileAcsProxySecurityTest.php`
     `php vendor/bin/phpunit tests/Feature/AttendanceOperationalAccessControlTest.php`
   - Memastikan 100% test lulus tanpa regresi.
2. **Kompilasi & Build APK Release:**
   - Melakukan kompilasi signed release bundle:
     `gradlew.bat assembleRelease`
   - Menguji hasil visual pada perangkat dengan navigasi 3-tombol untuk memastikan tidak ada lagi teks atau tombol yang terpotong.
3. **Penyalinan APK ke Direktori Download Web:**
   - Menyalin output release ke `D:\dsBilling\public\downloads\dsbilling-staff-release.apk` agar staf dan teknisi dapat langsung memperbarui aplikasi dari portal.

---

## 3. PERMOHONAN PERSETUJUAN (APPROVAL REQUEST)

Sesuai dengan instruksi operasional resmi:
> *"MULAI DENGAN AUDIT READ-ONLY. Jangan melakukan perubahan kode sebelum laporan perbandingan Web dan Android selesai dan disetujui."*

Keempat dokumen laporan audit telah selesai disusun:
1. `D:\dsBilling\AUDIT_WEB_BACKEND_BUSINESS_RULES.md`
2. `D:\dsBilling\AUDIT_ANDROID_BUSINESS_LOGIC.md`
3. `D:\dsBilling\WEB_ANDROID_RULES_COMPARISON.md`
4. `D:\dsBilling\ANDROID_CONSISTENCY_FIX_REPORT.md`

Apakah Bapak menyetujui rencana perbaikan di atas untuk segera kami eksekusi pada kode backend dan aplikasi Android?
