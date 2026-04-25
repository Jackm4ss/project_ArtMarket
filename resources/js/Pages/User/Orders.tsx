import type { FormEvent } from "react";
import { Link, router, useForm } from "@inertiajs/react";
import { ArrowLeft, ExternalLink, Star, Truck } from "lucide-react";

import { formatCurrency, MoneyValue } from "@/ArtMarket/commerce";
import { Button, cx, ui } from "@/ArtMarket/design-system";
import { UserLayout } from "@/Layouts/UserLayout";
import { OrderList, StatusBadge, formatDate } from "./Dashboard";

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
};

type OrderCard = {
    invoice: string;
    status: string;
    payment_status: string;
    grand_total: MoneyValue;
    created_at?: string | null;
    items_count: number;
    first_item?: string | null;
};

type OrdersProps = {
    orders: Paginated<OrderCard>;
};

export default function Orders({ orders }: OrdersProps) {
    return (
        <UserLayout title="Pesanan Saya">
            <section className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                <OrderList orders={orders.data} />
                <Pagination links={orders.links} />
            </section>
        </UserLayout>
    );
}

export function Pagination({ links }: { links: PaginationLink[] }) {
    const usefulLinks = links.filter((link) => link.url !== null || link.active);

    if (usefulLinks.length <= 3) {
        return null;
    }

    return (
        <div className="mt-8 flex flex-wrap gap-2">
            {usefulLinks.map((link, index) => (
                link.url ? (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        className={cx(
                            "border px-3 py-2 text-xs font-bold uppercase tracking-widest transition-colors",
                            link.active ? "border-gold bg-gold text-ink" : "border-ink/15 text-ink-muted hover:border-gold hover:text-gold",
                            ui.focus,
                        )}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={`${link.label}-${index}`} className="border border-ink/10 px-3 py-2 text-xs font-bold uppercase tracking-widest text-ink-muted/50" dangerouslySetInnerHTML={{ __html: link.label }} />
                )
            ))}
        </div>
    );
}

type OrderDetail = OrderCard & {
    subtotal: MoneyValue;
    discount_total: MoneyValue;
    shipping_total: MoneyValue;
    commission_total: MoneyValue;
    shipping_snapshot?: {
        name?: string;
        email?: string;
        phone?: string;
        address?: string;
        city?: string;
        province?: string;
        postal_code?: string;
    } | null;
    items: Array<{
        id: number;
        product_title: string;
        product_slug?: string | null;
        seller?: {
            store_name: string;
            slug: string;
        } | null;
        quantity: number;
        unit_price: MoneyValue;
        subtotal: MoneyValue;
        status: string;
        courier?: string | null;
        tracking_number?: string | null;
        shipped_at?: string | null;
        can_review?: boolean;
        review?: {
            id: number;
            rating: number;
            title?: string | null;
            body?: string | null;
            status: string;
            created_at?: string | null;
        } | null;
    }>;
    payment?: {
        gateway: string;
        status: string;
        amount: MoneyValue;
        redirect_url?: string | null;
    } | null;
    can_complete?: boolean;
    can_cancel?: boolean;
    can_request_refund?: boolean;
    cancelled_at?: string | null;
    refund_requested_at?: string | null;
    refunded_at?: string | null;
    customer_note?: string | null;
    admin_note?: string | null;
};

type ReviewFormData = {
    rating: number;
    title: string;
    body: string;
    order_item?: string;
};

