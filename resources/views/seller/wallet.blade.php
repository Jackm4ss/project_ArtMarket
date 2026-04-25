<x-seller-layout title="Wallet Seller">
    <section class="grid gap-6 lg:grid-cols-[0.75fr_1.25fr]">
        <aside class="space-y-6">
            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-gold-dark">Saldo tersedia</p>
                <p class="mt-3 font-display text-4xl font-semibold text-ink">Rp {{ number_format((float) $available, 0, ',', '.') }}</p>
                <p class="mt-4 text-sm leading-relaxed text-ink-muted">
                    Saldo tersedia hanya berasal dari ledger escrow yang sudah dirilis setelah order selesai.
                </p>
                <a href="{{ route('seller.withdrawals.index') }}" class="mt-5 inline-flex text-sm font-bold uppercase tracking-widest text-gold-dark hover:text-ink">
                    Buka halaman withdraw
                </a>
            </div>

            @if ($seller)
                <div class="rounded-ds-card bg-surface p-6 shadow-soft">
                    <h2 class="font-display text-2xl font-semibold">Request Withdraw</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                        Minimum Rp {{ number_format((float) $withdrawMinimum, 0, ',', '.') }}. Biaya admin Rp {{ number_format((float) $withdrawFee, 0, ',', '.') }}.
                    </p>
                    <form method="POST" action="{{ route('seller.withdrawals.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="amount" class="mb-1 block text-xs font-bold uppercase tracking-widest text-ink-muted">Nominal</label>
                            <input id="amount" name="amount" type="number" min="1" step="1000" class="w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm focus:border-gold focus:ring-gold" required>
                            @error('amount')<p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-ds-card border border-ink/10 bg-paper p-4 text-sm text-ink-muted">
                            <strong class="text-ink">Rekening:</strong><br>
                            {{ $seller->bank_name ?: '-' }} / {{ $seller->bank_account_name ?: '-' }} / {{ $seller->bank_account_number ?: '-' }}
                        </div>
                        <button type="submit" class="btn-elegant w-full bg-ink px-5 py-3 text-sm font-bold uppercase tracking-widest text-cream hover:bg-ink-light">
                            Ajukan Withdraw
                        </button>
                    </form>
                </div>
            @endif
        </aside>

        <div class="space-y-6">
            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <h2 class="font-display text-2xl font-semibold">Withdraw Terbaru</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left text-sm">
                        <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                            <tr>
                                <th class="py-3">Tanggal</th>
                                <th class="py-3">Nominal</th>
                                <th class="py-3">Fee</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/8">
                            @forelse ($withdraws as $withdraw)
                                <tr>
                                    <td class="py-4 text-ink-muted">{{ $withdraw->requested_at?->format('d M Y H:i') }}</td>
                                    <td class="py-4 font-semibold">Rp {{ number_format((float) $withdraw->amount, 0, ',', '.') }}</td>
                                    <td class="py-4 text-ink-muted">Rp {{ number_format((float) $withdraw->fee, 0, ',', '.') }}</td>
                                    <td class="py-4"><span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $withdraw->status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-ink-muted">Belum ada withdraw.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <h2 class="font-display text-2xl font-semibold">Ledger</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                            <tr>
                                <th class="py-3">Tanggal</th>
                                <th class="py-3">Tipe</th>
                                <th class="py-3">Deskripsi</th>
                                <th class="py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/8">
                            @forelse ($ledgers as $ledger)
                                <tr>
                                    <td class="py-4 text-ink-muted">{{ $ledger->occurred_at?->format('d M Y H:i') }}</td>
                                    <td class="py-4"><span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $ledger->type }}</span></td>
                                    <td class="py-4">{{ $ledger->description }}</td>
                                    <td class="py-4 text-right font-semibold">Rp {{ number_format((float) $ledger->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-ink-muted">Ledger masih kosong.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $ledgers->links() }}</div>
            </div>
        </div>
    </section>
</x-seller-layout>
