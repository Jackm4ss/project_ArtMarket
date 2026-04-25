<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $orders = Order::query()
            ->with(['items:id,order_id,product_title,quantity,subtotal,status,courier,tracking_number,shipped_at'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return Inertia::render('User/Dashboard', [
            'summary' => [
                'orders_count' => $user->orders()->count(),
                'wishlist_count' => $user->wishlists()->count(),
                'addresses_count' => $user->addresses()->count(),
                'unread_notifications_count' => $user->unreadNotifications()->count(),
            ],
            'recentOrders' => $orders->map(fn (Order $order): array => $this->serializeOrderCard($order))->values(),
        ]);
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
