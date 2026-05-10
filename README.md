# API Remuneration Application
### Sistem Remunerasi Restoran — Backend REST API

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

---

## 📋 Deskripsi Proyek

**API Remuneration Application** adalah backend REST API untuk sistem remunerasi (penggajian) restoran yang dibangun menggunakan Laravel 12. Sistem ini dirancang untuk mengotomatisasi proses penggajian yang sebelumnya dilakukan secara manual menggunakan Microsoft Excel.

### Masalah yang Diselesaikan

Sebelum sistem ini ada, admin restoran harus:
- Membuat slip gaji **satu per satu** dari Excel
- Mengkonversi ke PDF **secara manual**
- Mengirimkan ke karyawan via email **satu per satu**

Proses tersebut memakan waktu **4–6 jam** dan rawan kesalahan manusia.

### Solusi yang Ditawarkan

Dengan sistem ini, admin cukup:
1. Input komponen gaji karyawan
2. Sistem hitung otomatis berdasarkan kategori
3. Generate PDF slip gaji (individu/massal)
4. Kirim ke email karyawan dengan satu klik

---

## Fitur Utama

| Fitur             | Deskripsi |
|---                |---|
| **Autentikasi** | Login/logout berbasis token (Laravel Sanctum) |
| **Role-Based Access** | 3 role: Owner, Kepala Toko, Admin Toko |
| **Master Kategori Gaji** | Kelola kategori & komponen gaji |
| **Manajemen Karyawan** | CRUD data karyawan |
| **Periode Penggajian** | Manajemen periode open/close |
| **Kalkulasi Otomatis** | Hitung gaji berdasarkan kategori + komponen |
| **Generate PDF** | Slip gaji PDF per karyawan atau massal |
| **Distribusi Email** | Kirim slip via email (individu/massal) |
| **Dashboard** | Ringkasan statistik per role |
| **Laporan** | Rekap gaji per periode + export PDF |
| **Activity Log** | Pencatatan aktivitas pengguna |

---

## Teknologi yang Digunakan

| Teknologi | Versi | Kegunaan |
|---|---|---|
| **PHP** | 8.3 | Bahasa pemrograman |
| **Laravel** | 12.x | Framework backend |
| **MySQL** | 8.0 | Database |
| **Laravel Sanctum** | - | Autentikasi API token |
| **DomPDF** | ^2.0 | Generate PDF slip gaji |
| **Resend / SMTP** | - | Distribusi email |

---

## Role & Hak Akses

| Role | Deskripsi | Hak Akses |
|---|---|---|
| **Owner** | Pemilik restoran | Kelola kategori gaji, lihat semua laporan, dashboard owner, activity log |
| **Kepala Toko** | Manager restoran | Kelola karyawan, kelola periode, lihat laporan, dashboard head |
| **Admin Toko** | Staf admin | Input gaji, generate slip, kirim email, dashboard admin |

---

## Struktur Database

```
users               — Data pengguna sistem (owner, head, admin)
salary_categories   — Kategori & komponen gaji
employees           — Data karyawan
payroll_periods     — Periode penggajian
salary_slips        — Slip gaji per karyawan per periode
email_histories     — Riwayat pengiriman email
activity_logs       — Log aktivitas pengguna
```

---

## Cara Instalasi

### Prasyarat
- PHP >= 8.3
- Composer
- MySQL 8.0
- Git

### Langkah Instalasi

**1. Clone Repository**
```bash
git clone https://github.com/farhanfauzanazima/apiremunerationapplication.git
cd apiremunerationapplication
```

**2. Install Dependencies**
```bash
composer install
```

**3. Salin File Environment**
```bash
cp .env.example .env
```

**4. Generate Application Key**
```bash
php artisan key:generate
```

**5. Konfigurasi Database**

Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apiselfserviceapplication
DB_USERNAME=root
DB_PASSWORD=
```

**6. Konfigurasi Email**

Pilih salah satu driver (lihat bagian [Konfigurasi Email](#-konfigurasi-email)):
```env
# SMTP Gmail (default)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_digit_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"
```

**7. Jalankan Migration & Seeder**
```bash
php artisan migrate
php artisan db:seed
```

**8. Buat Storage Link**
```bash
php artisan storage:link
```

**9. Jalankan Server**
```bash
php artisan serve
```

API dapat diakses di: `http://localhost:8000`

---

## 📧 Konfigurasi Email

Proyek ini mendukung dua email driver yang bisa di-switch dengan mudah hanya melalui file `.env`.

### SMTP Gmail (Development / Skala Kecil)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_digit_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"