export function OrderShowContent({ order }: { order: OrderDetail }) {
    const completeOrder = () => {
        router.patch(`/user/orders/${order.invoice}/complete`, {}, { preserveScroll: true });
    };
    const cancelOrder = () => {
        router.patch(`/user/orders/${order.invoice}/cancel`, {}, { preserveScroll: true });
    };
    const requestRefund = () => {
        router.patch(`/user/orders/${order.invoice}/refund-request`, {}, { preserveScroll: true });
    };

    return (
        <UserLayout title={`Order ${order.invoice}`} eyebrow="Detail Pesanan">
            <div className="mb-8">
                <Link href="/user/orders" className={cx("inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted hover:text-gold", ui.focus)}>
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke pesanan
                </Link>
            </div>

            <div className="grid gap-8 lg:grid-cols-[1.35fr_0.75fr]">
                <section className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                    <div className="mb-6 flex flex-col justify-between gap-4 border-b border-ink/10 pb-6 sm:flex-row sm:items-start">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-widest text-gold-dark">{formatDate(order.created_at)}</p>
                            <h2 className="mt-2 font-display text-3xl font-bold text-ink">{order.invoice}</h2>
                        </div>
                        <div className="flex flex-wrap gap-2 sm:justify-end">
                            <StatusBadge label={order.status} />
                            <StatusBadge label={order.payment_status} muted />
                        </div>
                    </div>

                    <div className="divide-y divide-ink/8 border-y border-ink/8">
                        {order.items.map((item) => (
                            <article key={item.id} className="py-5">
                                <div className="flex flex-col justify-between gap-4 sm:flex-row">
                                    <div>
                                        {item.product_slug ? (
                                            <Link href={`/produk/${item.product_slug}`} className={cx("font-display text-xl font-bold text-ink hover:text-gold", ui.focus)}>
                                                {item.product_title}
                                            </Link>
                                        ) : (
                                            <h3 className="font-display text-xl font-bold text-ink">{item.product_title}</h3>
                                        )}
                                        <p className="mt-1 text-xs uppercase tracking-widest text-ink-muted">
                                            {item.seller?.store_name ?? "Art Market"} - Qty {item.quantity}
                                        </p>
                                        {item.tracking_number ? (
                                            <p className="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-ink">
                                                <Truck className="h-4 w-4 text-gold" />
                                                {item.courier} - {item.tracking_number}
                                            </p>
                                        ) : null}
                                    </div>
                                    <div className="text-left sm:text-right">
                                        <p className="font-display text-xl font-bold text-ink">{formatCurrency(item.subtotal)}</p>
                                        <StatusBadge label={item.status} muted />
                                    </div>
                                </div>
                                <ReviewPanel item={item} orderInvoice={order.invoice} />
                            </article>
                        ))}
                    </div>
                </section>

                <aside className="space-y-6">
                    <div className="rounded-[var(--radius-card)] bg-surface p-6 shadow-soft">
                        <h2 className="font-display text-2xl font-bold text-ink">Ringkasan</h2>
                        <div className="mt-5 space-y-3 text-sm">
                            <Line label="Subtotal" value={formatCurrency(order.subtotal)} />
                            <Line label="Diskon" value={`-${formatCurrency(order.discount_total)}`} />
                            <Line label="Pengiriman" value={formatCurrency(order.shipping_total)} />
                            <div className="border-t border-ink/10 pt-3">
                                <Line label="Total" value={formatCurrency(order.grand_total)} strong />
                            </div>
                        </div>
                        {order.payment?.redirect_url ? (
                            <Button href={order.payment.redirect_url} variant="primary" icon={ExternalLink} className="mt-6 w-full">
                                Lanjut Pembayaran
                            </Button>
                        ) : null}
                        {order.can_complete ? (
                            <Button type="button" variant="gold-outline" onClick={completeOrder} className="mt-3 w-full">
                                Terima Pesanan
                            </Button>
                        ) : null}
                        {order.can_cancel ? (
                            <Button type="button" variant="outline" onClick={cancelOrder} className="mt-3 w-full">
                                Batalkan Order
                            </Button>
                        ) : null}
                        {order.can_request_refund ? (
                            <Button type="button" variant="outline" onClick={requestRefund} className="mt-3 w-full">
                                Ajukan Refund
                            </Button>
                        ) : null}
                        {order.refund_requested_at ? (
                            <p className="mt-3 rounded-[var(--radius-card)] bg-gold/10 p-3 text-xs leading-relaxed text-ink-muted">
                                Refund diajukan pada {formatDate(order.refund_requested_at)}. Admin akan meninjau pengajuan ini.
                            </p>
                        ) : null}
                        {order.admin_note ? (
                            <p className="mt-3 rounded-[var(--radius-card)] bg-ink/5 p-3 text-xs leading-relaxed text-ink-muted">
                                Catatan admin: {order.admin_note}
                            </p>
                        ) : null}
                    </div>

                    <div className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft">
                        <h2 className="font-display text-2xl font-bold text-ink">Alamat Kirim</h2>
                        <p className="mt-4 text-sm leading-relaxed text-ink-muted">
                            {order.shipping_snapshot?.name}<br />
                            {order.shipping_snapshot?.phone}<br />
                            {order.shipping_snapshot?.address}<br />
                            {[order.shipping_snapshot?.city, order.shipping_snapshot?.province, order.shipping_snapshot?.postal_code].filter(Boolean).join(", ")}
                        </p>
                    </div>
                </aside>
            </div>
        </UserLayout>
    );
}

