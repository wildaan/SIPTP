# SIPTP - Sistem Informasi Pengajuan Transaksi Pengeluaran

SIPTP adalah aplikasi berbasis web yang digunakan untuk mengelola pengajuan transaksi pengeluaran dengan sistem persetujuan (approval) berbasis **threshold nominal dan kategori** (dengan mekanisme bypass otomatis ke approver tertentu sesuai kondisi), serta pengecekan budget secara otomatis.

---

## Cara Instalasi

1. **Clone Repository & Install Dependencies**
   ```bash
   git clone <url-repository>
   cd SIPTP
   composer install
   ```

2. **Konfigurasi Environment**
   Copy file `.env.example` menjadi `.env`, lalu generate application key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Setup Database**
   Buat database kosong di MySQL (misal bernama `lavanaya`), lalu sesuaikan konfigurasi di file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lavanaya
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Konfigurasi Email Notifikasi**
   Secara default, email menggunakan driver `log` untuk mempermudah testing (email tidak benar-benar dikirim, tapi isinya bisa dilihat di `storage/logs/laravel.log`).
   ```env
   MAIL_MAILER=log
   ```
   Jika ingin menguji pengiriman email sungguhan, ganti `MAIL_MAILER` menjadi `smtp` dan isi kredensial SMTP (misal Gmail App Password atau Mailtrap) pada variable `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`.

5. **Migrate & Seed Database**
   Jalankan perintah berikut untuk membuat semua tabel dan mengisi data dummy:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Storage Link**
   Buat symlink agar file upload (seperti dokumen pengajuan dan bukti transfer) bisa diakses secara publik:
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Aplikasi**
   Jalankan server aplikasi:
   ```bash
   php artisan serve
   ```
   Buka `http://localhost:8000` di browser.

   Jika ingin notifikasi email diproses lewat queue (bukan langsung/synchronous), jalankan juga queue worker di terminal terpisah:
   ```bash
   php artisan queue:work
   ```

---

## Akun Default untuk Testing

Setelah menjalankan seeder, gunakan akun berikut untuk mencoba login sebagai berbagai role:

| Role | Email | Password | Keterangan |
|---|---|---|---|
| Staff | staff@test.com | password | - |
| SPV | spv@test.com | password | - |
| Manager | manager@test.com | password | - |
| Direktur | direktur@test.com | password | - |
| Finance | finance@test.com | password | - |
| Admin | admin@test.com | password | Memiliki hak akses Kelola User (`users_is_admin = 1`) |

*(Role "Admin" tidak dibuat sebagai entitas role terpisah di tabel `roles`, melainkan di-handle via flag `users_is_admin = 1` pada tabel `users`, agar tidak bentrok dengan logic threshold-based routing yang berbasis `roles_code`.)*

---

## Dokumentasi API (Postman)

Dokumentasi API tersedia dalam bentuk **Postman Collection** di dalam folder `docs/`:
- `docs/SIPTP.postman_collection.json` (Semua endpoint)
- `docs/SIPTP.postman_environment.json` (Environment variables)

Silakan import kedua file tersebut ke Postman. **Pastikan melakukan request Login terlebih dahulu** (di folder Auth) dengan Postman Interceptor/Cookie Jar aktif agar cookie session tersimpan untuk request berikutnya.

*(Catatan: Modul Dashboard tidak memiliki API endpoint JSON terpisah karena langsung di-render melalui Blade view.)*

---

## Fitur Tambahan (Nilai Plus)

Berikut fitur tambahan di luar requirement minimum yang sudah diimplementasikan:

- [x] **Email Notification** — notifikasi otomatis di setiap perubahan status pengajuan (submit, approve/reject per tier approval, masuk ke Finance, hingga status akhir Paid/Rejected)
- [x] **API Endpoint** — dokumentasi lengkap tersedia dalam bentuk Postman Collection (lihat bagian "Dokumentasi API" di atas)
- [x] Dashboard Statistik
- [x] Activity Log
- [x] Export PDF
- [x] Export Excel
- [x] Audit Trail
- [x] Multi File Upload

---

## Relasi Antar Tabel

- `users.users_roles_uuid` → `roles.roles_uuid`
- `submissions.submissions_user_uuid` → `users.users_uuid`
- `submissions.submissions_category_uuid` → `categories.categories_uuid`
- `approvals.approvals_submissions_uuid` → `submissions.submissions_uuid`
- `approvals.approvals_user_uuid` → `users.users_uuid`
- `approvals.approvals_roles_uuid` → `roles.roles_uuid`
- `budgets.budgets_categories_uuid` → `categories.categories_uuid`
- `payments.payments_submissions_uuid` → `submissions.submissions_uuid`
- `payments.payments_finance_user_uuid` → `users.users_uuid`
- `user_activity.user_activity_user_uuid` → `users.users_uuid`

---

## Penjelasan Asumsi & Workflow Approval

Sistem menggunakan pendekatan **Threshold-Based Routing** (berdasarkan nilai nominal dan kategori pengajuan) seperti yang dijabarkan pada flowchart proses bisnis. Pada seluruh jalur di bawah, SPV/Manager yang tidak disebutkan dalam routing berarti **di-bypass sepenuhnya** (tidak melakukan approval action sama sekali untuk pengajuan tersebut, murni dilewati oleh sistem):

1. **Kategori PO Produk**
   - Routing: **Staff -> Direktur -> Finance** (SPV & Manager dilewati/bypass sepenuhnya, berapa pun nilainya).
   - *Asumsi*: Sesuai flowchart, untuk kategori "PO Produk", tidak ada *budget checking* di titik submit (saat create pengajuan).

2. **Nominal <= Rp 5.000.000 (Selain PO Produk)**
   - Routing: **Staff -> SPV -> Finance**.
   - Pengecekan Budget: Dilakukan di titik ini (saat Staff submit). Jika budget tersedia, status menjadi *Waiting SPV Approval* (3). Jika tidak, status langsung *Rejected* (8).

3. **Nominal > Rp 5.000.000 s/d Rp 10.000.000 (Selain PO Produk)**
   - Routing: **Staff -> Manager -> Finance** (SPV dilewati/bypass).
   - Pengecekan Budget: Dilakukan di titik ini (saat submit). Jika budget tersedia, status menjadi *Waiting Manager Approval* (4). Jika tidak, status langsung *Rejected* (8).

4. **Nominal > Rp 10.000.000 (Selain PO Produk)**
   - Routing: **Staff -> Direktur -> Finance** (SPV & Manager dilewati/bypass).
   - *Asumsi*: Sama seperti PO Produk, tidak ada *budget checking* di titik submit.

**Budget Check Final**

Meskipun pengajuan kategori PO Produk atau nominal >10jt tidak dicek budget-nya di titik submit, **selalu ada pengecekan saldo final di tahap Finance** (saat Finance melakukan aksi "Proses Pembayaran"). Jika saat eksekusi pembayaran saldo budget kategori sudah tidak mencukupi, tombol "Proses Pembayaran" otomatis ter-disable (divalidasi juga di backend) dan Finance hanya dapat menolak pengajuan tersebut (status menjadi *Rejected*).

**Catatan Tambahan**

- Fitur Email Notification hanya mencakup siklus hidup pengajuan (submit → approval per tier → masuk Finance → Paid/Rejected), tidak mencakup notifikasi sistem lain seperti notifikasi login atau notifikasi CRUD user.
- Reject oleh approver tier manapun (SPV/Manager/Direktur) menghentikan workflow secara permanen — status langsung menjadi *Rejected*, tidak lanjut ke tier berikutnya.