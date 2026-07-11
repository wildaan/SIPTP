# SIPTP - Sistem Informasi Pengajuan Transaksi Pengeluaran

SIPTP adalah aplikasi berbasis web yang digunakan untuk mengelola pengajuan transaksi pengeluaran (reimbursement, cash advance, PO, dll) dengan sistem persetujuan (approval) berjenjang dan pengecekan budget secara otomatis.

---

## Cara Instalasi

Ikuti langkah-langkah berikut untuk menjalankan project ini di lingkungan lokal Anda:

1. **Clone Repository & Install Dependencies**
   ```bash
   git clone <url-repository>
   cd lavanaya
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
   *Jika ingin mengetes pengiriman email sungguhan menggunakan Mailtrap atau Gmail SMTP, ubah `MAIL_MAILER=smtp` dan lengkapi `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, dan `MAIL_PASSWORD` sesuai provider Anda.*

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

   *(Opsional) Jika Anda mengubah pengaturan Queue menjadi selain `database` (misal menggunakan Redis) atau ingin memproses email secara sinkron via queue worker lokal, Anda bisa menjalankan:*
   ```bash
   php artisan queue:work
   ```

---

## Akun Default untuk Testing

Setelah menjalankan seeder, gunakan akun berikut untuk mencoba login sebagai berbagai role:

| Role | Email | Password | Keterangan |
|---|---|---|---|
| Staff | staff@test.com | password | Memiliki hak akses Admin (Kelola User) |
| SPV | spv@test.com | password | - |
| Manager | manager@test.com | password | - |
| Direktur | direktur@test.com | password | - |
| Finance | finance@test.com | password | - |

*(Role "Admin" tidak dibuat sebagai entitas role terpisah di tabel roles, melainkan di-handle via flag `users_is_admin = 1` di tabel users untuk menghindari bentrok dengan logika approval berjenjang.)*

---

## Dokumentasi API (Postman)

Dokumentasi API tersedia dalam bentuk **Postman Collection** di dalam folder `docs/`:
- `docs/SIPTP.postman_collection.json` (Semua endpoint)
- `docs/SIPTP.postman_environment.json` (Environment variables)

Silahkan import kedua file tersebut ke Postman Anda. **Pastikan melakukan request Login terlebih dahulu** (di folder Auth) dengan Postman Interceptor/Cookie Jar aktif agar cookie session tersimpan untuk request berikutnya.

*(Catatan: Modul Dashboard tidak memiliki API endpoint JSON terpisah karena langsung di-render melalui Blade view.)*

---

## ERD / Relasi Antar Tabel

Struktur database SIPTP menggunakan tabel dengan penamaan kolom ber-prefix nama tabel. Berikut relasi utamanya:

1. **`users` -> `roles`**: Relasi Many-to-One melalui kolom `users_roles_uuid` yang me-referensi `roles_uuid`.
2. **`submission` -> `users`**: Relasi Many-to-One melalui kolom `submissions_user_uuid` yang me-referensi `users_uuid` (siapa yang mengajukan).
3. **`submission` -> `categories`**: Relasi Many-to-One melalui kolom `submissions_category_uuid` yang me-referensi `categories_uuid`.
4. **`approvals` -> `submission` & `users`**: Relasi Many-to-One. Menyimpan jejak persetujuan (step, status, catatan) untuk setiap pengajuan dari tiap user/role approver.
5. **`payments` -> `submission` & `users`**: Relasi One-to-One ke submission, menyimpan data pembayaran oleh user Finance (`payments_finance_user_uuid`).
6. **`document_submission` -> `submission`**: Relasi Many-to-One. Menyimpan satu atau lebih file attachment pengajuan.
7. **`budgets` -> `categories`**: Relasi Many-to-One. Menyimpan pagu anggaran tahunan per kategori (`budgets_categories_uuid`).
8. **`user_activity` -> `users`**: Audit trail yang mencatat semua aksi sistem yang dilakukan oleh user (`user_activity_user_uuid`).

---

## Penjelasan Asumsi & Workflow Approval

Sistem menggunakan pendekatan **Threshold-Based Routing** (berdasarkan nilai nominal dan kategori pengajuan) seperti yang dijabarkan pada flowchart proses bisnis:

1. **Kategori PO Produk**
   - Nilai berapa pun: **Staff -> SPV -> Manager -> Direktur -> Finance**.
   - *Asumsi*: Sesuai flowchart, untuk kategori "PO Produk", tidak ada *budget checking* di awal (saat create pengajuan).

2. **Nominal <= Rp 5.000.000 (Selain PO Produk)**
   - Routing: **Staff -> SPV -> Finance**.
   - Pengecekan Budget: Dilakukan di awal (saat Staff submit). Jika budget tersedia, status menjadi *Waiting SPV Approval* (3).

3. **Nominal > Rp 5.000.000 s/d Rp 10.000.000 (Selain PO Produk)**
   - Routing: **Staff -> Manager -> Finance**.
   - Pengecekan Budget: Dilakukan di awal. Jika budget tersedia, status menjadi *Waiting Manager Approval* (4). (Tidak melewati SPV).

4. **Nominal > Rp 10.000.000 (Selain PO Produk)**
   - Routing: **Staff -> Direktur -> Finance**.
   - *Asumsi*: Sama seperti PO Produk, tidak ada *budget checking* di awal (saat submit).

**Pengamanan Ekstra (Budget Check Final)**
Meskipun pengajuan > 10jt atau PO Produk tidak dicek budget-nya di awal, **selalu ada pengecekan saldo final di tahap Finance** (saat Finance melakukan action "Pay"). Jika saat eksekusi pembayaran saldo budget kategori sudah tidak mencukupi, Finance akan menolak pengajuan tersebut secara sistem (status menjadi Rejected).

**Cakupan Email Notification**
Fitur Email Notification difokuskan murni pada siklus hidup pengajuan (Submission Lifecycle) yaitu: saat submit, saat approve/reject di tiap tier, saat pengajuan siap dibayar oleh Finance, dan saat pengajuan selesai dibayar/ditolak Finance. Notifikasi sistem lain (seperti login, update profile, CRUD admin) tidak dikirimkan via email.
