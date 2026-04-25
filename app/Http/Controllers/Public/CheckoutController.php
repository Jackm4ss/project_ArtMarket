<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Services\Cart\CartManager;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function create(CartManager $cart): Response
    {
        return Inertia::render('Public/Checkout', [
            'cart' => $cart->summary(),
            'checkout' => [
                'idempotency_key' => (string) Str::uuid(),
                'defaults' => [
                    'name' => request()->user()?->name ?? '',
                    'email' => request()->user()?->email ?? '',
                    'phone' => '',
                    'address' => '',
                    'city' => '',
                    'province' => '',
                    'postal_code' => '',
                    'voucher_code' => '',
                    'notes' => '',
                ],
            ],
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->createOrder($request->validated(), $request->user());

        return redirect()
            ->route('payments.show', $order)
            ->with('status', 'Order berhasil dibuat. Silakan lanjutkan pembayaran.');
    }
}
