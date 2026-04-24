import { Palette, ShoppingBag, User } from "lucide-react";
import { useEffect, useState } from "react";
import { cx, ui } from "../design-system";

const navItems = [
  { id: "nav-genres-link", targetId: "genres", label: "Kategori" },
  { id: "nav-gallery-link", targetId: "gallery", label: "Koleksi" },
  { id: "nav-value-link", targetId: "value", label: "Manfaat" },
  { id: "nav-about-link", targetId: "about", label: "Tentang" },
  { id: "nav-contact-link", targetId: "footer", label: "Kontak" },
] as const;

export function HeaderSection() {
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const scrollToSection = (sectionId: string) => {
    const section = document.getElementById(sectionId);
    const header = document.querySelector("header");

    if (!section) {
      return;
    }

    const target = section.querySelector<HTMLElement>("[data-nav-anchor]") ?? section;
    const headerOffset = header instanceof HTMLElement ? header.offsetHeight : 0;
    const top = window.scrollY + target.getBoundingClientRect().top - headerOffset - 20;

    window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
  };

  return (
    <header
      className={cx(
        "fixed left-0 top-0 z-50 w-full transition-all duration-300",
        scrolled
          ? // Scrolled: glassmorphism — tinted cream bg + blur + border
            "border-b border-cream-deeper/40 bg-cream/70 shadow-soft backdrop-blur-md"
          : // Top: solid original bg
            "border-b border-cream-deeper/60 bg-cream",
      )}
    >
      <nav className="mx-auto flex h-20 max-w-[1400px] items-center justify-between px-8 lg:px-12" aria-label="Navigasi utama">
        <button
          id="nav-logo-link"
          type="button"
          onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
          className={cx("group flex items-center gap-3", ui.focus)}
        >
          <img
            src="/logo-artmarket.png"
            alt=""
            aria-hidden="true"
            className="h-9 w-auto object-contain"
          />
          <span className="font-display text-xl font-semibold tracking-tight">Art Market</span>
        </button>

        <div className="hidden items-center gap-7 lg:flex xl:gap-10">
          {navItems.map((item) => (
            <button
              key={item.id}
              id={item.id}
              type="button"
              onClick={() => scrollToSection(item.targetId)}
              className={cx("gold-line text-sm font-medium uppercase tracking-wide text-ink-muted transition-colors hover:text-ink", ui.focus)}
            >
              {item.label}
            </button>
          ))}
        </div>

        <div className="flex items-center gap-3">
          <button
            id="nav-cart-btn"
            type="button"
            aria-label="Keranjang belanja"
            className="relative inline-flex h-10 w-10 items-center justify-center border border-ink/20 text-ink-muted transition-colors duration-200 hover:border-gold hover:text-gold"
          >
            <ShoppingBag aria-hidden="true" className="h-4 w-4" />
          </button>
          <button
            id="nav-buyer-btn"
            type="button"
            onClick={() => scrollToSection("gallery")}
            className={cx(
              "hidden items-center gap-2 border border-ink/20 px-5 py-2.5 text-sm font-medium tracking-wide transition-colors duration-300 hover:border-gold hover:text-gold sm:inline-flex",
              ui.focus,
            )}
          >
            <User aria-hidden="true" className="h-4 w-4" />
            Pembeli
          </button>
          <button
            id="nav-seller-btn"
            type="button"
            onClick={() => scrollToSection("cta")}
            className={cx(
              "btn-elegant inline-flex items-center gap-2 bg-ink px-5 py-2.5 text-sm font-medium tracking-wide text-cream transition-colors duration-300 hover:bg-ink-light",
              ui.focus,
            )}
          >
            <Palette aria-hidden="true" className="h-4 w-4" />
            Jual Karya
          </button>
        </div>
      </nav>
    </header>
  );
}
