import { Link, router } from "@inertiajs/react";
import { ArrowLeft, Minus, Plus, ShoppingBag, Trash2 } from "lucide-react";

import { Button, Container, Section, cx, ui } from "@/ArtMarket/design-system";
import { CartSummary, formatCurrency } from "@/ArtMarket/commerce";
import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";

type CartProps = {
    cart: CartSummary;
};

export default function Cart({ cart }: CartProps) {
    const updateQuantity = (slug: string, quantity: number) => {
        router.patch(
            `/cart/items/${slug}`,
            { quantity },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const removeItem = (slug: string) => {
        router.delete(`/cart/items/${slug}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const clearCart = () => {
        router.delete("/cart", {
            preserveScroll: true,
            preserveState: true,
        });
    };

    if (cart.items.length === 0) {
        return (
            <ArtMarketPublicLayout title="Keranjang">
                <div className="flex min-h-[70vh] items-center justify-center px-8 text-center">
                    <div>
                        <div className="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-[var(--radius-card)] bg-gold/10 text-gold">
                            <ShoppingBag className="h-10 w-10" />
                        </div>
                        <p className="mb-4 font-display text-2xl font-bold text-ink">Keranjang Anda kosong</p>
                        <p className="mx-auto mb-8 max-w-md text-sm leading-relaxed text-ink-muted">
                            Mulai pilih karya seni dari katalog. Stok dan harga akan divalidasi ulang saat checkout.
                        </p>
                        <Button href="/katalog" variant="outline">
                            Eksplorasi Karya
                        </Button>
                    </div>
                </div>
            </ArtMarketPublicLayout>
        );
    }

    return (
        <ArtMarketPublicLayout title="Keranjang">
            <Section id="cart">
                <Container>
                    <div className="mb-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <div>
                            <p className="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-gold">Checkout Aman</p>
                            <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">Keranjang</h1>
                            <p className="mt-4 max-w-xl text-sm leading-relaxed text-ink-muted">
                                Keranjang ini disimpan di backend session Laravel. Saat checkout, stok akan dikunci ulang di database.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={clearCart}
                            className={cx("text-sm font-bold uppercase tracking-widest text-ink-muted transition-colors hover:text-gold", ui.focus)}
                        >
                            Kosongkan
                        </button>
                    </div>

                    <div className="grid grid-cols-1 gap-12 lg:grid-cols-[1.55fr_0.95fr] lg:gap-16">
                        <div>
                            <div className="divide-y divide-ink/8 border-y border-ink/8">
                                {cart.items.map((item) => {
                                    const nextMin = Math.max(1, item.quantity - 1);
                                    const nextMax = Math.min(item.product.stock, item.quantity + 1);
                                    const hasStockIssue = item.stock_state !== "available";

                                    return (
                                        <article key={item.product.id} className="flex gap-5 py-6 sm:gap-6">
                                            <Link href={`/produk/${item.product.slug}`} className="block flex-shrink-0">
                                                <img
                                                    src={item.product.image.src}
                                                    alt={item.product.image.alt}
                                                    width={112}
                                                    height={112}
                                                    className="h-24 w-24 rounded-[var(--radius-frame)] object-cover shadow-soft sm:h-28 sm:w-28"
                                                    loading="lazy"
                                                />
                                            </Link>
                                            <div className="flex min-w-0 flex-1 flex-col justify-between gap-4">
                                                <div className="flex flex-col justify-between gap-3 sm:flex-row">
                                                    <div className="min-w-0">
                                                        <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-gold-dark">
                                                            {item.product.category?.name ?? "Karya Seni"}
                                                        </p>
                                                        <Link href={`/produk/${item.product.slug}`} className={cx("group mt-1 block", ui.focus)}>
                                                            <h2 className="font-display text-lg font-bold leading-snug text-ink transition-colors group-hover:text-gold sm:text-xl">
                                                                {item.product.title}
                                                            </h2>
                                                        </Link>
                                                        <p className="mt-1 text-xs uppercase tracking-widest text-ink-muted">
                                                            {item.product.seller?.store_name ?? "Art Market"}
                                                        </p>
                                                        {hasStockIssue ? (
                                                            <p className="mt-2 text-sm font-semibold text-red-700">
                                                                Stok berubah. Tersedia {item.product.stock} item.
                                                            </p>
                                                        ) : null}
                                                    </div>
                                                    <p className="font-display text-xl font-semibold text-ink">
                                                        {formatCurrency(item.line_total)}
                                                    </p>
                                                </div>

                                                <div className="flex flex-wrap items-center justify-between gap-4">
                                                    <div className="inline-flex h-10 items-center border border-ink/15 bg-paper">
                                                        <button
                                                            type="button"
                                                            onClick={() => updateQuantity(item.product.slug, nextMin)}
                                                            disabled={item.quantity <= 1}
                                                            className={cx("flex h-full w-10 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                                            aria-label={`Kurangi kuantitas ${item.product.title}`}
                                                        >
                                                            <Minus className="h-4 w-4" />
                                                        </button>
                                                        <span className="w-10 text-center text-sm font-semibold text-ink">{item.quantity}</span>
                                                        <button
                                                            type="button"
                                                            onClick={() => updateQuantity(item.product.slug, nextMax)}
                                                            disabled={item.quantity >= item.product.stock}
                                                            className={cx("flex h-full w-10 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                                            aria-label={`Tambah kuantitas ${item.product.title}`}
                                                        >
                                                            <Plus className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeItem(item.product.slug)}
                                                        className={cx("inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-ink-muted transition-colors hover:text-gold", ui.focus)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                            <Link
                                href="/katalog"
                                className={cx("mt-6 inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted hover:text-gold", ui.focus)}
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Kembali belanja
                            </Link>
                        </div>

                        <aside className="lg:sticky lg:top-28 lg:self-start">
                            <div className="rounded-[var(--radius-card)] bg-surface p-6 shadow-soft sm:p-8">
                                <h2 className="font-display text-2xl font-bold text-ink">Ringkasan</h2>
                                <div className="my-6 space-y-3 border-y border-ink/10 py-5">
                                    <div className="flex justify-between gap-4 text-sm">
                                        <span className="text-ink-muted">Total item</span>
                                        <span className="font-semibold text-ink">{cart.total_items}</span>
                                    </div>
                                    <div className="flex justify-between gap-4 text-sm">
                                        <span className="text-ink-muted">Subtotal</span>
                                        <span className="font-semibold text-ink">{formatCurrency(cart.subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between gap-4 text-sm">
                                        <span className="text-ink-muted">Pengiriman</span>
                                        <span className="font-semibold text-ink">Manual</span>
                                    </div>
                                    <div className="flex justify-between gap-4 pt-3">
                                        <span className="font-display text-xl font-bold text-ink">Total</span>
                                        <span className="font-display text-xl font-bold text-gold-dark">{formatCurrency(cart.subtotal)}</span>
                                    </div>
                                </div>
                                {cart.has_stock_issue ? (
                                    <p className="mb-5 text-sm font-medium leading-relaxed text-red-700">
                                        Ada item yang stoknya berubah. Sesuaikan kuantitas sebelum checkout.
                                    </p>
                                ) : null}
                                <Button href="/checkout" variant="primary" className="w-full" aria-disabled={cart.has_stock_issue}>
                                    Lanjut Checkout
                                </Button>
                                <p className="mt-4 text-xs leading-relaxed text-ink-muted">
                                    Pembayaran memakai invoice Midtrans. Jika sandbox belum aktif, sistem memakai invoice lokal untuk development.
                                </p>
                            </div>
                        </aside>
                    </div>
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
