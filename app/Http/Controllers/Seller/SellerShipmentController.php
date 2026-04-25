<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user()?->seller;
        $isAdmin = (bool) $request->user()?->hasRole('admin');

        abort_unless($seller || $isAdmin, 403);

        $filter = $request->string('status')->toString();
        $filter = in_array($filter, ['ready', 'shipped', 'all'], true) ? $filter : 'ready';

        $baseQuery = $this->baseQuery($request);

        $items = (clone $baseQuery)
            ->when($filter === 'ready', fn ($query) => $this->readyToShipScope($query))
            ->when($filter === 'shipped', fn ($query) => $this->shippedScope($query))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('seller.shipments', [
            'items' => $items,
            'filter' => $filter,
            'readyCount' => $this->readyToShipScope(clone $baseQuery)->count(),
            'shippedCount' => $this->shippedScope(clone $baseQuery)->count(),
            'allCount' => (clone $baseQuery)->count(),
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        $seller = $request->user()?->seller;

        return OrderItem::query()
            ->with([
                'order:id,invoice,status,payment_status,guest_name,guest_phone,shipping_snapshot,created_at',
                'product:id,title,slug',
                'seller:id,store_name',
            ])
            ->whereHas('order', fn ($query) => $query->where('payment_status', PaymentStatus::Paid->value))
            ->whereIn('status', [
                OrderStatus::Paid->value,
                OrderStatus::Processing->value,
                OrderStatus::Shipped->value,
            ])
            ->when(! $request->user()?->hasRole('admin'), fn ($query) => $query->where('seller_id', $seller?->id));
    }

    private function readyToShipScope(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [OrderStatus::Paid->value, OrderStatus::Processing->value])
            ->whereNull('tracking_number');
    }

    private function shippedScope(Builder $query): Builder
    {
        return $query->where(function ($nested): void {
            $nested
                ->where('status', OrderStatus::Shipped->value)
                ->orWhereNotNull('tracking_number');
        });
    }
}
