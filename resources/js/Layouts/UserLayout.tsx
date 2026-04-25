import { Link, usePage } from "@inertiajs/react";
import type { ReactNode } from "react";

import { ArtMarketPublicLayout } from "@/Layouts/ArtMarketPublicLayout";
import { Container, Section, cx, ui } from "@/ArtMarket/design-system";

type UserLayoutProps = {
    title: string;
    eyebrow?: string;
    children: ReactNode;
};

const navItems = [
    { href: "/user", label: "Ringkasan" },
    { href: "/user/orders", label: "Pesanan" },
    { href: "/user/wishlist", label: "Wishlist" },
    { href: "/user/addresses", label: "Alamat" },
    { href: "/user/notifications", label: "Notifikasi" },
    { href: "/user/chats", label: "Chat" },
] as const;

export function UserLayout({ title, eyebrow = "Akun Pembeli", children }: UserLayoutProps) {
    const { url } = usePage();

    return (
        <ArtMarketPublicLayout title={title}>
            <Section id="user-area">
                <Container>
                    <div className="mb-10 flex flex-col justify-between gap-6 border-b border-ink/10 pb-8 lg:flex-row lg:items-end">
                        <div>
                            <p className="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-gold">{eyebrow}</p>
                            <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">{title}</h1>
                        </div>
                        <nav className="flex gap-2 overflow-x-auto pb-2" aria-label="Navigasi akun pembeli">
                            {navItems.map((item) => {
                                const active = item.href === "/user" ? url === item.href : url.startsWith(item.href);

                                return (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={cx(
                                            "whitespace-nowrap border px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors",
                                            active
                                                ? "border-gold bg-gold text-ink"
                                                : "border-ink/15 text-ink-muted hover:border-gold hover:text-gold",
                                            ui.focus,
                                        )}
                                    >
                                        {item.label}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                    {children}
                </Container>
            </Section>
        </ArtMarketPublicLayout>
    );
}
