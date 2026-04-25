<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserAddressController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('User/Addresses', [
            'addresses' => $request->user()
                ->addresses()
                ->latest('is_default')
                ->latest('id')
                ->get()
                ->map(fn (Address $address): array => $this->serializeAddress($address))
                ->values(),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        if (! $request->user()->addresses()->exists()) {
            $data['is_default'] = true;
        }

        $request->user()->addresses()->create($data);

        return back()->with('status', 'Alamat ditambahkan.');
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('status', 'Alamat diperbarui.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->oldest('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('status', 'Alamat dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAddress(Address $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'district' => $address->district,
            'postal_code' => $address->postal_code,
            'address_line' => $address->address_line,
            'is_default' => $address->is_default,
        ];
    }
}
