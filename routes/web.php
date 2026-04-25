<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\SellerRegisteredUserController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\StoreController;
use App\Http\Controllers\Realtime\ChatMessageController;
use App\Http\Controllers\Realtime\ChatPollingController;
use App\Http\Controllers\Realtime\ChatStreamController;
use App\Http\Controllers\Realtime\NotificationPollingController;
use App\Http\Controllers\Realtime\NotificationStreamController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Seller\SellerChatController;
use App\Http\Controllers\Seller\SellerAdController;
use App\Http\Controllers\Seller\SellerNotificationController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerReferralController;
use App\Http\Controllers\Seller\SellerReportController;
use App\Http\Controllers\Seller\SellerShipmentController;
use App\Http\Controllers\Seller\SellerStoreController;
use App\Http\Controllers\Seller\SellerWithdrawController;
use App\Http\Controllers\Seller\SellerWalletController;
use App\Http\Controllers\User\UserAddressController;
use App\Http\Controllers\User\UserChatController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserProductReviewController;
use App\Http\Controllers\User\UserWishlistController;
use App\Http\Controllers\Webhooks\MidtransWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

Route::middleware('throttle:public-catalog')->group(function (): void {
    Route::get('/katalog', CatalogController::class)->name('catalog.index');
    Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('/toko/{seller:slug}', [StoreController::class, 'show'])->name('stores.show');
});

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::middleware('throttle:cart-mutation')->group(function (): void {
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{product:slug}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{product:slug}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
});
Route::post('/produk/{product:slug}/chat', [UserChatController::class, 'startFromProduct'])
    ->middleware(['auth', 'verified'])
    ->name('products.chat.start');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:checkout')
    ->name('checkout.store');
Route::get('/payment/{order:invoice}', [PaymentController::class, 'show'])->name('payments.show');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');

Route::middleware('guest')->group(function (): void {
    Route::get('/seller/register', [SellerRegisteredUserController::class, 'create'])->name('seller.register');
    Route::post('/seller/register', [SellerRegisteredUserController::class, 'store'])
        ->middleware('throttle:register')
        ->name('seller.register.store');
});

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/user', UserDashboardController::class)->name('user.dashboard');
    Route::get('/user/orders', [UserOrderController::class, 'index'])->name('user.orders.index');
    Route::get('/user/orders/{order:invoice}', [UserOrderController::class, 'show'])->name('user.orders.show');
    Route::patch('/user/orders/{order:invoice}/complete', [UserOrderController::class, 'complete'])->name('user.orders.complete');
    Route::patch('/user/orders/{order:invoice}/cancel', [UserOrderController::class, 'cancel'])->name('user.orders.cancel');
    Route::patch('/user/orders/{order:invoice}/refund-request', [UserOrderController::class, 'requestRefund'])->name('user.orders.refund-request');
    Route::post('/user/orders/{order:invoice}/items/{orderItem}/review', [UserProductReviewController::class, 'store'])
        ->middleware('throttle:review-submit')
        ->name('user.orders.items.review.store');
    Route::get('/user/wishlist', [UserWishlistController::class, 'index'])->name('user.wishlist.index');
    Route::post('/user/wishlist/{product:slug}', [UserWishlistController::class, 'store'])->name('user.wishlist.store');
    Route::delete('/user/wishlist/{product:slug}', [UserWishlistController::class, 'destroy'])->name('user.wishlist.destroy');
    Route::get('/user/addresses', [UserAddressController::class, 'index'])->name('user.addresses.index');
    Route::post('/user/addresses', [UserAddressController::class, 'store'])->name('user.addresses.store');
    Route::patch('/user/addresses/{address}', [UserAddressController::class, 'update'])->name('user.addresses.update');
    Route::delete('/user/addresses/{address}', [UserAddressController::class, 'destroy'])->name('user.addresses.destroy');
    Route::get('/user/notifications', [UserNotificationController::class, 'index'])->name('user.notifications.index');
    Route::patch('/user/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('user.notifications.read-all');
    Route::patch('/user/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])->name('user.notifications.read');
    Route::get('/user/chats', [UserChatController::class, 'index'])->name('user.chats.index');
    Route::get('/user/chats/{conversation}', [UserChatController::class, 'show'])->name('user.chats.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/sse/notifications', NotificationStreamController::class)->name('sse.notifications');
    Route::get('/sse/chats/{conversation}', ChatStreamController::class)->name('sse.chats.show');
    Route::get('/polling/notifications', NotificationPollingController::class)->name('polling.notifications');
    Route::get('/polling/chats/{conversation}', ChatPollingController::class)->name('polling.chats.show');
    Route::post('/chats/{conversation}/messages', [ChatMessageController::class, 'store'])
        ->middleware('throttle:chat-send')
        ->name('chats.messages.store');
});

Route::middleware(['auth', 'verified', 'role:seller|admin'])->prefix('seller')->name('seller.')->group(function (): void {
    Route::get('/', SellerDashboardController::class)->name('dashboard');
    Route::get('/store', [SellerStoreController::class, 'edit'])->name('store.edit');
    Route::patch('/store', [SellerStoreController::class, 'update'])->name('store.update');
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product:slug}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product:slug}', [SellerProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product:slug}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product:slug}/stock', [SellerProductController::class, 'updateStock'])->name('products.stock.update');
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{orderItem}/shipment', [SellerOrderController::class, 'updateShipment'])->name('orders.shipment.update');
    Route::get('/shipments', [SellerShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/wallet', SellerWalletController::class)->name('wallet.index');
    Route::get('/withdrawals', [SellerWithdrawController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [SellerWithdrawController::class, 'store'])->name('withdrawals.store')->middleware('throttle:withdraw');
    Route::get('/ads', [SellerAdController::class, 'index'])->name('ads.index');
    Route::post('/ads', [SellerAdController::class, 'store'])->name('ads.store');
    Route::delete('/ads/{sellerAd}', [SellerAdController::class, 'destroy'])->name('ads.destroy');
    Route::get('/referrals', SellerReferralController::class)->name('referrals.index');
    Route::get('/reports', SellerReportController::class)->name('reports.index');
    Route::get('/notifications', [SellerNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [SellerNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [SellerNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/chats', [SellerChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{conversation}', [SellerChatController::class, 'show'])->name('chats.show');
});

require __DIR__.'/auth.php';




