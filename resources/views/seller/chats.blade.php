<x-seller-layout title="Chat Seller">
    <section class="grid min-h-[620px] overflow-hidden rounded-ds-card border border-ink/10 bg-paper shadow-soft lg:grid-cols-[340px_1fr]">
        <aside class="border-b border-ink/10 bg-surface lg:border-b-0 lg:border-r">
            <div class="border-b border-ink/10 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-dark">Inbox Pembeli</p>
                <h2 class="mt-2 font-display text-2xl font-semibold">Percakapan</h2>
            </div>

            <div class="max-h-[560px] overflow-y-auto">
                @forelse ($conversations as $conversation)
                    <a
                        href="{{ route('seller.chats.show', $conversation['id']) }}"
                        class="block border-b border-ink/8 p-5 transition hover:bg-gold/10 {{ $activeConversation && $activeConversation['id'] === $conversation['id'] ? 'bg-gold/15' : '' }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-display text-lg font-semibold text-ink">{{ $conversation['counterpart'] }}</p>
                                <p class="mt-1 truncate text-xs uppercase tracking-widest text-ink-muted">
                                    {{ $conversation['product']['title'] ?? 'Diskusi produk' }}
                                </p>
                            </div>
                            @if ($conversation['unread_count'] > 0)
                                <span class="rounded-full bg-gold px-2 py-1 text-xs font-bold text-ink">{{ $conversation['unread_count'] }}</span>
                            @endif
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm text-ink-muted">
                            {{ $conversation['last_message']['body'] ?? 'Belum ada pesan.' }}
                        </p>
                    </a>
                @empty
                    <div class="p-6 text-sm leading-relaxed text-ink-muted">
                        Belum ada chat dari pembeli.
                    </div>
                @endforelse
            </div>
        </aside>

        <div class="flex min-h-[620px] flex-col">
            @if ($activeConversation)
                <header class="flex items-center justify-between gap-4 border-b border-ink/10 bg-cream/70 p-5">
                    <div>
                        <h2 class="font-display text-2xl font-semibold text-ink">{{ $activeConversation['counterpart'] }}</h2>
                        <p class="text-xs uppercase tracking-widest text-ink-muted">
                            {{ $activeConversation['product']['title'] ?? 'Percakapan marketplace' }}
                        </p>
                    </div>
                    @if ($activeConversation['product'])
                        <a href="{{ route('products.show', $activeConversation['product']['slug']) }}" class="text-xs font-bold uppercase tracking-widest text-gold-dark hover:text-gold">
                            Lihat Produk
                        </a>
                    @endif
                </header>

                <div class="flex-1 space-y-4 overflow-y-auto bg-cream/35 p-5">
                    @forelse ($messages as $message)
                        <article class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[78%] rounded-[22px] px-4 py-3 text-sm leading-relaxed shadow-sm {{ $message['is_mine'] ? 'rounded-br-sm bg-ink text-cream' : 'rounded-bl-sm bg-paper text-ink' }}">
                                <p class="whitespace-pre-line">{{ $message['body'] }}</p>
                                <p class="mt-2 text-[11px] {{ $message['is_mine'] ? 'text-cream/60' : 'text-ink-muted' }}">
                                    {{ $message['sender']['name'] ?? 'User' }}
                                    @if ($message['created_at'])
                                        · {{ \Illuminate\Support\Carbon::parse($message['created_at'])->translatedFormat('d M H:i') }}
                                    @endif
                                </p>
                            </div>
                        </article>
                    @empty
                        <div class="mx-auto mt-20 max-w-sm text-center">
                            <h3 class="font-display text-2xl font-semibold text-ink">Mulai balas pembeli</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-muted">
                                Jawab pertanyaan stok, detail karya, atau estimasi pengiriman.
                            </p>
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('chats.messages.store', $activeConversation['id']) }}" class="border-t border-ink/10 bg-paper p-5">
                    @csrf
                    <label for="seller-chat-body" class="sr-only">Tulis pesan</label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <textarea
                            id="seller-chat-body"
                            name="body"
                            rows="2"
                            placeholder="Tulis pesan untuk pembeli..."
                            class="min-h-[56px] flex-1 resize-none border border-ink/15 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold"
                        >{{ old('body') }}</textarea>
                        <button type="submit" class="btn-elegant bg-ink px-8 py-4 text-sm font-semibold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light">
                            Kirim
                        </button>
                    </div>
                    @error('body')
                        <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror
                </form>
            @else
                <div class="grid flex-1 place-items-center bg-cream/35 p-8 text-center">
                    <div class="max-w-md">
                        <h2 class="font-display text-3xl font-semibold text-ink">Pilih percakapan</h2>
                        <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                            Seller dashboard memakai refresh/manual submit agar tetap aman di shared hosting.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-seller-layout>
