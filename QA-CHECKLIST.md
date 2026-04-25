# QA Checklist Art Market

Dokumen ini adalah source of truth untuk memvalidasi progress Art Market berdasarkan bukti testing, bukan estimasi lisan. Semua item wajib diberi status, PIC, evidence, dan bug link bila gagal.

Last updated: 2026-04-25

## 1. Term & Status

### Status

| Status | Arti | Kapan Dipakai |
|---|---|---|
| Belum Diuji | Item belum pernah dicek oleh QA/tim | Default awal semua item |
| Lulus | Hasil aktual sesuai expected result | Ada evidence atau catatan run command |
| Bug | Hasil aktual tidak sesuai expected result | Wajib isi severity, reproduction, dan bug link |
| Blocked | Tidak bisa diuji karena dependency belum siap | Wajib tulis blocker spesifik |
| N/A | Tidak berlaku untuk scope saat ini | Wajib tulis alasan |

### Severity

| Severity | Arti | Contoh |
|---|---|---|
| P0 | Blocker produksi atau data/uang bisa rusak | Checkout menggandakan order, webhook menggandakan saldo |
| P1 | Flow utama gagal untuk role utama | User tidak bisa checkout, seller tidak bisa update resi |
| P2 | Bug penting tapi ada workaround | Filter katalog salah pada kombinasi tertentu |
| P3 | Polish/UI minor | Spacing kurang rapi, copy typo, hover kurang konsisten |

### Priority

| Priority | Arti |
|---|---|
| Critical | Wajib lulus sebelum demo/client review serius |
| High | Wajib lulus sebelum production candidate |
| Medium | Wajib lulus sebelum production launch |
| Low | Bisa masuk polish pass setelah core stabil |

### Kolom Checklist

| Kolom | Isi |
|---|---|
| ID | Kode unik item QA |
| Required | `Yes` untuk wajib dihitung progress, `No` untuk observasi/non-blocking |
| Priority | Critical/High/Medium/Low |
| Alur Uji | Langkah singkat yang harus dilakukan |
| Expected Result | Hasil yang harus terjadi |
| Status | Belum Diuji/Lulus/Bug/Blocked/N/A |
| PIC | Nama penguji |
| Evidence | Screenshot/video/path/log/command output |
| Bug | Link issue atau ID bug |

## 2. Aturan Wajib: UI-First Testing

Checklist ini dipakai untuk testing UI, UX, fitur, dan bug dari sudut pandang pengguna. Karena itu semua navigasi manual wajib dilakukan lewat UI.

| Aturan | Detail |
|---|---|
| Start point | QA hanya boleh mengetik URL awal homepage `/` di address bar |
| Navigasi | Setelah homepage terbuka, semua perpindahan halaman wajib lewat klik tombol, menu, link, card, CTA, form submit, atau redirect natural |
| Dilarang | Dilarang mengetik route langsung seperti `/katalog`, `/cart`, `/user`, `/seller`, `/admin`, `/produk/{slug}`, dan sejenisnya |
| Jika tidak ada jalur UI | Jangan bypass dengan URL manual; catat sebagai `Bug` karena navigation/discoverability tidak tersedia |
| Expected URL | Route boleh ditulis sebagai expected result untuk memastikan klik UI mengarah ke halaman benar |
| Automated/API test | Command, automated test, webhook, SSE, dan polling boleh diuji via CLI/HTTP client karena bukan navigasi UI pengguna |
| Evidence | Evidence manual wajib berupa screenshot/video dari alur klik atau catatan langkah klik yang bisa direproduksi |

Contoh penulisan alur yang benar:

```text
Mulai dari homepage -> klik tombol Jelajahi Koleksi -> klik salah satu card produk -> klik Tambah ke Keranjang -> klik ikon/cart link -> lanjut checkout.
```

Contoh yang salah:

```text
Buka /katalog langsung dari address bar.
```

## 3. Progress Formula

Progress project tidak lagi memakai angka estimasi seperti 70% tanpa bukti. Progress QA dihitung dari item required yang sudah lulus.

```text
Total Required = semua checklist dengan Required = Yes, kecuali N/A
Total Lulus = semua checklist Required = Yes dan Status = Lulus
QA Progress (%) = (Total Lulus / Total Required) * 100
Bug Rate (%) = (Total Bug / Total Required) * 100
Blocked Rate (%) = (Total Blocked / Total Required) * 100
```

Quality gate:

| Gate | Syarat Minimal |
|---|---|
| Internal Smoke Ready | `php artisan test` lulus, `npm run build` lulus, landing + katalog + checkout smoke lulus |
| Demo Ready | Tidak ada P0/P1 terbuka pada public buyer flow, seller shipment, admin payment/order |
| Production Candidate | Semua Critical dan High required lulus, P2 punya keputusan fix/defer |
| Production Ready | Semua required lulus atau N/A dengan alasan, backup/deploy/checklist monitoring jelas |

## 4. Baseline Commands

Jalankan command ini sebelum manual QA besar. Simpan output sebagai evidence.

