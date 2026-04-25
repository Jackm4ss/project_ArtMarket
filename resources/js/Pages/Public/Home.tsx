import { Head } from "@inertiajs/react";
import { useEffect } from "react";

import { CartProvider } from "@/ArtMarket/context/CartContext";
import { defaultTheme } from "@/ArtMarket/design-system";
import { FooterSection, HeaderSection } from "@/ArtMarket/sections";
import { HomePage as LandingSections } from "@/ArtMarket/pages/HomePage";

const scrollToPendingSection = () => {
    const sectionId = sessionStorage.getItem("artmarket:pending-scroll");

    if (!sectionId) {
        return;
    }

    sessionStorage.removeItem("artmarket:pending-scroll");

    window.requestAnimationFrame(() => {
        const section = document.getElementById(sectionId);
        const header = document.querySelector("header");

        if (!section) {
            return;
        }

        const target = section.querySelector<HTMLElement>("[data-nav-anchor]") ?? section;
        const headerOffset = header instanceof HTMLElement ? header.offsetHeight : 0;
        const top = window.scrollY + target.getBoundingClientRect().top - headerOffset - 20;

        window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
    });
};

export default function Home() {
    useEffect(() => {
        scrollToPendingSection();
    }, []);

    return (
        <CartProvider>
            <Head title="Art Market Indonesia" />
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
                <main id="main-content" className="flex-1">
                    <LandingSections />
                </main>
                <FooterSection />
            </div>
        </CartProvider>
    );
}
