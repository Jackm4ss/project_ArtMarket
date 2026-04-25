<x-seller-layout title="Referral Seller">
    <section class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-[0.82fr_1.18fr]">
            <aside class="rounded-ds-card bg-paper p-6 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Kode Referral</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">{{ $seller->store_name }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                    Bagikan kode ini ke calon seller baru. Reward akan mengikuti aturan admin dan baru dihitung sebagai earned ketika status referral menjadi rewarded.
                </p>

                <div class="mt-6 rounded-ds-card border border-ink/10 bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Kode</p>
                    <p class="mt-2 break-all font-display text-3xl font-semibold text-ink">{{ $referralCode }}</p>
                </div>

                <div class="mt-4 rounded-ds-card border border-ink/10 bg-cream p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Link Register Seller</p>
                    <a href="{{ $referralLink }}" class="mt-2 block break-all text-sm font-semibold text-gold-dark hover:text-ink">
                        {{ $referralLink }}
                    </a>
                </div>

                <div class="mt-5 rounded-ds-card bg-gold/10 p-4 text-sm text-ink">
                    Estimasi reward per seller qualified:
                    <strong>Rp {{ number_format((float) $rewardAmount, 0, ',', '.') }}</strong>
                </div>
            </aside>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-ds-card bg-paper p-5 shadow-soft">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Pending</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $pendingCount }}</p>
                </div>
                <div class="rounded-ds-card bg-paper p-5 shadow-soft">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Qualified</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $qualifiedCount }}</p>
                </div>
                <div class="rounded-ds-card bg-paper p-5 shadow-soft">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Rewarded</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">{{ $rewardedCount }}</p>
                </div>
                <div class="rounded-ds-card bg-gold/10 p-5 shadow-soft">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold-dark">Total Reward</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-ink">Rp {{ number_format((float) $rewardTotal, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Riwayat Referral</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Seller yang Direferensikan</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    Status pending/qualified/rewarded/rejected dikelola admin sesuai aturan referral marketplace.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                        <tr>
                            <th class="py-3">Seller/User</th>
                            <th class="py-3">Kode</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Reward</th>
                            <th class="py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/8">
                        @forelse ($referrals as $referral)
                            <tr>
                                <td class="py-4">
                                    <div class="font-display text-base font-semibold text-ink">{{ $referral->referred?->name ?? 'Belum terhubung' }}</div>
                                    <div class="text-xs text-ink-muted">{{ $referral->referred?->email ?? '-' }}</div>
                                </td>
                                <td class="py-4">
                                    <div class="font-semibold text-ink-muted">{{ $referral->referral_code ?? $referral->code }}</div>
                                    <div class="text-xs text-ink-muted">{{ $referral->code }}</div>
                                </td>
                                <td class="py-4">
                                    <span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">
                                        {{ $referral->status->label() }}
                                    </span>
                                </td>
                                <td class="py-4 font-semibold">Rp {{ number_format((float) $referral->reward_amount, 0, ',', '.') }}</td>
                                <td class="py-4 text-ink-muted">
                                    {{ $referral->created_at?->format('d M Y H:i') }}
                                    @if ($referral->rewarded_at)
                                        <div class="text-xs">Rewarded {{ $referral->rewarded_at->format('d M Y H:i') }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-ink-muted">Belum ada referral yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $referrals->links() }}</div>
        </div>
    </section>
</x-seller-layout>
