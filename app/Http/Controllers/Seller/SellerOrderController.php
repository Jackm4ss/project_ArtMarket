<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\Notifications\MarketplaceNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user()?->seller;

        $items = OrderItem::query()
            ->with(['order:id,invoice,status,payment_status,guest_name,grand_total,created_at', 'product:id,title,slug', 'seller:id,store_name'])
            ->when(! $request->user()?->hasRole('admin'), fn ($query) => $query->where('seller_id', $seller?->id))
            ->latest('id')
            ->paginate(15);

        return view('seller.orders', ['items' => $items]);
    }

    public function updateShipment(Request $request, OrderItem $orderItem, MarketplaceNotificationService $notifications): RedirectResponse
    {
        $seller = $request->user()?->seller;

        abort_unless($request->user()?->hasRole('admin') || $orderItem->seller_id === $seller?->id, 403);

        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:120'],
            'tracking_number' => ['required', 'string', 'max:120'],
        ]);

        $orderItem->update([
            ...$validated,
            'status' => OrderStatus::Shipped,
            'shipped_at' => now(),
        ]);

        $orderItem->order()->update(['status' => OrderStatus::Shipped]);
        $notifications->orderShipmentUpdated($orderItem->fresh(['order.user', 'seller.user']));

        return back()->with('status', 'Resi pengiriman diperbarui.');
    }
}
