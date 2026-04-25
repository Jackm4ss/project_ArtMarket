<x-seller-layout title="Notifikasi Seller">
    <section class="rounded-ds-card bg-paper p-6 shadow-soft">
        <div class="mb-6 flex flex-col justify-between gap-4 border-b border-ink/10 pb-5 md:flex-row md:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-dark">Inbox Operasional</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Update Seller</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    {{ $unreadCount }} notifikasi belum dibaca. Update order, chat, withdraw, dan promosi akan muncul di sini.
                </p>
            </div>

            <form method="POST" action="{{ route('seller.notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-elegant border border-ink/20 px-5 py-3 text-xs font-bold uppercase tracking-widest text-ink hover:border-gold hover:text-gold">
                    Tandai Semua Dibaca
                </button>
            </form>
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-ds-card border border-ink/10 bg-cream px-6 py-12 text-center">
                <p class="font-display text-2xl font-semibold text-ink">Belum ada notifikasi</p>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-muted">
                    Saat ada update operasional untuk toko, sistem akan menaruh notifikasi di halaman ini.
                </p>
            </div>
        @else
            <div class="divide-y divide-ink/8 border-y border-ink/8">
                @foreach ($notifications as $notification)
                    @php
                        $title = data_get($notification->data, 'title') ?: class_basename($notification->type);
                        $body = data_get($notification->data, 'body') ?: data_get($notification->data, 'message') ?: 'Ada update baru untuk toko Anda.';
                        $url = data_get($notification->data, 'url') ?: data_get($notification->data, 'action_url');
                    @endphp
                    <article class="grid gap-4 px-4 py-5 md:grid-cols-[1fr_auto] {{ $notification->read_at ? '' : 'bg-gold/5' }}">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gold-dark">{{ class_basename($notification->type) }}</p>
                            <h3 class="mt-1 font-display text-xl font-semibold text-ink">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $body }}</p>
                            <p class="mt-3 text-xs text-ink-muted">{{ $notification->created_at?->format('d M Y H:i') }}</p>
                            @if ($url)
                                <a href="{{ $url }}" class="mt-3 inline-flex text-xs font-bold uppercase tracking-widest text-gold-dark hover:text-ink">
                                    Buka Detail
                                </a>
                            @endif
                        </div>

                        <div class="flex items-start gap-3 md:justify-end">
                            @if (! $notification->read_at)
                                <span class="bg-gold px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink">Baru</span>
                                <form method="POST" action="{{ route('seller.notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-bold uppercase tracking-widest text-ink-muted hover:text-gold">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-semibold uppercase tracking-widest text-ink-muted">Dibaca</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="mt-6">{{ $notifications->links() }}</div>
    </section>
</x-seller-layout>
