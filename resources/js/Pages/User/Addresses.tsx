import { router, useForm } from "@inertiajs/react";
import { MapPin, Trash2 } from "lucide-react";
import type { FormEvent, ReactNode } from "react";

import { Button, cx, ui } from "@/ArtMarket/design-system";
import { UserLayout } from "@/Layouts/UserLayout";

type Address = {
    id: number;
    label: string;
    recipient_name: string;
    phone: string;
    province: string;
    city: string;
    district?: string | null;
    postal_code?: string | null;
    address_line: string;
    is_default: boolean;
};

type AddressesProps = {
    addresses: Address[];
};

export default function Addresses({ addresses }: AddressesProps) {
    const { data, setData, post, processing, reset, errors } = useForm({
        label: "Rumah",
        recipient_name: "",
        phone: "",
        province: "",
        city: "",
        district: "",
        postal_code: "",
        address_line: "",
        is_default: addresses.length === 0,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post("/user/addresses", {
            preserveScroll: true,
            onSuccess: () => reset("recipient_name", "phone", "province", "city", "district", "postal_code", "address_line"),
        });
    };

    const setDefault = (address: Address) => {
        router.patch(`/user/addresses/${address.id}`, { ...address, is_default: true }, { preserveScroll: true });
    };

    const destroy = (address: Address) => {
        router.delete(`/user/addresses/${address.id}`, { preserveScroll: true });
    };

    return (
        <UserLayout title="Alamat Pengiriman">
            <div className="grid gap-8 lg:grid-cols-[1fr_0.9fr]">
                <section className="rounded-[var(--radius-card)] bg-paper p-6 shadow-soft sm:p-8">
                    <h2 className="font-display text-2xl font-bold text-ink">Daftar Alamat</h2>
                    <div className="mt-6 space-y-4">
                        {addresses.length === 0 ? (
                            <div className="rounded-[var(--radius-card)] border border-ink/10 bg-cream px-6 py-10 text-center text-ink-muted">
                                Belum ada alamat tersimpan.
                            </div>
                        ) : addresses.map((address) => (
                            <article key={address.id} className="rounded-[var(--radius-card)] border border-ink/10 bg-cream p-5">
                                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                                    <div>
                                        <div className="mb-3 flex flex-wrap items-center gap-2">
                                            <span className="bg-ink px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-cream">{address.label}</span>
                                            {address.is_default ? <span className="bg-gold px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink">Default</span> : null}
                                        </div>
                                        <h3 className="font-display text-xl font-bold text-ink">{address.recipient_name}</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                                            {address.phone}<br />
                                            {address.address_line}<br />
                                            {[address.district, address.city, address.province, address.postal_code].filter(Boolean).join(", ")}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        {!address.is_default ? (
                                            <button type="button" onClick={() => setDefault(address)} className={cx("border border-ink/15 px-3 py-2 text-xs font-bold uppercase tracking-widest text-ink-muted hover:border-gold hover:text-gold", ui.focus)}>
                                                Jadikan Default
                                            </button>
                                        ) : null}
                                        <button type="button" onClick={() => destroy(address)} className={cx("flex h-9 w-9 items-center justify-center border border-ink/15 text-ink-muted hover:border-gold hover:text-gold", ui.focus)} aria-label={`Hapus alamat ${address.label}`}>
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <aside className="rounded-[var(--radius-card)] bg-surface p-6 shadow-soft sm:p-8 lg:self-start">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="flex h-11 w-11 items-center justify-center border border-gold/40 text-gold-dark">
                            <MapPin className="h-5 w-5" />
                        </div>
                        <h2 className="font-display text-2xl font-bold text-ink">Tambah Alamat</h2>
                    </div>
                    <form onSubmit={submit} className="space-y-4">
                        <Field id="label" label="Label" error={errors.label}>
                            <input id="label" name="label" value={data.label} onChange={(event) => setData("label", event.target.value)} className={inputClass} />
                        </Field>
                        <Field id="recipient_name" label="Nama Penerima" error={errors.recipient_name}>
                            <input id="recipient_name" name="recipient_name" value={data.recipient_name} onChange={(event) => setData("recipient_name", event.target.value)} className={inputClass} autoComplete="name" />
                        </Field>
                        <Field id="phone" label="Telepon" error={errors.phone}>
                            <input id="phone" name="phone" value={data.phone} onChange={(event) => setData("phone", event.target.value)} className={inputClass} autoComplete="tel" />
                        </Field>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field id="province" label="Provinsi" error={errors.province}>
                                <input id="province" name="province" value={data.province} onChange={(event) => setData("province", event.target.value)} className={inputClass} autoComplete="address-level1" />
                            </Field>
                            <Field id="city" label="Kota" error={errors.city}>
                                <input id="city" name="city" value={data.city} onChange={(event) => setData("city", event.target.value)} className={inputClass} autoComplete="address-level2" />
                            </Field>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field id="district" label="Kecamatan" error={errors.district}>
                                <input id="district" name="district" value={data.district} onChange={(event) => setData("district", event.target.value)} className={inputClass} />
                            </Field>
                            <Field id="postal_code" label="Kode Pos" error={errors.postal_code}>
                                <input id="postal_code" name="postal_code" value={data.postal_code} onChange={(event) => setData("postal_code", event.target.value)} className={inputClass} autoComplete="postal-code" />
                            </Field>
                        </div>
                        <Field id="address_line" label="Alamat Lengkap" error={errors.address_line}>
                            <textarea id="address_line" name="address_line" rows={3} value={data.address_line} onChange={(event) => setData("address_line", event.target.value)} className={inputClass} autoComplete="street-address" />
                        </Field>
                        <label className="flex items-center gap-3 text-sm font-medium text-ink">
                            <input type="checkbox" checked={data.is_default} onChange={(event) => setData("is_default", event.target.checked)} className="border-ink/20 text-gold focus:ring-gold" />
                            Jadikan alamat utama
                        </label>
                        <Button type="submit" variant="primary" disabled={processing} className="w-full">
                            {processing ? "Menyimpan" : "Simpan Alamat"}
                        </Button>
                    </form>
                </aside>
            </div>
        </UserLayout>
    );
}

const inputClass = cx("w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm text-ink placeholder:text-ink-muted", ui.focus);

function Field({ id, label, error, children }: { id: string; label: string; error?: string; children: ReactNode }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-xs font-medium uppercase tracking-widest text-ink-muted">{label}</label>
            {children}
            {error ? <p className="mt-1 text-xs font-semibold text-red-700">{error}</p> : null}
        </div>
    );
}
