# INVENTARISASI API RESMI ANDROID CUSTOMER DSBILLING
## Spesifikasi Endpoint Kontrak API Mobile Pelanggan (Fase 1 & 2)

> **Otoritas:** Laravel Backend `D:\dsBilling` (Single Source of Truth)  
> **Autentikasi:** Laravel Sanctum Token (`Authorization: Bearer <token>`)  
> **Role Guard:** `role:customer` (Strict Customer Scoping & Anti-IDOR)  

---

## 1. Status Inventarisasi Saat Ini

| Grup API | Status di Backend | Rencana Implementasi |
|---|---|---|
| **API Staff / Teknisi** (`/api/mobile/*`) | **Aktif** (42 routes) | Dikhususkan untuk aplikasi staff lapangan, dilindungi middleware `workforce.checked_in`. |
| **API Webhook Payment** (`/api/payment/*`) | **Aktif** | Digunakan oleh Midtrans, Xendit, Tripay, Duitku. |
| **API Customer Mobile** (`/api/customer/*`) | **Belum Terdaftar di `routes/api.php`** | **Wajib Dibuat** menggunakan Domain Services yang sudah teruji di Web (`CustomerPortalServices`). |

---

## 2. Inventarisasi & Spesifikasi Endpoint Resmi `/api/customer/*`

Seluruh endpoint pelanggan ditempatkan di bawah prefix `/api/customer/` dan menggunakan arsitektur controller tunggal yang memanggil domain service terkait.

### A. Autentikasi & Sesi Pelanggan

#### 1. `POST /api/customer/auth/login`
* **Deskripsi:** Autentikasi pelanggan menggunakan multi-identifier (ID Pelanggan, No WA, Email, Username PPPoE).
* **Autentikasi:** Publik.
* **Request Body:**
  ```json
  {
    "login": "2026101607",
    "password": "mypassword123",
    "device_name": "Samsung Galaxy A54 (Android)"
  }
  ```
* **Response Sukses (200 OK):**
  ```json
  {
    "success": true,
    "message": "Login berhasil",
    "token": "32|8x9d7fg7df8g7df8g...",
    "user": {
      "id": 15,
      "name": "Anisa Rahmawati",
      "customer_code": "2026101607",
      "email": "anisa@gmail.com",
      "whatsapp": "081234567890",
      "needs_password_change": false
    },
    "customer": {
      "id": 8,
      "code": "2026101607",
      "name": "Anisa Rahmawati",
      "status": "active",
      "address": "Jl. Mawar No. 12"
    }
  }
  ```
* **Response Error (422 / 401):**
  ```json
  {
    "success": false,
    "message": "Kredensial yang diberikan tidak cocok dengan data kami."
  }
  ```

#### 2. `POST /api/customer/auth/logout`
* **Deskripsi:** Menghapus Sanctum token aktif dari perangkat.
* **Autentikasi:** `auth:sanctum`.
* **Response Sukses (200 OK):**
  ```json
  {
    "success": true,
    "message": "Sesi berhasil diakhiri."
  }
  ```

#### 3. `GET /api/customer/auth/me`
* **Deskripsi:** Memvalidasi status token dan mengembalikan data profil pengguna saat cold start.
* **Autentikasi:** `auth:sanctum`.
* **Response (200 OK):** Mengembalikan data user dan customer terkini.

---

### B. Dashboard Pelanggan

#### 4. `GET /api/customer/dashboard`
* **Deskripsi:** Mengambil ringkasan lengkap data operasional pelanggan (memanggil `CustomerDashboardService::getDashboardData`).
* **Autentikasi:** `auth:sanctum`.
* **Response Sukses (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "customer": {
        "id": 8,
        "code": "0123",
        "name": "anisa",
        "phone": "081234567890",
        "address": "Jl. Dahlia No. 4"
      },
      "connection": {
        "status": "online",
        "ip_address": "10.20.30.45",
        "onu_sn": "ZTEGC85E2D31",
        "optical_power": "-20.04 dBm"
      },
      "service": {
        "profile_name": "Paket Internet 20 Mbps",
        "speed_download": "20M",
        "speed_upload": "10M",
        "price": 200000,
        "is_active": true
      },
      "billing": {
        "total_outstanding": 200000,
        "unpaid_invoices_count": 1,
        "bill_date_day": 1,
        "due_date_day": 3,
        "next_billing_date": "2026-12-01"
      },
      "recent_invoices": [
        {
          "id": 104,
          "invoice_number": "INV-202611-00104",
          "total_amount": 200000,
          "status": "unpaid",
          "due_date": "2026-11-03"
        }
      ],
      "banners": [
        {
          "id": 1,
          "title": "Koneksi Stabil Tanpa Batas",
          "image_url": "https://server.domain/images/banner_promo.jpg",
          "link_url": null
        }
      ]
    }
  }
  ```

---

### C. Tagihan & Pembayaran (Billing & Invoices)

#### 5. `GET /api/customer/invoices`
* **Deskripsi:** Mengambil daftar tagihan terpaginasi milik pelanggan (`CustomerBillingService::getPaginatedInvoices`).
* **Query Params:** `page=1`, `per_page=15`, `status=unpaid|paid|all`.
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 104,
        "invoice_number": "INV-SSL-0R0D0N57KT",
        "period": "Agustus 2026",
        "created_at": "2026-08-04T00:00:00Z",
        "total_amount": 100000,
        "paid_amount": 0,
        "status": "unpaid",
        "due_date": "2026-08-10",
        "payment_method": null,
        "paid_at": null
      },
      {
        "id": 98,
        "invoice_number": "INV-SSL-0N9HAFOALZ",
        "period": "Juni 2026",
        "created_at": "2026-06-10T00:00:00Z",
        "total_amount": 100000,
        "paid_amount": 100000,
        "status": "paid",
        "due_date": "2026-06-15",
        "payment_method": "Cash",
        "paid_at": "2026-06-27T23:29:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "total": 2
    }
  }
  ```

