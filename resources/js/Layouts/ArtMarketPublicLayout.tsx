import { Head } from "@inertiajs/react";
import type { ReactNode } from "react";

import { CartProvider } from "@/ArtMarket/context/CartContext";
import { defaultTheme, cx } from "@/ArtMarket/design-system";
import { FooterSection, HeaderSection } from "@/ArtMarket/sections";

type ArtMarketPublicLayoutProps = {
    title: string;
    children: ReactNode;
    mainClassName?: string;
};

export function ArtMarketPublicLayout({ title, children, mainClassName }: ArtMarketPublicLayoutProps) {
    return (
        <CartProvider>
            <Head title={title} />
            <div
                data-theme={defaultTheme}
                className="flex min-h-screen flex-col overflow-x-hidden bg-cream font-body text-ink"
            >
                <a
                    href="#main-content"
                    className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[10000] focus:bg-ink focus:px-4 focus:py-2 focus:text-cream"
                >
                    Lewati ke konten utama
                </a>
                <div className="grain-overlay" />
                <HeaderSection />
                <main id="main-content" className={cx("flex-1 pt-20", mainClassName)}>
                    {children}
                </main>
                <FooterSection />
            </div>
        </CartProvider>
    );
}
