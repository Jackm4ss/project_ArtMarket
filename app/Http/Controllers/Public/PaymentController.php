<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load([
            'items.seller:id,store_name,slug',
            'payments' => fn ($query) => $query->latest('id'),
        ]);

        $payment = $order->payments->first();

        return Inertia::render('Public/PaymentStatus', [
            'order' => [
                'invoice' => $order->invoice,
                'status' => $order->status->value,
                'payment_status' => $order->payment_status->value,
                'subtotal' => (float) $order->subtotal,
                'discount_total' => (float) $order->discount_total,
                'shipping_total' => (float) $order->shipping_total,
                'grand_total' => (float) $order->grand_total,
                'shipping_snapshot' => $order->shipping_snapshot,
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_title' => $item->product_title,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'seller' => $item->seller ? [
                        'store_name' => $item->seller->store_name,
                        'slug' => $item->seller->slug,
                    ] : null,
                ])->values(),
                'payment' => $payment ? [
                    'invoice' => $payment->invoice,
                    'gateway' => $payment->gateway,
                    'gateway_reference' => $payment->gateway_reference,
                    'status' => $payment->status->value,
                    'amount' => (float) $payment->amount,
                    'redirect_url' => $payment->raw_payload['redirect_url'] ?? null,
                    'message' => $payment->raw_payload['message'] ?? null,
                ] : null,
            ],
        ]);
    }
}
