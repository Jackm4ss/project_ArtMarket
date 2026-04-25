import { Link } from "@inertiajs/react";
import { Bell, Heart, MapPin, PackageCheck } from "lucide-react";

import { formatCurrency, MoneyValue } from "@/ArtMarket/commerce";
import { Button, cx, ui } from "@/ArtMarket/design-system";
import { UserLayout } from "@/Layouts/UserLayout";

type OrderCard = {
    invoice: string;
    status: string;
    payment_status: string;
    grand_total: MoneyValue;
    created_at?: string | null;
    items_count: number;
    first_item?: string | null;
};

type UserDashboardProps = {
    summary: {
        orders_count: number;
        wishlist_count: number;
        addresses_count: number;
        unread_notifications_count: number;
    };
    recentOrders: OrderCard[];
};

const stats = [
    { key: "orders_count", label: "Pesanan", icon: PackageCheck, href: "/user/orders" },
    { key: "wishlist_count", label: "Wishlist", icon: Heart, href: "/user/wishlist" },
    { key: "addresses_count", label: "Alamat", icon: MapPin, href: "/user/addresses" },
    { key: "unread_notifications_count", label: "Notifikasi Baru", icon: Bell, href: "/user/notifications" },
] as const;

export default function Dashboard({ summary, recentOrders }: UserDashboardProps) {
    return (
        <UserLayout title="Dashboard Pembeli">
            <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                {stats.map((item) => {
                    const Icon = item.icon;

                    return (
                        <Link key={item.key} href={item.href} className={cx("rounded-[var(--radius-card)] bg-paper p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-float", ui.focus)}>
                            <div className="mb-5 flex h-11 w-11 items-center justify-center border border-gold/40 text-gold-dark">
                                <Icon className="h-5 w-5" />
                            </div>
                            <p className="font-display text-4xl font-bold text-ink">{summary[item.key]}</p>
                            <p className="mt-2 text-xs font-bold uppercase tracking-widest text-ink-muted">{item.label}</p>
                        </Link>
                    );
                })}
            </div>

            <section className="mt-10 rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-gold">Riwayat</p>
                        <h2 className="mt-2 font-display text-3xl font-bold text-ink">Pesanan Terbaru</h2>
                    </div>
                    <Button href="/user/orders" variant="outline">Lihat Semua</Button>
                </div>

                <OrderList orders={recentOrders} />
            </section>
        </UserLayout>
    );
}

export function OrderList({ orders }: { orders: OrderCard[] }) {
    if (orders.length === 0) {
        return (
            <div className="rounded-[var(--radius-card)] border border-ink/10 bg-cream px-6 py-12 text-center">
                <p className="font-display text-2xl font-bold text-ink">Belum ada pesanan</p>
                <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-muted">Karya yang kamu checkout akan muncul di sini.</p>
                <Button href="/katalog" variant="primary" className="mt-6">Belanja Sekarang</Button>
            </div>
        );
    }

    return (
        <div className="divide-y divide-ink/8 border-y border-ink/8">
            {orders.map((order) => (
                <Link key={order.invoice} href={`/user/orders/${order.invoice}`} className={cx("grid gap-4 py-5 transition hover:bg-cream/60 sm:grid-cols-[1fr_auto] sm:px-4", ui.focus)}>
                    <div>
                        <p className="text-xs font-bold uppercase tracking-widest text-gold-dark">{order.invoice}</p>
                        <h3 className="mt-1 font-display text-xl font-bold text-ink">{order.first_item ?? "Pesanan Art Market"}</h3>
                        <p className="mt-1 text-sm text-ink-muted">
                            {order.items_count} item - {formatDate(order.created_at)}
                        </p>
                    </div>
                    <div className="text-left sm:text-right">
                        <p className="font-display text-xl font-bold text-ink">{formatCurrency(order.grand_total)}</p>
                        <div className="mt-2 flex flex-wrap gap-2 sm:justify-end">
                            <StatusBadge label={order.status} />
                            <StatusBadge label={order.payment_status} muted />
                        </div>
                    </div>
                </Link>
            ))}
        </div>
    );
}

export function StatusBadge({ label, muted = false }: { label: string; muted?: boolean }) {
    return (
        <span className={cx("inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest", muted ? "bg-ink/5 text-ink-muted" : "bg-gold/15 text-gold-dark")}>
            {label.replaceAll("_", " ")}
        </span>
    );
}

export function formatDate(value?: string | null) {
    if (!value) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(new Date(value));
}
