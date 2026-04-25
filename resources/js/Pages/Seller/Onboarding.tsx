import { Head, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";
import type { ReactNode } from "react";
import { Store, ArrowRight } from "lucide-react";

import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";
import { Button, Container, Section, cx, ui } from "@/ArtMarket/design-system";

type StoreForm = {
    store_name: string;
    bio: string;
    location: string;
    phone: string;
    bank_name: string;
    bank_account_name: string;
    bank_account_number: string;
};

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
            <label htmlFor={id} className="text-xs font-bold uppercase tracking-[0.18em] text-ink-muted">
                {label}
            </label>
            <div className="mt-2">{children}</div>
            {error ? <p className="mt-2 text-sm font-medium text-red-700">{error}</p> : null}
        </div>
    );
}

export default function Onboarding() {
    const { data, setData, post, processing, errors } = useForm<StoreForm>({
        store_name: "",
        bio: "",
        location: "",
        phone: "",
        bank_name: "",
        bank_account_name: "",
        bank_account_number: "",
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post("/seller/onboarding", { preserveScroll: true });
    };

    const inputClass = cx(
        "w-full border border-ink/12 bg-paper px-4 py-3 text-sm text-ink outline-none transition-[border-color,box-shadow] duration-200 placeholder:text-ink-muted/70 focus:border-gold focus:shadow-soft",
        ui.focus,
    );

    return (
        <ArtMarketPublicLayout title="Buka Toko Gratis">
            <Head title="Buka Toko Gratis" />
            <Section className="bg-cream" compact>
                <Container>
                    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
                        <aside className="rounded-[var(--radius-card)] border border-gold/25 bg-[radial-gradient(circle_at_top_right,rgba(var(--color-accent),0.22),transparent_34%),linear-gradient(135deg,rgba(var(--color-surface),0.95),rgba(var(--color-paper),0.98))] p-8 shadow-soft">
                            <div className="grid h-14 w-14 place-items-center bg-paper text-gold-dark shadow-soft">
                                <Store aria-hidden="true" className="h-7 w-7" />
                            </div>
                            <p className="mt-8 text-xs font-bold uppercase tracking-[0.22em] text-gold-dark">Seller Art Market</p>
                            <h1 className="mt-3 font-display text-4xl font-bold leading-tight text-ink lg:text-5xl">
                                Buka Toko Gratis
                            </h1>
                            <p className="mt-5 text-sm leading-7 text-ink-muted">
                                Lengkapi profil toko untuk mulai mengelola karya, menerima chat pembeli, dan masuk ke dashboard seller.
                            </p>
                            <div className="mt-8 space-y-3 text-sm text-ink-muted">
                                <p className="border-l-2 border-gold pl-4">Produk seller akan otomatis aktif setelah diunggah.</p>
                                <p className="border-l-2 border-gold pl-4">Payout dan rekening tetap bisa dilengkapi bertahap.</p>
                                <p className="border-l-2 border-gold pl-4">Admin tetap dapat memoderasi konten yang melanggar aturan.</p>
                            </div>
                        </aside>

                        <form onSubmit={submit} className="rounded-[var(--radius-card)] border border-ink/8 bg-paper p-6 shadow-soft lg:p-8">
                            <div className="grid gap-5 md:grid-cols-2">
                                <Field id="store_name" label="Nama Toko" error={errors.store_name}>
                                    <input
                                        id="store_name"
                                        name="store_name"
                                        value={data.store_name}
                                        onChange={(event) => setData("store_name", event.target.value)}
                                        className={inputClass}
                                        autoComplete="organization"
                                        required
                                    />
                                </Field>

                                <Field id="location" label="Lokasi" error={errors.location}>
                                    <input
                                        id="location"
                                        name="location"
                                        value={data.location}
                                        onChange={(event) => setData("location", event.target.value)}
                                        className={inputClass}
                                        autoComplete="address-level2"
                                        placeholder="Contoh: Bandung"
                                    />
                                </Field>
                            </div>

                            <div className="mt-5 grid gap-5 md:grid-cols-2">
                                <Field id="phone" label="Nomor Telepon" error={errors.phone}>
                                    <input
                                        id="phone"
                                        name="phone"
                                        value={data.phone}
                                        onChange={(event) => setData("phone", event.target.value)}
                                        className={inputClass}
                                        autoComplete="tel"
                                        placeholder="08xxxxxxxxxx"
                                    />
                                </Field>

                                <Field id="bank_name" label="Bank Payout" error={errors.bank_name}>
                                    <input
                                        id="bank_name"
                                        name="bank_name"
                                        value={data.bank_name}
                                        onChange={(event) => setData("bank_name", event.target.value)}
                                        className={inputClass}
                                        autoComplete="off"
                                        placeholder="BCA / Mandiri / BNI"
                                    />
                                </Field>
                            </div>

                            <div className="mt-5 grid gap-5 md:grid-cols-2">
                                <Field id="bank_account_name" label="Nama Rekening" error={errors.bank_account_name}>
                                    <input
                                        id="bank_account_name"
                                        name="bank_account_name"
                                        value={data.bank_account_name}
                                        onChange={(event) => setData("bank_account_name", event.target.value)}
                                        className={inputClass}
                                        autoComplete="off"
                                    />
                                </Field>

                                <Field id="bank_account_number" label="Nomor Rekening" error={errors.bank_account_number}>
                                    <input
                                        id="bank_account_number"
                                        name="bank_account_number"
                                        value={data.bank_account_number}
                                        onChange={(event) => setData("bank_account_number", event.target.value)}
                                        className={inputClass}
                                        autoComplete="off"
                                    />
                                </Field>
                            </div>

                            <div className="mt-5">
                                <Field id="bio" label="Bio Toko" error={errors.bio}>
                                    <textarea
                                        id="bio"
                                        name="bio"
                                        value={data.bio}
                                        onChange={(event) => setData("bio", event.target.value)}
                                        className={cx(inputClass, "min-h-32 resize-y leading-7")}
                                        placeholder="Ceritakan karakter karya, studio, atau kurasi toko Anda."
                                    />
                                </Field>
                            </div>

                            <div className="mt-8 flex flex-col gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p className="text-xs leading-relaxed text-ink-muted">
                                    Dengan membuka toko, Anda menyetujui aturan seller dan moderasi marketplace Art Market.
                                </p>
                                <Button type="submit" icon={ArrowRight} disabled={processing} className="shrink-0">
                                    {processing ? "Membuat Toko" : "Buka Toko Gratis"}
                                </Button>
                            </div>
                        </form>
                    </div>
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
