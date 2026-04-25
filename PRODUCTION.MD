# PRD.md — Product Requirements Document

**Project:** Art Marketplace Platform

**Versi dokumen:** 1.0
**Status:** Full Scope Reference

**Stack:** Backend
| Layer       | Teknologi                                                     |
| ----------- | ------------------------------------------------------------- |
| Framework   | **Laravel**                                                |
| Auth        | **Laravel Breeze** (Inertia + React stack)                    |
| Role        | **Spatie Laravel Permission**                                 |
| Database    | **MySQL/MariaDB** (satu database, tidak multi-DB)             |
| Queue       | **Database driver** + cron job (nanti VPS → Redis)            |
| Cache       | **File driver** (nanti VPS → Redis)                           |
| Session     | **Database driver** (nanti VPS → Redis)                       |
| Search      | **Laravel Scout + Database driver** (nanti VPS → Meilisearch) |
| Upload      | **Spatie Laravel Medialibrary**                               |
| Image       | **Intervention Image Laravel**                                |
| Slug        | **Spatie Laravel Sluggable**                                  |
| Tags        | **Spatie Laravel Tags**                                       |
| Settings    | **Spatie Laravel Settings**                                   |
| Audit       | **Spatie Laravel Activity Log**                               |
| Backup      | **Spatie Laravel Backup**                                     |
| Sitemap     | **Spatie Laravel Sitemap**                                    |
| Cart        | **Darryldecode Cart**                                         |
| Payment     | **Midtrans PHP**                                              |
| Export      | **Maatwebsite Excel + Barryvdh DomPDF**                       |
| Admin Panel | **Filament 3** (atau Livewire manual)                         |



Frontend — Hybrid
| Area                 | Teknologi                                                |
| -------------------- | -------------------------------------------------------- |
| **Buyer/Publik**     | **Inertia.js + React+ Vite**                         |
| **Seller Dashboard** | **Livewire + Blade + TailwindCSS**                     |
| **Admin Panel**      | **Filament ** atau **Livewire + Blade**                 |
| Pagination           | **TanStack**            |
| Routing Buyer        | **Inertia `<Link>`** (bukan React Router)                |
| Routing Seller/Admin | **Livewire `wire:navigate`** atau `<a>`                  |
| Meta Tag SEO         | **Inertia `<Head>`** (native, tanpa `artesaos/seotools`) |
| Realtime             | **SSE (Server-Sent Events)** — HTTP based                |
| Icons                | **Lucide React**   |


Infrastructure

wajib berdasarkan .env agar mudah switch dari web hostinger ke VPS


Shared Hosting Constraints (Batasan yang Harus Diterima)
Redis: tidak tersedia. Solusi: file cache + database queue + database session.
WebSocket / Reverb: tidak tersedia. Solusi: SSE (Server-Sent Events) HTTP-based.
Supervisor / Daemon: tidak tersedia. Solusi: cron job tiap menit queue:work --stop-when-empty.
Node.js Runtime: tidak tersedia. Solusi: Vite build di local/dev, upload public/build/ hasil production.
Meilisearch: tidak tersedia. Solusi: Scout database driver + FULLTEXT index MySQL.
PostgreSQL: tidak tersedia. Solusi: MySQL/MariaDB.
Root Access: tidak tersedia. Solusi: semua konfigurasi via .env dan panel hosting.
Port Terbuka: tidak tersedia. Reverb butuh port jadi tidak bisa. SSE pakai port 80/443.


perhatikan ini : 
- Env-Based Flexible Architecture (Wajib) : Semua service yang memiliki alternatif driver harus di-abstract melalui konfigurasi `.env`. Migrasi dari Hostinger Shared ke VPS hanya dengan mengubah nilai `.env`, **tanpa perubahan kode bisnis sama sekali**.
-Race Condition & Data Integrity (Wajib) : Semua operasi yang mengubah data finansial atau stok wajib menggunakan proteksi race condition.
= Optimization & Bottleneck Prevention (Wajib)

---

## 1. Gambaran Umum