| ID | Required | Priority | Command | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| BASE-001 | Yes | Critical | `composer install` | Dependency PHP terpasang tanpa error | Belum Diuji |  |  |  |
| BASE-002 | Yes | Critical | `npm install` | Dependency frontend terpasang tanpa error | Belum Diuji |  |  |  |
| BASE-003 | Yes | Critical | `cp .env.example .env` lalu sesuaikan `.env` lokal | App bisa membaca konfigurasi lokal | Belum Diuji |  |  |  |
| BASE-004 | Yes | Critical | `php artisan key:generate` | `APP_KEY` terisi dan app tidak error key missing | Belum Diuji |  |  |  |
| BASE-005 | Yes | Critical | `php artisan migrate --seed` | Database lokal terbentuk dan seed account tersedia | Belum Diuji |  |  |  |
| BASE-006 | Yes | Critical | `php artisan test` | Semua automated test lulus | Belum Diuji |  |  |  |
| BASE-007 | Yes | Critical | `npm run build` | TypeScript dan Vite production build lulus | Belum Diuji |  |  |  |
| BASE-008 | Yes | High | `php artisan route:list --except-vendor` | Route list tampil, baseline saat dokumen dibuat: 121 routes | Belum Diuji |  |  |  |
| BASE-009 | Yes | High | `php artisan migrate:fresh --seed --env=testing` | Migration dan seeder testing bisa jalan bersih | Belum Diuji |  |  |  |
| BASE-010 | Yes | High | `php artisan queue:work --stop-when-empty` | Queue command dapat dipanggil tanpa fatal error | Belum Diuji |  |  |  |

## 5. Gap Audit Awal Repo

Audit awal ini adalah snapshot dari repo saat checklist dibuat. Status final tetap harus dibuktikan lewat checklist manual dan automated.

| ID | Area | Temuan Awal | Risiko | Status Verifikasi | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|
| GAP-001 | Routes | `php artisan route:list --except-vendor` menampilkan 121 routes | Route sudah banyak, perlu smoke per flow agar tidak ada route mati | Belum Diuji |  |  |  |
| GAP-002 | Automated Test | Test feature untuk auth, commerce, seller, user, chat, notification sudah ada | Coverage ada, tapi belum membuktikan UI polish manual | Belum Diuji |  |  |  |
| GAP-003 | Demo Doc | `DEMO.md` belum ada, tetapi disebut di `PRODUCTION.MD` | Dokumen lama bisa membingungkan tim | Belum Diuji |  |  |  |
| GAP-004 | Admin Users | Filament resources belum terlihat memiliki `UserResource.php` | Admin user management perlu diverifikasi apakah lewat resource lain atau belum dibuat | Belum Diuji |  |  |  |
| GAP-005 | Design Source | `DESIGN-SYSTEM.md` ada, tetapi perlu audit implementasi aktual | UI bisa drift kalau komponen tidak memakai token/focus/design-system | Belum Diuji |  |  |  |
| GAP-006 | Production Backup | README mencatat `spatie/laravel-backup` belum dipasang karena `ext-pcntl` | Backup perlu prosedur shared hosting alternatif | Belum Diuji |  |  |  |

## 6. Test Account & Data Seed

Gunakan akun seed berikut bila `php artisan migrate --seed` berhasil.

| Role | Email | Password | Catatan |
|---|---|---|---|
| Admin | `admin@artmarket.test` | `password` | Login lewat UI, lalu gunakan redirect/menu menuju admin panel |
| Seller | `seller@artmarket.test` | `password` | Login lewat UI, lalu gunakan redirect/menu menuju seller dashboard |
| User | `user@artmarket.test` | `password` | Login lewat UI, lalu gunakan redirect/menu menuju user dashboard |

## 7. Setup & Environment

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| ENV-001 | Yes | Critical | Jalankan app dengan MySQL/MariaDB lokal | Home dapat dibuka tanpa database connection refused | Belum Diuji |  |  |  |
| ENV-002 | Yes | High | Jalankan app dengan SQLite/file session lokal sesuai README | App bisa dibuka tanpa MySQL running | Belum Diuji |  |  |  |
| ENV-003 | Yes | High | Ubah `SESSION_DRIVER=file`, clear config, mulai dari homepage, lalu klik UI ke cart/auth flow | Session tetap berjalan dan tidak error | Belum Diuji |  |  |  |
| ENV-004 | Yes | High | Ubah `CACHE_STORE=file`, clear config, mulai dari homepage, lalu klik CTA/menu menuju katalog | Cache tidak mematahkan page | Belum Diuji |  |  |  |
| ENV-005 | Yes | High | Ubah `QUEUE_CONNECTION=sync`, submit flow yang memicu notification | Job tidak fatal dan flow tetap selesai | Belum Diuji |  |  |  |
| ENV-006 | Yes | Medium | Ubah `QUEUE_CONNECTION=database`, jalankan queue work stop when empty | Job database queue terproses | Belum Diuji |  |  |  |
| ENV-007 | Yes | Medium | Start local server, ketik homepage `/` saja, lalu klik UI menuju katalog, cart, login/register, dan dashboard role | Semua halaman utama bisa dicapai dari UI tanpa mengetik URL lain | Belum Diuji |  |  |  |
| ENV-008 | Yes | Medium | Cek `.env.example` berisi driver penting production/shared hosting | Cache, queue, session, payment, mail, realtime mudah diswitch | Belum Diuji |  |  |  |

## 8. Auth & Role Flow

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| AUTH-001 | Yes | Critical | Mulai dari homepage, klik CTA/menu/card publik menuju katalog, detail produk, dan artikel | Halaman publik bisa dicapai tanpa login lewat UI | Belum Diuji |  |  |  |
| AUTH-002 | Yes | Critical | Mulai dari homepage, klik tombol/link daftar pembeli/register yang tersedia | Akun user dibuat, role user/buyer benar, redirect ke area user atau flow login yang jelas | Belum Diuji |  |  |  |
| AUTH-003 | Yes | Critical | Mulai dari homepage, klik `Jual Karya` atau CTA seller register | Akun seller dan profil toko dibuat, redirect sesuai flow seller | Belum Diuji |  |  |  |
| AUTH-004 | Yes | Critical | Mulai dari homepage, buka form login lewat UI, login sebagai admin | Redirect natural ke admin panel atau ada menu UI menuju admin panel | Belum Diuji |  |  |  |
| AUTH-005 | Yes | Critical | Mulai dari homepage, buka form login lewat UI, login sebagai seller | Redirect natural ke seller dashboard atau ada menu UI menuju seller dashboard | Belum Diuji |  |  |  |
| AUTH-006 | Yes | Critical | Mulai dari homepage, buka form login lewat UI, login sebagai user | Redirect natural ke user dashboard atau ada menu UI menuju user dashboard | Belum Diuji |  |  |  |
| AUTH-007 | Yes | Critical | Sebagai guest, klik UI yang membutuhkan auth seperti checkout/wishlist/dashboard entry | User diarahkan ke login, bukan error mentah | Belum Diuji |  |  |  |
| AUTH-008 | Yes | High | Login lalu logout | Session logout, area private tidak bisa diakses | Belum Diuji |  |  |  |
| AUTH-009 | Yes | Medium | Dari form login, klik forgot password | Form menerima email dan tidak fatal | Belum Diuji |  |  |  |
| AUTH-010 | Yes | Medium | Uji validasi login salah | Error tampil jelas, tidak bocor informasi sensitif | Belum Diuji |  |  |  |

