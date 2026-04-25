<x-seller-layout :title="$title">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[1fr_340px]">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <section class="rounded-ds-card bg-paper p-6 shadow-soft">
            <div class="mb-6 border-b border-ink/10 pb-5">
                <h2 class="font-display text-2xl font-semibold text-ink">Informasi Produk</h2>
                <p class="mt-1 text-sm text-ink-muted">Isi data produk dengan jelas agar mudah ditemukan di katalog.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="title" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Nama Produk</label>
                    <input id="title" name="title" value="{{ old('title', $product->title) }}" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('title') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sku" class="text-xs font-bold uppercase tracking-widest text-ink-muted">SKU</label>
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('sku') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Kategori</label>
                    <select id="category_id" name="category_id" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="excerpt" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Ringkasan</label>
                    <textarea id="excerpt" name="excerpt" rows="3" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">{{ old('excerpt', $product->excerpt) }}</textarea>
                    @error('excerpt') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Deskripsi</label>
                    <textarea id="description" name="description" rows="7" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-ds-card bg-paper p-6 shadow-soft">
                <h2 class="font-display text-2xl font-semibold text-ink">Harga & Stok</h2>
                <div class="mt-5 space-y-4">
                    <div>
                        <label for="price" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Harga</label>
                        <input id="price" name="price" type="number" min="1000" step="1000" value="{{ old('price', (int) $product->price) }}" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="compare_at_price" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Harga Coret</label>
                        <input id="compare_at_price" name="compare_at_price" type="number" min="0" step="1000" value="{{ old('compare_at_price', (int) $product->compare_at_price) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('compare_at_price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="stock" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Stok</label>
                        <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" required class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('stock') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_type" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Tipe Produk</label>
                        <select id="product_type" name="product_type" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                            <option value="ready" @selected(old('product_type', $product->product_type) === 'ready')>Ready</option>
                            <option value="preorder" @selected(old('product_type', $product->product_type) === 'preorder')>Preorder</option>
                        </select>
                        @error('product_type') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="preorder_days" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Estimasi Preorder Hari</label>
                        <input id="preorder_days" name="preorder_days" type="number" min="1" max="365" value="{{ old('preorder_days', $product->preorder_days) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                        @error('preorder_days') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-ds-card bg-paper p-6 shadow-soft">
                <h2 class="font-display text-2xl font-semibold text-ink">Media</h2>
                @if ($product->exists && $product->getFirstMediaUrl('products'))
                    <img src="{{ $product->getFirstMediaUrl('products') }}" alt="{{ $product->title }}" class="mt-4 aspect-[4/5] w-full rounded-ds-card object-cover">
                    <label class="mt-4 flex items-center gap-2 text-sm text-ink-muted">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-ink/20 text-gold focus:ring-gold">
                        Hapus gambar saat ini
                    </label>
                @endif
                <label for="image" class="mt-4 block text-xs font-bold uppercase tracking-widest text-ink-muted">Upload Gambar</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                <p class="mt-2 text-xs text-ink-muted">JPG, PNG, atau WebP maksimal 5MB.</p>
                @error('image') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </section>
        </aside>

        <section class="rounded-ds-card bg-paper p-6 shadow-soft lg:col-span-2">
            <h2 class="font-display text-2xl font-semibold text-ink">Detail Karya</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="material" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Material</label>
                    <input id="material" name="material" value="{{ old('material', $product->material) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('material') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="dimensions" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Dimensi</label>
                    <input id="dimensions" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('dimensions') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="weight_gram" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Berat Gram</label>
                    <input id="weight_gram" name="weight_gram" type="number" min="0" value="{{ old('weight_gram', $product->weight_gram) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('weight_gram') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="location" class="text-xs font-bold uppercase tracking-widest text-ink-muted">Lokasi</label>
                    <input id="location" name="location" value="{{ old('location', $product->location) }}" class="mt-2 w-full border border-ink/20 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold">
                    @error('location') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-6 flex flex-col gap-3 border-t border-ink/10 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('seller.products.index') }}" class="inline-flex items-center justify-center border border-ink/20 px-5 py-3 text-xs font-bold uppercase tracking-widest text-ink-muted hover:border-gold hover:text-gold">Kembali</a>
                <button type="submit" class="btn-elegant inline-flex items-center justify-center bg-ink px-6 py-3 text-xs font-bold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light">
                    Simpan Produk
                </button>
            </div>
        </section>
    </form>
</x-seller-layout>
