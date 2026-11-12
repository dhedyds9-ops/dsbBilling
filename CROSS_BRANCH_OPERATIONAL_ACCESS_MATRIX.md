# MATRIKS AKSES OPERASIONAL & PENUGASAN LINTAS CABANG
## dsBilling Role-Based Access Control & Active Shift Governance

---

## 1. Matriks Status Presensi vs Fitur Sistem

| Fitur / Endpoint | Tanpa Presensi (Belum Absen Masuk) | Presensi Aktif (Sudah Masuk, Belum Pulang) | Pasca Checkout (Sudah Absen Pulang) | Catatan Kebijakan |
| :--- | :---: | :---: | :---: | :--- |
| **Login & Autentikasi** | ✅ DIZINKAN | ✅ DIZINKAN | ✅ DIZINKAN | Endpoint bebas prasyarat presensi. |
| **Melihat Profil Sendiri (`/me`)** | ✅ DIZINKAN | ✅ DIZINKAN | ✅ DIZINKAN | Menampilkan status presensi aktif & flag operasional. |
| **Absen Masuk (`check-in`)** | ✅ DIZINKAN | ⚠️ (Sudah Masuk) | ❌ (Shift Selesai) | Hanya satu presensi aktif per siklus shift. |
| **Absen Pulang (`check-out`)** | ❌ (Belum Masuk) | ✅ DIZINKAN | ❌ (Sudah Pulang) | Check-out langsung mengunci seluruh hak operasional. |
| **Slip Gaji / Payroll Pribadi** | ✅ DIZINKAN | ✅ DIZINKAN | ✅ DIZINKAN | Sesuai permission, data read-only pribadi. |
| **Peta ODP (Read-Only)** | ✅ DIZINKAN | ✅ DIZINKAN | ✅ DIZINKAN | Informasi spasial navigasi lapangan. |
| **Daftar SPK / Work Order** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Mulai & Selesaikan SPK** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif & penugasan sah. |
| **Input Material Usage SPK** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Upload Bukti & Tanda Tangan** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Provisioning PSB / Pasang ONT** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Swap ONT (Ganti Perangkat)** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Dismantle / Cabut ONT** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **TR-069: Diagnosa & Sinyal** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **TR-069: Set SSID & Password WiFi** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif & permission `onu.configure.wifi`. |
| **TR-069: Set Akun WAN PPPoE** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif & permission `onu.configure.wan`. |
| **TR-069: Reboot / Summon ONT** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif & permission `onu.reboot`. |
| **Stok Tangan (My Stock Custody)** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Transfer / Requisition Material** | ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |
| **Registrasi Pelanggan Baru (POST)**| ❌ DITOLAK (403) | ✅ DIZINKAN | ❌ DITOLAK (403) | Memerlukan presensi aktif. |

---

## 2. Matriks Penugasan Lintas Cabang (Cross-Branch Scope)

Berdasarkan keputusan bisnis resmi: kesamaan `branch_id` **tidak boleh menjadi pembatas mutlak**. Otorisasi dievaluasi berdasarkan relasi penugasan dan batasan tenant/reseller:

| Hubungan Teknisi vs Target Aset | Status Penugasan | Hak Akses Operasional | Perlakuan Database & Audit Trail |
| :--- | :--- | :---: | :--- |
| **Cabang Sama (Branch A -> Branch A)** | Ditugaskan ke diri sendiri (`assigned_to == self`) | **DIZINKAN** | Normal log. |
| **Cabang Sama (Branch A -> Branch A)** | Belum ditugaskan (`assigned_to == null`) | **DIZINKAN** | Teknisi cabang sendiri dapat mengambil pekerjaan (Self-assign). |
| **Cabang Sama (Branch A -> Branch A)** | Ditugaskan ke teknisi lain di cabang sama | **DITOLAK (403)** | Mencegah konflik penanganan pekerjaan antar rekan sejawat. |
| **Lintas Cabang (Branch A -> Branch B)** | **Ditugaskan secara resmi** (`assigned_to == self`) | **DIZINKAN** | **Log Cross-Branch Audit:** Mencatat cabang teknisi (#A) dan cabang target (#B). `branch_id` target aset **tidak diubah**. |
| **Lintas Cabang (Branch A -> Branch B)** | Ditugaskan ke teknisi Cabang B lain | **DITOLAK (403)** | Pesan: `Akses ditolak: SPK ini milik cabang lain.` |
| **Lintas Cabang (Branch A -> Branch B)** | Belum ditugaskan / Unassigned | **DITOLAK (403)** | Pekerjaan cabang lain wajib melalui penugasan supervisor/manager terlebih dahulu. |
| **Lintas Tenant / Reseller Berbeda** | Beda Reseller / Beda Perusahaan Tenant | **DITOLAK (403)** | **Multi-Tenant Boundary:** Isolasi absolut data pelanggan/perangkat antar mitra bisnis. |

---

## 3. Kontrak Rejection API Backend (HTTP 403)

Ketika teknisi mencoba mengeksekusi operasi tanpa presensi aktif, backend Laravel mengembalikan response seragam:

```json
{
  "success": false,
  "code": "ATTENDANCE_REQUIRED",
  "message": "Pengguna harus melakukan absensi masuk sebelum menjalankan aktivitas operasional.",
  "attendance_required": true
}
```

Format ini memicu penanganan otomatis pada Android client (`AttendanceRequiredException`) untuk menampilkan dialog konfirmasi absensi langsung ke layar `AttendanceActivity`.
