import { Link, router } from "@inertiajs/react";
import { Trash2 } from "lucide-react";

import { ArtworkCard, Button, cx, ui } from "@/ArtMarket/design-system";
import { formatCurrency, ProductSummary } from "@/ArtMarket/commerce";
import { UserLayout } from "@/Layouts/UserLayout";
import { Pagination } from "./Orders";

type WishlistItem = {
    id: number;
    product: ProductSummary | null;
    created_at?: string | null;
};

type WishlistProps = {
    wishlist: {
        data: WishlistItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

export default function Wishlist({ wishlist }: WishlistProps) {
    const remove = (slug: string) => {
        router.delete(`/user/wishlist/${slug}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <UserLayout title="Wishlist Saya">
            {wishlist.data.length === 0 ? (
                <div className="rounded-[var(--radius-card)] bg-paper px-6 py-16 text-center shadow-soft">
                    <p className="font-display text-3xl font-bold text-ink">Wishlist masih kosong</p>
                    <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-muted">Simpan karya yang kamu suka agar mudah ditemukan lagi.</p>
                    <Button href="/katalog" variant="primary" className="mt-6">Jelajahi Katalog</Button>
                </div>
            ) : (
                <section className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                    <div className="grid grid-cols-2 gap-5 md:grid-cols-3 xl:grid-cols-4">
                        {wishlist.data.map((item) => item.product ? (
                            <article key={item.id} className="relative">
                                <Link href={`/produk/${item.product.slug}`} className="block">
                                    <ArtworkCard
                                        category={item.product.category?.name ?? "Karya Seni"}
                                        artist={item.product.seller?.store_name ?? "Art Market"}
                                        title={item.product.title}
                                        price={formatCurrency(item.product.price)}
                                        image={item.product.image}
                                    />
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => remove(item.product!.slug)}
                                    className={cx("absolute right-3 top-3 flex h-9 w-9 items-center justify-center bg-paper text-ink shadow-soft transition hover:bg-gold", ui.focus)}
                                    aria-label={`Hapus ${item.product.title} dari wishlist`}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </article>
                        ) : null)}
                    </div>
                    <Pagination links={wishlist.links} />
                </section>
            )}
        </UserLayout>
    );
}
