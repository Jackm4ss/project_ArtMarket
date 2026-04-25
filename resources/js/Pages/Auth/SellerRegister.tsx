import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

type SellerRegisterProps = {
    referralCode?: string | null;
    referrerName?: string | null;
};

export default function SellerRegister({ referralCode, referrerName }: SellerRegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        store_name: "",
        bio: "",
        location: "",
        phone: "",
        bank_name: "",
        bank_account_name: "",
        bank_account_number: "",
        referral_code: referralCode ?? "",
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route("seller.register.store"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    return (
        <GuestLayout>
            <Head title="Daftar Seller" />

            <div className="mb-6">
                <p className="text-sm font-semibold uppercase tracking-[0.25em] text-gold-dark">Seller</p>
                <h1 className="mt-2 font-display text-3xl font-semibold text-ink">Daftar sebagai seller</h1>
                <p className="mt-2 text-sm leading-relaxed text-gray-600">
                    Buat akun seller, profil toko, dan rekening payout dalam satu alur. Jika memakai kode referral,
                    sistem akan mencatat referral pending untuk ditinjau admin.
                </p>
            </div>

            {referrerName ? (
                <div className="mb-6 rounded-ds-card border border-gold/30 bg-gold/10 px-4 py-3 text-sm text-ink">
                    Kode referral terdeteksi dari <strong>{referrerName}</strong>.
                </div>
            ) : null}

            <form onSubmit={submit} className="space-y-5">
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="name" value="Nama Penanggung Jawab" />
                        <TextInput
                            id="name"
                            name="name"
                            value={data.name}
                            className="mt-1 block w-full"
                            autoComplete="name"
                            isFocused
                            onChange={(event) => setData("name", event.target.value)}
                            required
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            autoComplete="username"
                            onChange={(event) => setData("email", event.target.value)}
                            required
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            onChange={(event) => setData("password", event.target.value)}
                            required
                        />
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password_confirmation" value="Konfirmasi Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            onChange={(event) => setData("password_confirmation", event.target.value)}
                            required
                        />
                        <InputError message={errors.password_confirmation} className="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel htmlFor="store_name" value="Nama Toko" />
                    <TextInput
                        id="store_name"
                        name="store_name"
                        value={data.store_name}
                        className="mt-1 block w-full"
                        autoComplete="organization"
                        onChange={(event) => setData("store_name", event.target.value)}
                        required
                    />
                    <InputError message={errors.store_name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="bio" value="Bio Toko" />
                    <textarea
                        id="bio"
                        name="bio"
                        value={data.bio}
                        onChange={(event) => setData("bio", event.target.value)}
                        className="mt-1 block min-h-24 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError message={errors.bio} className="mt-2" />
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="location" value="Lokasi" />
                        <TextInput
                            id="location"
                            name="location"
                            value={data.location}
                            className="mt-1 block w-full"
                            autoComplete="address-level2"
                            onChange={(event) => setData("location", event.target.value)}
                        />
                        <InputError message={errors.location} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="phone" value="Nomor Telepon" />
                        <TextInput
                            id="phone"
                            name="phone"
                            value={data.phone}
                            className="mt-1 block w-full"
                            autoComplete="tel"
                            onChange={(event) => setData("phone", event.target.value)}
                        />
                        <InputError message={errors.phone} className="mt-2" />
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <div>
                        <InputLabel htmlFor="bank_name" value="Bank" />
                        <TextInput
                            id="bank_name"
                            name="bank_name"
                            value={data.bank_name}
                            className="mt-1 block w-full"
                            autoComplete="off"
                            onChange={(event) => setData("bank_name", event.target.value)}
                        />
                        <InputError message={errors.bank_name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="bank_account_name" value="Nama Rekening" />
                        <TextInput
                            id="bank_account_name"
                            name="bank_account_name"
                            value={data.bank_account_name}
                            className="mt-1 block w-full"
                            autoComplete="off"
                            onChange={(event) => setData("bank_account_name", event.target.value)}
                        />
                        <InputError message={errors.bank_account_name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="bank_account_number" value="Nomor Rekening" />
                        <TextInput
                            id="bank_account_number"
                            name="bank_account_number"
                            value={data.bank_account_number}
                            className="mt-1 block w-full"
                            autoComplete="off"
                            onChange={(event) => setData("bank_account_number", event.target.value)}
                        />
                        <InputError message={errors.bank_account_number} className="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel htmlFor="referral_code" value="Kode Referral Seller" />
                    <TextInput
                        id="referral_code"
                        name="referral_code"
                        value={data.referral_code}
                        className="mt-1 block w-full"
                        autoComplete="off"
                        onChange={(event) => setData("referral_code", event.target.value)}
                    />
                    <InputError message={errors.referral_code} className="mt-2" />
                </div>

                <div className="flex items-center justify-between gap-4">
                    <Link href={route("login")} className="text-sm text-gray-600 underline hover:text-gray-900">
                        Sudah punya akun?
                    </Link>

                    <PrimaryButton disabled={processing}>Daftar Seller</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
