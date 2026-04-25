<x-seller-layout title="Dashboard Seller">
    <section class="rounded-ds-card bg-paper p-8 shadow-soft">
        <h2 class="font-display text-3xl font-semibold">Operasional Marketplace</h2>
        <p class="mt-3 max-w-2xl text-ink-muted">
            Area ini disiapkan untuk operasional seller di shared hosting: update stok, lihat order, input courier/resi,
            dan pantau ledger wallet. Modul lanjutan bisa ditambah tanpa mengubah public frontend.
        </p>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <a href="{{ route('seller.products.index') }}" class="rounded-ds-card border border-ink/10 bg-cream p-5 transition hover:border-gold">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-dark">Produk</span>
                <strong class="mt-2 block font-display text-2xl">Update Stok</strong>
            </a>
            <a href="{{ route('seller.orders.index') }}" class="rounded-ds-card border border-ink/10 bg-cream p-5 transition hover:border-gold">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-dark">Order</span>
                <strong class="mt-2 block font-display text-2xl">Input Resi</strong>
            </a>
            <a href="{{ route('seller.wallet.index') }}" class="rounded-ds-card border border-ink/10 bg-cream p-5 transition hover:border-gold">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-dark">Wallet</span>
                <strong class="mt-2 block font-display text-2xl">Lihat Ledger</strong>
            </a>
        </div>
    </section>
</x-seller-layout>
