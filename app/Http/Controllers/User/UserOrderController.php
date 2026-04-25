<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderResolutionRequest;
use App\Models\Order;
use App\Services\Orders\OrderCompletionService;
use App\Services\Orders\OrderResolutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with(['items:id,order_id,product_title,quantity,subtotal,status,courier,tracking_number,shipped_at'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('User/Orders', [
            'orders' => $orders->through(fn (Order $order): array => $this->serializeOrderCard($order)),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load([
            'items.product:id,title,slug',
            'items.review:id,order_item_id,rating,title,body,status,created_at',
            'items.seller:id,store_name,slug',
            'payments' => fn ($query) => $query->latest('id'),
        ]);

        return Inertia::render('User/OrderShow', [
            'order' => [
                ...$this->serializeOrderCard($order),
                'subtotal' => (float) $order->subtotal,
                'discount_total' => (float) $order->discount_total,
                'shipping_total' => (float) $order->shipping_total,
                'commission_total' => (float) $order->commission_total,
                'shipping_snapshot' => $order->shipping_snapshot,
                'completed_at' => $order->completed_at?->toISOString(),
                'cancelled_at' => $order->cancelled_at?->toISOString(),
                'refund_requested_at' => $order->refund_requested_at?->toISOString(),
                'refunded_at' => $order->refunded_at?->toISOString(),
                'customer_note' => $order->customer_note,
                'admin_note' => $order->admin_note,
                'can_complete' => $order->payment_status->value === 'paid'
                    && in_array($order->status->value, ['paid', 'processing', 'shipped'], true),
                'can_cancel' => $order->payment_status->value !== 'paid'
                    && $order->status->value === 'pending_payment',
                'can_request_refund' => $order->payment_status->value === 'paid'
                    && in_array($order->status->value, ['paid', 'processing', 'shipped', 'completed'], true),
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_title' => $item->product_title,
                    'product_slug' => $item->product?->slug,
                    'seller' => $item->seller ? [
                        'store_name' => $item->seller->store_name,
                        'slug' => $item->seller->slug,
                    ] : null,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'status' => $item->status->value,
                    'courier' => $item->courier,
                    'tracking_number' => $item->tracking_number,
                    'shipped_at' => $item->shipped_at?->toISOString(),
                    'can_review' => $order->status === OrderStatus::Completed
                        && $order->payment_status === PaymentStatus::Paid
                        && $item->product_id !== null
                        && $item->review === null,
                    'review' => $item->review ? [
                        'id' => $item->review->id,
                        'rating' => $item->review->rating,
                        'title' => $item->review->title,
                        'body' => $item->review->body,
                        'status' => $item->review->status->value,
                        'created_at' => $item->review->created_at?->toISOString(),
                    ] : null,
                ])->values(),
                'payment' => $order->payments->first() ? [
                    'gateway' => $order->payments->first()->gateway,
                    'status' => $order->payments->first()->status->value,
                    'amount' => (float) $order->payments->first()->amount,
                    'redirect_url' => $order->payments->first()->raw_payload['redirect_url'] ?? null,
                ] : null,
            ],
        ]);
    }

    public function complete(Request $request, Order $order, OrderCompletionService $completion): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $completion->complete($order);

        return back()->with('status', 'Order diselesaikan dan dana seller dirilis dari escrow.');
    }

    public function cancel(OrderResolutionRequest $request, Order $order, OrderResolutionService $resolution): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $resolution->cancelUnpaid($order, $request->note());

        return back()->with('status', 'Order dibatalkan dan stok dikembalikan.');
    }

    public function requestRefund(OrderResolutionRequest $request, Order $order, OrderResolutionService $resolution): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $resolution->requestRefund($order, $request->note());

        return back()->with('status', 'Request refund dikirim. Admin akan meninjau pengajuan ini.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeOrderCard(Order $order): array
    {
        return [
            'invoice' => $order->invoice,
            'status' => $order->status->value,
            'payment_status' => $order->payment_status->value,
            'grand_total' => (float) $order->grand_total,
            'created_at' => $order->created_at?->toISOString(),
            'items_count' => $order->items->sum('quantity'),
            'first_item' => $order->items->first()?->product_title,
        ];
    }
}
