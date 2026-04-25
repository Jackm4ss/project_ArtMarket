<x-seller-layout title="Withdraw Seller">
    <section class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr]">
        <aside class="space-y-6">
            <div class="rounded-ds-card bg-paper p-6 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-gold-dark">Saldo Tersedia</p>
                <p class="mt-3 font-display text-4xl font-semibold text-ink">
                    Rp {{ number_format((float) $available, 0, ',', '.') }}
                </p>
                <p class="mt-4 text-sm leading-relaxed text-ink-muted">
                    Saldo ini sudah dikurangi request withdraw pending dan hanya berasal dari order selesai.
                </p>
            </div>

            <div class="rounded-ds-card bg-surface p-6 shadow-soft">
                <h2 class="font-display text-2xl font-semibold text-ink">Ajukan Withdraw</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                    Minimum Rp {{ number_format((float) $withdrawMinimum, 0, ',', '.') }} dengan biaya admin Rp {{ number_format((float) $withdrawFee, 0, ',', '.') }}.
                </p>

                <div class="mt-5 rounded-ds-card border border-ink/10 bg-paper p-4 text-sm text-ink-muted">
                    <p class="font-bold uppercase tracking-widest text-ink">Rekening payout</p>
                    <p class="mt-2">
                        {{ $seller->bank_name ?: '-' }} /
                        {{ $seller->bank_account_name ?: '-' }} /
                        {{ $seller->bank_account_number ?: '-' }}
                    </p>
                    @if (! $seller->bank_name || ! $seller->bank_account_name || ! $seller->bank_account_number)
                        <p class="mt-3 text-xs font-semibold text-red-700">
                            Lengkapi rekening payout di halaman toko sebelum mengajukan withdraw.
                        </p>
                    @endif
                </div>

                <form method="POST" action="{{ route('seller.withdrawals.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="amount" class="mb-1 block text-xs font-bold uppercase tracking-widest text-ink-muted">Nominal</label>
                        <input
                            id="amount"
                            name="amount"
                            type="number"
                            min="1"
                            step="1000"
                            value="{{ old('amount') }}"
                            class="w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm focus:border-gold focus:ring-gold"
                            required
                        >
                        @error('amount')<p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn-elegant w-full bg-ink px-5 py-3 text-sm font-bold uppercase tracking-widest text-cream hover:bg-ink-light">
                        Kirim Request Withdraw
                    </button>
                </form>
            </div>
        </aside>

        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Riwayat Payout</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Request Withdraw</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    Admin akan approve, reject, atau mark paid dari panel operasional. Ledger tetap append-only.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                        <tr>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Nominal</th>
                            <th class="py-3">Fee</th>
                            <th class="py-3">Rekening</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/8">
                        @forelse ($withdraws as $withdraw)
                            <tr>
                                <td class="py-4 text-ink-muted">{{ $withdraw->requested_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="py-4 font-semibold">Rp {{ number_format((float) $withdraw->amount, 0, ',', '.') }}</td>
                                <td class="py-4 text-ink-muted">Rp {{ number_format((float) $withdraw->fee, 0, ',', '.') }}</td>
                                <td class="py-4 text-ink-muted">
                                    {{ $withdraw->bank_name }} /
                                    {{ $withdraw->bank_account_name }} /
                                    {{ $withdraw->bank_account_number }}
                                </td>
                                <td class="py-4">
                                    <span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">
                                        {{ $withdraw->status->label() }}
                                    </span>
                                </td>
                                <td class="py-4 text-ink-muted">{{ $withdraw->admin_note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-ink-muted">Belum ada request withdraw.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $withdraws->links() }}</div>
        </div>
    </section>
</x-seller-layout>