#### 6. `GET /api/customer/invoices/{id}`
* **Deskripsi:** Detail tagihan lengkap dengan rincian item layanan dan daftar gateway pembayaran aktif.
* **Autentikasi:** `auth:sanctum` (Mencegah IDOR via `customer_id` check).

#### 7. `POST /api/customer/invoices/{id}/pay`
* **Deskripsi:** Memulai pembayaran tagihan melalui `PaymentOrchestrationService`.
* **Request Body:**
  ```json
  {
    "gateway": "tripay",
    "payment_method_code": "qris"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Transaksi pembayaran berhasil dibuat",
    "payment_mode": "qris",
    "qr_string": "00020101021226590014ID.LINKAJA.WWW0118936009110022359483...",
    "qr_image_url": "https://tripay.co.id/qr/...",
    "checkout_url": "https://tripay.co.id/checkout/...",
    "pay_code": null,
    "amount": 100000,
    "expired_at": "2026-11-12T23:30:00Z"
  }
  ```

---

### D. Pengaturan Wi-Fi Mandiri (Self-Service)

#### 8. `GET /api/customer/wifi`
* **Deskripsi:** Mengambil nama SSID dan password aktif dari modem ONT pelanggan via GenieACS TR-069 proxy.
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "onu_id": 42,
      "model": "Huawei HG8245H5",
      "wlan_index": 1,
      "ssid": "Tecanusa_5G",
      "password": "••••••••",
      "security_mode": "WPA2-PSK (AES)",
      "is_enabled": true,
      "connected_devices_count": 5
    }
  }
  ```

#### 9. `POST /api/customer/wifi`
* **Deskripsi:** Memperbarui nama WiFi (SSID) dan/atau kata sandi modem pelanggan.
* **Request Body:**
  ```json
  {
    "onu_id": 42,
    "ssid": "Tecanusa_Rumah",
    "password": "PasswordBaru123",
    "security_mode": "WPA2-PSK (AES)"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Pengaturan Wi-Fi berhasil diperbarui pada modem pelanggan."
  }
  ```

#### 10. `GET /api/customer/wifi/connected-devices`
* **Deskripsi:** Mengambil daftar perangkat (handphone, laptop, smart TV) yang sedang terhubung ke WiFi modem.
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      { "hostname": "Galaxy-S23", "ip": "192.168.1.15", "mac": "3C:28:6D:..." },
      { "hostname": "MacBook-Air", "ip": "192.168.1.20", "mac": "A4:83:E7:..." }
    ]
  }
  ```

---

### E. Tiket Pengaduan & Gangguan (Support)

#### 11. `GET /api/customer/tickets`
* **Deskripsi:** Mengambil daftar riwayat tiket aduan milik pelanggan aktif.

#### 12. `POST /api/customer/tickets`
* **Deskripsi:** Membuat laporan gangguan baru dengan foto lampiran kendala.
* **Request (Multipart):**
  * `title`: "Koneksi Internet Lambat / LOS Merah"
  * `category`: "Teknis"
  * `priority`: "medium"
  * `description`: "Lampu PON berkedip merah sejak tadi pagi."
  * `photos[]`: [file foto]
* **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Tiket laporan gangguan berhasil dibuat.",
    "ticket_id": 45
  }
  ```

#### 13. `GET /api/customer/tickets/{id}`
* **Deskripsi:** Detail tiket, status penanganan, teknisi yang ditugaskan, dan catatan penyelesaian.

---

### F. Informasi Kontak & Speedtest

#### 14. `GET /api/customer/support/contact`
* **Deskripsi:** Informasi kontak Customer Service resmi (nomor WhatsApp admin) dari setting sistem.
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "whatsapp_url": "https://wa.me/6281234567890?text=Halo%20Admin%20dsBilling...",
    "phone": "081234567890",
    "email": "support@ispprovider.com"
  }
  ```

#### 15. `GET /api/customer/speedtest/config`
* **Deskripsi:** Parameter server speedtest lokal (IP publik, ISP name, endpoint ping & payload download/upload).
