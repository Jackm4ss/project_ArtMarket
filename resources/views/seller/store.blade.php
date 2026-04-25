<x-seller-layout title="Profil Toko">
    <form method="POST" action="{{ route('seller.store.update') }}" class="grid gap-6 lg:grid-cols-[1fr_360px]">
        @csrf
        @method('PATCH')

        <section class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 border-b border-ink/10 pb-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Identitas Toko</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Informasi yang Dilihat Pembeli</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                    Nama toko, lokasi, dan bio tampil di katalog, detail produk, serta halaman toko publik.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="store_name" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nama Toko</label>
                    <input
                        id="store_name"
                        name="store_name"
                        value="{{ old('store_name', $seller->store_name) }}"
                        required
                        class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                    >
                    @error('store_name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="location" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Lokasi</label>
                    <input
                        id="location"
                        name="location"
                        value="{{ old('location', $seller->location) }}"
                        class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                    >
                    @error('location') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nomor Kontak</label>
                    <input
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $seller->phone) }}"
                        autocomplete="tel"
                        class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                    >
                    @error('phone') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="bio" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Bio Toko</label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="7"
                        class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                    >{{ old('bio', $seller->bio) }}</textarea>
                    @error('bio') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-ds-card bg-surface p-6 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Status Toko</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">{{ $seller->store_name }}</h2>
                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Status</dt>
                        <dd class="font-semibold uppercase text-ink">{{ $seller->status }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Rating</dt>
                        <dd class="font-semibold text-ink">{{ number_format((float) $seller->rating_average, 1) }} / {{ $seller->rating_count }} review</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Slug Publik</dt>
                        <dd class="max-w-[170px] truncate text-right font-semibold text-ink">{{ $seller->slug }}</dd>
                    </div>
                </dl>
                <a href="{{ route('stores.show', $seller) }}" class="mt-5 inline-flex w-full items-center justify-center border border-ink/20 px-4 py-3 text-xs font-bold uppercase tracking-widest text-ink-muted hover:border-gold hover:text-gold">
                    Lihat Toko Publik
                </a>
            </section>

            <section class="rounded-ds-card bg-paper p-6 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Rekening Payout</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Data Withdraw</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                    Data ini dipakai sebagai snapshot saat seller mengajukan withdraw.
                </p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="bank_name" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nama Bank</label>
                        <input
                            id="bank_name"
                            name="bank_name"
                            value="{{ old('bank_name', $seller->bank_name) }}"
                            autocomplete="organization"
                            class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                        >
                        @error('bank_name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bank_account_name" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nama Pemilik Rekening</label>
                        <input
                            id="bank_account_name"
                            name="bank_account_name"
                            value="{{ old('bank_account_name', $seller->bank_account_name) }}"
                            autocomplete="name"
                            class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                        >
                        @error('bank_account_name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bank_account_number" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nomor Rekening</label>
                        <input
                            id="bank_account_number"
                            name="bank_account_number"
                            value="{{ old('bank_account_number', $seller->bank_account_number) }}"
                            inputmode="numeric"
                            autocomplete="off"
                            class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                        >
                        @error('bank_account_number') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </aside>

        <section class="rounded-ds-card bg-paper p-6 shadow-soft lg:col-span-2">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center justify-center border border-ink/20 px-5 py-3 text-xs font-bold uppercase tracking-widest text-ink-muted hover:border-gold hover:text-gold">
                    Kembali
                </a>
                <button type="submit" class="btn-elegant inline-flex items-center justify-center bg-ink px-6 py-3 text-xs font-bold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light">
                    Simpan Profil Toko
                </button>
            </div>
        </section>
    </form>
</x-seller-layout>
