# SIPTP - Sistem Informasi Pengajuan Transaksi Pengeluaran

SIPTP adalah aplikasi berbasis web yang digunakan untuk mengelola pengajuan transaksi pengeluaran dengan sistem persetujuan (approval) berjenjang dan pengecekan budget secara otomatis.

---

## Cara Instalasi

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

Silahkan import kedua file tersebut ke Postman. **Pastikan melakukan request Login terlebih dahulu** (di folder Auth) dengan Postman Interceptor/Cookie Jar aktif agar cookie session tersimpan untuk request berikutnya.

*(Catatan: Modul Dashboard tidak memiliki API endpoint JSON terpisah karena langsung di-render melalui Blade view.)*

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

---
**Budget Check Final**

Meskipun pengajuan > 10jt atau PO Produk tidak dicek budget-nya di awal, **selalu ada pengecekan saldo final di tahap Finance** (saat Finance melakukan action "Pay"). Jika saat eksekusi pembayaran saldo budget kategori sudah tidak mencukupi, Finance akan menolak pengajuan tersebut secara sistem (status menjadi Rejected).