Platform marketplace khusus produk seni dengan konsep mirip e-commerce marketplace, fokus pada kategori:

- Lukisan
- Patung
- Relief
- Kerajinan seni
- Dekorasi artistik
- Karya custom
- Produk seni lainnya

### Role Utama

- **Buyer / User** — datang untuk melihat, mencari, membeli produk
- **Seller / Penjual** — memiliki toko sendiri di dalam platform
- **Admin / Owner Platform** — pemilik dan pengelola penuh website

### Fitur Tambahan Platform (di luar jual beli)

- Halaman berita / artikel / news
- Sistem iklan / promosi seller
- Laporan keuangan
- Referral seller
- Payment gateway
- Withdraw seller

---

## 2. Tujuan Sistem

- Seller membuka toko dan menjual karya seni
- Buyer membeli produk dengan mudah (login maupun tanpa login)
- Admin mengatur seluruh ekosistem platform
- Seller mempromosikan produk lewat sistem iklan
- Platform memiliki monetisasi dari biaya iklan, komisi, atau biaya layanan

---

## 3. Role & Permission Detail

### Guest

- Lihat katalog produk
- Search & filter produk
- Lihat detail produk
- Guest checkout (tanpa login)
- Baca berita / artikel

### Buyer (login)

Semua hak Guest, ditambah:

- Simpan alamat pengiriman
- Lihat histori order
- Wishlist produk
- Tracking status pesanan
- Pakai voucher / promo khusus member
- Akun lebih praktis untuk pembelian berikutnya

### Seller

- Registrasi sebagai seller
- Membuat dan mengatur profil toko
- Upload produk (CRUD)
- Kelola stok
- Lihat pesanan masuk
- Update status pesanan
- Melihat pendapatan
- Ajukan withdraw
- Lihat laporan penjualan
- Beli iklan / promosi
- Menggunakan sistem referral seller

### Admin

- Mengelola semua user
- Mengelola seller
- Mengelola semua produk
- Memoderasi konten
- Melihat semua transaksi
- Mengelola berita / news
- Mengatur banner slider homepage
- Mengatur iklan seller
- Mengatur komisi / fee platform
- Mengatur promo global
- Approval withdraw seller
- Export laporan keuangan
- Mengatur kategori dan filter

---

## 4. Modul Utama

### 4.1 Homepage / Landing Page 

Homepage harus interaktif dan modern.

**Komponen:**

- hero
- ads slider/banner (promo, karya unggulan, iklan seller) 
- Search bar
- Filter cepat
- Kategori art
- Produk terbaru
- Produk terlaris / rekomendasi
- Toko unggulan
- Berita / artikel terbaru
- Iklan sponsor
- Promo / voucher section


