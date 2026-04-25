<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\StoreProductReviewRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Reviews\ProductReviewService;
use Illuminate\Http\RedirectResponse;

class UserProductReviewController extends Controller
{
    public function store(
        StoreProductReviewRequest $request,
        Order $order,
        OrderItem $orderItem,
        ProductReviewService $reviews,
    ): RedirectResponse {
        abort_unless($order->user_id === $request->user()->id, 404);

        $reviews->createForOrderItem($request->user(), $order, $orderItem, $request->validated());

        return back()->with('status', 'Terima kasih, ulasan Anda sudah dipublikasikan.');
    }
}
