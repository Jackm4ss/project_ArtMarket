<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartManager;
use App\Services\Payments\MidtransPaymentService;
use App\Services\Vouchers\VoucherService;
use App\Support\MarketplaceConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartManager $cart,
        private readonly MidtransPaymentService $paymentService,
        private readonly VoucherService $vouchers,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createOrder(array $data, ?User $user): Order
    {
        $existingOrder = Order::query()
            ->where('idempotency_key', $data['idempotency_key'])
            ->with(['items.product', 'payments'])
            ->first();

        if ($existingOrder) {
            return $existingOrder;
        }

        $cartItems = $this->cart->rawItems();

        if ($cartItems === []) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang masih kosong.',
            ]);
        }

        return DB::transaction(function () use ($cartItems, $data, $user): Order {
            $products = Product::query()
                ->with(['seller:id,store_name,slug,location', 'category:id,name,slug', 'media'])
                ->whereIn('id', array_keys($cartItems))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $commissionTotal = 0.0;
            $items = [];
            $commissionRate = MarketplaceConfig::commissionRate();

            foreach ($cartItems as $productId => $quantity) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product || $product->status !== ProductStatus::Published) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada produk yang sudah tidak tersedia.',
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->title} tidak mencukupi.",
                    ]);
                }

                $lineSubtotal = round((float) $product->price * $quantity, 2);
                $commissionAmount = round($lineSubtotal * $commissionRate, 2);
                $subtotal += $lineSubtotal;
                $commissionTotal += $commissionAmount;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_subtotal' => $lineSubtotal,
                    'commission_amount' => $commissionAmount,
                ];
            }

            $voucherQuote = $this->vouchers->quote($data['voucher_code'] ?? null, $subtotal, $user, $data);
            $discountTotal = $voucherQuote?->discountAmount ?? 0.0;
            $shippingTotal = 0.0;
            $grandTotal = max(0, round($subtotal - $discountTotal + $shippingTotal, 2));

            $order = Order::query()->create([
                'user_id' => $user?->id,
                'voucher_id' => $voucherQuote?->voucher->id,
                'invoice' => $this->generateInvoice(),
                'guest_name' => $data['name'],
                'guest_email' => $data['email'],
                'guest_phone' => $data['phone'],
                'status' => OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Pending,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'shipping_total' => $shippingTotal,
                'commission_total' => $commissionTotal,
                'grand_total' => $grandTotal,
                'currency' => MarketplaceConfig::currency(),
                'idempotency_key' => $data['idempotency_key'],
                'shipping_snapshot' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'province' => $data['province'],
                    'postal_code' => $data['postal_code'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ],
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $quantity = (int) $item['quantity'];

                $order->items()->create([
                    'seller_id' => $product->seller_id,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'product_snapshot' => $this->snapshotProduct($product),
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $item['line_subtotal'],
                    'commission_amount' => $item['commission_amount'],
                    'status' => OrderStatus::PendingPayment,
                ]);

                $product->decrement('stock', $quantity);
            }

            if ($voucherQuote) {
                $this->vouchers->redeem($voucherQuote, $order, $user, $data);
            }

            $this->paymentService->createInvoice($order->fresh(['items']));
            $this->cart->clear();

            return $order->fresh(['items.product', 'payments']);
        });
    }

    private function generateInvoice(): string
    {
        do {
            $invoice = 'AM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Order::query()->where('invoice', $invoice)->exists());

        return $invoice;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'price' => (float) $product->price,
            'seller' => $product->seller ? [
                'id' => $product->seller->id,
                'store_name' => $product->seller->store_name,
                'slug' => $product->seller->slug,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'image' => $product->getFirstMediaUrl('products') ?: $product->getFirstMediaUrl(),
            'material' => $product->material,
            'dimensions' => $product->dimensions,
            'location' => $product->location,
        ];
    }
}
