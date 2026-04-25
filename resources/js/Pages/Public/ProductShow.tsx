import { Link, useForm } from "@inertiajs/react";
import { ArrowLeft, MessageCircle, Minus, Plus, ShieldCheck, ShoppingBag, Star, Truck } from "lucide-react";

import { ArtworkCard, Button, Container, MediaFrame, Section, cx, ui } from "@/ArtMarket/design-system";
import { formatCurrency, ProductReview, ProductSummary } from "@/ArtMarket/commerce";
import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";

type ProductShowProps = {
    product: ProductSummary & {
        reviews?: ProductReview[];
    };
    relatedProducts: ProductSummary[];
};

export default function ProductShow({ product, relatedProducts }: ProductShowProps) {
    const { data, setData, post: postCart, processing, errors } = useForm({
        product_id: product.id,
        quantity: 1,
    });
    const { post: postChat, processing: chatProcessing } = useForm({});

    const maxQuantity = Math.max(1, product.stock);
    const canBuy = product.stock > 0;

    const submitCart = () => {
        postCart("/cart/items", {
            preserveScroll: true,
        });
    };

    return (
        <ArtMarketPublicLayout title={product.title}>
            <Section id="product-detail">
                <Container>
                    <div className="mb-8">
                        <Link
                            href="/katalog"
                            className={cx(
                                "inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted transition-colors hover:text-gold",
                                ui.focus,
                            )}
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Katalog
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-20">
                        <div className="lg:sticky lg:top-28 lg:self-start">
                            <MediaFrame
                                src={product.image.src}
                                alt={product.image.alt}
                                width={product.image.width}
                                height={product.image.height}
                                className="aspect-[4/5] rounded-[var(--radius-frame)] shadow-soft"
                                imageClassName="object-cover"
                                loading="eager"
                            />
                        </div>

                        <div className="flex flex-col">
                            <div className="mb-4 flex flex-wrap items-center gap-2">
                                <span className="bg-ink/5 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.15em] text-ink">
                                    {product.category?.name ?? "Karya Seni"}
                                </span>
                                {product.product_type ? (
                                    <span className="bg-gold/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.15em] text-gold-dark">
                                        {product.product_type === "preorder" ? "Preorder" : "Ready"}
                                    </span>
                                ) : null}
                            </div>

                            <h1 className="font-display text-4xl font-bold leading-tight lg:text-5xl">
                                {product.title}
                            </h1>
                            <p className="mt-2 text-sm font-medium uppercase tracking-[0.15em] text-ink-muted">
                                Oleh <span className="text-ink">{product.seller?.store_name ?? "Art Market"}</span>
                            </p>

                            <div className="my-8 border-y border-ink/8 py-6">
                                <p className="font-display text-3xl font-semibold text-ink">
                                    {formatCurrency(product.price)}
                                </p>
                                <p className="mt-2 text-sm text-ink-muted">
                                    Stok tersedia: <span className="font-semibold text-ink">{product.stock}</span>
                                </p>
                            </div>

                            <p className="mb-8 text-sm leading-relaxed text-ink-muted">
                                {product.description ?? product.excerpt ?? "Detail karya sedang dilengkapi oleh seller."}
                            </p>

                            <dl className="mb-8 grid gap-4 border-y border-ink/8 py-6 text-sm sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Material</dt>
                                    <dd className="mt-1 text-ink">{product.material ?? "Belum diisi"}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Dimensi</dt>
                                    <dd className="mt-1 text-ink">{product.dimensions ?? "Belum diisi"}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Lokasi</dt>
                                    <dd className="mt-1 text-ink">{product.location ?? product.seller?.location ?? "Indonesia"}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-bold uppercase tracking-widest text-ink-muted">Rating Seller</dt>
                                    <dd className="mt-1 flex items-center gap-1 text-ink">
                                        <Star className="h-4 w-4 fill-gold text-gold" />
                                        {(product.seller?.rating_average ?? product.rating_average ?? 0).toFixed(1)}
                                    </dd>
                                </div>
                            </dl>

                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div className="inline-flex h-14 items-center border border-ink/15 bg-paper">
                                    <button
                                        type="button"
                                        onClick={() => setData("quantity", Math.max(1, data.quantity - 1))}
                                        disabled={!canBuy || data.quantity <= 1}
                                        className={cx("flex h-full w-12 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                        aria-label="Kurangi kuantitas"
                                    >
                                        <Minus className="h-4 w-4" />
                                    </button>
                                    <span className="w-12 text-center text-sm font-semibold text-ink">{data.quantity}</span>
                                    <button
                                        type="button"
                                        onClick={() => setData("quantity", Math.min(maxQuantity, data.quantity + 1))}
                                        disabled={!canBuy || data.quantity >= maxQuantity}
                                        className={cx("flex h-full w-12 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                        aria-label="Tambah kuantitas"
                                    >
                                        <Plus className="h-4 w-4" />
                                    </button>
                                </div>

                                <Button
                                    type="button"
                                    variant="primary"
                                    icon={ShoppingBag}
                                    onClick={submitCart}
                                    disabled={!canBuy || processing}
                                    className="w-full sm:w-auto"
                                >
                                    {processing ? "Memproses" : canBuy ? "Tambah ke Keranjang" : "Stok Habis"}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    icon={MessageCircle}
                                    onClick={() => postChat(`/produk/${product.slug}/chat`, { preserveScroll: true })}
                                    disabled={chatProcessing}
                                    className="w-full sm:w-auto"
                                >
                                    {chatProcessing ? "Membuka Chat" : "Chat Seller"}
                                </Button>
                            </div>
                            {errors.quantity || errors.product_id ? (
                                <p className="mt-3 text-sm font-medium text-red-700">{errors.quantity ?? errors.product_id}</p>
                            ) : null}

                            <div className="mt-12 space-y-4 rounded-[var(--radius-card)] bg-surface p-6">
                                <div className="flex items-center gap-3">
                                    <ShieldCheck className="h-5 w-5 text-gold" />
                                    <span className="text-sm font-medium text-ink">Jaminan keaslian dan moderasi marketplace</span>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Truck className="h-5 w-5 text-gold" />
                                    <span className="text-sm font-medium text-ink">Pengiriman manual dengan resi dari seller/admin</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {product.reviews && product.reviews.length > 0 ? (
                        <div className="mt-20 border-t border-ink/10 pt-12">
                            <h2 className="font-display text-3xl font-bold text-ink">Ulasan Pembeli</h2>
                            <div className="mt-8 grid gap-5 md:grid-cols-2">
                                {product.reviews.map((review) => (
                                    <article key={review.id} className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft">
                                        <div className="mb-3 flex items-center gap-1 text-gold">
                                            {Array.from({ length: review.rating }).map((_, index) => (
                                                <Star key={index} className="h-4 w-4 fill-current" />
                                            ))}
                                        </div>
                                        <h3 className="font-display text-lg font-bold text-ink">{review.title ?? "Ulasan karya"}</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-ink-muted">{review.body}</p>
                                        <p className="mt-4 text-xs font-bold uppercase tracking-widest text-ink-muted">
                                            {review.user?.name ?? "Pembeli"}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    ) : null}

                    {relatedProducts.length > 0 ? (
                        <div className="mt-20 border-t border-ink/10 pt-12">
                            <div className="mb-8 flex items-end justify-between gap-6">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-gold">Karya Terkait</p>
                                    <h2 className="mt-3 font-display text-3xl font-bold text-ink">Masih dari kategori yang sama</h2>
                                </div>
                                <Link href="/katalog" className={cx("hidden text-sm font-bold uppercase tracking-widest text-ink hover:text-gold sm:inline-flex", ui.focus)}>
                                    Lihat Katalog
                                </Link>
                            </div>
                            <div className="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                                {relatedProducts.slice(0, 4).map((item) => (
                                    <Link href={`/produk/${item.slug}`} key={item.id} className="block">
                                        <ArtworkCard
                                            category={item.category?.name ?? "Karya Seni"}
                                            artist={item.seller?.store_name ?? "Art Market"}
                                            title={item.title}
                                            price={formatCurrency(item.price)}
                                            image={item.image}
                                        />
                                    </Link>
                                ))}
                            </div>
                        </div>
                    ) : null}
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
