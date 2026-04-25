<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class SellerRegisteredUserController extends Controller
{
    public function create(Request $request, ReferralService $referrals): Response
    {
        $referralCode = is_string($request->query('ref')) ? strtoupper(trim($request->query('ref'))) : null;
        $referrer = $referrals->referrerForCode($referralCode);

        return Inertia::render('Auth/SellerRegister', [
            'referralCode' => $referralCode,
            'referrerName' => $referrer?->seller?->store_name ?? $referrer?->name,
        ]);
    }

    public function store(Request $request, ReferralService $referrals): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
            'referral_code' => ['nullable', 'string', 'max:80'],
        ]);

        $referrals->assertValidCode($validated['referral_code'] ?? null);

        $user = DB::transaction(function () use ($validated, $referrals): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

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

            $referrals->createPendingForSellerRegistration($user, $validated['referral_code'] ?? null);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('seller.dashboard', absolute: false));
    }
}