## 9. Landing Page UI Polish

Viewport wajib: desktop 1440px, tablet 768px, mobile 390px.

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| LAND-001 | Yes | Critical | Ketik homepage `/` desktop 1440px sebagai satu-satunya direct URL manual yang diperbolehkan | Landing render tanpa error console | Belum Diuji |  |  |  |
| LAND-002 | Yes | Critical | Klik nav Kategori, Koleksi, Manfaat, Tentang, Blog, FAQ, Kontak | Scroll ke section tepat, URL tidak perlu hash panjang | Belum Diuji |  |  |  |
| LAND-003 | Yes | High | Klik CTA hero `Jelajahi Koleksi` | Masuk ke `/katalog` | Belum Diuji |  |  |  |
| LAND-004 | Yes | High | Klik CTA hero `Daftar Seniman` atau `Jual Karya` | Masuk ke `/seller/register` | Belum Diuji |  |  |  |
| LAND-005 | Yes | High | Cek jarak Koleksi -> Manfaat -> Tentang -> Blog -> FAQ -> Bergabung -> Footer | Section tidak mepet dan spacing konsisten | Belum Diuji |  |  |  |
| LAND-006 | Yes | High | Cek desktop/tablet/mobile dengan Chrome DevTools | Tidak ada horizontal overflow | Belum Diuji |  |  |  |
| LAND-007 | Yes | High | Cek hero collage desktop | Visual tidak pecah, gambar dan badge tidak tabrakan | Belum Diuji |  |  |  |
| LAND-008 | Yes | Medium | Cek marquee | Background hitam, teks putih, animasi berjalan wajar | Belum Diuji |  |  |  |
| LAND-009 | Yes | Medium | Cek Kategori section | 5 card tampil rapi dan link kategori menuju `/katalog?category=...` | Belum Diuji |  |  |  |
| LAND-010 | Yes | Medium | Cek Blog section | Card konsisten, CTA menuju `/artikel`, tidak terasa placeholder rusak | Belum Diuji |  |  |  |
| LAND-011 | Yes | Medium | Tab keyboard seluruh landing | Focus visible terlihat di semua elemen interaktif | Belum Diuji |  |  |  |
| LAND-012 | Yes | Medium | Cek gambar dekoratif dan gambar informatif | Alt text sesuai, dekoratif `alt=""`/`aria-hidden` | Belum Diuji |  |  |  |
| LAND-013 | Yes | Low | Cek copywriting landing | Tidak ada typo mencolok atau encoding rusak di UI | Belum Diuji |  |  |  |

## 10. Public Catalog

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| CAT-001 | Yes | Critical | Mulai dari homepage, klik CTA/menu/card yang menuju katalog | Katalog tampil dengan produk published tanpa mengetik URL | Belum Diuji |  |  |  |
| CAT-002 | Yes | Critical | Cari produk dengan keyword valid | Hasil sesuai keyword | Belum Diuji |  |  |  |
| CAT-003 | Yes | High | Cari keyword tanpa hasil | Empty state tampil jelas dan tidak error | Belum Diuji |  |  |  |
| CAT-004 | Yes | High | Filter kategori | Produk sesuai kategori | Belum Diuji |  |  |  |
| CAT-005 | Yes | High | Filter harga minimum dan maksimum | Produk sesuai rentang harga | Belum Diuji |  |  |  |
| CAT-006 | Yes | High | Filter seller/toko | Produk hanya dari seller terpilih | Belum Diuji |  |  |  |
| CAT-007 | Yes | Medium | Filter material, lokasi, ready/preorder, promo, rating seller | Hasil filter konsisten atau empty state jelas | Belum Diuji |  |  |  |
| CAT-008 | Yes | High | Sort terbaru, harga termurah, harga tertinggi, populer | Urutan data sesuai pilihan | Belum Diuji |  |  |  |
| CAT-009 | Yes | High | Navigasi pagination/cursor next | Data lanjut muncul tanpa duplikasi urutan fatal | Belum Diuji |  |  |  |
| CAT-010 | Yes | High | Pastikan produk unpublished/deleted tidak tampil | Hanya produk published visible publik | Belum Diuji |  |  |  |
| CAT-011 | Yes | Medium | Toggle grid/list jika tersedia di UI | Layout berubah tanpa merusak data | Belum Diuji |  |  |  |
| CAT-012 | Yes | Medium | Mobile catalog 390px | Filter dan card usable, tidak overflow | Belum Diuji |  |  |  |

