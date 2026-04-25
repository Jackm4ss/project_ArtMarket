<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SellerReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $seller = $request->user()?->seller;
        $isAdmin = (bool) $request->user()?->hasRole('admin');

        abort_unless($seller || $isAdmin, 403);

        $startDate = filled($validated['start_date'] ?? null)
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->subDays(30)->startOfDay();
        $endDate = filled($validated['end_date'] ?? null)
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfDay();

        $baseQuery = $this->baseQuery($request, $startDate, $endDate);

        $grossSales = (float) (clone $baseQuery)->sum('subtotal');
        $commissionTotal = (float) (clone $baseQuery)->sum('commission_amount');
        $netSales = round($grossSales - $commissionTotal, 2);
        $itemsSold = (int) (clone $baseQuery)->sum('quantity');
        $orderCount = (int) (clone $baseQuery)->distinct('order_id')->count('order_id');

        $statusBreakdown = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn (int $total, string $status): array => [
                OrderStatus::from($status)->label() => $total,
            ]);

        $topProducts = (clone $baseQuery)
            ->select([
                'product_id',
                'product_title',
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as gross_total'),
                DB::raw('SUM(commission_amount) as commission_total'),
            ])
            ->groupBy('product_id', 'product_title')
            ->orderByDesc('gross_total')
            ->limit(8)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'product_id' => $item->product_id,
                'title' => $item->product_title,
                'quantity_sold' => (int) $item->quantity_sold,
                'gross_total' => (float) $item->gross_total,
                'commission_total' => (float) $item->commission_total,
                'net_total' => round((float) $item->gross_total - (float) $item->commission_total, 2),
            ]);

        $recentItems = (clone $baseQuery)
            ->with(['order:id,invoice,guest_name,created_at,status,payment_status', 'product:id,title,slug'])
            ->latest('id')
            ->limit(10)
            ->get();

        return view('seller.reports', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'grossSales' => $grossSales,
            'commissionTotal' => $commissionTotal,
            'netSales' => $netSales,
            'itemsSold' => $itemsSold,
            'orderCount' => $orderCount,
            'statusBreakdown' => $statusBreakdown,
            'topProducts' => $topProducts,
            'recentItems' => $recentItems,
        ]);
    }

    private function baseQuery(Request $request, Carbon $startDate, Carbon $endDate): Builder
    {
        $seller = $request->user()?->seller;

        return OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($startDate, $endDate): void {
                $query
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->whereIn('status', [
                OrderStatus::Paid->value,
                OrderStatus::Processing->value,
                OrderStatus::Shipped->value,
                OrderStatus::Completed->value,
            ])
            ->when(! $request->user()?->hasRole('admin'), fn (Builder $query) => $query->where('seller_id', $seller?->id));
    }
}
