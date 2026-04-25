<x-seller-layout title="Laporan Seller">
    <section class="space-y-6">
        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="flex flex-col justify-between gap-5 border-b border-ink/10 pb-5 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Performance Snapshot</p>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Ringkasan Penjualan</h2>
                    <p class="mt-2 text-sm text-ink-muted">
                        Laporan dihitung dari order paid pada periode yang dipilih. Angka net adalah subtotal seller dikurangi komisi platform.
                    </p>
                </div>

                <form method="GET" action="{{ route('seller.reports.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                    <div>
                        <label for="start_date" class="mb-1 block text-xs font-bold uppercase tracking-widest text-ink-muted">Mulai</label>
                        <input id="start_date" name="start_date" type="date" value="{{ $startDate->format('Y-m-d') }}" class="w-full border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold">
                    </div>
                    <div>
                        <label for="end_date" class="mb-1 block text-xs font-bold uppercase tracking-widest text-ink-muted">Selesai</label>
                        <input id="end_date" name="end_date" type="date" value="{{ $endDate->format('Y-m-d') }}" class="w-full border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold">
                    </div>
                    <button type="submit" class="self-end border border-ink/20 px-4 py-2 text-xs font-bold uppercase tracking-widest hover:border-gold hover:text-gold">
                        Terapkan
                    </button>
                </form>
            </div>

            @error('end_date')
                <p class="mt-4 rounded-ds-card bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $message }}</p>
            @enderror

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-ds-card bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Gross Sales</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">Rp {{ number_format((float) $grossSales, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-ds-card bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Komisi</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">Rp {{ number_format((float) $commissionTotal, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-ds-card bg-gold/10 p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold-dark">Net Seller</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">Rp {{ number_format((float) $netSales, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-ds-card bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Invoice</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $orderCount }}</p>
                </div>
                <div class="rounded-ds-card bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Item Terjual</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $itemsSold }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <div class="border-b border-ink/10 pb-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Status</p>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Breakdown Order Item</h2>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($statusBreakdown as $status => $total)
                        <div class="flex items-center justify-between rounded-ds-card bg-cream px-4 py-3">
                            <span class="text-sm font-semibold text-ink">{{ $status }}</span>
                            <span class="rounded-ds-badge bg-ink px-2.5 py-1 text-xs font-bold text-cream">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="rounded-ds-card bg-cream px-4 py-8 text-center text-sm text-ink-muted">Belum ada transaksi paid pada periode ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <div class="border-b border-ink/10 pb-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Top Produk</p>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Produk Terlaris</h2>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                            <tr>
                                <th class="py-3">Produk</th>
                                <th class="py-3">Qty</th>
                                <th class="py-3">Gross</th>
                                <th class="py-3">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/8">
                            @forelse ($topProducts as $product)
                                <tr>
                                    <td class="py-4 font-display text-base font-semibold text-ink">{{ $product['title'] }}</td>
                                    <td class="py-4 text-ink-muted">{{ $product['quantity_sold'] }}</td>
                                    <td class="py-4">Rp {{ number_format((float) $product['gross_total'], 0, ',', '.') }}</td>
                                    <td class="py-4 font-semibold">Rp {{ number_format((float) $product['net_total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-ink-muted">Belum ada produk terjual pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Recent Activity</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Order Item Terbaru</h2>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[840px] text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                        <tr>
                            <th class="py-3">Invoice</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3">Pembeli</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/8">
                        @forelse ($recentItems as $item)
                            <tr>
                                <td class="py-4 font-semibold">{{ $item->order?->invoice }}</td>
                                <td class="py-4">
                                    <div class="font-display text-base font-semibold text-ink">{{ $item->product_title }}</div>
                                    <div class="text-xs text-ink-muted">{{ $item->order?->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td class="py-4 text-ink-muted">{{ $item->order?->guest_name ?? '-' }}</td>
                                <td class="py-4">
                                    <span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $item->status->label() }}</span>
                                </td>
                                <td class="py-4 text-right font-semibold">
                                    Rp {{ number_format((float) $item->subtotal - (float) $item->commission_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-ink-muted">Belum ada order item terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-seller-layout>