## 11. Product Detail

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| PROD-001 | Yes | Critical | Mulai dari homepage -> klik katalog -> klik card produk published | Detail produk tampil dan URL akhir sesuai produk | Belum Diuji |  |  |  |
| PROD-002 | Yes | Critical | Dari admin UI unpublish/delete produk, lalu dari homepage -> katalog/search produk tersebut | Produk tidak bisa ditemukan/dibuka dari UI publik; old tab refresh boleh menunjukkan 404 | Belum Diuji |  |  |  |
| PROD-003 | Yes | High | Cek informasi nama, harga, stok, kategori, dimensi, material | Informasi sesuai database | Belum Diuji |  |  |  |
| PROD-004 | Yes | High | Cek media gallery dan optional video | Media tampil, tidak broken image | Belum Diuji |  |  |  |
| PROD-005 | Yes | High | Dari detail produk, klik nama/link toko seller | Toko seller terbuka lewat UI dan URL akhir sesuai `/toko/{slug}` | Belum Diuji |  |  |  |
| PROD-006 | Yes | High | Klik tambah ke keranjang | Item masuk cart backend | Belum Diuji |  |  |  |
| PROD-007 | Yes | High | Klik buy now jika tersedia | User diarahkan ke cart/checkout sesuai flow | Belum Diuji |  |  |  |
| PROD-008 | Yes | Medium | Cek reviews/rating | Review published tampil, agregat rating masuk akal | Belum Diuji |  |  |  |
| PROD-009 | Yes | Medium | Cek related products dan seller products | Tidak N+1 terlihat, link produk valid | Belum Diuji |  |  |  |
| PROD-010 | Yes | High | Login user, klik chat seller | Conversation buyer-seller dibuat/diambil ulang, tidak duplikat thread | Belum Diuji |  |  |  |
| PROD-011 | Yes | Low | Klik share/social jika tersedia | Tidak error dan link valid | Belum Diuji |  |  |  |

## 12. Cart

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| CART-001 | Yes | Critical | Guest tambah produk ke cart | Cart backend session bertambah | Belum Diuji |  |  |  |
| CART-002 | Yes | Critical | Logged-in user tambah produk ke cart | Cart bertambah dan tidak bercampur user lain | Belum Diuji |  |  |  |
| CART-003 | Yes | Critical | Update qty valid | Subtotal dan quantity berubah sesuai | Belum Diuji |  |  |  |
| CART-004 | Yes | Critical | Update qty melebihi stok | Validasi muncul, stok tidak oversold | Belum Diuji |  |  |  |
| CART-005 | Yes | High | Remove satu item | Item hilang dari cart | Belum Diuji |  |  |  |
| CART-006 | Yes | High | Clear cart | Semua item hilang dan subtotal nol | Belum Diuji |  |  |  |
| CART-007 | Yes | High | Refresh page cart | Data cart tetap ada sesuai session/login | Belum Diuji |  |  |  |
| CART-008 | Yes | Medium | Add produk unpublished/out of stock | Ditolak dengan pesan validasi | Belum Diuji |  |  |  |
| CART-009 | Yes | Medium | Mutasi cart cepat berulang | Tidak terjadi error/double inconsistent subtotal | Belum Diuji |  |  |  |

## 13. Checkout

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| CHECK-001 | Yes | Critical | Guest checkout isi nama, email, phone, alamat lengkap | Order dan payment invoice dibuat | Belum Diuji |  |  |  |
| CHECK-002 | Yes | Critical | Logged-in user checkout memakai alamat default | Data alamat terisi dan order dibuat | Belum Diuji |  |  |  |
| CHECK-003 | Yes | Critical | Submit checkout dua kali dengan idempotency key sama | Tidak membuat order ganda | Belum Diuji |  |  |  |
| CHECK-004 | Yes | Critical | Checkout stok terakhir dengan dua browser/session | Hanya satu checkout berhasil, stok tidak negatif | Belum Diuji |  |  |  |
| CHECK-005 | Yes | High | Apply voucher valid | Discount masuk ke total dan redemption tercatat | Belum Diuji |  |  |  |
| CHECK-006 | Yes | High | Apply voucher invalid/expired/quota habis | Voucher ditolak dengan pesan jelas | Belum Diuji |  |  |  |
| CHECK-007 | Yes | High | Checkout cart kosong | Ditolak dan diarahkan dengan pesan jelas | Belum Diuji |  |  |  |
| CHECK-008 | Yes | High | Cek snapshot order item setelah produk diubah seller | Order tetap menyimpan snapshot harga/nama saat checkout | Belum Diuji |  |  |  |
| CHECK-009 | Yes | Medium | Validasi form kosong/format email/phone | Error tampil di field terkait | Belum Diuji |  |  |  |
| CHECK-010 | Yes | Medium | Mobile checkout 390px | Form dan ringkasan usable tanpa overflow | Belum Diuji |  |  |  |

## 14. Payment

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| PAY-001 | Yes | Critical | `MIDTRANS_SERVER_KEY` kosong, lakukan checkout | Payment memakai local fallback dan flow development tetap bisa dites | Belum Diuji |  |  |  |
| PAY-002 | Yes | Critical | `MIDTRANS_SERVER_KEY` sandbox terisi, lakukan checkout | Invoice/token/redirect Midtrans dibuat sesuai sandbox | Belum Diuji |  |  |  |
| PAY-003 | Yes | Critical | Kirim webhook Midtrans signature valid status paid | Payment menjadi paid, order menjadi paid/processing sesuai rule | Belum Diuji |  |  |  |
| PAY-004 | Yes | Critical | Kirim webhook signature invalid | Ditolak, status tidak berubah | Belum Diuji |  |  |  |
| PAY-005 | Yes | Critical | Kirim duplicate webhook paid | Payment event dan ledger tidak dobel | Belum Diuji |  |  |  |
| PAY-006 | Yes | High | Webhook failed | Payment failed dan order tidak diproses seller | Belum Diuji |  |  |  |
| PAY-007 | Yes | High | Webhook expired | Payment expired dan order bisa ditandai sesuai lifecycle | Belum Diuji |  |  |  |
| PAY-008 | Yes | High | Webhook refunded | Refund state tercatat dan ledger reversal sesuai kondisi | Belum Diuji |  |  |  |
| PAY-009 | Yes | Medium | Setelah checkout, klik/ikuti redirect natural ke halaman status pembayaran | Status pembayaran tampil jelas dan URL akhir sesuai `/payment/{invoice}` | Belum Diuji |  |  |  |

