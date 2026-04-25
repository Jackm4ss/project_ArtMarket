<x-seller-layout title="Iklan Seller">
    <section class="grid gap-6 lg:grid-cols-[0.78fr_1.22fr]">
        <aside class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Promosi Manual</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Request Slot Iklan</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                    Seller mengajukan placement, admin meninjau dan mengaktifkan manual. Billing otomatis belum masuk v1.
                </p>
            </div>

            <form method="POST" action="{{ route('seller.ads.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="title" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Judul Campaign</label>
                    <input id="title" name="title" value="{{ old('title') }}" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('title') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_id" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Produk Fokus</label>
                    <select id="product_id" name="product_id" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        <option value="">Promosikan toko saja</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>{{ $product->title }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="placement" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Placement</label>
                    <select id="placement" name="placement" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @foreach ($placements as $value => $label)
                            <option value="{{ $value }}" @selected(old('placement') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('placement') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="budget" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Budget Manual</label>
                    <input id="budget" name="budget" type="number" min="0" step="1000" value="{{ old('budget', 0) }}" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('budget') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="starts_at" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Mulai</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('starts_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ends_at" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Selesai</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('ends_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" class="btn-elegant w-full bg-ink px-5 py-3 text-sm font-bold uppercase tracking-widest text-cream hover:bg-ink-light">
                    Kirim Request
                </button>
            </form>
        </aside>

        <div class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 flex flex-col justify-between gap-4 border-b border-ink/10 pb-5 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Riwayat Request</p>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Campaign Iklan</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                        <tr>
                            <th class="py-3">Campaign</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3">Placement</th>
                            <th class="py-3">Budget</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/8">
                        @forelse ($ads as $ad)
                            <tr>
                                <td class="py-4">
                                    <div class="font-display text-base font-semibold text-ink">{{ $ad->title }}</div>
                                    <div class="text-xs text-ink-muted">
                                        {{ $ad->starts_at?->format('d M Y H:i') ?? 'Mulai fleksibel' }}
                                        -
                                        {{ $ad->ends_at?->format('d M Y H:i') ?? 'Selesai fleksibel' }}
                                    </div>
                                    @if ($ad->admin_note)
                                        <div class="mt-2 rounded-ds-card bg-ink/5 p-2 text-xs text-ink-muted">Admin: {{ $ad->admin_note }}</div>
                                    @endif
                                </td>
                                <td class="py-4 text-ink-muted">{{ $ad->product?->title ?? 'Toko' }}</td>
                                <td class="py-4">{{ $ad->placement->label() }}</td>
                                <td class="py-4 font-semibold">Rp {{ number_format((float) $ad->budget, 0, ',', '.') }}</td>
                                <td class="py-4">
                                    <span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $ad->status->label() }}</span>
                                </td>
                                <td class="py-4 text-right">
                                    @if (in_array($ad->status, [\App\Enums\AdsStatus::Pending, \App\Enums\AdsStatus::Rejected], true))
                                        <form method="POST" action="{{ route('seller.ads.destroy', $ad) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border border-red-200 px-3 py-2 text-xs font-bold uppercase tracking-widest text-red-700 hover:border-red-500">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-ink-muted">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10 text-center text-ink-muted">Belum ada request iklan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $ads->links() }}</div>
        </div>
    </section>
</x-seller-layout>
