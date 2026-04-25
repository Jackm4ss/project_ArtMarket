import { Link, useForm } from "@inertiajs/react";
import { MessageCircle, Send, Store, UserRound } from "lucide-react";
import { FormEvent, useEffect, useMemo, useRef, useState } from "react";

import { Button, cx, ui } from "@/ArtMarket/design-system";
import { UserLayout } from "@/Layouts/UserLayout";

type ChatSender = {
    id: number;
    name: string;
};

type ChatMessage = {
    id: number;
    conversation_id: number;
    body: string;
    status: string;
    read_at: string | null;
    created_at: string | null;
    sender: ChatSender | null;
    is_mine: boolean;
};

type ConversationSummary = {
    id: number;
    counterpart: string;
    buyer: ChatSender | null;
    seller: {
        id: number;
        store_name: string;
        slug: string;
        location: string | null;
    } | null;
    product: {
        id: number;
        title: string;
        slug: string;
    } | null;
    last_message_at: string | null;
    last_message: ChatMessage | null;
    unread_count: number;
};

type ChatsProps = {
    conversations: ConversationSummary[];
    activeConversation: ConversationSummary | null;
    messages: ChatMessage[];
    pollingIntervalSeconds: number;
    sseBackoffSeconds: number[];
};

type ChatPayload = {
    conversation_id: number;
    messages: ChatMessage[];
    server_time: string;
};

const formatDate = (value: string | null) => {
    if (!value) {
        return "";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));
};

const mergeMessages = (current: ChatMessage[], incoming: ChatMessage[]) => {
    const merged = new Map<number, ChatMessage>();

    current.forEach((message) => merged.set(message.id, message));
    incoming.forEach((message) => merged.set(message.id, message));

    return Array.from(merged.values()).sort((a, b) => a.id - b.id);
};

