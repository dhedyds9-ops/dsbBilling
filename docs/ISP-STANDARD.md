WiFiNan ERP
ISP STANDARD

Version: 1.0
Status: Official Standard
Reference:

SSOT.md
UI-STANDARD.md
BACKEND-STANDARD.md
MODULE-STANDARD.md
COMPONENT-STANDARD.md
1. Tujuan

Dokumen ini menjadi standar resmi seluruh modul ISP pada WiFiNan ERP.

Semua modul ISP wajib mengikuti standar ini.

2. Ruang Lingkup ISP

Standar ini berlaku untuk seluruh modul berikut.

Infrastruktur
POP
Tower
OLT
ODC
ODP
Splitter
FAT
ONU / ONT
Router
Switch
Access Point
IP Pool
VLAN
DNS
NTP
Provider
MikroTik
GenieACS
FreeRADIUS
DHCP Server
Service
Service Profile
PPPoE
Hotspot
Static IP
Dedicated
Voucher
Provisioning
Queue
PPP Profile
Hotspot Profile
Radius Profile
TR-069
Monitoring
Operasional
Customer Service
Activation
Suspension
Termination
Migration
Relocation
3. Arsitektur ISP
Customer
        │
        ▼
Service Profile
        │
        ▼
Provisioning Engine
        │
 ┌──────┴────────────┐
 │                   │
 ▼                   ▼
MikroTik        GenieACS
 │                   │
 ▼                   ▼
PPPoE           ONU
Hotspot         TR-069
Queue           WiFi
Radius          Firmware
4. Standar Modul

Setiap modul ISP minimal memiliki:

Index
Create
Edit
Show
Archive
Activity Log
Import
Export
Bulk Action
5. Workflow Layanan
Draft

↓

Pending

↓

Provisioning

↓

Active

↓

Suspended

↓

Terminated

↓

Archived

Seluruh modul menggunakan workflow yang sama.

6. Customer Flow
Customer

↓

Service Profile

↓

Provisioning

↓

Router / ACS

↓

Internet Active
7. Provisioning Engine

Seluruh provisioning dilakukan melalui Provisioning Engine.

Tidak boleh langsung dari UI ke MikroTik atau GenieACS.

Provisioning harus menggunakan Queue Job.

8. Standar MikroTik

Harus mendukung:

Router API
PPP Secret
PPP Profile
Queue
Queue Tree
Simple Queue
Hotspot User
IP Binding
Firewall
Scheduler
Script
9. Standar GenieACS

Harus mendukung:

ACS Server
Device
Preset
Provision
Virtual Parameter
Config
Task
Fault
Firmware
Reboot
Factory Reset
10. Standar OLT

Minimal memiliki:

Vendor
Model
IP
SNMP
API
Firmware
Rack
POP
Status
11. Standar ODC

Minimal:

Nama
Kode
POP
Latitude
Longitude
Kapasitas
Status
12. Standar ODP

Minimal:

Nama
Kode
ODC
Splitter
Port
Kapasitas
Status
13. Standar ONU

Minimal:

Serial Number
GPON SN
Vendor
Model
Firmware
MAC
OLT
PON
ODP
Customer
Status
14. Standar Router

Minimal:

Nama
IP
API Port
Username
Password
Versi
Branch
Owner
Status
15. Standar Service Profile

Minimal:

Kode
Nama
Service Type
Download
Upload
Shared User
Owner Price
Reseller Price
Radius Profile
PPP Profile
Queue
Status
16. Standar PPPoE

Minimal:

Username
Password
Profile
Router
Customer
Service Profile
Last Sync
Status
17. Standar Hotspot

Minimal:

Username
Password
Server
Profile
Customer
Router
Status
18. Standar Voucher

Minimal:

Batch
Voucher Code
Username
Password
QR Code
Expired
Kuota
Harga
Status
19. Standar Monitoring

Semua modul ISP mendukung:

Online
Offline
Last Seen
Traffic
Session
Uptime
Alarm
20. Standar Automation

Mendukung:

Auto Provision
Auto Suspend
Auto Unsuspend
Auto Sync
Auto Backup
Auto Restore
21. Standar Audit

Semua perubahan mencatat:

User
Waktu
Router
Device
Nilai Lama
Nilai Baru
22. Standar Bulk Action

Minimal:

Bulk Activate
Bulk Suspend
Bulk Delete
Bulk Export
Bulk Import
Bulk Sync
Bulk Archive
23. Dashboard ISP

Minimal menampilkan:

Total Customer
PPPoE Active
Hotspot Active
Voucher Active
Router Online
OLT Online
ONU Online
Traffic
Alarm
Provisioning Queue
24. Definition of Done

Modul ISP dianggap selesai apabila:

Mengikuti SSOT
Mengikuti UI Standard
Mengikuti Backend Standard
Mengikuti Module Standard
Mengikuti Component Standard
Mengikuti ISP Standard
Menggunakan Service Layer
Mendukung Queue Job
Mendukung Audit Log
Mendukung Activity Log
Mendukung Soft Delete
Mendukung Restore
Mendukung Import/Export
Mendukung Bulk Action
Mendukung Provisioning
Tidak menghasilkan error Livewire
Tidak menghasilkan error JavaScript
Responsive
Siap digunakan pada operasional ISP
Saran penting

Menurut saya, setelah dokumen ini selesai jangan langsung coding lagi. Agar proyek benar-benar konsisten, urutannya sebaiknya menjadi:

SSOT.md
        ↓
UI-STANDARD.md
        ↓
BACKEND-STANDARD.md
        ↓
MODULE-STANDARD.md
        ↓
COMPONENT-STANDARD.md
        ↓
ISP-STANDARD.md
        ↓
WORKFLOW-STANDARD.md
        ↓
DATABASE-STANDARD.md
        ↓
IMPLEMENTASI MODUL