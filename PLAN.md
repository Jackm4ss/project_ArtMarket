# Revised Production Plan Art Market Multivendor

## Summary
- Build as **Laravel monolith**, optimized for shared hosting first and VPS later.
- Buyer/public uses **Laravel Breeze Inertia React + Vite**, seller dashboard uses **Livewire/Blade/Tailwind**, admin uses **Filament 3**.
- Current React/Vite landing is migration/reference material only, not final production foundation.
- Target scope is **Phase 1 + Phase 2 + Phase 3 live chat only**.
- Deferred Phase 3 items: auction, multi-language, PWA/mobile app, AI recommendation.

## Key Architecture
- Required stack: Spatie Permission, MySQL/MariaDB, database queue, file cache, database session, Scout database/FULLTEXT search, Medialibrary, Intervention Image, Sluggable, Tags, Settings, Activity Log, Backup, Sitemap, Darryldecode Cart, Midtrans, Excel, DomPDF.
- Shared hosting constraints: no Redis, no WebSocket/Reverb, no Supervisor, no Node runtime on hosting, no Meilisearch, no PostgreSQL, no root access, no custom ports.
- Production build: Vite build locally, upload `public/build`; cron runs queue every minute with `queue:work --stop-when-empty`.
- Queue delay can be up to 1 minute because shared hosting cron runs per minute.
- All switchable services must be `.env` based: cache, queue, session, search, realtime, filesystem, payment, mail, notification.
- Future VPS migration must only change `.env`, not business logic.

## Realtime, Chat, Pagination, And Data Fetching
- Live chat is included in production scope.
- Chat model is 1-on-1 buyer-seller only; no group chat in v1.
- Chat conversation may optionally link to `order_id` and/or `product_id`.
- Chat send uses authenticated `POST`.
- Chat receive uses SSE only for the active conversation, with 10-second polling fallback.
- SSE reconnect backoff: 1s, 2s, 4s, 8s, max 30s; reset after successful connection.
- SSE auto-closes on route leave, conversation switch, logout, or tab hidden too long.
- One active chat SSE stream per tab maximum; do not open streams for all conversations.
- `/sse/notifications` handles unread count, order updates, payment updates, withdraw updates, and chat summary only.
- `/sse/chats/{conversation}` streams active conversation messages only.
- Polling pauses when tab is hidden and resumes when active.
- Seller/admin dashboard uses Livewire `wire:poll` or manual refresh.
- VPS later can switch chat/notifications to Reverb/WebSocket via `REALTIME_DRIVER`.
- Chat messages are stored in database; realtime layer is delivery only, not source of truth.
- Public catalog uses cursor-based pagination with stable sort keys: `created_at + id`, `price + id`, `popularity + id`.
- Dashboard/admin tables may use standard `paginate()` because Filament and reports need page numbers.
- Inertia remains source of truth for full page props, SEO, form submit, and validation.
- TanStack Query may be used only for dynamic buyer-side data: cart count, notification polling fallback, wishlist toggle, chat polling fallback, and infinite catalog JSON endpoints.

## Product Scope
- Public/buyer: landing, catalog, product detail, cart, guest checkout, logged-in checkout, payment status, `/user` dashboard, order history, tracking, wishlist, reviews, vouchers, articles, seller public store, live chat with seller.
- Catalog: grid/list toggle, search, filters, sorting, cursor pagination, category, price, type, seller, material, size, location, ready/preorder, promo, seller rating.
- Product detail: media gallery, optional video, description, price, stock, category, dimension, material, seller info, rating/reviews, shipping estimate, promo, add to cart, buy now, related products, seller products, share, open chat with seller.
- Seller: registration, store profile, bank account, product CRUD, stock, order management, shipment/resi update, wallet, withdraw, reports, ads/promotions, referral, notifications, buyer chat inbox.
- Admin: users, sellers, products, categories, orders, payments, withdrawals, news, banners/sliders, ads, promo/voucher, referral, settings, commission, reports, exports, chat moderation visibility.
- Growth features: news/articles, tags, SEO slug, seller ads/manual featured slots, promo/voucher, seller referral, wishlist, review/rating, simple analytics, PDF/Excel/CSV reports.

## Business Rules
- Seller products are auto-published after upload.
- Admin can unpublish/delete products, sellers, articles, ads, chat messages, or content that violates rules.
- Payment gateway default is Midtrans.
- Marketplace uses escrow: buyer pays platform, seller balance becomes available after order completion.
- Revenue model is transaction commission + manual seller ads.
- Shipping v1 is manual: seller/admin inputs courier and tracking number/resi.
- Refund/cancel uses admin approval.
- Withdraw only uses available seller balance from completed orders.
- Minimum withdraw, withdraw fee, payout schedule, commission rate, promo settings, and platform rules are configurable from settings.