export default function Chats({ conversations, activeConversation, messages, pollingIntervalSeconds, sseBackoffSeconds }: ChatsProps) {
    const [liveMessages, setLiveMessages] = useState(messages);
    const latestMessageIdRef = useRef(0);
    const activeConversationId = activeConversation?.id ?? null;
    const form = useForm({ body: "" });

    const latestMessageId = useMemo(
        () => liveMessages.reduce((latest, message) => Math.max(latest, message.id), 0),
        [liveMessages],
    );

    useEffect(() => {
        setLiveMessages(messages);
    }, [activeConversationId, messages]);

    useEffect(() => {
        latestMessageIdRef.current = latestMessageId;
    }, [latestMessageId]);

    useEffect(() => {
        if (!activeConversationId) {
            return;
        }

        let eventSource: EventSource | null = null;
        let reconnectTimer: number | null = null;
        let pollingTimer: number | null = null;
        let retryIndex = 0;
        let stopped = false;

        const clearReconnect = () => {
            if (reconnectTimer) {
                window.clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
        };

        const clearPolling = () => {
            if (pollingTimer) {
                window.clearInterval(pollingTimer);
                pollingTimer = null;
            }
        };

        const closeStream = () => {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        };

        const consumePayload = (event: MessageEvent<string>) => {
            const payload = JSON.parse(event.data) as ChatPayload;

            if (payload.conversation_id !== activeConversationId) {
                return;
            }

            setLiveMessages((current) => mergeMessages(current, payload.messages));
            retryIndex = 0;
        };

        const poll = async () => {
            if (document.hidden || stopped) {
                return;
            }

            const response = await fetch(`/polling/chats/${activeConversationId}?after_id=${latestMessageIdRef.current}`, {
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                return;
            }

            const payload = (await response.json()) as ChatPayload;
            setLiveMessages((current) => mergeMessages(current, payload.messages));
        };

        const startPolling = () => {
            clearPolling();
            pollingTimer = window.setInterval(poll, Math.max(5, pollingIntervalSeconds) * 1000);
        };

        const connect = () => {
            clearReconnect();
            closeStream();

            if (stopped || document.hidden) {
                return;
            }

            if (!("EventSource" in window)) {
                startPolling();
                void poll();
                return;
            }

            eventSource = new EventSource(`/sse/chats/${activeConversationId}?after_id=${latestMessageIdRef.current}`);
            eventSource.addEventListener("messages.snapshot", (event) => consumePayload(event as MessageEvent<string>));
            eventSource.addEventListener("messages.append", (event) => consumePayload(event as MessageEvent<string>));
            eventSource.onerror = () => {
                closeStream();
                const delaySeconds = sseBackoffSeconds[Math.min(retryIndex, sseBackoffSeconds.length - 1)] ?? 30;
                retryIndex += 1;
                reconnectTimer = window.setTimeout(connect, delaySeconds * 1000);
                startPolling();
            };
        };

        const handleVisibility = () => {
            if (document.hidden) {
                closeStream();
                clearReconnect();
                clearPolling();
                return;
            }

            retryIndex = 0;
            connect();
            void poll();
        };

        connect();
        document.addEventListener("visibilitychange", handleVisibility);

        return () => {
            stopped = true;
            closeStream();
            clearReconnect();
            clearPolling();
            document.removeEventListener("visibilitychange", handleVisibility);
        };
    }, [activeConversationId, pollingIntervalSeconds, sseBackoffSeconds]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!activeConversationId || !form.data.body.trim()) {
            return;
        }

        form.post(`/chats/${activeConversationId}/messages`, {
            preserveScroll: true,
            onSuccess: () => form.reset("body"),
        });
    };

    return (
        <UserLayout title="Chat">
            <div className="grid min-h-[620px] overflow-hidden rounded-[var(--radius-card)] border border-ink/10 bg-paper shadow-soft lg:grid-cols-[360px_1fr]">
                <aside className="border-b border-ink/10 bg-surface lg:border-b-0 lg:border-r">
                    <div className="border-b border-ink/10 p-5">
                        <p className="text-xs font-bold uppercase tracking-[0.18em] text-gold">Inbox Seller</p>
                        <h2 className="mt-2 font-display text-2xl font-bold text-ink">Percakapan Aktif</h2>
                    </div>

                    <div className="max-h-[560px] overflow-y-auto">
                        {conversations.length === 0 ? (
                            <div className="p-6 text-sm leading-relaxed text-ink-muted">
                                Belum ada percakapan. Buka detail produk lalu pilih <strong>Chat Seller</strong>.
                            </div>
                        ) : (
                            conversations.map((conversation) => (
                                <Link
                                    key={conversation.id}
                                    href={`/user/chats/${conversation.id}`}
                                    className={cx(
                                        "block border-b border-ink/8 p-5 transition-colors hover:bg-gold/10",
                                        activeConversationId === conversation.id ? "bg-gold/15" : "bg-transparent",
                                        ui.focus,
                                    )}
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="min-w-0">
                                            <p className="truncate font-display text-lg font-semibold text-ink">{conversation.counterpart}</p>
                                            <p className="mt-1 truncate text-xs uppercase tracking-widest text-ink-muted">
                                                {conversation.product?.title ?? "Diskusi produk"}
                                            </p>
                                        </div>
                                        {conversation.unread_count > 0 ? (
                                            <span className="rounded-full bg-gold px-2 py-1 text-xs font-bold text-ink">{conversation.unread_count}</span>
                                        ) : null}
                                    </div>
                                    <p className="mt-3 line-clamp-2 text-sm text-ink-muted">
                                        {conversation.last_message?.body ?? "Mulai percakapan dengan seller."}
                                    </p>
                                </Link>
                            ))
                        )}
                    </div>
                </aside>

                <section className="flex min-h-[620px] flex-col">
                    {activeConversation ? (
                        <>
                            <header className="flex items-center justify-between gap-4 border-b border-ink/10 bg-cream/70 p-5">
                                <div className="flex items-center gap-3">
                                    <div className="grid h-11 w-11 place-items-center rounded-full bg-gold/20 text-gold-dark">
                                        <Store className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h2 className="font-display text-xl font-bold text-ink">{activeConversation.counterpart}</h2>
                                        <p className="text-xs uppercase tracking-widest text-ink-muted">
                                            {activeConversation.product?.title ?? "Percakapan marketplace"}
                                        </p>
                                    </div>
                                </div>
                                {activeConversation.product ? (
                                    <Link
                                        href={`/produk/${activeConversation.product.slug}`}
                                        className={cx("hidden text-xs font-bold uppercase tracking-widest text-gold hover:text-gold-dark sm:inline-flex", ui.focus)}
                                    >
                                        Lihat Produk
                                    </Link>
                                ) : null}
                            </header>

                            <div className="flex-1 space-y-4 overflow-y-auto bg-cream/35 p-5">
                                {liveMessages.length === 0 ? (
                                    <div className="mx-auto mt-20 max-w-sm text-center">
                                        <MessageCircle className="mx-auto h-10 w-10 text-gold" />
                                        <h3 className="mt-4 font-display text-2xl font-bold text-ink">Mulai percakapan</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                                            Tanya detail karya, stok, pengemasan, atau estimasi pengiriman sebelum checkout.
                                        </p>
                                    </div>
                                ) : (
                                    liveMessages.map((message) => (
                                        <article
                                            key={message.id}
                                            className={cx("flex gap-3", message.is_mine ? "justify-end" : "justify-start")}
                                        >
                                            {!message.is_mine ? (
                                                <div className="mt-1 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-ink/5 text-ink-muted">
                                                    <UserRound className="h-4 w-4" />
                                                </div>
                                            ) : null}
                                            <div
                                                className={cx(
                                                    "max-w-[78%] rounded-[22px] px-4 py-3 text-sm leading-relaxed shadow-sm",
                                                    message.is_mine
                                                        ? "rounded-br-sm bg-ink text-cream"
                                                        : "rounded-bl-sm bg-paper text-ink",
                                                )}
                                            >
                                                <p className="whitespace-pre-line">{message.body}</p>
                                                <p className={cx("mt-2 text-[11px]", message.is_mine ? "text-cream/60" : "text-ink-muted")}>
                                                    {message.sender?.name ?? "User"} · {formatDate(message.created_at)}
                                                </p>
                                            </div>
                                        </article>
                                    ))
                                )}
                            </div>

                            <form onSubmit={submit} className="border-t border-ink/10 bg-paper p-5">
                                <label htmlFor="chat-body" className="sr-only">Tulis pesan</label>
                                <div className="flex flex-col gap-3 sm:flex-row">
                                    <textarea
                                        id="chat-body"
                                        name="body"
                                        value={form.data.body}
                                        onChange={(event) => form.setData("body", event.target.value)}
                                        placeholder="Tulis pesan untuk seller..."
                                        rows={2}
                                        className={cx("min-h-[56px] flex-1 resize-none border border-ink/15 bg-cream px-4 py-3 text-sm focus:border-gold focus:ring-gold", ui.focus)}
                                    />
                                    <Button type="submit" icon={Send} disabled={form.processing || !form.data.body.trim()} className="sm:self-end">
                                        Kirim
                                    </Button>
                                </div>
                                {form.errors.body ? <p className="mt-2 text-sm font-medium text-red-700">{form.errors.body}</p> : null}
                            </form>
                        </>
                    ) : (
                        <div className="grid flex-1 place-items-center bg-cream/35 p-8 text-center">
                            <div className="max-w-md">
                                <MessageCircle className="mx-auto h-12 w-12 text-gold" />
                                <h2 className="mt-5 font-display text-3xl font-bold text-ink">Pilih percakapan</h2>
                                <p className="mt-3 text-sm leading-relaxed text-ink-muted">
                                    Semua chat buyer-seller tersimpan di database. Realtime hanya dipakai untuk delivery, bukan sumber data utama.
                                </p>
                            </div>
                        </div>
                    )}
                </section>
            </div>
        </UserLayout>
    );
}