function ReviewPanel({
    item,
    orderInvoice,
}: {
    item: OrderDetail["items"][number];
    orderInvoice: string;
}) {
    const { data, setData, post, processing, errors, reset } = useForm<ReviewFormData>({
        rating: item.review?.rating ?? 5,
        title: "",
        body: "",
    });

    const submitReview = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        post(`/user/orders/${orderInvoice}/items/${item.id}/review`, {
            preserveScroll: true,
            onSuccess: () => reset("title", "body"),
        });
    };

    if (item.review) {
        return (
            <div className="mt-5 rounded-[var(--radius-card)] bg-surface p-4">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="flex items-center gap-1 text-gold" aria-label={`Rating ${item.review.rating} dari 5`}>
                        {Array.from({ length: 5 }).map((_, index) => (
                            <Star
                                key={index}
                                className={cx("h-4 w-4", index < item.review!.rating ? "fill-current" : "text-ink/20")}
                            />
                        ))}
                    </div>
                    <span className="text-xs font-bold uppercase tracking-widest text-ink-muted">
                        Ulasan Anda {item.review.status === "hidden" ? "disembunyikan admin" : "terpublikasi"}
                    </span>
                </div>
                {item.review.title ? (
                    <h4 className="mt-3 font-display text-lg font-bold text-ink">{item.review.title}</h4>
                ) : null}
                {item.review.body ? (
                    <p className="mt-2 text-sm leading-relaxed text-ink-muted">{item.review.body}</p>
                ) : null}
            </div>
        );
    }

    if (!item.can_review) {
        return null;
    }

    return (
        <form onSubmit={submitReview} className="mt-5 rounded-[var(--radius-card)] border border-gold/30 bg-gold/5 p-4">
            <div className="grid gap-4 sm:grid-cols-[150px_1fr]">
                <label className="block">
                    <span className="text-xs font-bold uppercase tracking-widest text-ink-muted">Rating</span>
                    <select
                        value={data.rating}
                        onChange={(event) => setData("rating", Number(event.target.value))}
                        className={cx("mt-2 w-full border border-ink/15 bg-paper px-3 py-2 text-sm font-semibold text-ink", ui.focus)}
                    >
                        {[5, 4, 3, 2, 1].map((rating) => (
                            <option key={rating} value={rating}>
                                {rating} bintang
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block">
                    <span className="text-xs font-bold uppercase tracking-widest text-ink-muted">Judul ulasan</span>
                    <input
                        value={data.title}
                        onChange={(event) => setData("title", event.target.value)}
                        maxLength={120}
                        className={cx("mt-2 w-full border border-ink/15 bg-paper px-3 py-2 text-sm text-ink", ui.focus)}
                        placeholder="Contoh: Karya sampai dengan aman"
                    />
                </label>
            </div>
            <label className="mt-4 block">
                <span className="text-xs font-bold uppercase tracking-widest text-ink-muted">Ceritakan pengalaman Anda</span>
                <textarea
                    value={data.body}
                    onChange={(event) => setData("body", event.target.value)}
                    maxLength={2000}
                    rows={3}
                    className={cx("mt-2 w-full border border-ink/15 bg-paper px-3 py-2 text-sm leading-relaxed text-ink", ui.focus)}
                    placeholder="Bantu calon pembeli memahami kualitas karya dan layanan seller."
                />
            </label>
            {errors.rating || errors.title || errors.body || errors.order_item ? (
                <p className="mt-3 text-sm font-medium text-red-700">
                    {errors.rating ?? errors.title ?? errors.body ?? errors.order_item}
                </p>
            ) : null}
            <Button type="submit" variant="gold-outline" disabled={processing} className="mt-4">
                {processing ? "Mengirim" : "Kirim Ulasan"}
            </Button>
        </form>
    );
}

function Line({ label, value, strong = false }: { label: string; value: string; strong?: boolean }) {
    return (
        <div className="flex justify-between gap-4">
            <span className={strong ? "font-display text-xl font-bold text-ink" : "text-ink-muted"}>{label}</span>
            <span className={strong ? "font-display text-xl font-bold text-gold-dark" : "font-semibold text-ink"}>{value}</span>
        </div>
    );
}
