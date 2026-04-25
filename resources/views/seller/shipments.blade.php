<x-seller-layout title="Pengiriman Seller">
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('seller.shipments.index', ['status' => 'ready']) }}" class="rounded-ds-card border {{ $filter === 'ready' ? 'border-gold bg-gold/10' : 'border-ink/10 bg-paper' }} p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Siap Kirim</p>
                <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $readyCount }}</p>
            </a>
            <a href="{{ route('seller.shipments.index', ['status' => 'shipped']) }}" class="rounded-ds-card border {{ $filter === 'shipped' ? 'border-gold bg-gold/10' : 'border-ink/10 bg-paper' }} p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Sudah Dikirim</p>
                <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $shippedCount }}</p>
            </a>
            <a href="{{ route('seller.shipments.index', ['status' => 'all']) }}" class="rounded-ds-card border {{ $filter === 'all' ? 'border-gold bg-gold/10' : 'border-ink/10 bg-paper' }} p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Semua Shipment</p>
                <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $allCount }}</p>
            </a>
        </div>

        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Operasional Pengiriman</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Update Courier dan Resi</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    Hanya order yang sudah paid yang masuk ke halaman ini. Resi tersimpan di order item seller terkait.
                </p>
            </div>

            @error('courier')<p class="mb-4 rounded-ds-card bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            @error('tracking_number')<p class="mb-4 rounded-ds-card bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                        <tr>
                            <th class="py-3">Invoice</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3">Pembeli</th>
                            <th class="py-3">Alamat</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Resi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/8">
                        @forelse ($items as $item)
                            @php($shipping = $item->order?->shipping_snapshot ?? [])
                            <tr>
                                <td class="py-4">
                                    <div class="font-semibold text-ink">{{ $item->order?->invoice }}</div>
                                    <div class="text-xs text-ink-muted">{{ $item->order?->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td class="py-4">
                                    <div class="font-display text-base font-semibold text-ink">{{ $item->product_title }}</div>
                                    <div class="text-xs text-ink-muted">
                                        Qty {{ $item->quantity }} - Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="py-4 text-ink-muted">
                                    {{ $item->order?->guest_name ?? '-' }}<br>
                                    <span class="text-xs">{{ $item->order?->guest_phone ?? '-' }}</span>
                                </td>
                                <td class="py-4 text-ink-muted">
                                    {{ $shipping['address'] ?? '-' }}<br>
                                    <span class="text-xs">
                                        {{ $shipping['city'] ?? '' }}
                                        {{ $shipping['province'] ?? '' }}
                                        {{ $shipping['postal_code'] ?? '' }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">
                                        {{ $item->status->label() }}
                                    </span>
                                    @if ($item->shipped_at)
                                        <div class="mt-2 text-xs text-ink-muted">{{ $item->shipped_at->format('d M Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="py-4">
                                    <form method="POST" action="{{ route('seller.orders.shipment.update', $item) }}" class="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="shipment-courier-{{ $item->id }}">Courier</label>
                                        <input id="shipment-courier-{{ $item->id }}" name="courier" value="{{ old('courier', $item->courier) }}" placeholder="Courier" class="border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold" required>
                                        <label class="sr-only" for="shipment-tracking-{{ $item->id }}">Nomor resi</label>
                                        <input id="shipment-tracking-{{ $item->id }}" name="tracking_number" value="{{ old('tracking_number', $item->tracking_number) }}" placeholder="Nomor resi" class="border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold" required>
                                        <button type="submit" class="border border-ink/20 px-3 py-2 text-xs font-bold uppercase tracking-widest hover:border-gold hover:text-gold">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-ink-muted">Belum ada shipment pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $items->links() }}</div>
        </div>
    </section>
</x-seller-layout>
