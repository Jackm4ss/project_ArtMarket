import { Link, useForm } from "@inertiajs/react";
import {
    ArrowLeft,
    Award,
    ChevronLeft,
    ChevronRight,
    Heart,
    MapPin,
    MessageCircle,
    Minus,
    PackageCheck,
    Plus,
    Share2,
    ShieldCheck,
    ShoppingBag,
    Star,
    Store,
    Truck,
    X,
    Zap,
} from "lucide-react";
import { type PointerEvent, useEffect, useMemo, useState } from "react";

import { ArtworkCard, Button, Container, MediaFrame, Section, cx, ui } from "@/ArtMarket/design-system";
import { formatCurrency, moneyNumber, ProductReview, ProductSummary } from "@/ArtMarket/commerce";
import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";

type ProductShowProps = {
    product: ProductSummary & {
        reviews?: ProductReview[];
    };
    relatedProducts: ProductSummary[];
};

const productPanels = {
    detail: "product-detail-panel",
    reviews: "product-reviews-panel",
    recommendations: "product-recommendations-panel",
} as const;

export default function ProductShow({ product, relatedProducts }: ProductShowProps) {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [previewImageIndex, setPreviewImageIndex] = useState<number | null>(null);
    const [isGalleryOpen, setIsGalleryOpen] = useState(false);
    const [zoomOrigin, setZoomOrigin] = useState({ x: 50, y: 50 });
    const { data, setData, post: postCart, processing, errors } = useForm({
        product_id: product.id,
        quantity: 1,
    });
    const { post: postChat, processing: chatProcessing } = useForm({});
    const { post: postWishlist, processing: wishlistProcessing } = useForm({});

    const galleryImages = useMemo(
        () => (product.images?.length ? product.images : [product.image]),
        [product.image, product.images],
    );
    const selectedImage =
        galleryImages[Math.min(previewImageIndex ?? activeImageIndex, galleryImages.length - 1)] ?? product.image;
    const lightboxImage = galleryImages[Math.min(activeImageIndex, galleryImages.length - 1)] ?? product.image;
    const maxQuantity = Math.max(1, product.stock);
    const canBuy = product.stock > 0;
    const sellerRating = product.seller?.rating_average ?? product.rating_average ?? 0;
    const sellerRatingCount = product.seller?.rating_count ?? product.rating_count ?? 0;
    const productTypeLabel = product.product_type === "preorder" ? "Preorder" : "Ready";
    const subtotal = formatCurrency(moneyNumber(product.price) * data.quantity);

    const showPreviousImage = () => {
        setActiveImageIndex((index) => (index === 0 ? galleryImages.length - 1 : index - 1));
        setPreviewImageIndex(null);
    };

    const showNextImage = () => {
        setActiveImageIndex((index) => (index + 1) % galleryImages.length);
        setPreviewImageIndex(null);
    };

    useEffect(() => {
        if (!isGalleryOpen) {
            return;
        }

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === "Escape") {
                setIsGalleryOpen(false);
            }

            if (event.key === "ArrowLeft" && galleryImages.length > 1) {
                showPreviousImage();
            }

            if (event.key === "ArrowRight" && galleryImages.length > 1) {
                showNextImage();
            }
        };

        window.addEventListener("keydown", handleKeyDown);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener("keydown", handleKeyDown);
        };
    }, [galleryImages.length, isGalleryOpen]);

    const submitCart = () => {
        postCart("/cart/items", {
            preserveScroll: true,
        });
    };

    const moveZoomOrigin = (event: PointerEvent<HTMLButtonElement>) => {
        const rect = event.currentTarget.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        setZoomOrigin({
            x: Math.min(100, Math.max(0, x)),
            y: Math.min(100, Math.max(0, y)),
        });
    };

    const openGallery = () => {
        if (previewImageIndex !== null) {
            setActiveImageIndex(previewImageIndex);
            setPreviewImageIndex(null);
        }

        setIsGalleryOpen(true);
    };

    const buyNow = () => {
        postCart("/cart/items", {
            preserveScroll: true,
            onSuccess: () => {
                window.location.href = "/checkout";
            },
        });
    };

    const shareProduct = async () => {
        if (typeof navigator === "undefined") {
            return;
        }

        const browserNavigator = navigator as Navigator & {
            share?: (data: ShareData) => Promise<void>;
            clipboard?: Clipboard;
        };

        if (browserNavigator.share) {
            await browserNavigator.share({ title: product.title, url: window.location.href });
            return;
        }

        await browserNavigator.clipboard?.writeText(window.location.href);
    };

    const detailItems = [
        { label: "Kondisi", value: "Baru" },
        { label: "Status", value: productTypeLabel },
        { label: "Material", value: product.material ?? "Belum diisi" },
        { label: "Dimensi", value: product.dimensions ?? "Belum diisi" },
        { label: "Min. Beli", value: "1 karya" },
        { label: "Kategori", value: product.category?.name ?? "Karya Seni" },
        { label: "Lokasi", value: product.location ?? product.seller?.location ?? "Indonesia" },
    ];

    const trustItems = [
        {
            icon: ShieldCheck,
            title: "Keaslian karya terjaga",
            description: "Setiap karya dilengkapi informasi seller dan detail koleksi agar pembelian terasa aman.",
        },
        {
            icon: Truck,
            title: "Pengiriman transparan",
            description: "Informasi kurir dan nomor resi akan diperbarui setelah pesanan diproses oleh seller.",
        },
        {
            icon: PackageCheck,
            title: "Transaksi lebih tenang",
            description: "Pembayaran diproses melalui platform sehingga status pesanan bisa dipantau dengan jelas.",
        },
    ];

    return (
        <ArtMarketPublicLayout title={product.title}>
            <Section id="product-detail" className="bg-cream" compact>
                <Container>
                    <div className="mb-5 flex flex-wrap items-center justify-between gap-4 border-b border-ink/8 pb-4">
                        <Link
                            id="product-back-to-catalog"
                            href="/katalog"
                            className={cx(
                                "inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-ink-muted transition-colors hover:text-gold",
                                ui.focus,
                            )}
                        >
                            <ArrowLeft aria-hidden="true" className="h-4 w-4" />
                            Kembali ke Katalog
                        </Link>

                    </div>

                    <div className="grid grid-cols-1 gap-8 xl:grid-cols-[350px_minmax(0,1fr)_330px] xl:items-start">
                        <aside className="xl:sticky xl:top-36 xl:self-start">
                            <button
                                id="product-main-image"
                                type="button"
                                aria-label={`Lihat gambar ${product.title}`}
                                onPointerMove={moveZoomOrigin}
                                onPointerLeave={() => setZoomOrigin({ x: 50, y: 50 })}
                                onClick={openGallery}
                                className={cx(
                                    "group block w-full rounded-[var(--radius-frame)] border border-ink/8 bg-paper p-0 text-left shadow-soft",
                                    "cursor-zoom-in transition-[border-color,box-shadow,transform] duration-300 ease-out",
                                    "hover:-translate-y-0.5 hover:border-gold/45 hover:shadow-float",
                                    "motion-reduce:transition-none motion-reduce:hover:translate-y-0",
                                    ui.focus,
                                )}
                            >
                                <MediaFrame
                                    src={selectedImage.src}
                                    alt={selectedImage.alt}
                                    width={selectedImage.width}
                                    height={selectedImage.height}
                                    style={{ transformOrigin: `${zoomOrigin.x}% ${zoomOrigin.y}%` }}
                                    className="aspect-square rounded-[calc(var(--radius-frame)-1px)] bg-paper"
                                    imageClassName="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.85] motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                    loading="eager"
                                />
                            </button>

                            <div className="mt-4 flex gap-3 overflow-x-auto pb-2" onMouseLeave={() => setPreviewImageIndex(null)}>
                                {galleryImages.map((image, index) => (
                                    <button
                                        id={`product-gallery-thumb-${index + 1}`}
                                        key={`${image.src}-${index}`}
                                        type="button"
                                        onMouseEnter={() => setPreviewImageIndex(index)}
                                        onFocus={() => setPreviewImageIndex(index)}
                                        onBlur={() => setPreviewImageIndex(null)}
                                        onClick={() => setActiveImageIndex(index)}
                                        aria-label={`Tampilkan gambar ${index + 1}`}
                                        aria-pressed={activeImageIndex === index}
                                        className={cx(
                                            "h-[70px] w-[70px] shrink-0 overflow-hidden rounded-lg border-2 bg-paper p-0.5",
                                            "transition-[border-color,box-shadow,transform] duration-200 ease-out",
                                            "hover:-translate-y-0.5 hover:border-gold hover:shadow-soft",
                                            "motion-reduce:transition-none motion-reduce:hover:translate-y-0",
                                            activeImageIndex === index || previewImageIndex === index ? "border-gold" : "border-ink/10",
                                            ui.focus,
                                        )}
                                    >
                                        <img
                                            src={image.src}
                                            alt=""
                                            aria-hidden="true"
                                            width={image.width}
                                            height={image.height}
                                            className="h-full w-full object-cover"
                                            loading={index === 0 ? "eager" : "lazy"}
                                        />
                                    </button>
                                ))}
                            </div>

                            <div className="mt-5 grid grid-cols-1 gap-3 text-sm text-ink-muted">
                                <div className="flex items-center gap-2 border border-ink/8 bg-paper px-4 py-3">
                                    <Award aria-hidden="true" className="h-4 w-4 text-gold" />
                                    <span>Kurasi marketplace</span>
                                </div>
                                <div className="flex items-center gap-2 border border-ink/8 bg-paper px-4 py-3">
                                    <MapPin aria-hidden="true" className="h-4 w-4 text-gold" />
                                    <span>{product.location ?? product.seller?.location ?? "Indonesia"}</span>
                                </div>
                            </div>
                        </aside>

                        <main className="min-w-0">
                            <article className="rounded-[var(--radius-card)] border border-ink/8 bg-paper p-6 shadow-soft lg:p-7">
                                <h1 className="font-display text-3xl font-bold leading-tight text-ink lg:text-4xl">
                                    {product.title}
                                </h1>

                                <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-ink-muted">
                                    <span>{sellerRatingCount > 0 ? `${sellerRatingCount} kolektor menilai` : "Karya pilihan Art Market"}</span>
                                    <span className="hidden h-1 w-1 rounded-full bg-ink/25 sm:inline-block" aria-hidden="true" />
                                    <span className="flex items-center gap-1 text-ink">
                                        <Star aria-hidden="true" className="h-4 w-4 fill-gold text-gold" />
                                        {sellerRating.toFixed(1)}
                                        {sellerRatingCount ? <span className="text-ink-muted">({sellerRatingCount} rating)</span> : null}
                                    </span>
                                </div>

                                <p className="mt-6 font-display text-4xl font-semibold text-ink">
                                    {formatCurrency(product.price)}
                                </p>

                                <section id={productPanels.detail} className="mt-7 scroll-mt-32 border-t border-ink/8 pt-6">
                                    <div className="flex border-b border-ink/8">
                                        <button
                                            id="product-tab-detail"
                                            type="button"
                                            className={cx("border-b-2 border-gold px-0 pb-3 text-sm font-bold text-ink", ui.focus)}
                                        >
                                            Detail Produk
                                        </button>
                                    </div>

                                    <dl className="mt-5 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                                        {detailItems.map((item) => (
                                            <div key={item.label} className="grid grid-cols-[96px_1fr] gap-2">
                                                <dt className="text-ink-muted">{item.label}</dt>
                                                <dd className="font-medium text-ink">: {item.value}</dd>
                                            </div>
                                        ))}
                                    </dl>

                                    <div className="mt-6 text-sm leading-7 text-ink-muted">
                                        <p className="whitespace-pre-line">
                                            {product.description ?? product.excerpt ?? "Detail karya sedang dilengkapi oleh seller."}
                                        </p>
                                    </div>
                                </section>
                            </article>

                            <section className="mt-5 rounded-[var(--radius-card)] border border-ink/8 bg-paper p-6 shadow-soft">
                                <div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="flex items-start gap-4">
                                        <div className="grid h-14 w-14 shrink-0 place-items-center bg-surface text-gold-dark shadow-soft">
                                            <Store aria-hidden="true" className="h-6 w-6" />
                                        </div>
                                        <div>
                                            <h2 className="font-display text-2xl font-bold text-ink">
                                                {product.seller?.store_name ?? "Art Market"}
                                            </h2>
                                            <p className="mt-1 flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                                                <span>{product.seller?.location ?? "Indonesia"}</span>
                                                <span aria-hidden="true">-</span>
                                                <span className="flex items-center gap-1 text-ink">
                                                    <Star aria-hidden="true" className="h-4 w-4 fill-gold text-gold" />
                                                    {sellerRating.toFixed(1)}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    {product.seller?.slug ? (
                                        <Link
                                            id="product-seller-store-link"
                                            href={`/toko/${product.seller.slug}`}
                                            className={cx(
                                                "inline-flex items-center justify-center border border-ink/15 px-5 py-3 text-xs font-bold uppercase tracking-[0.16em] text-ink hover:border-gold hover:text-gold",
                                                ui.focus,
                                            )}
                                        >
                                            Lihat Toko
                                        </Link>
                                    ) : null}
                                </div>
                            </section>

                            <section className="mt-5 rounded-[var(--radius-card)] border border-ink/8 bg-paper p-6 shadow-soft">
                                <h2 className="font-display text-2xl font-bold text-ink">Pengiriman</h2>
                                <div className="mt-4 flex items-start gap-3 text-sm text-ink-muted">
                                    <Truck aria-hidden="true" className="mt-1 h-5 w-5 shrink-0 text-gold" />
                                    <div>
                                        <p className="font-semibold text-ink">Dikirim dari {product.location ?? product.seller?.location ?? "Indonesia"}</p>
                                        <p className="mt-1 leading-relaxed">Kurir dan nomor resi diinput seller/admin setelah pesanan diproses.</p>
                                    </div>
                                </div>
                            </section>
                        </main>

                        <aside className="xl:sticky xl:top-36 xl:self-start">
                            <div className="rounded-[var(--radius-card)] border border-ink/10 bg-paper p-5 shadow-soft">
                                <h2 className="font-display text-xl font-bold text-ink">Atur jumlah dan catatan</h2>

                                <div className="mt-5 border-b border-ink/8 pb-4">
                                    <p className="text-xs font-bold uppercase tracking-[0.16em] text-ink-muted">Terpilih</p>
                                    <p className="mt-1 line-clamp-2 text-sm font-semibold text-ink">{product.title}</p>
                                </div>

                                <div className="mt-5 flex items-center gap-4">
                                    <div className="inline-flex h-11 items-center border border-ink/15 bg-cream">
                                        <button
                                            id="product-quantity-decrease"
                                            type="button"
                                            onClick={() => setData("quantity", Math.max(1, data.quantity - 1))}
                                            disabled={!canBuy || data.quantity <= 1}
                                            className={cx("flex h-full w-10 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                            aria-label="Kurangi kuantitas"
                                        >
                                            <Minus aria-hidden="true" className="h-4 w-4" />
                                        </button>
                                        <span className="w-10 text-center text-sm font-semibold text-ink">{data.quantity}</span>
                                        <button
                                            id="product-quantity-increase"
                                            type="button"
                                            onClick={() => setData("quantity", Math.min(maxQuantity, data.quantity + 1))}
                                            disabled={!canBuy || data.quantity >= maxQuantity}
                                            className={cx("flex h-full w-10 items-center justify-center text-ink-muted transition-colors hover:text-gold disabled:opacity-30", ui.focus)}
                                            aria-label="Tambah kuantitas"
                                        >
                                            <Plus aria-hidden="true" className="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p className="text-sm text-ink-muted">
                                        Stok <span className="font-semibold text-ink">{product.stock}</span>
                                    </p>
                                </div>

                                <div className="mt-5 flex items-center justify-between gap-4 border-t border-ink/8 pt-4">
                                    <span className="text-sm text-ink-muted">Subtotal</span>
                                    <span className="font-display text-2xl font-semibold text-ink">{subtotal}</span>
                                </div>

                                <div className="mt-5 space-y-3">
                                    <Button
                                        id="product-buy-now"
                                        type="button"
                                        variant="primary"
                                        icon={Zap}
                                        onClick={buyNow}
                                        disabled={!canBuy || processing}
                                        className="w-full"
                                    >
                                        {processing ? "Memproses" : canBuy ? "Beli Langsung" : "Stok Habis"}
                                    </Button>
                                    <Button
                                        id="product-add-to-cart"
                                        type="button"
                                        variant="gold-outline"
                                        icon={ShoppingBag}
                                        onClick={submitCart}
                                        disabled={!canBuy || processing}
                                        className="w-full"
                                    >
                                        {processing ? "Memproses" : canBuy ? "+ Keranjang" : "Stok Habis"}
                                    </Button>
                                </div>

                                {errors.quantity || errors.product_id ? (
                                    <p className="mt-4 text-sm font-medium text-red-700">{errors.quantity ?? errors.product_id}</p>
                                ) : null}

                                <div className="mt-5 grid grid-cols-3 divide-x divide-ink/10 border-t border-ink/8 pt-4">
                                    <button
                                        id="product-open-chat"
                                        type="button"
                                        onClick={() => postChat(`/produk/${product.slug}/chat`, { preserveScroll: true })}
                                        disabled={chatProcessing}
                                        className={cx(
                                            "inline-flex items-center justify-center gap-2 px-2 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-ink transition-colors hover:text-gold disabled:cursor-not-allowed disabled:opacity-50",
                                            ui.focus,
                                        )}
                                    >
                                        <MessageCircle aria-hidden="true" className="h-4 w-4" />
                                        {chatProcessing ? "Membuka" : "Chat"}
                                    </button>
                                    <button
                                        id="product-wishlist-action"
                                        type="button"
                                        onClick={() => postWishlist(`/user/wishlist/${product.slug}`, { preserveScroll: true })}
                                        disabled={wishlistProcessing}
                                        className={cx(
                                            "inline-flex items-center justify-center gap-2 px-2 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-ink transition-colors hover:text-gold disabled:cursor-not-allowed disabled:opacity-50",
                                            ui.focus,
                                        )}
                                    >
                                        <Heart aria-hidden="true" className="h-4 w-4" />
                                        Wishlist
                                    </button>
                                    <button
                                        id="product-share-action"
                                        type="button"
                                        onClick={() => void shareProduct()}
                                        className={cx(
                                            "inline-flex items-center justify-center gap-2 px-2 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-ink transition-colors hover:text-gold",
                                            ui.focus,
                                        )}
                                    >
                                        <Share2 aria-hidden="true" className="h-4 w-4" />
                                        Share
                                    </button>
                                </div>
                            </div>

                            <div className="relative mt-5 overflow-hidden rounded-[var(--radius-card)] border border-gold/25 bg-[radial-gradient(circle_at_top_right,rgba(var(--color-accent),0.24),transparent_34%),linear-gradient(135deg,rgba(var(--color-surface),0.95),rgba(var(--color-paper),0.98))] p-5 shadow-soft">
                                <div aria-hidden="true" className="pointer-events-none absolute -right-14 -top-14 h-32 w-32 rounded-full bg-gold/20 blur-3xl" />
                                <div aria-hidden="true" className="pointer-events-none absolute -bottom-16 left-6 h-28 w-28 rounded-full bg-cream-dark/70 blur-3xl" />
                                <div className="relative space-y-4">
                                    {trustItems.map((item) => (
                                        <div key={item.title} className="flex gap-3">
                                            <div className="grid h-9 w-9 shrink-0 place-items-center border border-gold/20 bg-paper/90 text-gold-dark shadow-soft">
                                                <item.icon aria-hidden="true" className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-bold text-ink">{item.title}</p>
                                                <p className="mt-1 text-xs leading-relaxed text-ink-muted">{item.description}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </aside>
                    </div>

                    {isGalleryOpen ? (
                        <div
                            id="product-gallery-lightbox"
                            className="fixed inset-0 z-[80] flex items-center justify-center bg-ink/65 px-4 py-6 backdrop-blur-sm"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="product-gallery-lightbox-title"
                            onClick={() => setIsGalleryOpen(false)}
                        >
                            <div
                                className="relative grid max-h-[92vh] w-full max-w-6xl grid-cols-1 overflow-hidden rounded-[var(--radius-card)] bg-paper shadow-float lg:grid-cols-[minmax(0,1fr)_260px]"
                                onClick={(event) => event.stopPropagation()}
                            >
                                <div className="flex min-h-0 flex-col">
                                    <div className="flex items-start justify-between gap-4 border-b border-ink/8 px-5 py-4 lg:px-8">
                                        <div>
                                            <p className="text-xs font-bold uppercase tracking-[0.18em] text-gold">Galeri Karya</p>
                                            <h2 id="product-gallery-lightbox-title" className="mt-1 font-display text-xl font-bold text-ink lg:text-2xl">
                                                {product.title}
                                            </h2>
                                        </div>
                                        <button
                                            id="product-gallery-close"
                                            type="button"
                                            aria-label="Tutup galeri gambar"
                                            onClick={() => setIsGalleryOpen(false)}
                                            className={cx(
                                                "grid h-11 w-11 shrink-0 place-items-center border border-ink/10 text-ink transition-colors hover:border-gold hover:text-gold",
                                                ui.focus,
                                            )}
                                        >
                                            <X aria-hidden="true" className="h-5 w-5" />
                                        </button>
                                    </div>

                                    <div className="relative grid min-h-0 flex-1 place-items-center bg-cream px-5 py-6 lg:px-8">
                                        <img
                                            src={lightboxImage.src}
                                            alt={lightboxImage.alt}
                                            width={lightboxImage.width}
                                            height={lightboxImage.height}
                                            className="max-h-[58vh] w-full max-w-3xl object-contain"
                                        />

                                        {galleryImages.length > 1 ? (
                                            <>
                                                <button
                                                    id="product-gallery-previous"
                                                    type="button"
                                                    aria-label="Tampilkan gambar sebelumnya"
                                                    onClick={showPreviousImage}
                                                    className={cx(
                                                        "absolute left-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-paper text-ink shadow-soft transition-[background-color,color,box-shadow] hover:bg-gold hover:text-ink hover:shadow-float",
                                                        ui.focus,
                                                    )}
                                                >
                                                    <ChevronLeft aria-hidden="true" className="h-5 w-5" />
                                                </button>
                                                <button
                                                    id="product-gallery-next"
                                                    type="button"
                                                    aria-label="Tampilkan gambar berikutnya"
                                                    onClick={showNextImage}
                                                    className={cx(
                                                        "absolute right-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-paper text-ink shadow-soft transition-[background-color,color,box-shadow] hover:bg-gold hover:text-ink hover:shadow-float",
                                                        ui.focus,
                                                    )}
                                                >
                                                    <ChevronRight aria-hidden="true" className="h-5 w-5" />
                                                </button>
                                            </>
                                        ) : null}
                                    </div>
                                </div>

                                <aside className="border-t border-ink/8 bg-paper p-5 lg:max-h-[92vh] lg:overflow-y-auto lg:border-l lg:border-t-0">
                                    <p className="text-sm font-bold text-ink">Gambar Karya</p>
                                    <div className="mt-4 grid grid-cols-4 gap-3 lg:grid-cols-3">
                                        {galleryImages.map((image, index) => (
                                            <button
                                                id={`product-gallery-lightbox-thumb-${index + 1}`}
                                                key={`lightbox-${image.src}-${index}`}
                                                type="button"
                                                aria-label={`Pilih gambar ${index + 1}`}
                                                aria-pressed={activeImageIndex === index}
                                                onClick={() => setActiveImageIndex(index)}
                                                className={cx(
                                                    "aspect-square overflow-hidden rounded-lg border-2 bg-cream p-0.5 transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-gold hover:shadow-soft",
                                                    "motion-reduce:transition-none motion-reduce:hover:translate-y-0",
                                                    activeImageIndex === index ? "border-gold" : "border-ink/10",
                                                    ui.focus,
                                                )}
                                            >
                                                <img
                                                    src={image.src}
                                                    alt=""
                                                    aria-hidden="true"
                                                    width={image.width}
                                                    height={image.height}
                                                    className="h-full w-full rounded-md object-cover"
                                                    loading={index < 3 ? "eager" : "lazy"}
                                                />
                                            </button>
                                        ))}
                                    </div>
                                </aside>
                            </div>
                        </div>
                    ) : null}

                    <section id={productPanels.reviews} className="mt-16 scroll-mt-32 border-t border-ink/10 pt-12">
                        <div className="mb-8 flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-[0.2em] text-gold">Ulasan Pembeli</p>
                                <h2 className="mt-3 font-display text-3xl font-bold text-ink">Cerita dari kolektor</h2>
                            </div>
                            <div className="flex items-center gap-2 text-sm font-semibold text-ink">
                                <Star aria-hidden="true" className="h-4 w-4 fill-gold text-gold" />
                                {(product.rating_average ?? sellerRating).toFixed(1)}
                                <span className="text-ink-muted">/ {product.reviews?.length ?? 0} ulasan tampil</span>
                            </div>
                        </div>

                        {product.reviews && product.reviews.length > 0 ? (
                            <div className="grid gap-5 md:grid-cols-2">
                                {product.reviews.map((review) => (
                                    <article key={review.id} className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft">
                                        <div className="mb-3 flex items-center gap-1 text-gold">
                                            {Array.from({ length: review.rating }).map((_, index) => (
                                                <Star key={index} aria-hidden="true" className="h-4 w-4 fill-current" />
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
                        ) : (
                            <div className="rounded-[var(--radius-card)] border border-ink/8 bg-paper p-6 text-sm text-ink-muted shadow-soft">
                                Belum ada ulasan yang tampil untuk karya ini.
                            </div>
                        )}
                    </section>

                    {relatedProducts.length > 0 ? (
                        <section id={productPanels.recommendations} className="mt-16 scroll-mt-32 border-t border-ink/10 pt-12">
                            <div className="mb-8 flex items-end justify-between gap-6">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-gold">Rekomendasi</p>
                                    <h2 className="mt-3 font-display text-3xl font-bold text-ink">Karya lain yang relevan</h2>
                                </div>
                                <Link
                                    id="product-related-catalog-link"
                                    href="/katalog"
                                    className={cx("hidden text-sm font-bold uppercase tracking-widest text-ink hover:text-gold sm:inline-flex", ui.focus)}
                                >
                                    Lihat Katalog
                                </Link>
                            </div>
                            <div className="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                                {relatedProducts.slice(0, 4).map((item) => (
                                    <Link id={`product-related-${item.id}`} href={`/produk/${item.slug}`} key={item.id} className={cx("block", ui.focus)}>
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
                        </section>
                    ) : null}
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
