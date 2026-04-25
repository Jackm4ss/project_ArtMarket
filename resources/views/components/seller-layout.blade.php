@props(['title' => 'Seller Area'])

<!doctype html>
<html lang="id" data-theme="art-market">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Art Market</title>
    @vite('resources/js/app.tsx')
    @livewireStyles
</head>
<body class="bg-cream font-body text-ink">
    <main class="mx-auto flex min-h-screen max-w-6xl flex-col gap-6 px-6 py-10">
        <header class="flex flex-col justify-between gap-4 border-b border-ink/10 pb-6 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-gold-dark">Seller Area</p>
                <h1 class="mt-2 font-display text-4xl font-semibold">{{ $title }}</h1>
            </div>
            <nav class="flex flex-wrap gap-3 text-sm font-semibold text-ink-muted">
                <a href="{{ route('seller.dashboard') }}" class="hover:text-gold">Dashboard</a>
                <a href="{{ route('seller.store.edit') }}" class="hover:text-gold">Toko</a>
                <a href="{{ route('seller.products.index') }}" class="hover:text-gold">Produk</a>
                <a href="{{ route('seller.orders.index') }}" class="hover:text-gold">Order</a>
                <a href="{{ route('seller.shipments.index') }}" class="hover:text-gold">Pengiriman</a>
                <a href="{{ route('seller.wallet.index') }}" class="hover:text-gold">Wallet</a>
                <a href="{{ route('seller.withdrawals.index') }}" class="hover:text-gold">Withdraw</a>
                <a href="{{ route('seller.chats.index') }}" class="hover:text-gold">Chat</a>
                <a href="{{ route('seller.ads.index') }}" class="hover:text-gold">Iklan</a>
                <a href="{{ route('seller.referrals.index') }}" class="hover:text-gold">Referral</a>
                <a href="{{ route('seller.reports.index') }}" class="hover:text-gold">Laporan</a>
                <a href="{{ route('seller.notifications.index') }}" class="hover:text-gold">Notifikasi</a>
            </nav>
        </header>

        @if (session('status'))
            <div class="rounded-ds-card border border-gold/30 bg-gold/10 px-4 py-3 text-sm font-medium text-ink">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>

