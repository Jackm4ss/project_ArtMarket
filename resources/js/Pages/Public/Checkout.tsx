import { Link, router, useForm } from "@inertiajs/react";
import { ArrowLeft, LockKeyhole, Trash2 } from "lucide-react";
import type { FormEvent, ReactNode } from "react";

import { Button, Container, Section, cx, ui } from "@/ArtMarket/design-system";
import { CartSummary, formatCurrency } from "@/ArtMarket/commerce";
import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";

type CheckoutProps = {
    cart: CartSummary;
    checkout: {
        idempotency_key: string;
        defaults: {
            name: string;
            email: string;
            phone: string;
            address: string;
            city: string;
            province: string;
            postal_code: string;
            voucher_code: string;
            notes: string;
        };
    };
};

export default function Checkout({ cart, checkout }: CheckoutProps) {
    const { data, setData, post, processing, errors } = useForm({
        idempotency_key: checkout.idempotency_key,
        ...checkout.defaults,
    });
    const formErrors = errors as typeof errors & { cart?: string };

    const removeItem = (slug: string) => {
        router.delete(`/cart/items/${slug}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post("/checkout", {
            preserveScroll: true,
        });
    };

    if (cart.items.length === 0) {
        return (
            <ArtMarketPublicLayout title="Checkout">
                <div className="flex min-h-[70vh] items-center justify-center px-8 text-center">
                    <div>
                        <p className="mb-4 text-xl font-display text-ink-muted">Keranjang Anda kosong</p>
                        <Button href="/katalog" variant="outline">Eksplorasi Karya</Button>
                    </div>
                </div>
            </ArtMarketPublicLayout>
        );
    }

    return (
        <ArtMarketPublicLayout title="Checkout">
            <Section id="checkout">
                <Container>
                    <div className="mb-10">
                        <p className="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-gold">Pembayaran</p>
                        <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">Checkout</h1>
                        <p className="mt-4 max-w-xl text-sm leading-relaxed text-ink-muted">
                            Isi data pengiriman. Order, stok, voucher, dan invoice dibuat oleh backend dalam satu transaksi.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-12 lg:grid-cols-[1.45fr_1fr] lg:gap-16">
                        <div>
                            <h2 className="mb-6 font-display text-2xl font-bold">Ringkasan Pesanan ({cart.total_items})</h2>
                            <div className="divide-y divide-ink/8 border-y border-ink/8">
                                {cart.items.map((item) => (
                                    <article key={item.product.id} className="flex gap-5 py-6 sm:gap-6">
                                        <img
                                            src={item.product.image.src}
                                            alt={item.product.image.alt}
                                            width={96}
                                            height={96}
                                            className="h-24 w-24 rounded-[var(--radius-frame)] object-cover shadow-soft"
                                            loading="lazy"
                                        />
                                        <div className="flex min-w-0 flex-1 flex-col justify-between gap-4">
                                            <div className="flex justify-between gap-5">
                                                <div className="min-w-0">
                                                    <h3 className="font-display text-lg font-bold text-ink">{item.product.title}</h3>
                                                    <p className="mt-1 text-xs uppercase tracking-widest text-ink-muted">
                                                        {item.product.seller?.store_name ?? "Art Market"}
                                                    </p>
                                                </div>
                                                <p className="shrink-0 font-semibold text-ink">{formatCurrency(item.line_total)}</p>
                                            </div>
                                            <div className="flex items-center justify-between gap-4">
                                                <span className="text-sm text-ink-muted">Kuantitas: {item.quantity}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => removeItem(item.product.slug)}
                                                    className={cx("flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-ink-muted transition-colors hover:text-gold", ui.focus)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                            <Link
                                href="/cart"
                                className={cx("mt-6 inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted hover:text-gold", ui.focus)}
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Edit keranjang
                            </Link>
                        </div>

                        <div>
                            <div className="rounded-[var(--radius-card)] bg-surface p-6 shadow-soft sm:p-8">
                                <div className="mb-6 flex items-center justify-between gap-4">
                                    <h2 className="font-display text-2xl font-bold">Detail Pengiriman</h2>
                                    <LockKeyhole className="h-5 w-5 text-gold" />
                                </div>

                                <form onSubmit={submit} className="space-y-4">
                                    <input type="hidden" value={data.idempotency_key} name="idempotency_key" />

                                    <Field id="name" label="Nama Lengkap" error={errors.name}>
                                        <input
                                            required
                                            id="name"
                                            name="name"
                                            type="text"
                                            autoComplete="name"
                                            value={data.name}
                                            onChange={(event) => setData("name", event.target.value)}
                                            className={inputClass}
                                        />
                                    </Field>

                                    <Field id="email" label="Email" error={errors.email}>
                                        <input
                                            required
                                            id="email"
                                            name="email"
                                            type="email"
                                            autoComplete="email"
                                            value={data.email}
                                            onChange={(event) => setData("email", event.target.value)}
                                            className={inputClass}
                                        />
                                    </Field>

                                    <Field id="phone" label="Nomor Telepon" error={errors.phone}>
                                        <input
                                            required
                                            id="phone"
                                            name="phone"
                                            type="tel"
                                            autoComplete="tel"
                                            value={data.phone}
                                            onChange={(event) => setData("phone", event.target.value)}
                                            className={inputClass}
                                        />
                                    </Field>

                                    <Field id="address" label="Alamat Pengiriman" error={errors.address}>
                                        <textarea
                                            required
                                            id="address"
                                            name="address"
                                            rows={3}
                                            autoComplete="street-address"
                                            value={data.address}
                                            onChange={(event) => setData("address", event.target.value)}
                                            className={inputClass}
                                        />
                                    </Field>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <Field id="city" label="Kota" error={errors.city}>
                                            <input
                                                required
                                                id="city"
                                                name="city"
                                                type="text"
                                                autoComplete="address-level2"
                                                value={data.city}
                                                onChange={(event) => setData("city", event.target.value)}
                                                className={inputClass}
                                            />
                                        </Field>
                                        <Field id="province" label="Provinsi" error={errors.province}>
                                            <input
                                                required
                                                id="province"
                                                name="province"
                                                type="text"
                                                autoComplete="address-level1"
                                                value={data.province}
                                                onChange={(event) => setData("province", event.target.value)}
                                                className={inputClass}
                                            />
                                        </Field>
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <Field id="postal_code" label="Kode Pos" error={errors.postal_code}>
                                            <input
                                                id="postal_code"
                                                name="postal_code"
                                                type="text"
                                                autoComplete="postal-code"
                                                value={data.postal_code}
                                                onChange={(event) => setData("postal_code", event.target.value)}
                                                className={inputClass}
                                            />
                                        </Field>
                                        <Field id="voucher_code" label="Voucher" error={errors.voucher_code}>
                                            <input
                                                id="voucher_code"
                                                name="voucher_code"
                                                type="text"
                                                value={data.voucher_code}
                                                onChange={(event) => setData("voucher_code", event.target.value.toUpperCase())}
                                                className={inputClass}
                                            />
                                        </Field>
                                    </div>

                                    <Field id="notes" label="Catatan" error={errors.notes}>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows={2}
                                            value={data.notes}
                                            onChange={(event) => setData("notes", event.target.value)}
                                            className={inputClass}
                                        />
                                    </Field>

                                    {formErrors.cart ? <p className="text-sm font-medium text-red-700">{formErrors.cart}</p> : null}

                                    <div className="my-6 border-y border-ink/10 py-4">
                                        <div className="mb-2 flex justify-between">
                                            <span className="text-sm text-ink-muted">Subtotal</span>
                                            <span className="text-sm font-semibold">{formatCurrency(cart.subtotal)}</span>
                                        </div>
                                        <div className="mb-2 flex justify-between">
                                            <span className="text-sm text-ink-muted">Pengiriman</span>
                                            <span className="text-sm font-semibold">Manual</span>
                                        </div>
                                        <div className="mt-4 flex justify-between">
                                            <span className="font-display text-xl font-bold">Total</span>
                                            <span className="font-display text-xl font-bold text-gold-dark">{formatCurrency(cart.subtotal)}</span>
                                        </div>
                                    </div>

                                    <Button type="submit" variant="primary" disabled={processing || cart.has_stock_issue} className="w-full">
                                        {processing ? "Membuat Order" : "Bayar Sekarang"}
                                    </Button>
                                </form>
                            </div>
                        </div>
                    </div>
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}

const inputClass = cx("w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm text-ink placeholder:text-ink-muted", ui.focus);

function Field({
    id,
    label,
    error,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-xs font-medium uppercase tracking-widest text-ink-muted">
                {label}
            </label>
            {children}
            {error ? <p className="mt-1 text-xs font-semibold text-red-700">{error}</p> : null}
        </div>
    );
}
