import { Head, Link, router } from "@inertiajs/react";
import { ChevronDown, ListFilter, Minus, Plus, Search, X } from "lucide-react";
import { FormEvent, useMemo, useState } from "react";

import { CartProvider } from "@/ArtMarket/context/CartContext";
import {
    ArtworkCard,
    Container,
    Eyebrow,
    Section,
    cx,
    defaultTheme,
    ui,
} from "@/ArtMarket/design-system";
import { FooterSection, HeaderSection } from "@/ArtMarket/sections";

type Category = {
    id: number;
    name: string;
    slug: string;
};

type ProductCard = {
    id: number;
    title: string;
    slug: string;
    price: string | number;
    category?: Category | null;
    seller?: {
        store_name: string;
        slug: string;
    } | null;
    media?: Array<{
        original_url?: string;
        preview_url?: string;
        name?: string;
    }>;
};

type CursorPage<T> = {
    data: T[];
    next_page_url: string | null;
    prev_page_url: string | null;
};

type CatalogFilters = {
    q?: string;
    category?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
};

type CatalogProps = {
    products: CursorPage<ProductCard>;
    categories: Category[];
    filters: CatalogFilters;
};

const defaultCategories: Category[] = [
    { id: 0, name: "Lukisan", slug: "lukisan" },
    { id: 1, name: "Patung", slug: "patung" },
    { id: 2, name: "Relief", slug: "relief" },
    { id: 3, name: "Kerajinan seni", slug: "kerajinan-seni" },
    { id: 4, name: "Dekorasi artistik", slug: "dekorasi-artistik" },
];

const priceRanges = [
    { id: "under-2m", label: "Di bawah Rp 2.000.000", min: "", max: "2000000" },
    { id: "2m-5m", label: "Rp 2.000.000 - Rp 5.000.000", min: "2000000", max: "5000000" },
    { id: "5m-10m", label: "Rp 5.000.000 - Rp 10.000.000", min: "5000000", max: "10000000" },
    { id: "over-10m", label: "Di atas Rp 10.000.000", min: "10000000", max: "" },
];

const sortOptions = [
    { label: "Ulasan", value: "reviews" },
    { label: "Terbaru", value: "latest" },
    { label: "Harga Tertinggi", value: "price_desc" },
    { label: "Harga Terendah", value: "price_asc" },
];

const fallbackImage = {
    src: "https://images.unsplash.com/photo-1549490349-8643362247b5?q=80&w=600&auto=format&fit=crop",
    alt: "Karya seni pilihan Art Market",
    width: 600,
    height: 800,
};

const formatPrice = (price: string | number) => {
    const value = typeof price === "number" ? price : Number.parseFloat(price);

    return `Rp ${Number.isFinite(value) ? value.toLocaleString("id-ID") : price}`;
};

const compactParams = (params: Record<string, string | undefined>) =>
    Object.fromEntries(Object.entries(params).filter(([, value]) => value !== undefined && value !== ""));