## Routes And Interfaces
- Public routes: `/`, `/katalog`, `/produk/{slug}`, `/cart`, `/checkout`, `/payment/{invoice}`, `/artikel`, `/artikel/{slug}`, `/toko/{slug}`.
- Auth routes: `/login`, `/register`, `/seller/register`, `/dashboard`.
- `/dashboard` redirects by role: admin to `/admin`, seller to `/seller`, buyer to `/user`, guest to `/login`.
- User routes: `/user`, `/user/orders`, `/user/orders/{invoice}`, `/user/wishlist`, `/user/addresses`, `/user/profile`, `/user/notifications`, `/user/chats`, `/user/chats/{conversation}`.
- Seller routes: `/seller`, `/seller/store`, `/seller/products`, `/seller/orders`, `/seller/shipments`, `/seller/wallet`, `/seller/withdrawals`, `/seller/ads`, `/seller/referrals`, `/seller/reports`, `/seller/notifications`, `/seller/chats`, `/seller/chats/{conversation}`.
- Admin routes: `/admin` through Filament resources.
- Realtime endpoints: `/sse/notifications`, `/sse/chats/{conversation}`, with polling fallback endpoints for notifications and chat messages.
- Core statuses: product `published/unpublished/deleted`, order `pending_payment/paid/processing/shipped/completed/cancelled/refund_requested/refunded`, payment `pending/paid/failed/expired/refunded`, withdraw `pending/approved/rejected/paid`, ads `pending/active/expired/rejected`, chat message `sent/read/hidden`.

## Integrity And Security
- Backend is source of truth for cart, stock, order, payment, wallet, commission, withdraw, voucher, refund, chat, and notification state.
- Financial and stock-changing operations use database transactions, row locks, idempotency keys, and ledger records.
- Idempotency required for checkout submit, Midtrans webhook, order settlement, withdraw request, and refund processing.
- Rate limits: login 5/min/IP, register 5/min/IP, checkout 5/min/user-or-IP, withdraw 3/min/user, public catalog/search 60/min/IP, cart mutation 30/min/user-or-IP, chat send 30/min/user.
- Midtrans webhook is not normal-throttled; it validates signature and idempotency.
- SoftDeletes required for users, sellers, products, categories, articles, banners, vouchers, ads, conversations, messages, and admin-visible orders.
- Payments, wallet ledgers, commissions, withdraw logs, and audit records are immutable or append-only.
- Prevent N+1 with eager loading, no lazy relationship loading in loops, and `preventLazyLoading` in local/testing.
- Upload security: validate mime, size, dimensions, optimize images, separate public/private disks, block executable uploads.
- Audit logs required for admin moderation, payment changes, withdraw decisions, product unpublish/delete, chat moderation, and settings changes.

## Implementation Order
1. Scaffold Laravel monolith, Breeze Inertia React, Filament, Livewire, Tailwind, auth, roles, permissions, and env-based service config.
2. Fix encoding in [PRODUCTION.MD](<C:/laragon/www/Project Artmarket/PRODUCTION.MD>) and record final architecture decisions.
3. Migrate design system and current landing into Inertia while keeping [DESIGN-SYSTEM.md](<C:/laragon/www/Project Artmarket/DESIGN-SYSTEM.md>) as UI source of truth.
4. Build marketplace core: categories, sellers, products, media, catalog, detail, cart, checkout, orders, Midtrans invoice/payment/webhook.
5. Build escrow ledger: commission, seller balance, completed-order settlement, withdraw request, admin withdraw processing.
6. Build seller dashboard: store setup, products, stock, orders, shipment/resi, wallet, withdraw, ads, referral, reports, notifications.
7. Build user dashboard: orders, tracking, wishlist, addresses, profile, notifications.
8. Build admin Filament resources for operational modules, moderation, settings, reporting, export, backup, sitemap, banners, articles, vouchers, ads.
9. Build chat: conversations, participants, messages, unread counts, user inbox, seller inbox, admin moderation, SSE/polling delivery.
10. Build Phase 2 features: wishlist, reviews/ratings, referral rewards, seller ads, vouchers, analytics, advanced exports.
11. Harden production: cron queue, backups, logs, activity audit, sitemap, indexes, search, cache, rate limits, access policies.

## Test Plan
- Auth and permission tests for guest, user/buyer, seller, and admin access boundaries.
- Catalog tests for search, filters, sorting, cursor pagination, published/unpublished visibility, and seller store listings.
- Checkout tests for guest and logged-in user, address handling, voucher application, stock reservation, cart updates, and order creation.
- Payment tests for Midtrans request, webhook signature validation, idempotency, status transitions, failed/expired/refunded states.
- Race-condition tests for stock decrement, duplicate webhook, duplicate checkout submit, seller balance settlement, withdraw, and refund processing.
- Seller tests for product CRUD, auto-publish, stock update, order processing, shipment/resi update, ads request, referral, and reports.
- Admin tests for moderation/delete, settings, commission, vouchers, ads, withdraw approval/rejection/payment, exports, articles, banners, chat moderation.
- Chat tests for conversation creation, buyer-seller access, message sending, unread counts, read status, hidden messages, SSE auto-close, reconnect backoff, polling fallback, and endpoint authorization.
- Financial tests for commission calculation, escrow ledger, seller available balance, payout eligibility, refund reversal, and report totals.
- Frontend tests for Inertia page rendering, form validation errors, empty states, responsive layout, accessibility labels, focus states, and no horizontal overflow.
- Env-switch tests for `CACHE_DRIVER`, `QUEUE_CONNECTION`, `SESSION_DRIVER`, `SEARCH_DRIVER`, and `REALTIME_DRIVER`; app must keep working without code changes.
- Deployment checks for local Vite production build, uploaded `public/build`, shared-hosting cron queue, database session, file cache, backup, and sitemap.

## Assumptions
- Laravel version is locked during scaffold, with Laravel 11 as safe default unless another version is explicitly chosen.
- Current React Router app will not remain in production.
- Seller product moderation is post-publish, not pre-approval.
- Seller ads are manual/admin-managed in v1, not fully automated billing.
- Admin controls refund/cancel approval.
- Phase 3 scope included only for live chat; auction, multi-language, PWA/mobile app, and AI recommendation stay deferred.