## 15. Order Lifecycle

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| ORD-001 | Yes | Critical | Checkout berhasil | Order status `pending_payment`, payment `pending` | Belum Diuji |  |  |  |
| ORD-002 | Yes | Critical | Payment paid | Order masuk status paid/processing dan seller melihat pesanan | Belum Diuji |  |  |  |
| ORD-003 | Yes | Critical | Seller update courier dan resi | Order item/shipment update, user bisa melihat tracking | Belum Diuji |  |  |  |
| ORD-004 | Yes | Critical | User klik terima pesanan/complete | Order completed, settlement available diproses sekali | Belum Diuji |  |  |  |
| ORD-005 | Yes | High | User cancel unpaid order | Order cancelled, stock kembali, tidak ada ledger paid | Belum Diuji |  |  |  |
| ORD-006 | Yes | High | User request refund | Status refund requested dan admin melihat tindakan | Belum Diuji |  |  |  |
| ORD-007 | Yes | High | Admin approve refund | Payment/order/ledger reversal sesuai business rule | Belum Diuji |  |  |  |
| ORD-008 | Yes | High | Admin reject refund | Status kembali sesuai rule dan user mendapat notifikasi | Belum Diuji |  |  |  |
| ORD-009 | Yes | Critical | Proses complete order dua kali | Tidak menggandakan available ledger | Belum Diuji |  |  |  |
| ORD-010 | Yes | Medium | Dari UI user, pastikan order milik user lain tidak muncul; direct boundary diverifikasi automated/HTTP test | User tidak punya jalur UI untuk membuka order orang lain | Belum Diuji |  |  |  |

## 16. User Area (via UI, target route `/user`)

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| USER-001 | Yes | High | Mulai dari homepage, login sebagai user lewat UI, lalu ikuti redirect/menu ke dashboard user | Dashboard user tampil ringkasan order/wishlist/address/notification | Belum Diuji |  |  |  |
| USER-002 | Yes | High | Dari dashboard user, klik menu/tautan order | Hanya order user terkait tampil | Belum Diuji |  |  |  |
| USER-003 | Yes | High | Dari daftar order user, klik salah satu invoice/order card | Detail order, item, payment, tracking, address snapshot tampil | Belum Diuji |  |  |  |
| USER-004 | Yes | High | Dari dashboard/user menu, klik alamat lalu lakukan tambah/edit/hapus/default | Tambah/edit/hapus/default address berjalan | Belum Diuji |  |  |  |
| USER-005 | Yes | High | Dari katalog/detail produk, klik wishlist, lalu buka wishlist dari menu user | Wishlist sinkron backend | Belum Diuji |  |  |  |
| USER-006 | Yes | High | Dari dashboard/user menu, klik notifikasi lalu mark read/read all | Notification owner-only dan status read berubah | Belum Diuji |  |  |  |
| USER-007 | Yes | High | Dari dashboard/user menu atau tombol chat produk, buka inbox chat lalu pilih conversation | Inbox dan active conversation tampil | Belum Diuji |  |  |  |
| USER-008 | Yes | High | Kirim pesan dari user ke seller | Pesan tersimpan dan unread seller bertambah | Belum Diuji |  |  |  |
| USER-009 | Yes | High | Review produk setelah order completed paid | Review berhasil dibuat satu kali per order item | Belum Diuji |  |  |  |
| USER-010 | Yes | Medium | Coba review sebelum completed/paid | Ditolak | Belum Diuji |  |  |  |

## 17. Seller Area (via UI, target route `/seller`)

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| SELL-001 | Yes | Critical | Mulai dari homepage, login sebagai seller lewat UI, lalu ikuti redirect/menu ke seller dashboard | Dashboard seller tampil tanpa error | Belum Diuji |  |  |  |
| SELL-002 | Yes | Critical | Dari seller dashboard, klik menu store/profil toko lalu edit toko dan rekening payout | Data toko dan rekening tersimpan | Belum Diuji |  |  |  |
| SELL-003 | Yes | Critical | Dari seller dashboard, klik menu produk lalu klik tambah produk | Produk dibuat dan auto-published sesuai rule | Belum Diuji |  |  |  |
| SELL-004 | Yes | Critical | Edit produk milik sendiri | Perubahan tersimpan, seller tidak bisa mengubah produk orang lain | Belum Diuji |  |  |  |
| SELL-005 | Yes | Critical | Update stock produk | Stok berubah dan validasi input aman | Belum Diuji |  |  |  |
| SELL-006 | Yes | High | Delete produk sendiri | Produk tidak tampil publik atau soft delete sesuai rule | Belum Diuji |  |  |  |
| SELL-007 | Yes | Critical | Dari seller dashboard, klik menu orders setelah ada order paid | Order item seller tampil | Belum Diuji |  |  |  |
| SELL-008 | Yes | Critical | Update shipment courier/resi | User melihat tracking, notification terkirim | Belum Diuji |  |  |  |
| SELL-009 | Yes | Critical | Dari seller dashboard, klik menu wallet setelah order completed | Saldo available dan ledger sesuai | Belum Diuji |  |  |  |
| SELL-010 | Yes | Critical | Request withdraw valid | Withdraw pending dibuat, saldo tidak bisa ditarik dobel | Belum Diuji |  |  |  |
| SELL-011 | Yes | High | Request withdraw tanpa rekening/saldo kurang | Ditolak dengan pesan jelas | Belum Diuji |  |  |  |
| SELL-012 | Yes | High | Request seller ad | Ad pending dibuat untuk admin review | Belum Diuji |  |  |  |
| SELL-013 | Yes | Medium | Cek referral page/link/register referral | Referral tercatat sesuai lifecycle | Belum Diuji |  |  |  |
| SELL-014 | Yes | Medium | Cek seller reports dengan filter tanggal | Angka laporan sesuai order paid/completed | Belum Diuji |  |  |  |
| SELL-015 | Yes | High | Cek seller notifications mark read/read all | Notification owner-only dan read state benar | Belum Diuji |  |  |  |
| SELL-016 | Yes | High | Cek seller chat inbox dan balas pesan | Pesan tersimpan dan user menerima update | Belum Diuji |  |  |  |

