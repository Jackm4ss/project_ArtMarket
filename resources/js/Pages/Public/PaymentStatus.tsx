import { Link } from "@inertiajs/react";
import { CheckCircle2, Clock3, ExternalLink, PackageCheck, XCircle } from "lucide-react";

import { Button, Container, Section } from "@/ArtMarket/design-system";
import { formatCurrency, MoneyValue, OrderPayment } from "@/ArtMarket/commerce";
import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";

type PaymentStatusProps = {
    order: {
        invoice: string;
        status: string;
        payment_status: string;
        subtotal: MoneyValue;
        discount_total: MoneyValue;
        shipping_total: MoneyValue;
        grand_total: MoneyValue;
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
            quantity: number;
            unit_price: MoneyValue;
            subtotal: MoneyValue;
            seller?: {
                store_name: string;
                slug: string;
            } | null;
        }>;
        payment?: OrderPayment | null;
    };
};

const statusCopy: Record<string, { title: string; description: string; icon: typeof Clock3; tone: string }> = {
    pending: {
        title: "Menunggu Pembayaran",
        description: "Invoice sudah dibuat. Lanjutkan pembayaran agar seller bisa memproses pesanan.",
        icon: Clock3,
        tone: "bg-gold/10 text-gold-dark",
    },
    paid: {
        title: "Pembayaran Berhasil",
        description: "Dana masuk escrow Art Market dan akan tersedia untuk seller setelah order selesai.",
        icon: CheckCircle2,
        tone: "bg-green-100 text-green-700",
    },
    failed: {
        title: "Pembayaran Gagal",
        description: "Pembayaran gagal diproses. Silakan ulangi atau hubungi admin.",
        icon: XCircle,
        tone: "bg-red-100 text-red-700",
    },
    expired: {
        title: "Invoice Kedaluwarsa",
        description: "Invoice sudah kedaluwarsa. Buat checkout baru jika masih ingin membeli karya ini.",
        icon: XCircle,
        tone: "bg-red-100 text-red-700",
    },
    refunded: {
        title: "Dana Dikembalikan",
        description: "Refund sudah diproses sesuai keputusan admin.",
        icon: PackageCheck,
        tone: "bg-ink/10 text-ink",
    },
};

export default function PaymentStatus({ order }: PaymentStatusProps) {
    const copy = statusCopy[order.payment_status] ?? statusCopy.pending;
    const Icon = copy.icon;

    return (
        <ArtMarketPublicLayout title={`Payment ${order.invoice}`}>
            <Section id="payment-status">
                <Container>
                    <div className="mx-auto max-w-5xl">
                        <div className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8 lg:p-10">
                            <div className="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div className={`mb-6 inline-flex h-16 w-16 items-center justify-center rounded-[var(--radius-card)] ${copy.tone}`}>
                                        <Icon className="h-8 w-8" />
                                    </div>
                                    <p className="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-gold">Status Pembayaran</p>
                                    <h1 className="font-display text-4xl font-bold tracking-tight text-ink lg:text-5xl">
                                        {copy.title}
                                    </h1>
                                    <p className="mt-4 max-w-2xl text-sm leading-relaxed text-ink-muted">
                                        {copy.description}
                                    </p>
                                </div>
                                <div className="min-w-[240px] border-y border-ink/10 py-5 lg:border-y-0 lg:border-l lg:py-0 lg:pl-8">
                                    <p className="text-xs font-bold uppercase tracking-widest text-ink-muted">Invoice</p>
                                    <p className="mt-1 font-display text-2xl font-bold text-ink">{order.invoice}</p>
                                    <p className="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">Total</p>
                                    <p className="mt-1 font-display text-2xl font-bold text-gold-dark">{formatCurrency(order.grand_total)}</p>
                                </div>
                            </div>

                            {order.payment?.message ? (
                                <div className="mt-8 rounded-[var(--radius-card)] border border-gold/30 bg-gold/10 p-4 text-sm leading-relaxed text-ink">
                                    {order.payment.message}
                                </div>
                            ) : null}

                            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                {order.payment?.redirect_url && order.payment.gateway !== "local-fallback" ? (
                                    <Button href={order.payment.redirect_url} variant="primary" icon={ExternalLink}>
                                        Buka Payment Gateway
                                    </Button>
                                ) : null}
                                <Button href="/katalog" variant="outline">
                                    Lanjut Belanja
                                </Button>
                                <Button href="/user/orders" variant="gold-outline">
                                    Lihat Pesanan
                                </Button>
                            </div>
                        </div>

                        <div className="mt-10 grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
                            <div className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                                <h2 className="font-display text-2xl font-bold text-ink">Item Pesanan</h2>
                                <div className="mt-6 divide-y divide-ink/8 border-y border-ink/8">
                                    {order.items.map((item) => (
                                        <article key={item.id} className="flex justify-between gap-6 py-5">
                                            <div>
                                                <h3 className="font-display text-lg font-bold text-ink">{item.product_title}</h3>
                                                <p className="mt-1 text-xs uppercase tracking-widest text-ink-muted">
                                                    {item.seller?.store_name ?? "Art Market"} - {item.quantity} item
                                                </p>
                                            </div>
                                            <p className="font-semibold text-ink">{formatCurrency(item.subtotal)}</p>
                                        </article>
                                    ))}
                                </div>
                            </div>

                            <aside className="rounded-[var(--radius-card)] bg-surface p-6 shadow-soft sm:p-8">
                                <h2 className="font-display text-2xl font-bold text-ink">Pengiriman</h2>
                                <dl className="mt-6 space-y-4 text-sm">
                                    <div>
                                        <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Nama</dt>
                                        <dd className="mt-1 text-ink">{order.shipping_snapshot?.name ?? "-"}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Kontak</dt>
                                        <dd className="mt-1 text-ink">{order.shipping_snapshot?.email ?? "-"}</dd>
                                        <dd className="text-ink-muted">{order.shipping_snapshot?.phone ?? "-"}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Alamat</dt>
                                        <dd className="mt-1 leading-relaxed text-ink">
                                            {order.shipping_snapshot?.address ?? "-"}
                                            <br />
                                            {[order.shipping_snapshot?.city, order.shipping_snapshot?.province, order.shipping_snapshot?.postal_code]
                                                .filter(Boolean)
                                                .join(", ")}
                                        </dd>
                                    </div>
                                </dl>
                                <div className="mt-6 border-t border-ink/10 pt-5 text-sm">
                                    <div className="mb-2 flex justify-between">
                                        <span className="text-ink-muted">Subtotal</span>
                                        <span className="font-semibold text-ink">{formatCurrency(order.subtotal)}</span>
                                    </div>
                                    <div className="mb-2 flex justify-between">
                                        <span className="text-ink-muted">Diskon</span>
                                        <span className="font-semibold text-ink">-{formatCurrency(order.discount_total)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-ink-muted">Pengiriman</span>
                                        <span className="font-semibold text-ink">{formatCurrency(order.shipping_total)}</span>
                                    </div>
                                </div>
                            </aside>
                        </div>

                        <p className="mt-8 text-center text-sm text-ink-muted">
                            Butuh bantuan? <Link href="/user/chats" className="font-semibold text-gold hover:text-gold-dark">Hubungi seller atau admin</Link>.
                        </p>
                    </div>
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
