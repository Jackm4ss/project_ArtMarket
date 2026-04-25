import { Menu, Palette, ShoppingBag, User, X, ChevronRight } from "lucide-react";
import { Link } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { cx, ui } from "../design-system";
import { useCart } from "../context/CartContext";

// ─── Nav items ────────────────────────────────────────────────────────────────

const navItems = [
  { id: "nav-genres-link", targetId: "genres", label: "Kategori" },
  { id: "nav-gallery-link", targetId: "gallery", label: "Koleksi" },
  { id: "nav-value-link", targetId: "value", label: "Manfaat" },
  { id: "nav-about-link", targetId: "about", label: "Tentang" },
  { id: "nav-blog-link", targetId: "blog", label: "Blog" },
  { id: "nav-faq-link", targetId: "faq", label: "FAQ" },
  { id: "nav-contact-link", targetId: "footer", label: "Kontak" },
] as const;

// ─── Component ────────────────────────────────────────────────────────────────

export function HeaderSection() {
  const [scrolled, setScrolled] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { totalItems } = useCart();

  // Scroll-aware glassmorphism
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  // Lock body scroll when sidebar open
  useEffect(() => {
    document.body.style.overflow = sidebarOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [sidebarOpen]);

  const scrollToSection = (sectionId: string) => {
    setSidebarOpen(false);

    if (window.location.pathname !== "/") {
      sessionStorage.setItem("artmarket:pending-scroll", sectionId);
      window.location.assign("/");
      return;
    }

    setTimeout(() => doScroll(sectionId), 300);
  };

  const doScroll = (sectionId: string) => {
    const section = document.getElementById(sectionId);
    const header = document.querySelector("header");
    if (!section) return;
    const target = section.querySelector<HTMLElement>("[data-nav-anchor]") ?? section;
    const headerOffset = header instanceof HTMLElement ? header.offsetHeight : 0;
    const top = window.scrollY + target.getBoundingClientRect().top - headerOffset - 20;
    window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
  };

  return (
    <>
      {/* ── Header bar ──────────────────────────────────────────────────── */}
      <header
        className={cx(
          "fixed left-0 top-0 z-50 w-full transition-all duration-300",
          scrolled
            ? "border-b border-cream-deeper/40 bg-cream/70 shadow-soft backdrop-blur-md"
            : "border-b border-cream-deeper/60 bg-cream",
        )}
      >
        <nav
          className="mx-auto flex h-20 max-w-[1400px] items-center justify-between px-8 lg:px-12"
          aria-label="Navigasi utama"
        >
          {/* Logo */}
          <button
            id="nav-logo-link"
            type="button"
            onClick={() => {
              if (window.location.pathname !== "/") {
                window.location.assign("/");
                return;
              }

              window.scrollTo({ top: 0, behavior: "smooth" });
            }}
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

          {/* Desktop nav */}
          <div className="hidden items-center gap-7 lg:flex xl:gap-10">
            {navItems.map((item) => (
              <button
                key={item.id}
                id={item.id}
                type="button"
                onClick={() => scrollToSection(item.targetId)}
                className={cx(
                  "gold-line text-sm font-medium uppercase tracking-wide text-ink-muted transition-colors hover:text-ink",
                  ui.focus,
                )}
              >
                {item.label}
              </button>
            ))}
          </div>

          {/* Desktop actions */}
          <div className="hidden items-center gap-3 lg:flex">
            <Link
              href="/cart"
              id="nav-cart-btn"
              aria-label="Keranjang belanja"
              className="relative inline-flex h-10 w-10 items-center justify-center border border-ink/20 text-ink-muted transition-colors duration-200 hover:border-gold hover:text-gold"
            >
              <ShoppingBag aria-hidden="true" className="h-4 w-4" />
              {totalItems > 0 && (
                <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-bold text-ink">
                  {totalItems}
                </span>
              )}
            </Link>
            <Link
              href="/katalog"
              id="nav-buyer-btn"
              className={cx(
                "hidden items-center gap-2 border border-ink/20 px-5 py-2.5 text-sm font-medium tracking-wide transition-colors duration-300 hover:border-gold hover:text-gold sm:inline-flex",
                ui.focus,
              )}
            >
              <User aria-hidden="true" className="h-4 w-4" />
              Pembeli
            </Link>
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

          {/* Mobile: hamburger only */}
          <button
            id="nav-menu-btn"
            type="button"
            aria-label="Buka menu"
            aria-expanded={sidebarOpen}
            onClick={() => setSidebarOpen(true)}
            className={cx(
              "inline-flex h-10 w-10 items-center justify-center border border-ink/20 text-ink transition-colors hover:border-gold hover:text-gold lg:hidden",
              ui.focus,
            )}
          >
            <Menu aria-hidden="true" className="h-5 w-5" />
          </button>
        </nav>
      </header>

      {/* ── Sidebar overlay ──────────────────────────────────────────────── */}
      {/* Backdrop */}
      <div
        aria-hidden="true"
        onClick={() => setSidebarOpen(false)}
        className={cx(
          "fixed inset-0 z-[60] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
          sidebarOpen ? "opacity-100" : "pointer-events-none opacity-0",
        )}
      />

      {/* Sidebar panel */}
      <aside
        id="mobile-sidebar"
        aria-label="Menu navigasi"
        aria-hidden={!sidebarOpen}
        className={cx(
          "fixed right-0 top-0 z-[70] flex h-full w-[300px] flex-col bg-paper shadow-float transition-transform duration-300 ease-in-out lg:hidden",
          sidebarOpen ? "translate-x-0" : "translate-x-full",
        )}
      >
        {/* Sidebar header */}
        <div className="flex items-center justify-between border-b border-ink/8 px-6 py-5">
          <span className="font-display text-base font-semibold tracking-tight">Menu</span>
          <button
            type="button"
            aria-label="Tutup menu"
            onClick={() => setSidebarOpen(false)}
            className={cx(
              "inline-flex h-9 w-9 items-center justify-center border border-ink/15 text-ink transition-colors hover:border-gold hover:text-gold",
              ui.focus,
            )}
          >
            <X aria-hidden="true" className="h-4 w-4" />
          </button>
        </div>

        {/* Nav items */}
        <nav className="flex-1 overflow-y-auto">
          {navItems.map((item) => (
            <button
              key={item.id}
              id={`mobile-${item.id}`}
              type="button"
              onClick={() => scrollToSection(item.targetId)}
              className={cx(
                "flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream",
                ui.focus,
              )}
            >
              <span className="font-display text-base font-semibold text-ink">{item.label}</span>
              <ChevronRight aria-hidden="true" className="h-4 w-4 text-ink-muted" />
            </button>
          ))}
        </nav>

        {/* Sidebar CTA */}
        <div className="flex flex-col gap-3 border-t border-ink/8 p-6">
          <div className="flex gap-3">
            <Link
              href="/cart"
              onClick={() => setSidebarOpen(false)}
              id="mobile-cart-btn"
              aria-label="Keranjang belanja"
              className="relative flex h-12 w-12 flex-shrink-0 items-center justify-center border border-ink/20 text-ink-muted transition-colors duration-200 hover:border-gold hover:text-gold"
            >
              <ShoppingBag aria-hidden="true" className="h-5 w-5" />
              {totalItems > 0 && (
                <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-bold text-ink">
                  {totalItems}
                </span>
              )}
            </Link>
            <Link
              href="/katalog"
              onClick={() => setSidebarOpen(false)}
              id="mobile-buyer-btn"
              className={cx(
                "flex flex-1 items-center justify-center gap-2 border border-ink/20 py-3.5 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:border-gold hover:text-gold",
                ui.focus,
              )}
            >
              <User aria-hidden="true" className="h-4 w-4" />
              Pembeli
            </Link>
          </div>
          <button
            id="mobile-seller-btn"
            type="button"
            onClick={() => scrollToSection("cta")}
            className={cx(
              "btn-elegant flex w-full items-center justify-center gap-2 bg-ink py-3.5 text-sm font-semibold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light",
              ui.focus,
            )}
          >
            <Palette aria-hidden="true" className="h-4 w-4" />
            Jual Karya
          </button>
        </div>
      </aside>
    </>
  );
}
