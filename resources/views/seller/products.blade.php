<x-seller-layout title="Produk Seller">
    <section class="rounded-ds-card bg-paper p-6 shadow-soft">
        <div class="mb-6 flex flex-col justify-between gap-4 border-b border-ink/10 pb-5 md:flex-row md:items-center">
            <div>
                <h2 class="font-display text-2xl font-semibold text-ink">Daftar Produk</h2>
                <p class="mt-1 text-sm text-ink-muted">Produk baru otomatis publish. Jika admin unpublish, edit seller tidak akan publish ulang.</p>
            </div>
            <a href="{{ route('seller.products.create') }}" class="btn-elegant inline-flex items-center justify-center bg-ink px-5 py-3 text-xs font-bold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light">
                Tambah Produk
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="border-b border-ink/10 text-xs uppercase tracking-widest text-ink-muted">
                    <tr>
                        <th class="py-3">Produk</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Harga</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Stok</th>
                        <th class="py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/8">
                    @forelse ($products as $product)
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->getFirstMediaUrl('products'))
                                        <img src="{{ $product->getFirstMediaUrl('products') }}" alt="{{ $product->title }}" class="h-14 w-14 rounded-ds-card object-cover">
                                    @else
                                        <div class="grid h-14 w-14 place-items-center rounded-ds-card bg-ink/5 text-xs font-bold uppercase text-ink-muted">Art</div>
                                    @endif
                                    <div>
                                        <div class="font-display text-base font-semibold text-ink">{{ $product->title }}</div>
                                        <div class="text-xs text-ink-muted">{{ $product->seller?->store_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-ink-muted">{{ $product->category?->name ?? '-' }}</td>
                            <td class="py-4 font-semibold">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                            <td class="py-4"><span class="rounded-ds-badge bg-ink/5 px-2 py-1 text-xs font-semibold uppercase">{{ $product->status->value }}</span></td>
                            <td class="py-4">
                                <form method="POST" action="{{ route('seller.products.stock.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="stock-{{ $product->id }}">Stok {{ $product->title }}</label>
                                    <input id="stock-{{ $product->id }}" name="stock" type="number" min="0" value="{{ $product->stock }}" class="w-24 border border-ink/20 bg-transparent px-3 py-2 text-sm focus:border-gold focus:ring-gold">
                                    <button type="submit" class="border border-ink/20 px-3 py-2 text-xs font-bold uppercase tracking-widest hover:border-gold hover:text-gold">Simpan</button>
                                </form>
                            </td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('seller.products.edit', $product) }}" class="border border-ink/20 px-3 py-2 text-xs font-bold uppercase tracking-widest hover:border-gold hover:text-gold">Edit</a>
                                    <form method="POST" action="{{ route('seller.products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border border-red-200 px-3 py-2 text-xs font-bold uppercase tracking-widest text-red-700 hover:border-red-500">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-ink-muted">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </section>
</x-seller-layout>