## 18. Admin Filament (via UI, target route `/admin`)

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| ADM-001 | Yes | Critical | Mulai dari homepage, login sebagai admin lewat UI, lalu ikuti redirect/menu ke admin panel | Admin panel tampil tanpa mengetik URL admin langsung | Belum Diuji |  |  |  |
| ADM-002 | Yes | Critical | Login user/seller lewat UI, pastikan tidak ada menu admin; direct boundary diverifikasi automated/HTTP test | User/seller tidak punya jalur UI menuju admin panel | Belum Diuji |  |  |  |
| ADM-003 | Yes | High | CRUD categories | Kategori bisa dibuat/edit/delete/restore jika tersedia | Belum Diuji |  |  |  |
| ADM-004 | Yes | High | Manage sellers | Seller bisa dilihat/edit sesuai resource | Belum Diuji |  |  |  |
| ADM-005 | Yes | Critical | Manage products: unpublish/delete product | Produk hilang dari public catalog/detail | Belum Diuji |  |  |  |
| ADM-006 | Yes | Critical | Manage orders: approve/reject refund | Status dan ledger sesuai rule | Belum Diuji |  |  |  |
| ADM-007 | Yes | Critical | Manage payments | Payment event/status terlihat, tidak bisa merusak immutable data sembarang | Belum Diuji |  |  |  |
| ADM-008 | Yes | Critical | Manage withdraw approval/reject/paid | Withdraw lifecycle dan notification berjalan | Belum Diuji |  |  |  |
| ADM-009 | Yes | High | Manage vouchers | Voucher fixed/percent, quota, min order, active dates tersimpan | Belum Diuji |  |  |  |
| ADM-010 | Yes | Medium | Dari admin menu artikel, buat/edit publish article lalu dari homepage klik UI menuju artikel | Draft/published/scheduled article bekerja di public articles | Belum Diuji |  |  |  |
| ADM-011 | Yes | Medium | Manage banners | Banner aktif sesuai placement/schedule, tidak fatal di home | Belum Diuji |  |  |  |
| ADM-012 | Yes | Medium | Manage seller ads | Pending -> active/rejected sesuai action | Belum Diuji |  |  |  |
| ADM-013 | Yes | Medium | Manage referrals | Qualify/reward/reject idempotent | Belum Diuji |  |  |  |
| ADM-014 | Yes | Medium | Manage product reviews | Publish/hide/delete review tanpa hapus histori order | Belum Diuji |  |  |  |
| ADM-015 | Yes | High | Update marketplace settings | Commission, withdraw, referral, shipping config tersimpan | Belum Diuji |  |  |  |
| ADM-016 | Yes | High | Cari user management di admin | Jika tidak ada `UserResource`, catat gap untuk implementasi admin users | Belum Diuji |  |  |  |

## 19. Realtime & Chat

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| CHAT-001 | Yes | Critical | User mulai chat dari product detail | Conversation 1-on-1 user-seller dibuat | Belum Diuji |  |  |  |
| CHAT-002 | Yes | Critical | User buka conversation yang sama dari produk sama | Tidak membuat conversation duplikat | Belum Diuji |  |  |  |
| CHAT-003 | Yes | Critical | User kirim pesan | Message tersimpan database, status sent | Belum Diuji |  |  |  |
| CHAT-004 | Yes | Critical | Seller balas pesan | User melihat pesan di inbox/conversation | Belum Diuji |  |  |  |
| CHAT-005 | Yes | High | Cek unread count user/seller | Count naik/turun saat message dibaca | Belum Diuji |  |  |  |
| CHAT-006 | Yes | High | Dari UI chat participant, buka DevTools Network dan verifikasi request SSE aktif | Stream aktif atau response valid tanpa fatal error | Belum Diuji |  |  |  |
| CHAT-007 | Yes | High | Pastikan non-participant tidak punya jalur UI ke conversation; direct endpoint boundary diverifikasi automated/HTTP test | Access denied/404 untuk akses non-participant | Belum Diuji |  |  |  |
| CHAT-008 | Yes | High | Dari UI chat, ganggu SSE/network lalu amati fallback polling di DevTools Network | Polling mengembalikan message terbaru | Belum Diuji |  |  |  |
| CHAT-009 | Yes | Medium | Pindah halaman/ganti conversation | Stream lama auto-close, tidak banyak connection aktif | Belum Diuji |  |  |  |
| CHAT-010 | Yes | Medium | Tab hidden lalu aktif lagi | Polling pause/resume sesuai rule | Belum Diuji |  |  |  |
| CHAT-011 | Yes | High | Dari UI yang punya notifikasi, amati SSE/polling notifications di DevTools Network | Summary unread/order/payment/withdraw/chat valid | Belum Diuji |  |  |  |

