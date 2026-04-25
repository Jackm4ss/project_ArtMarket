import { router } from "@inertiajs/react";
import { Bell, CheckCheck } from "lucide-react";

import { Button, cx, ui } from "@/ArtMarket/design-system";
import { UserLayout } from "@/Layouts/UserLayout";
import { Pagination } from "./Orders";

type NotificationItem = {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at?: string | null;
    created_at?: string | null;
};

type NotificationsProps = {
    notifications: {
        data: NotificationItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

export default function Notifications({ notifications }: NotificationsProps) {
    const markAll = () => {
        router.patch("/user/notifications/read-all", {}, { preserveScroll: true });
    };

    const markOne = (id: string) => {
        router.patch(`/user/notifications/${id}/read`, {}, { preserveScroll: true, preserveState: true });
    };

    return (
        <UserLayout title="Notifikasi">
            <section className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-gold">Inbox</p>
                        <h2 className="mt-2 font-display text-3xl font-bold text-ink">Update Akun</h2>
                    </div>
                    <Button type="button" variant="outline" onClick={markAll} icon={CheckCheck}>Tandai Semua Dibaca</Button>
                </div>

                {notifications.data.length === 0 ? (
                    <div className="rounded-[var(--radius-card)] border border-ink/10 bg-cream px-6 py-12 text-center">
                        <Bell className="mx-auto h-10 w-10 text-gold" />
                        <p className="mt-4 font-display text-2xl font-bold text-ink">Belum ada notifikasi</p>
                        <p className="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-muted">Update order, pembayaran, wishlist, dan chat akan muncul di sini.</p>
                    </div>
                ) : (
                    <div className="divide-y divide-ink/8 border-y border-ink/8">
                        {notifications.data.map((notification) => {
                            const unread = !notification.read_at;
                            const title = stringValue(notification.data.title) ?? notification.type;
                            const body = stringValue(notification.data.body) ?? stringValue(notification.data.message) ?? "Ada update baru untuk akun Anda.";

                            return (
                                <article key={notification.id} className={cx("grid gap-4 py-5 sm:grid-cols-[1fr_auto] sm:px-4", unread && "bg-gold/5")}>
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-widest text-gold-dark">{notification.type}</p>
                                        <h3 className="mt-1 font-display text-xl font-bold text-ink">{title}</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-ink-muted">{body}</p>
                                    </div>
                                    <div className="flex items-start justify-between gap-3 sm:justify-end">
                                        {unread ? <span className="bg-gold px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink">Baru</span> : null}
                                        {unread ? (
                                            <button type="button" onClick={() => markOne(notification.id)} className={cx("text-xs font-bold uppercase tracking-widest text-ink-muted hover:text-gold", ui.focus)}>
                                                Tandai dibaca
                                            </button>
                                        ) : null}
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                )}
                <Pagination links={notifications.links} />
            </section>
        </UserLayout>
    );
}

function stringValue(value: unknown) {
    return typeof value === "string" && value.trim() !== "" ? value : null;
}