# MAIL_MAILER=resend
# RESEND_API_KEY=re_xxxxxxxxxxxxxxxx
```

### Resend (Production / Skala Besar)
```env
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.gmail.com
# MAIL_PORT=465
# MAIL_USERNAME=your_gmail@gmail.com
# MAIL_PASSWORD=your_16_digit_app_password
# MAIL_ENCRYPTION=ssl

MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxxxxxxxxx
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> Setelah mengganti driver, jalankan: `php artisan config:clear`

#### Cara Mendapatkan Gmail App Password
1. Buka [myaccount.google.com](https://myaccount.google.com)
2. Pilih **Security** → aktifkan **2-Step Verification**
3. Cari **App Passwords**
4. Pilih **Other** → beri nama "Laravel App"
5. Copy 16 digit password yang digenerate
6. Masukkan ke `MAIL_PASSWORD` di `.env`

---

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| POST | `/api/auth/login` | Login | Public |
| POST | `/api/auth/logout` | Logout | All |
| GET | `/api/auth/profile` | Lihat profil | All |
| PUT | `/api/auth/profile` | Update profil | All |
| POST | `/api/auth/change-password` | Ganti password | All |

### Salary Categories
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/salary-categories` | Lihat semua kategori | Owner |
| POST | `/api/salary-categories` | Tambah kategori | Owner |
| GET | `/api/salary-categories/{id}` | Detail kategori | Owner |
| PUT | `/api/salary-categories/{id}` | Update kategori | Owner |
| DELETE | `/api/salary-categories/{id}` | Hapus kategori | Owner |

### Employees
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/employees` | Lihat semua karyawan | Owner, Head |
| POST | `/api/employees` | Tambah karyawan | Owner, Head |
| GET | `/api/employees/{id}` | Detail karyawan | Owner, Head |
| PUT | `/api/employees/{id}` | Update karyawan | Owner, Head |
| DELETE | `/api/employees/{id}` | Hapus karyawan | Owner, Head |
| GET | `/api/employees/{id}/salary-history` | Riwayat gaji | Owner, Head |

### Payroll Periods
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/payroll-periods` | Lihat semua periode | Owner, Head |
| POST | `/api/payroll-periods` | Tambah periode | Owner, Head |
| GET | `/api/payroll-periods/{id}` | Detail periode | Owner, Head |
| PUT | `/api/payroll-periods/{id}` | Update periode | Owner, Head |
| DELETE | `/api/payroll-periods/{id}` | Hapus periode | Owner, Head |
| PUT | `/api/payroll-periods/{id}/close` | Tutup periode | Owner, Head |
| PUT | `/api/payroll-periods/{id}/reopen` | Buka kembali periode | Owner, Head |

### Salary Slips
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/salary-slips` | Lihat semua slip | All |
| POST | `/api/salary-slips` | Buat slip (single) | All |
| GET | `/api/salary-slips/{id}` | Detail slip | All |
| PUT | `/api/salary-slips/{id}` | Update slip | All |
| DELETE | `/api/salary-slips/{id}` | Hapus slip | All |
| POST | `/api/salary-slips/bulk-generate` | Generate bulk slip | All |
| GET | `/api/salary-slips/{id}/preview-pdf` | Preview PDF | All |
| GET | `/api/salary-slips/{id}/download-pdf` | Download PDF | All |
| POST | `/api/salary-slips/{id}/generate-pdf` | Generate PDF | All |
| POST | `/api/salary-slips/bulk-generate-pdf` | Bulk generate PDF | All |

### Email Distribution
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| POST | `/api/email/send/{slipId}` | Kirim email satu karyawan | All |
| POST | `/api/email/send-bulk` | Kirim email massal | All |
| POST | `/api/email/resend/{slipId}` | Kirim ulang email | All |
| GET | `/api/email/history` | Riwayat pengiriman | All |
| GET | `/api/email/history/{slipId}` | Riwayat per slip | All |

### Dashboard
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/dashboard/owner` | Dashboard owner | Owner |
| GET | `/api/dashboard/head` | Dashboard kepala toko | Owner, Head |
| GET | `/api/dashboard/admin` | Dashboard admin | All |

### Reports
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/reports/salary-summary` | Rekap gaji per periode | Owner, Head |
| GET | `/api/reports/salary-summary/export-pdf` | Export laporan PDF | Owner, Head |
| GET | `/api/reports/employee/{id}` | Laporan per karyawan | Owner, Head |
| GET | `/api/reports/statistics` | Statistik tren gaji | Owner, Head |

### Activity Logs
| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/api/activity-logs` | Lihat semua log | Owner |
| GET | `/api/activity-logs/{id}` | Detail log | Owner |

---

## Formula Perhitungan Gaji

```
Total Gaji = Gaji Pokok
           + Tunjangan
           + Bonus
           - (Jumlah Terlambat × Potongan per Keterlambatan)
           - Potongan Tambahan
```

### Kategori Gaji Default (Seeder)

| Kategori | Gaji Pokok | Tunjangan | Potongan Terlambat |
|---|---|---|---|
| Kategori 1 | Rp 3.000.000 | Rp 500.000 | Rp 50.000/x |
| Kategori 2 | Rp 2.500.000 | Rp 300.000 | Rp 35.000/x |
| Kategori 3 | Rp 2.000.000 | Rp 200.000 | Rp 25.000/x |
| Magang | Rp 1.000.000 | Rp 100.000 | Rp 15.000/x |

---

## Default User (Seeder)

| Role | Email | Password |
|---|---|---|
| Owner | owner@resto.com | password123 |
| Kepala Toko | head@resto.com | password123 |
| Admin Toko | admin@resto.com | password123 |

> Ganti password default setelah instalasi pertama!

---

## Struktur Folder

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── SalaryCategoryController.php
│   │   ├── EmployeeController.php
│   │   ├── PayrollPeriodController.php
│   │   ├── SalarySlipController.php
│   │   ├── EmailController.php
│   │   ├── DashboardController.php
│   │   ├── ReportController.php
│   │   └── ActivityLogController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── Auth/
│       ├── SalaryCategory/
│       ├── Employee/
│       ├── PayrollPeriod/
│       └── SalarySlip/
├── Mail/
│   └── SalarySlipMail.php
├── Models/
│   ├── User.php
│   ├── SalaryCategory.php
│   ├── Employee.php
│   ├── PayrollPeriod.php
│   ├── SalarySlip.php
│   ├── EmailHistory.php
│   └── ActivityLog.php
└── Services/
    ├── SalaryCalculationService.php
    ├── PDFService.php
    ├── EmailService.php
    └── ActivityLogService.php

resources/views/
├── pdf/
│   ├── salary-slip.blade.php
│   └── salary-report.blade.php
└── emails/
    └── salary-slip.blade.php

database/
├── migrations/
└── seeders/
    ├── UserSeeder.php
    ├── SalaryCategorySeeder.php
    ├── EmployeeSeeder.php
    └── PayrollPeriodSeeder.php
```

---

## Git Branch Strategy

| Branch | Deskripsi |
|---|---|
| `main` | Branch utama, production-ready |
| `feature/authentication-rbac` | Autentikasi & role-based access |
| `feature/salary-category` | Master data kategori gaji |
| `feature/employee-management` | Manajemen karyawan |
| `feature/payroll-period` | Periode penggajian |
| `feature/salary-calculation` | Perhitungan gaji & slip |
| `feature/pdf-generation` | Generate PDF DomPDF |
| `feature/email-distribution` | Distribusi email |
| `feature/dashboard-reports` | Dashboard & laporan |
| `feature/email-driver-flexibility` | Konfigurasi fleksibel SMTP/Resend |

---

## Konvensi Commit

```
feat:     Fitur baru
fix:      Perbaikan bug
config:   Perubahan konfigurasi
refactor: Refactoring kode
docs:     Perubahan dokumentasi
test:     Penambahan test
```

---

## Perintah Artisan yang Sering Digunakan

```bash
# Jalankan server development
php artisan serve

# Jalankan semua migration
php artisan migrate

# Jalankan ulang migration + seeder
php artisan migrate:fresh --seed

# Jalankan seeder tertentu
php artisan db:seed --class=UserSeeder

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Lihat semua route API
php artisan route:list --path=api

# Buat storage link
php artisan storage:link
```

---

## Catatan Penting

- File `.env` **tidak di-commit** ke GitHub, gunakan `.env.example` sebagai template
- Pastikan folder `storage/app/public/salary-slips` memiliki permission write
- Gunakan **App Password** Gmail, bukan password akun utama
- Untuk production, gunakan **Resend** dengan domain terverifikasi untuk pengiriman massal yang lebih stabil
- Default password seeder `password123` wajib diganti setelah instalasi

---

## Developer

**Farhan Fauzan Azima**
- GitHub: [@farhanfauzanazima](https://github.com/farhanfauzanazima)
- Repository: [apiremunerationapplication](https://github.com/farhanfauzanazima/apiremunerationapplication)

---

## Lisensi

Proyek ini dibuat untuk keperluan pengembangan sistem remunerasi restoran.

---

*Dibuat dengan ❤️ menggunakan Laravel 12*
