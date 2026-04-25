<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class SellerOnboardingController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user()->loadMissing('seller');

        if ($user->seller || $user->hasRole('seller') || $user->hasRole('admin')) {
            return redirect()->route('seller.dashboard');
        }

        return Inertia::render('Seller/Onboarding');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('seller');

        if ($user->seller || $user->hasRole('seller') || $user->hasRole('admin')) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'store_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sellers', 'store_name')->whereNull('deleted_at'),
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $user->assignRole(Role::findOrCreate('seller'));

            Seller::query()->create([
                'user_id' => $user->id,
                'store_name' => $validated['store_name'],
                'bio' => $validated['bio'] ?? null,
                'status' => 'active',
                'location' => $validated['location'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('seller.dashboard')
            ->with('status', 'Toko berhasil dibuat. Anda sudah bisa mulai mengelola karya.');
    }
}
