<x-seller-layout title="Order Seller">
    <section class="rounded-ds-card bg-paper p-6 shadow-soft">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                    <tr>
                        <th class="py-3">Invoice</th>
                        <th class="py-3">Produk</th>
                        <th class="py-3">Pembeli</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Resi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/8">
                    @forelse ($items as $item)
                        <tr>
                            <td class="py-4 font-semibold">{{ $item->order?->invoice }}</td>
                            <td class="py-4">
                                <div class="font-display text-base font-semibold text-ink">{{ $item->product_title }}</div>
                                <div class="text-xs text-ink-muted">Qty {{ $item->quantity }} - Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-4 text-ink-muted">{{ $item->order?->guest_name ?? '-' }}</td>
                            <td class="py-4"><span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $item->status->label() }}</span></td>
                            <td class="py-4">
                                <form method="POST" action="{{ route('seller.orders.shipment.update', $item) }}" class="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="courier-{{ $item->id }}">Courier</label>
                                    <input id="courier-{{ $item->id }}" name="courier" value="{{ $item->courier }}" placeholder="Courier" class="border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold">
                                    <label class="sr-only" for="tracking-{{ $item->id }}">Nomor resi</label>
                                    <input id="tracking-{{ $item->id }}" name="tracking_number" value="{{ $item->tracking_number }}" placeholder="Nomor resi" class="border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold">
                                    <button type="submit" class="border border-ink/20 px-3 py-2 text-xs font-bold uppercase tracking-widest hover:border-gold hover:text-gold">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-ink-muted">Belum ada order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $items->links() }}</div>
    </section>
</x-seller-layout>