## 20. Financial Integrity

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| FIN-001 | Yes | Critical | Payment paid untuk order seller | Ledger `escrow_pending` dibuat sekali | Belum Diuji |  |  |  |
| FIN-002 | Yes | Critical | Order completed | Ledger available/settlement dibuat sekali | Belum Diuji |  |  |  |
| FIN-003 | Yes | Critical | Hitung commission order | Komisi sesuai marketplace settings | Belum Diuji |  |  |  |
| FIN-004 | Yes | Critical | Withdraw valid | Available balance berkurang/terkunci sesuai rule | Belum Diuji |  |  |  |
| FIN-005 | Yes | Critical | Duplicate withdraw request cepat | Tidak membuat payout ganda dari saldo sama | Belum Diuji |  |  |  |
| FIN-006 | Yes | Critical | Refund setelah escrow released | Ledger refund reversal dibuat, seller balance tidak overstated | Belum Diuji |  |  |  |
| FIN-007 | Yes | Critical | Duplicate order completion | Ledger tidak dobel | Belum Diuji |  |  |  |
| FIN-008 | Yes | High | Export/report financial jika tersedia | Total report sama dengan ledger/order source | Belum Diuji |  |  |  |
| FIN-009 | Yes | High | Audit wallet ledgers di database | Ledger append-only, tidak ada update saldo sembarang tanpa jejak | Belum Diuji |  |  |  |

## 21. Security & Access Boundary

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| SEC-001 | Yes | Critical | Sebagai guest dari homepage, klik UI yang membutuhkan auth seperti checkout/wishlist/seller/admin entry jika tersedia | Redirect/login/access denied sesuai role, tidak ada halaman privat terbuka | Belum Diuji |  |  |  |
| SEC-002 | Yes | Critical | Login user lewat UI dan pastikan tidak ada menu/aksi edit produk seller | Access denied untuk akses non-UI diverifikasi automated/HTTP test | Belum Diuji |  |  |  |
| SEC-003 | Yes | Critical | Seller edit produk seller lain | Access denied/404 | Belum Diuji |  |  |  |
| SEC-004 | Yes | Critical | Login user lewat UI dan pastikan daftar order hanya menampilkan miliknya | Direct order boundary diverifikasi automated/HTTP test | Belum Diuji |  |  |  |
| SEC-005 | Yes | Critical | Login user lewat UI dan pastikan daftar notifikasi hanya miliknya | Direct notification boundary diverifikasi automated/HTTP test | Belum Diuji |  |  |  |
| SEC-006 | Yes | High | Login participant/non-participant lewat UI dan pastikan conversation tidak bocor di inbox | Direct chat boundary diverifikasi automated/HTTP test | Belum Diuji |  |  |  |
| SEC-007 | Yes | High | Upload file produk dengan mime executable | Ditolak | Belum Diuji |  |  |  |
| SEC-008 | Yes | High | Submit form dengan payload HTML/script di nama/catatan | Output escaped, tidak XSS | Belum Diuji |  |  |  |
| SEC-009 | Yes | High | Rate limit login 5/min/IP | Request berlebih ditahan | Belum Diuji |  |  |  |
| SEC-010 | Yes | High | Rate limit checkout/cart/chat/withdraw sesuai rule | Request berlebih tidak merusak data | Belum Diuji |  |  |  |
| SEC-011 | Yes | Medium | CSRF form mutation | Request tanpa token ditolak | Belum Diuji |  |  |  |

## 22. Design System Audit

Audit ini untuk memastikan UI yang sudah ada tetap konsisten dengan `DESIGN-SYSTEM.md`, bukan redesign.

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| DS-001 | Yes | High | Cek public pages memakai warna token `cream`, `ink`, `gold`, `paper` | Tidak ada warna random yang merusak theme | Belum Diuji |  |  |  |
| DS-002 | Yes | High | Cek heading section | Menggunakan karakter font display sesuai design system | Belum Diuji |  |  |  |
| DS-003 | Yes | High | Cek tombol dan elemen interaktif | Ada focus visible `ui.focus`/`ui.focusDark` atau equivalent jelas | Belum Diuji |  |  |  |
| DS-004 | Yes | High | Cek semua page public desktop/tablet/mobile | Tidak ada horizontal overflow | Belum Diuji |  |  |  |
| DS-005 | Yes | Medium | Cek contrast teks di gambar/card overlay | Teks tetap terbaca, overlay cukup gelap | Belum Diuji |  |  |  |
| DS-006 | Yes | Medium | Cek semantic heading order | Tidak lompat heading secara membingungkan | Belum Diuji |  |  |  |
| DS-007 | Yes | Medium | Cek image alt | Informative image punya alt, decorative image disembunyikan | Belum Diuji |  |  |  |
| DS-008 | Yes | Medium | Search code `transition-all` | Jika ada, evaluasi ganti transition spesifik | Belum Diuji |  |  |  |
| DS-009 | Yes | Medium | Search hardcoded hex/rgb di public UI | Jika ada, pastikan justified atau ganti token | Belum Diuji |  |  |  |
| DS-010 | Yes | Low | Cek copy dan encoding di UI | Tidak ada mojibake/karakter rusak di layar | Belum Diuji |  |  |  |

## 23. Production Readiness

| ID | Required | Priority | Alur Uji | Expected Result | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|---|
| PRODREADY-001 | Yes | Critical | Build local `npm run build` lalu cek `public/build` | Asset production tersedia untuk upload shared hosting | Belum Diuji |  |  |  |
| PRODREADY-002 | Yes | Critical | Cek cron command `php artisan queue:work --stop-when-empty` | Command documented dan bisa jalan | Belum Diuji |  |  |  |
| PRODREADY-003 | Yes | High | Cek config cache `php artisan config:cache` | App tidak fatal karena config cache | Belum Diuji |  |  |  |
| PRODREADY-004 | Yes | High | Cek route cache jika kompatibel | Route cache tidak fatal atau didokumentasikan tidak dipakai | Belum Diuji |  |  |  |
| PRODREADY-005 | Yes | High | Switch `.env` MySQL + database session + database queue | App tetap jalan tanpa code change | Belum Diuji |  |  |  |
| PRODREADY-006 | Yes | High | Cek filesystem upload public/private | File produk tersimpan, executable blocked | Belum Diuji |  |  |  |
| PRODREADY-007 | Yes | Medium | Cek sitemap package/command strategy | Sitemap production punya prosedur jelas | Belum Diuji |  |  |  |
| PRODREADY-008 | Yes | Medium | Cek backup strategy shared hosting | Backup alternatif documented karena `spatie/laravel-backup` belum dipasang | Belum Diuji |  |  |  |
| PRODREADY-009 | Yes | Medium | Cek log file dan error reporting production | Error tidak bocor ke user, log dapat dibaca admin/dev | Belum Diuji |  |  |  |
| PRODREADY-010 | Yes | Medium | Cek `.env.example` tidak berisi secret asli | Tidak ada credential production di repo | Belum Diuji |  |  |  |