(that's all old, no needed, and already build with current landing page)

---


### 4.2 Katalog Produk

Halaman listing semua produk art.

**Fitur:**

- Grid / list view toggle
- Search produk
- Filter produk
- Sorting
- Pagination 

**Filter:**

- Kategori
- Harga (min-max)
- Jenis karya
- Seller / toko
- Material
- Ukuran
- Lokasi seller
- Ready stock / preorder
- Promo / tidak
- Rating toko

**Sorting:**

- Terbaru
- Harga termurah
- Harga tertinggi
- Paling populer
- Paling banyak dibeli

---

### 4.3 Detail Produk

**Informasi:**

- Nama produk
- Foto gallery / slider gambar
- Video (opsional)
- Deskripsi produk
- Harga
- Stok
- Kategori
- Dimensi / ukuran
- Material
- Nama seller / toko
- Rating / ulasan
- Estimasi pengiriman
- Promo aktif
- Tombol tambah ke keranjang
- Tombol beli sekarang

**Tambahan:**

- Produk terkait
- Produk lain dari seller yang sama
- Share ke sosial media

---

### 4.4 Cart & Checkout

**Cart:**

- Tambah / hapus produk
- Update qty
- Catatan untuk seller
- Estimasi total belanja

**Checkout — Tanpa Login (Guest):**

Buyer isi: nama lengkap, email, nomor HP, alamat lengkap, kota/provinsi, kode pos, catatan pesanan.

**Checkout — Dengan Login:**

Data checkout otomatis terisi dari akun.

**Fitur checkout:**

- Pilih alamat
- Pilih kurir / pengiriman
- Pilih metode pembayaran
- Apply voucher / promo
- Ringkasan pesanan
- Konfirmasi pembayaran

---

### 4.5 Payment Gateway

**Opsi pembayaran:**

- Transfer bank virtual account
- E-wallet
- QRIS
- Kartu kredit / debit
- Retail payment (jika diperlukan)

**Flow:**

1. Buyer checkout
2. Sistem membuat invoice
3. Sistem kirim request ke payment gateway
4. Buyer bayar
5. Payment gateway kirim callback / webhook
6. Status pembayaran diupdate otomatis
7. Order diproses seller / admin

**Status pembayaran:** `pending` → `paid` → `failed` / `expired` / `refunded`

---

### 4.6 Seller Store / Toko Seller

**Data toko:**

- Nama toko
- Logo toko
- Banner toko
- Deskripsi toko
- Alamat / lokasi
- Kontak
- Social media
- Rating toko

**Fitur toko:**

- Halaman profil toko publik
- List produk toko
- Promo toko
- Ulasan toko
- Performa toko

---

### 4.7 Dashboard Seller

**Menu utama:**

- Dashboard overview
- Manajemen produk
- Manajemen pesanan
- Manajemen stok
- Laporan penjualan
- Saldo & withdraw
- Pengaturan toko
- Iklan / promosi
- Referral
- Notifikasi

**Ringkasan dashboard:**

- Total penjualan
- Total order
- Order pending
- Produk aktif
- Saldo tersedia
- Jumlah withdraw
- Performa iklan

---

### 4.8 Withdraw Seller

**Data rekening seller:** nama bank, nomor rekening, nama pemilik rekening.

**Flow withdraw:**

1. Seller input data rekening
2. Seller ajukan withdraw
3. Sistem validasi saldo tersedia
4. Admin review → approve / reject
5. Admin transfer manual atau semi-otomatis
6. Status withdraw diupdate

**Status:** `pending` → `approved` → `rejected` / `paid`

**Aturan:**

- Minimal withdraw (dikonfigurasi admin)
- Biaya admin withdraw (dikonfigurasi admin)
- Jadwal proses withdraw (dikonfigurasi admin)
- Saldo yang bisa ditarik hanya dari order selesai

---

### 4.9 Dashboard Admin

**Menu:**

- Dashboard statistik
- Manajemen user
- Manajemen seller
- Manajemen produk
- Manajemen kategori
- Manajemen order
- Manajemen pembayaran
- Manajemen withdraw
- Manajemen berita / news
- Manajemen banner & slider
- Manajemen iklan
- Manajemen promo / voucher
- Laporan keuangan
- Pengaturan sistem
- Referral management

**Statistik:**

- Total GMV
- Total transaksi
- Total seller
- Total buyer
- Total produk
- Total komisi
- Withdraw pending
- Pendapatan dari iklan
- Performa promo

---

### 4.10 News / Artikel / Berita

Dikelola dari dashboard admin.

**Fitur:**

- Buat, edit, hapus artikel
- Publish / unpublish
- Kategori artikel
- Tag
- Thumbnail
- SEO slug

**Jenis konten:**

- Berita seni
- Event pameran
- Edukasi art
- Profil seniman
- Tips koleksi karya seni

---

### 4.11 Iklan Seller

**Bentuk iklan:**

- Banner di homepage
- Produk tampil di atas / featured
- Toko unggulan
- Slot khusus di landing page

**Flow:**

1. Seller pilih paket iklan
2. Seller bayar
3. Admin approve / sistem aktif otomatis
4. Iklan tayang sesuai durasi

**Admin bisa atur:** posisi, durasi, harga, kuota slot, status aktif/nonaktif.

---

### 4.12 Promo & Voucher

**Sumber promo:** dari admin atau dari seller.

**Jenis promo:**

- Diskon persen
- Diskon nominal
- Free ongkir
- Minimum pembelian
- Promo member / login only

---

### 4.13 Referral Seller

**Konsep:**

- Seller punya kode referral unik
- Seller baru daftar menggunakan kode referral
- Seller pengundang dapat benefit tertentu

**Benefit (dikonfigurasi admin):**

- Bonus saldo
- Potongan biaya iklan
- Komisi referral
- Badge tertentu

**Admin bisa atur:** aturan referral, reward, masa berlaku, syarat pencairan.

---

### 4.14 Laporan Keuangan

**Laporan Seller:**

- Total penjualan
- Transaksi selesai & pending
- Biaya admin
- Saldo masuk
- Withdraw
- Laba bersih estimasi
- Export laporan

**Laporan Admin:**

- Total transaksi platform
- Komisi platform
- Income iklan
- Payout seller
- Refund
- Laporan periodik
- Export laporan

**Format export:** PDF, Excel / CSV

---

## 5. Flow Sistem Utama

### 5.1 Flow Buyer Tanpa Login

1. Buyer buka website
2. Search / filter produk
3. Buka detail produk
4. Tambah ke cart / beli sekarang
5. Checkout — isi data lengkap
6. Pilih pembayaran
7. Bayar
8. Order dibuat
9. Seller proses pesanan

### 5.2 Flow Buyer Dengan Login

1. Login / register
2. Pilih produk
3. Cart / checkout
4. Gunakan alamat tersimpan
5. Gunakan promo jika ada
6. Bayar
7. Order diproses
8. Lihat histori order

### 5.3 Flow Seller

1. Daftar akun seller
2. Lengkapi data toko
3. Input rekening
4. Upload produk
5. Produk tampil di marketplace
6. Buyer melakukan order
7. Seller menerima order
8. Seller proses & kirim pesanan
9. Order selesai
10. Saldo masuk ke wallet seller
11. Seller ajukan withdraw

### 5.4 Flow Admin

1. Admin login
2. Monitoring seller, buyer, order, pembayaran
3. Approval withdraw
4. Kelola banner / slider
5. Kelola artikel / news
6. Kelola iklan seller
7. Lihat laporan keuangan
8. Atur promo dan kebijakan platform

---

## 6. Phase Roadmap

### Phase 1 — MVP (Target: Demo Event)

Lihat `DEMO.md` untuk checklist detail.

- Homepage interaktif
- Katalog produk + search & filter
- Detail produk
- Cart & checkout (guest + login)
- Payment gateway
- Dashboard seller dasar
- Dashboard admin dasar
- Withdraw seller
- News / artikel
- Laporan sederhana

### Phase 2

- Wishlist
- Review / rating produk & toko
- Referral seller
- Sistem iklan seller
- Promo & voucher lanjutan
- Analytics dashboard lebih lengkap
- Export laporan lebih kompleks

### Phase 3

- Live chat buyer-seller
- Auction / lelang art
- Multi-language
- Mobile app ( pwa)
- AI recommendation produk seni (optional)

---

## 7. Hal Penting yang Harus Ditentukan (Open Questions)

### Model Bisnis

- Apakah platform ambil komisi per transaksi?
- Apakah seller bayar biaya langganan?
- Apakah monetisasi utama dari iklan?
- Apakah ada biaya withdraw?

### Shipping

- Apakah platform integrasi ongkir otomatis?
- Apakah seller input resi manual?
- Apakah ada multi-kurir?

### Payment

- Payment gateway apa yang dipakai? (default: Midtrans)
- Apakah admin pegang escrow sementara?
- Kapan saldo seller cair?

### Moderasi

- Apakah semua produk seller perlu approval admin?
- Apakah artikel harus full dari admin saja?
- Apakah iklan seller harus disetujui admin?

### Refund / Cancel

- Apakah buyer bisa cancel?
- Kapan dana dikembalikan?
- Siapa yang approve refund?