export default function Catalog({ products, categories, filters }: CatalogProps) {
    const [search, setSearch] = useState(filters.q ?? "");
    const [catExpanded, setCatExpanded] = useState(true);
    const [priceExpanded, setPriceExpanded] = useState(true);
    const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
    const [mobileSortOpen, setMobileSortOpen] = useState(false);
    const [minPrice, setMinPrice] = useState(filters.min_price ?? "");
    const [maxPrice, setMaxPrice] = useState(filters.max_price ?? "");

    const categoryOptions = categories.length > 0 ? categories : defaultCategories;
    const selectedSort = filters.sort ?? "latest";

    const selectedPriceRange = useMemo(() => {
        return priceRanges.find((range) => range.min === (filters.min_price ?? "") && range.max === (filters.max_price ?? ""))?.id;
    }, [filters.max_price, filters.min_price]);

    const visitCatalog = (nextFilters: CatalogFilters) => {
        router.get(
            "/katalog",
            compactParams({
                q: search,
                category: filters.category,
                min_price: minPrice,
                max_price: maxPrice,
                sort: selectedSort,
                ...nextFilters,
            }),
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const submitSearch = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        visitCatalog({ q: search });
    };

    const selectPriceRange = (range: (typeof priceRanges)[number]) => {
        setMinPrice(range.min);
        setMaxPrice(range.max);
        visitCatalog({ min_price: range.min, max_price: range.max });
    };

    const productsCount = products.data.length;

    return (
        <CartProvider>
            <Head title="Katalog Karya Seni" />
            <div
                data-theme={defaultTheme}
                className="flex min-h-screen flex-col overflow-x-hidden bg-cream font-body text-ink"
            >
                <div className="grain-overlay" />
                <HeaderSection />
                <main id="main-content" className="flex-1 pt-20">
                    <Section id="catalog" className="min-h-screen">
                        <Container>
                            <div className="mb-14">
                                <Eyebrow className="mb-4">Katalog</Eyebrow>
                                <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">
                                    Eksplorasi Karya Seni
                                </h1>
                                <p className="mt-4 max-w-lg text-sm leading-relaxed text-ink-muted">
                                    Temukan karya seni autentik dari seniman berbakat di seluruh Indonesia.
                                </p>
                            </div>

                            <div className="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-16">
                                <div
                                    aria-hidden="true"
                                    onClick={() => setMobileFilterOpen(false)}
                                    className={cx(
                                        "fixed inset-0 z-[100] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
                                        mobileFilterOpen ? "opacity-100" : "pointer-events-none opacity-0",
                                    )}
                                />

                                <div
                                    aria-hidden="true"
                                    onClick={() => setMobileSortOpen(false)}
                                    className={cx(
                                        "fixed inset-0 z-[100] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
                                        mobileSortOpen ? "opacity-100" : "pointer-events-none opacity-0",
                                    )}
                                />

                                <aside
                                    className={cx(
                                        "fixed bottom-0 left-0 z-[110] flex max-h-[85vh] w-full flex-col overflow-y-auto rounded-t-3xl bg-paper px-6 pb-8 pt-6 shadow-[0_-10px_40px_rgba(26,26,26,0.1)] transition-transform duration-300 ease-out lg:hidden",
                                        mobileSortOpen ? "translate-y-0" : "translate-y-full",
                                    )}
                                >
                                    <div className="mb-4 flex items-center justify-between border-b border-ink/10 pb-4">
                                        <div className="flex items-center gap-3">
                                            <button
                                                type="button"
                                                onClick={() => setMobileSortOpen(false)}
                                                className={cx("text-ink transition-colors hover:text-gold", ui.focus)}
                                                aria-label="Tutup urutan"
                                            >
                                                <X className="h-5 w-5" />
                                            </button>
                                            <span className="font-display text-lg font-bold text-ink">Urutkan</span>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => visitCatalog({ sort: "latest" })}
                                            className={cx("text-sm font-bold text-gold transition-colors hover:text-gold-dark", ui.focus)}
                                        >
                                            Reset
                                        </button>
                                    </div>

                                    <div className="flex flex-col">
                                        {sortOptions.map((option) => (
                                            <label
                                                key={option.value}
                                                className="group flex cursor-pointer items-center justify-between border-b border-ink/5 py-4 last:border-0"
                                            >
                                                <span
                                                    className={cx(
                                                        "text-sm transition-colors",
                                                        selectedSort === option.value
                                                            ? "font-bold text-ink"
                                                            : "font-medium text-ink-muted group-hover:text-ink",
                                                    )}
                                                >
                                                    {option.label}
                                                </span>
                                                <input
                                                    type="radio"
                                                    name="sort-mobile"
                                                    checked={selectedSort === option.value}
                                                    onChange={() => {
                                                        visitCatalog({ sort: option.value });
                                                        setTimeout(() => setMobileSortOpen(false), 250);
                                                    }}
                                                    className="h-5 w-5 border-ink/20 text-gold focus:ring-gold"
                                                />
                                            </label>
                                        ))}
                                    </div>
                                </aside>

                                <aside
                                    className={cx(
                                        "fixed left-0 top-0 z-[110] flex h-full w-[300px] flex-col overflow-y-auto bg-paper p-6 shadow-float transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:h-auto lg:w-64 lg:flex-shrink-0 lg:translate-x-0 lg:overflow-y-visible lg:bg-transparent lg:p-0 lg:shadow-none",
                                        mobileFilterOpen ? "translate-x-0" : "-translate-x-full",
                                    )}
                                >
                                    <div className="mb-6 flex items-center justify-between border-b border-ink/10 pb-4 lg:hidden">
                                        <span className="font-display text-xl font-bold">Filter</span>
                                        <button
                                            type="button"
                                            onClick={() => setMobileFilterOpen(false)}
                                            className={cx("text-ink transition-colors hover:text-gold", ui.focus)}
                                            aria-label="Tutup filter"
                                        >
                                            <X className="h-5 w-5" />
                                        </button>
                                    </div>

                                    <div className="flex flex-col gap-8">
                                        <div className="border-b border-ink/10 pb-8">
                                            <button
                                                type="button"
                                                onClick={() => setCatExpanded(!catExpanded)}
                                                className={cx("flex w-full items-center justify-between text-left", ui.focus)}
                                            >
                                                <div>
                                                    <h3 className="font-display text-lg font-bold text-ink">Kategori</h3>
                                                    <p className="mt-1 text-xs text-ink-muted">Pilih satu medium.</p>
                                                </div>
                                                {catExpanded ? <Minus className="h-4 w-4 text-ink" /> : <Plus className="h-4 w-4 text-ink" />}
                                            </button>

                                            {catExpanded && (
                                                <div className="mt-6 flex flex-col gap-4">
                                                    <label className="group flex cursor-pointer items-center gap-3">
                                                        <input
                                                            type="radio"
                                                            name="category"
                                                            checked={!filters.category}
                                                            onChange={() => visitCatalog({ category: undefined })}
                                                            className="h-4 w-4 border-ink/40 text-gold focus:ring-gold"
                                                        />
                                                        <span className="text-sm font-bold text-ink">Semua</span>
                                                    </label>
                                                    {categoryOptions.map((category) => (
                                                        <label key={category.slug} className="group flex cursor-pointer items-center gap-3">
                                                            <input
                                                                type="radio"
                                                                name="category"
                                                                checked={filters.category === category.slug}
                                                                onChange={() => visitCatalog({ category: category.slug })}
                                                                className="h-4 w-4 border-ink/40 text-gold focus:ring-gold"
                                                            />
                                                            <span
                                                                className={cx(
                                                                    "text-sm font-medium transition-colors",
                                                                    filters.category === category.slug
                                                                        ? "font-bold text-ink"
                                                                        : "text-ink-muted group-hover:text-ink",
                                                                )}
                                                            >
                                                                {category.name}
                                                            </span>
                                                        </label>
                                                    ))}
                                                </div>
                                            )}
                                        </div>

                                        <div className="border-b border-ink/10 pb-8">
                                            <button
                                                type="button"
                                                onClick={() => setPriceExpanded(!priceExpanded)}
                                                className={cx("flex w-full items-center justify-between text-left", ui.focus)}
                                            >
                                                <h3 className="font-display text-lg font-bold text-ink">Harga</h3>
                                                {priceExpanded ? <Minus className="h-4 w-4 text-ink" /> : <Plus className="h-4 w-4 text-ink" />}
                                            </button>

                                            {priceExpanded && (
                                                <div className="mt-6 flex flex-col gap-5">
                                                    <div className="flex flex-col gap-4">
                                                        {priceRanges.map((range) => (
                                                            <label key={range.id} className="group flex cursor-pointer items-center gap-3">
                                                                <input
                                                                    type="radio"
                                                                    name="price-range"
                                                                    checked={selectedPriceRange === range.id}
                                                                    onChange={() => selectPriceRange(range)}
                                                                    className="h-4 w-4 border-ink/40 text-gold focus:ring-gold"
                                                                />
                                                                <span className="text-sm text-ink-muted transition-colors group-hover:text-ink">
                                                                    {range.label}
                                                                </span>
                                                            </label>
                                                        ))}
                                                    </div>

                                                    <form
                                                        onSubmit={(event) => {
                                                            event.preventDefault();
                                                            visitCatalog({ min_price: minPrice, max_price: maxPrice });
                                                        }}
                                                        className="mt-2"
                                                    >
                                                        <p className="mb-3 text-sm text-ink-muted">Atau masukkan rentang harga</p>
                                                        <div className="flex items-center gap-2">
                                                            <input
                                                                type="number"
                                                                placeholder="Min"
                                                                value={minPrice}
                                                                onChange={(event) => setMinPrice(event.target.value)}
                                                                className={cx("w-full rounded-[var(--radius-base)] border border-ink/20 px-3 py-2 text-sm text-ink placeholder:text-ink/40", ui.focus)}
                                                            />
                                                            <span className="text-ink-muted">-</span>
                                                            <input
                                                                type="number"
                                                                placeholder="Max"
                                                                value={maxPrice}
                                                                onChange={(event) => setMaxPrice(event.target.value)}
                                                                className={cx("w-full rounded-[var(--radius-base)] border border-ink/20 px-3 py-2 text-sm text-ink placeholder:text-ink/40", ui.focus)}
                                                            />
                                                        </div>
                                                        <button
                                                            type="submit"
                                                            className={cx("mt-3 w-full bg-ink px-4 py-2 text-xs font-bold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light", ui.focus)}
                                                        >
                                                            Terapkan
                                                        </button>
                                                    </form>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </aside>

                                <div className="flex-1">
                                    <div className="mb-8 flex flex-col gap-6">
                                        <div className="flex items-center justify-between border-y border-ink/10 py-4 lg:hidden">
                                            <button
                                                type="button"
                                                onClick={() => setMobileFilterOpen(true)}
                                                className={cx("flex items-center gap-2 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:text-gold", ui.focus)}
                                            >
                                                <ListFilter className="h-4 w-4" />
                                                Filter
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => setMobileSortOpen(true)}
                                                className={cx("flex items-center gap-2 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:text-gold", ui.focus)}
                                            >
                                                Urutkan
                                                <ChevronDown className="h-4 w-4" />
                                            </button>
                                        </div>

                                        <div className="flex flex-col-reverse gap-4 md:flex-row md:items-end md:justify-between">
                                            <p className="text-sm font-medium text-ink-muted">
                                                Menampilkan <span className="font-bold text-ink">{productsCount}</span> karya
                                            </p>

                                            <form onSubmit={submitSearch} className="relative w-full md:max-w-sm">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    placeholder="Cari karya atau seniman..."
                                                    value={search}
                                                    onChange={(event) => setSearch(event.target.value)}
                                                    className={cx(
                                                        "w-full rounded-[var(--radius-base)] border border-ink/20 bg-transparent py-3 pl-10 pr-4 text-sm text-ink placeholder:text-ink-muted transition-colors hover:border-ink/40 focus:border-gold",
                                                        ui.focus,
                                                    )}
                                                />
                                                <Search className="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" />
                                            </form>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-3">
                                        {products.data.map((product) => {
                                            const media = product.media?.[0];
                                            const image = media?.original_url
                                                ? {
                                                      src: media.original_url,
                                                      alt: media.name ?? product.title,
                                                      width: 600,
                                                      height: 800,
                                                  }
                                                : fallbackImage;

                                            return (
                                                <Link href={`/produk/${product.slug}`} key={product.id} className="block">
                                                    <ArtworkCard
                                                        category={product.category?.name ?? "Karya Seni"}
                                                        artist={product.seller?.store_name ?? "Art Market"}
                                                        title={product.title}
                                                        price={formatPrice(product.price)}
                                                        image={image}
                                                    />
                                                </Link>
                                            );
                                        })}
                                    </div>

                                    {products.data.length === 0 && (
                                        <div className="rounded-ds-card border border-ink/10 bg-paper px-6 py-16 text-center shadow-soft">
                                            <h2 className="font-display text-2xl font-bold text-ink">Belum ada karya yang cocok</h2>
                                            <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-muted">
                                                Coba ubah kata kunci, kategori, atau rentang harga. Jika database lokal baru dibuat,
                                                isi produk bisa ditambahkan melalui seller/admin module berikutnya.
                                            </p>
                                        </div>
                                    )}

                                    {(products.prev_page_url || products.next_page_url) && (
                                        <div className="mt-12 flex items-center justify-between border-t border-ink/10 pt-6">
                                            {products.prev_page_url ? (
                                                <Link href={products.prev_page_url} className={cx("text-sm font-bold uppercase tracking-widest text-ink hover:text-gold", ui.focus)}>
                                                    Sebelumnya
                                                </Link>
                                            ) : (
                                                <span />
                                            )}
                                            {products.next_page_url ? (
                                                <Link href={products.next_page_url} className={cx("text-sm font-bold uppercase tracking-widest text-ink hover:text-gold", ui.focus)}>
                                                    Berikutnya
                                                </Link>
                                            ) : null}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </Container>
                    </Section>
                </main>
                <FooterSection />
            </div>
        </CartProvider>
    );
}