## 24. Automated Coverage Map

Checklist ini membantu tim melihat area yang sudah punya test otomatis, tetapi status manual tetap harus diisi.

| Area | Test File Yang Ada | Yang Masih Wajib Manual |
|---|---|---|
| Auth | `tests/Feature/Auth/*` | UX form, copy, redirect visual |
| Commerce Cart/Checkout/Payment | `tests/Feature/Commerce/CartTest.php`, `CheckoutTest.php`, `MidtransWebhookTest.php`, `VoucherCheckoutTest.php` | Browser flow end-to-end dan UI error state |
| Product Detail/Content | `ProductDetailTest.php`, `ArticleContentTest.php`, `BannerContentTest.php` | Visual gallery, responsive, SEO/head checks |
| Order/Wallet/Withdraw | `OrderResolutionTest.php`, `SettlementWithdrawTest.php` | Admin/seller manual lifecycle in browser |
| Reviews | `ProductReviewTest.php` | UI review form dan moderation behavior di panel |
| Seller | `tests/Feature/Seller/*` | Blade dashboard UI, mobile/tablet, business workflow smoke |
| User Area | `tests/Feature/User/UserAreaTest.php` | Inertia page UX, empty states, navigation |
| Chat | `tests/Feature/Chat/ChatTest.php` | SSE browser behavior, fallback polling, multi-tab |
| Notifications | `MarketplaceNotificationAutomationTest.php`, `SellerNotificationTest.php` | Notification inbox UX and unread badge timing |

## 25. UI Navigation Smoke Matrix

Isi minimal satu evidence per flow setelah smoke test. Kolom target route hanya untuk memverifikasi URL akhir setelah klik UI, bukan untuk diketik langsung.

| Flow Group | UI Path Wajib | Target URL Akhir | Role | Status | PIC | Evidence | Bug |
|---|---|---|---|---|---|---|---|
| Public Home | Ketik homepage `/` sekali | `/` | Guest | Belum Diuji |  |  |  |
| Catalog | Homepage -> klik `Jelajahi Koleksi`/menu katalog/card kategori | `/katalog` atau `/katalog?category=...` | Guest/User | Belum Diuji |  |  |  |
| Product Detail | Homepage -> katalog -> klik card produk | `/produk/{slug}` | Guest/User | Belum Diuji |  |  |  |
| Cart | Homepage -> katalog/detail produk -> tambah cart -> klik cart UI | `/cart` | Guest/User | Belum Diuji |  |  |  |
| Checkout | Homepage -> katalog/detail -> cart -> klik checkout | `/checkout` atau redirect login jika perlu | Guest/User | Belum Diuji |  |  |  |
| Payment | Checkout berhasil -> klik/ikuti redirect payment status | `/payment/{invoice}` | Guest/User terkait | Belum Diuji |  |  |  |
| Articles | Homepage -> klik section/blog card/artikel link | `/artikel` atau `/artikel/{slug}` | Guest | Belum Diuji |  |  |  |
| Store | Homepage -> katalog -> produk -> klik toko seller | `/toko/{slug}` | Guest | Belum Diuji |  |  |  |
| Auth | Homepage -> klik login/register/seller register UI | `/login`, `/register`, atau `/seller/register` | Guest | Belum Diuji |  |  |  |
| User | Homepage -> login user -> redirect/menu user | `/user/*` | User | Belum Diuji |  |  |  |
| Seller | Homepage -> login seller -> redirect/menu seller | `/seller/*` | Seller | Belum Diuji |  |  |  |
| Admin | Homepage -> login admin -> redirect/menu admin | `/admin/*` | Admin | Belum Diuji |  |  |  |
| Realtime | UI chat/notification -> cek Network DevTools | `/sse/*`, `/polling/*` | Auth participant | Belum Diuji |  |  |  |
| Webhook | Bukan navigasi UI; uji via Midtrans sandbox/HTTP client | `/webhooks/midtrans` | Midtrans/server | Belum Diuji |  |  |  |

## 26. Bug Report Template

Gunakan format ini untuk setiap item berstatus Bug.

```md
## Bug ID / Link
- Severity: P0/P1/P2/P3
- Checklist ID:
- Environment:
- Role:
- Browser/Device:
- Steps to Reproduce:
  1.
  2.
  3.
- Expected Result:
- Actual Result:
- Evidence:
- Suspected Area:
- Workaround:
- Owner:
- Status:
```

## 27. Evidence Naming Convention

Simpan evidence dengan pola berikut agar mudah dicari.

```text
qa-evidence/YYYY-MM-DD/<CHECKLIST-ID>-<role>-<short-description>.<png|mp4|txt>
```

Contoh:

```text
qa-evidence/2026-04-25/LAND-006-guest-mobile-no-overflow.png
qa-evidence/2026-04-25/CHECK-003-user-duplicate-submit.txt
qa-evidence/2026-04-25/PAY-005-webhook-duplicate-ledger.txt
```
