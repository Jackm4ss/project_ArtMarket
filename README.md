# Art Market Multivendor

Art Market sekarang disiapkan sebagai Laravel monolith untuk ecommerce marketplace seni multivendor. Target awalnya shared hosting, dengan jalur migrasi VPS lewat konfigurasi `.env`, bukan rewrite business logic.

## Stack

- Laravel 11
- Breeze Inertia React + Vite untuk public dan user area
- Livewire + Blade + Tailwind untuk seller area
- Filament 3 untuk admin area
- MySQL/MariaDB production, SQLite in-memory untuk test
- Database queue, database session, file cache
- Laravel Scout database driver untuk search awal
- Spatie Permission, Medialibrary, Activity Log, Settings, Tags, Sluggable, Sitemap
- Midtrans, Darryldecode Cart, Excel, DomPDF, Intervention Image

## Struktur Penting

- `app/Models`: model domain marketplace, order, wallet, content, dan chat
- `app/Enums`: status production agar tidak hardcode string di banyak tempat
- `app/Http/Controllers/Public`: public catalog, product, cart, checkout, payment, article, store
- `app/Services/Cart`, `app/Services/Checkout`, `app/Services/Payments`, `app/Services/Wallet`: business logic commerce agar controller tetap tipis
- `app/Filament/Resources`: admin resources minimum untuk kategori, seller, produk, order, payment, withdraw, voucher, seller ads, referral, review produk, artikel, dan banner
- `app/Filament/Pages/MarketplaceSettingsPage.php`: panel settings operasional marketplace berbasis Spatie Settings
- `app/Settings/MarketplaceSettings.php`: source of truth runtime untuk komisi, withdraw, currency, shipping mode, auto-publish, dan referral reward
- `app/Http/Controllers/Realtime`: SSE dan polling fallback untuk notifikasi/chat
- `app/Services/Chat`: aturan conversation buyer-seller, unread count, message serialization, dan read state
- `app/Realtime`: helper terfokus untuk stream SSE
- `resources/js/ArtMarket`: landing React lama yang dimigrasikan ke Inertia sebagai referensi visual
- `resources/js/Layouts/ArtMarketPublicLayout.tsx`: wrapper public frontend supaya halaman lama tetap konsisten dengan header/footer/design system
- `resources/js/Pages`: halaman Inertia production
- `resources/views/seller`: foundation dashboard seller Livewire/Blade
- `legacy/react-vite-reference`: arsip React/Vite lama sebelum migrasi monolith
- `PRODUCTION.MD`: keputusan scope dan arsitektur production
- `DESIGN-SYSTEM.md`: sumber truth UI yang akan dipakai saat memperbaiki halaman frontend berikutnya

## Route Utama

- Public: `/`, `/katalog`, `/produk/{slug}`, `/cart`, `/checkout`, `/payment/{invoice}`, `/artikel`, `/toko/{slug}`
- Cart mutation: `POST /cart/items`, `PATCH /cart/items/{slug}`, `DELETE /cart/items/{slug}`, `DELETE /cart`
- Webhook: `POST /webhooks/midtrans`
- Auth: `/login`, `/register`, `/seller/register`, `/dashboard`
- User: `/user`, `/user/orders`, `/user/wishlist`, `/user/addresses`, `/user/notifications`, `/user/chats`
- User review: `POST /user/orders/{invoice}/items/{orderItem}/review`
- Seller: `/seller`, `/seller/products`, `/seller/orders`, `/seller/shipments`, `/seller/wallet`, `/seller/withdrawals`, `/seller/ads`, `/seller/referrals`, `/seller/reports`, `/seller/chats`, `/seller/notifications`
- Admin: `/admin`
- Realtime: `/sse/notifications`, `/sse/chats/{conversation}`, `/polling/notifications`, `/polling/chats/{conversation}`

`/dashboard` redirect berdasarkan role:

- `admin` ke `/admin`
- `seller` ke `/seller`
- default user ke `/user`

## Development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

Jika MySQL lokal belum menyala, development bisa memakai SQLite/file session:

```env
DB_CONNECTION=sqlite
DB_DATABASE="C:/laragon/www/Project Artmarket/database/database.sqlite"
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Build production asset:

```bash
npm run build
```

Shared hosting deployment:

- Build Vite secara lokal.
- Upload `public/build`.
- Jalankan queue dari cron per menit:

```bash
php artisan queue:work --stop-when-empty
```

Karena shared hosting cron umumnya per menit, delay job bisa sampai 1 menit.

## Testing

```bash
php artisan test
npm run build
php artisan migrate:fresh --seed --env=testing
```

Test memakai SQLite in-memory lewat `phpunit.xml`. Local/testing mengaktifkan `Model::preventLazyLoading()` agar N+1 lebih cepat ketahuan.

## Core Commerce

Public frontend memakai UI lama dari `legacy/react-vite-reference` yang dimigrasikan ke Inertia, bukan layout placeholder baru.

- Katalog memakai backend cursor pagination dan filter dari `ProductCatalogQuery`.
- Product detail memakai eager loading seller, category, media, review, dan related product.
- Cart adalah source of truth backend berbasis session Laravel melalui `CartManager`.
- Checkout memakai `CheckoutService` dengan database transaction, stock lock, idempotency key, voucher validation, order snapshot, dan invoice payment.
- Voucher/promo memakai `VoucherService` dan `voucher_redemptions` supaya limit global, limit per user/guest, minimum order, max discount, dan tanggal aktif tercatat secara transactional.
- Midtrans webhook memakai signature validation dan idempotency event log; duplicate webhook tidak menggandakan ledger.
- Wallet ledger bersifat append-only: pembayaran paid membuat `escrow_pending`, dan release order completion akan membuat `escrow_available`.
- Cancel unpaid order mengembalikan stok secara idempotent lewat `OrderResolutionService`.
- Refund berstatus admin approval: user mengajukan refund, admin approve/reject dari Filament order actions.
- Refund order yang escrow-nya sudah released akan membuat `refund_debited` supaya available seller balance tidak overstated.
- Jika `MIDTRANS_SERVER_KEY` kosong, sistem membuat payment `local-fallback` supaya flow development tetap bisa dites tanpa sandbox.
- Review produk hanya bisa dibuat oleh user pemilik order setelah status order `completed` dan payment `paid`.
- Satu `order_item` hanya bisa punya satu review; rating aggregate produk dan seller dihitung ulang otomatis dari review `published`.
- Notification automation sudah terpusat lewat `MarketplaceNotificationService` untuk payment paid, order cancelled/completed/shipped, refund request/approve/reject, withdraw lifecycle, chat message, review, seller ads, dan referral.

## Admin CMS & Settings

Admin Filament sudah punya modul awal untuk konten dan konfigurasi:

- `ArticleResource`: draft/published/archived, author, slug otomatis, publish schedule, soft delete, restore.
- `BannerResource`: placement `home_hero`, `home_featured`, `catalog_top`, `article_top`, image upload, schedule aktif, sort order, soft delete.
- `Marketplace Settings`: currency, commission rate, shipping mode, product auto-publish, minimum withdraw, fee withdraw, schedule withdraw, dan referral reward.
- `ProductReviewResource`: admin bisa publish/hide/delete/restore review untuk moderasi tanpa menghapus histori order.
- `VoucherResource`: admin bisa mengatur fixed/percent voucher, minimum order, maksimal diskon, kuota global, batas per user/guest, dan jadwal aktif.

Public article hanya menampilkan status `published` yang sudah masuk jadwal. Home menerima prop banner aktif, tetapi visual landing tetap memakai UI lama sampai redesign public memang diminta.

## User Area

Area `/user` sudah memakai Inertia page production, bukan placeholder:

- `/user`: ringkasan pesanan, wishlist, alamat, dan notifikasi.
- `/user/orders`: riwayat pesanan milik user yang sedang login.
- `/user/orders/{invoice}`: detail order, item, status pembayaran, tracking courier/resi, alamat snapshot, terima pesanan, cancel unpaid, dan request refund.
- `/user/wishlist`: daftar produk favorit dengan add/remove backend.
- `/user/addresses`: CRUD alamat pengiriman dengan alamat default.
- `/user/notifications`: database notifications dan mark-as-read.
- `/user/chats`: inbox dan active conversation buyer-seller, dengan SSE aktif dan fallback polling.

Access boundary dites: user tidak bisa membuka order, alamat, atau notifikasi milik user lain.

## Seller Area

Area seller masih dibuat konservatif untuk shared hosting, tetapi modul minimum sudah fungsional:

- `/seller/store`: edit profil toko, lokasi, kontak, dan rekening payout.
- `/seller/products`: daftar produk dan update stok.
- `/seller/products/create`: tambah produk seller, otomatis publish setelah upload.
- `/seller/products/{slug}/edit`: edit produk sendiri tanpa mengubah status unpublish dari admin.
- `/seller/orders`: daftar order item seller dan update courier/resi.
- `/seller/shipments`: daftar order paid yang siap dikirim/sudah dikirim, dengan form courier dan resi.
- `/seller/wallet`: ringkasan saldo available dan ledger append-only.
- `/seller/withdrawals`: request withdraw, validasi rekening payout, dan riwayat status payout.
- `/seller/ads`: request slot iklan/promosi manual untuk ditinjau admin.
- `/seller/referrals`: kode referral seller, link register, summary reward, dan riwayat referral.
- `/seller/reports`: ringkasan penjualan seller berdasarkan order paid dan filter tanggal.
- `/seller/chats`: inbox chat pembeli dengan form balasan manual.
- `/seller/notifications`: inbox notifikasi operasional seller dengan mark-as-read owner-only.
- Seller mendapat notifikasi database otomatis untuk order paid/cancelled/completed, shipment/refund update, withdraw, chat, review, ads, dan referral yang relevan.

Demo seed:

- `admin@artmarket.test` / `password`
- `seller@artmarket.test` / `password`
- `user@artmarket.test` / `password`

## Realtime

Database tetap source of truth. Realtime hanya delivery layer.

- Default local/shared-hosting: `REALTIME_DRIVER=polling` agar chat tidak menahan navigasi pada server satu worker.
- Buyer active chat: polling 10 detik di `/polling/chats/{conversation}`.
- Opsional jika hosting mendukung long-running request: set `REALTIME_DRIVER=sse` untuk SSE di `/sse/chats/{conversation}`.
- Global notification summary: `/sse/notifications` atau `/polling/notifications`
- Seller/admin dashboard: Livewire `wire:poll` atau manual refresh
- VPS nanti bisa pindah ke Reverb/WebSocket via `REALTIME_DRIVER`
- Tombol `Chat Seller` di product detail membuat/mengambil conversation 1-on-1 buyer-seller yang sama, bukan membuat thread baru per produk.
- Notification inbox memakai database sebagai source of truth; SSE/polling hanya membaca unread summary agar aman di shared hosting.

Client chat wajib:

- satu stream SSE aktif per tab jika `REALTIME_DRIVER=sse`
- auto-close saat pindah halaman, ganti conversation, logout, atau tab hidden terlalu lama
- reconnect backoff 1s, 2s, 4s, 8s, max 30s
- pause polling saat tab hidden

## Referral Lifecycle

- `/seller/register?ref=...` menerima kode referral seller.
- Registrasi seller membuat akun, role seller, profil toko, dan referral pending secara transaksional.
- Admin dapat `qualify`, `reward`, atau `reject` referral dari Filament `ReferralResource`.
- Reward referral masuk ke `wallet_ledgers` dengan tipe `referral_rewarded` dan idempotent, sehingga tidak menggandakan saldo saat action diproses ulang.
- Nilai default reward dikontrol dari `MARKETPLACE_REFERRAL_REWARD_AMOUNT`.
- Setelah migration settings berjalan, nilai reward bisa diubah dari Filament `Marketplace Settings` tanpa mengubah kode.

## Catatan Package

`spatie/laravel-backup` belum dipasang karena membutuhkan `ext-pcntl`, yang tidak tersedia di setup Laragon saat ini dan sering tidak tersedia di shared hosting. Untuk backup production shared hosting, gunakan backup bawaan hosting atau command custom yang tidak bergantung pada `pcntl`; jika pindah VPS, package ini bisa dipasang ulang lewat `.env` dan deployment checklist.
